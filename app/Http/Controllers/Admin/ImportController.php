<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessImportJob;
use App\Models\Import;
use App\Models\ImportTemplate;
use App\Services\Import\DuplicateDetector;
use App\Services\Import\ImportProcessor;
use App\Services\Import\ImportService;
use App\Services\Import\MappingEngine;
use App\Services\Import\ValidationEngine;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class ImportController extends Controller
{
    public function index(Request $request)
    {
        $imports = Import::where('company_id', $this->companyId())
            ->with('user')
            ->latest()
            ->paginate(15);

        return view('admin.imports.index', compact('imports'));
    }

    public function create()
    {
        $templates = ImportTemplate::where('company_id', $this->companyId())->get();

        return view('admin.imports.create', compact('templates'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:51200'],
            'template_id' => ['nullable', 'integer'],
        ]);

        $service = app(ImportService::class);
        try {
            $result = $service->uploadFile($request->file('file'), $this->companyId());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['file' => $e->getMessage()])->withInput();
        }

        $detection = $service->detectDestination($result['columns']);

        $import = Import::create([
            'company_id' => $this->companyId(),
            'user_id' => auth()->id(),
            'file_name' => $result['file_name'],
            'file_path' => $result['file_path'],
            'file_type' => $result['file_type'],
            'file_size' => $result['file_size'],
            'destination' => $detection['destination'],
            'status' => 'uploaded',
            'total_rows' => $result['total_rows'],
            'settings' => ['preview' => $result['preview'], 'all_rows' => $result['all_rows'], 'columns' => $result['columns']],
        ]);

        $mappingEngine = new MappingEngine;
        $autoMappings = $mappingEngine->autoMapColumns($result['columns'], $detection['destination']);

        $templateId = $request->input('template_id');
        if ($templateId) {
            $template = ImportTemplate::where('company_id', $this->companyId())->findOrFail((int) $templateId);
            $autoMappings = $this->applyTemplateMapping($template->column_mapping ?? [], $autoMappings);
        }

        Session::put('import_id', $import->id);
        Session::put('import_rows', $result['all_rows']);

        $destFields = $mappingEngine->getDestinationFields($detection['destination']);
        $fieldLabels = array_map(fn ($aliases) => ucfirst(str_replace('_', ' ', array_key_exists(0, $aliases) ? $aliases[0] : $aliases)), $destFields);

        return view('admin.imports.map', [
            'import' => $import,
            'columns' => $result['columns'],
            'preview' => $result['preview'],
            'detection' => $detection,
            'mappings' => $autoMappings,
            'fieldLabels' => $fieldLabels,
            'allDestinations' => $this->getDestinations(),
        ]);
    }

    public function saveMapping(Request $request)
    {
        $request->validate([
            'import_id' => 'required|exists:imports,id',
            'destination' => 'required|string',
            'mappings' => 'required|array',
        ]);

        $import = Import::findOrFail($request->import_id);
        $this->authorizeAccess($import);

        $import->update([
            'destination' => $request->destination,
            'column_mapping' => $request->mappings,
        ]);

        $rows = Session::get('import_rows', []);
        $validator = new ValidationEngine;
        $validation = $validator->validateRows($import->destination, $rows, $request->mappings);

        $detector = new DuplicateDetector;
        $duplicates = $detector->detect($rows, $request->mappings, $import->destination, $this->companyId());

        $errorCount = 0;
        $warningCount = 0;
        $dupeCount = 0;
        foreach ($validation['errors'] as $errs) {
            $errorCount += count($errs);
        }
        foreach ($validation['warnings'] as $warns) {
            $warningCount += count($warns);
        }
        foreach ($duplicates as $dupes) {
            $dupeCount += count($dupes);
        }

        return view('admin.imports.preview', [
            'import' => $import,
            'rows' => array_slice($rows, 0, 50),
            'mappings' => $request->mappings,
            'validation' => $validation,
            'duplicates' => $duplicates,
            'stats' => [
                'total' => count($rows),
                'errors' => $errorCount,
                'warnings' => $warningCount,
                'duplicates' => $dupeCount,
            ],
        ]);
    }

    public function execute(Request $request)
    {
        $request->validate([
            'import_id' => 'required|exists:imports,id',
            'duplicate_strategy' => 'required|in:skip,update',
        ]);

        $import = Import::findOrFail($request->import_id);
        $this->authorizeAccess($import);

        $import->update([
            'duplicate_strategy' => $request->duplicate_strategy,
        ]);

        $rows = Session::get('import_rows', []);
        $mappings = $import->column_mapping;

        if (config('venturex.queue_heavy_tasks', false)) {
            $import->update(['status' => 'queued']);
            ProcessImportJob::dispatch($import->id, $rows, (array) $mappings, $this->companyId());

            return view('admin.imports.results', [
                'import' => $import->fresh(),
                'results' => ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []],
                'queued' => true,
            ]);
        }

        $processor = new ImportProcessor;
        $results = $processor->execute($import, $rows, $mappings, $this->companyId());

        return view('admin.imports.results', [
            'import' => $import,
            'results' => $results,
            'queued' => false,
        ]);
    }

    public function history(Request $request)
    {
        $imports = Import::where('company_id', $this->companyId())
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('admin.imports.history', compact('imports'));
    }

    public function show(Import $import)
    {
        $this->authorizeAccess($import);
        $rows = $import->rows()->orderBy('row_number')->paginate(50);

        return view('admin.imports.show', compact('import', 'rows'));
    }

    public function destroy(Import $import)
    {
        $this->authorizeAccess($import);
        $import->delete();

        return redirect()->route('admin.imports.index')->with('success', 'Import deleted.');
    }

    public function templateStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'destination' => 'required|string',
            'column_mapping' => 'required|array',
        ]);

        ImportTemplate::create([
            'company_id' => $this->companyId(),
            'user_id' => auth()->id(),
            'name' => $request->name,
            'destination' => $request->destination,
            'column_mapping' => $request->column_mapping,
        ]);

        return redirect()->back()->with('success', 'Template saved.');
    }

    public function templateDestroy(ImportTemplate $template)
    {
        abort_unless($template->company_id === $this->companyId(), 403);
        $template->delete();

        return redirect()->back()->with('success', 'Template deleted.');
    }

    public function errorReport(Import $import)
    {
        $this->authorizeAccess($import);
        $failedRows = $import->rows()->whereIn('status', ['failed', 'skipped'])->get();

        $csv = "Row,Status,Errors\n";
        foreach ($failedRows as $row) {
            $csv .= "{$row->row_number},{$row->status},\"".str_replace('"', '""', $row->errors ?? '')."\"\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"import-errors-{$import->id}.csv\"",
        ]);
    }

    public function templateDownload(string $destination): Response
    {
        abort_unless(array_key_exists($destination, $this->getDestinations()), 404);

        $headers = $this->templateHeaders($destination);
        $samples = $this->templateSamples($destination);
        $lines = [$this->csvLine($headers)];

        foreach ($samples as $sample) {
            $lines[] = $this->csvLine(array_map(fn ($header) => $sample[$header] ?? '', $headers));
        }

        $csv = "\xEF\xBB\xBF".implode("\r\n", $lines)."\r\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.str_replace('_', '-', $destination).'-import-template.csv"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Saved template fields take precedence over auto-mapping for the columns
     * they define. Columns not covered by the template keep their auto-map.
     */
    protected function applyTemplateMapping(array $saved, array $autoMappings): array
    {
        $byColumn = [];
        foreach ($saved as $mapping) {
            if (is_array($mapping) && isset($mapping['column'])) {
                $byColumn[(string) $mapping['column']] = $mapping;
            } elseif (is_string($mapping) && $mapping !== '') {
                // Accept a simple ["column" => "field"] template shape too.
                $byColumn[$mapping] = ['column' => $mapping, 'field' => $mapping];
            }
        }

        foreach ($autoMappings as $index => $mapping) {
            if (! isset($byColumn[$mapping['column']])) {
                continue;
            }

            $field = $byColumn[$mapping['column']]['field'] ?? null;
            $field = is_string($field) && $field !== '' ? $field : null;

            $autoMappings[$index]['field'] = $field;
            $autoMappings[$index]['confidence'] = $field !== null ? 100 : 0;
            $autoMappings[$index]['status'] = $field !== null ? 'mapped' : 'ignored';
        }

        return $autoMappings;
    }

    protected function templateHeaders(string $destination): array
    {
        return match ($destination) {
            'customers' => ['customer_code', 'name', 'email', 'phone', 'address', 'city', 'state', 'country', 'postal_code', 'tax_id', 'contact_person', 'notes'],
            'suppliers' => ['supplier_code', 'name', 'email', 'phone', 'address', 'city', 'state', 'country', 'postal_code', 'tax_id', 'contact_person', 'notes'],
            'leads' => ['company_name', 'contact_name', 'email', 'phone', 'source', 'status', 'value', 'notes'],
            'products' => ['name', 'sku', 'sell_price', 'cost_price', 'category', 'unit', 'tax_rate', 'warehouse_code', 'opening_quantity', 'unit_cost', 'valuation_date', 'min_stock', 'max_stock', 'notes'],
            'opening_balances' => ['account_code', 'as_of_date', 'debit', 'credit', 'currency_code', 'reference', 'description'],
            default => ['name', 'email', 'phone', 'notes'],
        };
    }

    protected function templateSamples(string $destination): array
    {
        return match ($destination) {
            'customers' => [[
                'customer_code' => 'C1001', 'name' => 'Sample Customer Ltd', 'email' => 'billing@example.com',
                'phone' => '+10000000000', 'city' => 'Sample City', 'country' => 'US', 'notes' => 'Replace this row with your data.',
            ]],
            'suppliers' => [[
                'supplier_code' => 'S1001', 'name' => 'Sample Supplier Ltd', 'email' => 'supplier@example.com',
                'phone' => '+10000000001', 'city' => 'Sample City', 'country' => 'US', 'notes' => 'Replace this row with your data.',
            ]],
            'leads' => [[
                'company_name' => 'Sample Prospect Ltd', 'contact_name' => 'Sample Contact',
                'email' => 'contact@example.com', 'phone' => '+10000000002', 'source' => 'Website',
                'status' => 'new', 'value' => '1000', 'notes' => 'Replace this row with your data.',
            ]],
            'products' => [[
                'name' => 'Sample Product', 'sku' => 'SKU-1001', 'sell_price' => '25.00',
                'cost_price' => '15.00', 'category' => 'General', 'unit' => 'pcs', 'tax_rate' => '0',
                'warehouse_code' => 'MAIN', 'opening_quantity' => '100', 'unit_cost' => '15.00',
                'valuation_date' => now()->toDateString(), 'min_stock' => '10', 'notes' => 'Replace this row with your data.',
            ]],
            'opening_balances' => [
                [
                    'account_code' => 'CASH', 'as_of_date' => now()->toDateString(), 'debit' => '10000.00',
                    'credit' => '', 'currency_code' => 'USD', 'reference' => 'OPENING-1', 'description' => 'Opening cash balance',
                ],
                [
                    'account_code' => 'EQUITY', 'as_of_date' => now()->toDateString(), 'debit' => '',
                    'credit' => '10000.00', 'currency_code' => 'USD', 'reference' => 'OPENING-1', 'description' => 'Opening equity balance',
                ],
            ],
            default => [],
        };
    }

    protected function csvLine(array $values): string
    {
        return implode(',', array_map(function ($value): string {
            $value = (string) $value;

            // Prevent spreadsheet formula injection from template content.
            if (preg_match('/^[=+\-@]/', $value) === 1) {
                $value = "'".$value;
            }

            return '"'.str_replace('"', '""', $value).'"';
        }, $values));
    }

    protected function companyId(): int
    {
        return session('company_id') ?? auth()->user()->company_id ?? 0;
    }

    protected function authorizeAccess(Import $import): void
    {
        abort_if($import->company_id !== $this->companyId(), 403);
    }

    protected function getDestinations(): array
    {
        return [
            'customers' => 'Customers',
            'suppliers' => 'Suppliers',
            'products' => 'Products / Inventory',
            'opening_balances' => 'Opening Balances',
            'leads' => 'Leads',
            'invoices' => 'Invoices',
            'payments' => 'Payments',
            'employees' => 'Employees',
            'contacts' => 'Contacts',
            'opportunities' => 'Opportunities',
            'purchase_orders' => 'Purchase Orders',
            'quotations' => 'Quotations',
            'sales_orders' => 'Sales Orders',
        ];
    }
}

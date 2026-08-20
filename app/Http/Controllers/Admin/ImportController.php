<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Import;
use App\Models\ImportTemplate;
use App\Services\Import\DuplicateDetector;
use App\Services\Import\ImportProcessor;
use App\Services\Import\ImportService;
use App\Services\Import\MappingEngine;
use App\Services\Import\ValidationEngine;
use Illuminate\Http\Request;
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
            'file' => 'required|file|max:51200|mimes:csv,json,txt',
        ]);

        $service = new ImportService;
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

        $processor = new ImportProcessor;
        $results = $processor->execute($import, $rows, $mappings, $this->companyId());

        return view('admin.imports.results', [
            'import' => $import,
            'results' => $results,
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
            'products' => 'Products',
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

<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\ExportRequest;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Shipment;
use App\Models\SalesOrder;
use App\Models\Supplier;
use App\Models\SupplierOffer;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExportService
{
    protected const SOURCES = [
        'customers' => ['model' => Customer::class, 'columns' => ['name', 'customer_code', 'tax_id', 'email', 'phone', 'city', 'state', 'status']],
        'suppliers' => ['model' => Supplier::class, 'columns' => ['name', 'supplier_code', 'tax_id', 'contact_person', 'email', 'phone', 'city', 'status']],
        'leads' => ['model' => Lead::class, 'columns' => ['company_name', 'contact_name', 'email', 'phone', 'source', 'status']],
        'opportunities' => ['model' => Opportunity::class, 'columns' => ['name', 'customer_id', 'expected_value', 'stage', 'probability']],
        'offers' => ['model' => SupplierOffer::class, 'columns' => ['supplier_id', 'material_category', 'grade', 'quantity_mt', 'price_per_mt', 'quality_status', 'risk_level']],
        'orders' => ['model' => SalesOrder::class, 'columns' => ['order_number', 'status', 'payment_status', 'order_date', 'delivery_date', 'subtotal', 'discount', 'tax', 'shipping', 'total', 'paid_amount', 'notes']],
        'quotations' => ['model' => Quotation::class, 'columns' => ['quotation_number', 'status', 'currency_code', 'subtotal', 'discount', 'tax', 'total', 'valid_until', 'notes']],
        'invoices' => ['model' => Invoice::class, 'columns' => ['invoice_number', 'status', 'issue_date', 'due_date', 'subtotal', 'discount', 'tax', 'total', 'paid_amount', 'notes']],
        'payments' => ['model' => Payment::class, 'columns' => ['payment_number', 'payment_date', 'amount', 'method', 'reference', 'status', 'notes']],
        'products' => ['model' => Product::class, 'columns' => ['sku', 'name', 'category', 'description', 'purchase_price', 'selling_price', 'reorder_level', 'status', 'notes']],
        'shipments' => ['model' => Shipment::class, 'columns' => ['shipment_number', 'type', 'mode', 'carrier', 'tracking_number', 'origin', 'destination', 'status', 'departure_date', 'arrival_date', 'delivered_at', 'weight', 'volume', 'notes']],
        'users' => ['model' => User::class, 'columns' => ['name', 'first_name', 'last_name', 'email', 'phone', 'job_title', 'timezone', 'is_active', 'email_verified_at', 'last_login_at']],
    ];

    /** PII patterns that should be redacted regardless of column name. */
    protected const PII_PATTERNS = [
        'credit_card' => '/\b(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|3[47][0-9]{13}|6(?:011|5[0-9]{2})[0-9]{12})\b/',
        'ssn' => '/\b\d{3}-\d{2}-\d{4}\b/',
        'pan' => '/\b[A-Z]{5}\d{4}[A-Z]\b/',  // Indian PAN
        'aadhaar' => '/\b\d{4}\s?\d{4}\s?\d{4}\b/',  // 12-digit Aadhaar
        'iban' => '/\b[A-Z]{2}\d{2}[A-Z0-9]{4,30}\b/',
    ];

    public function sources(): array
    {
        return array_keys(static::SOURCES);
    }

    public function requiresApproval(string $dataType): bool
    {
        return in_array($dataType, (array) config('security.export.require_approval', []), true);
    }

    public function createRequest(User $user, string $dataType, string $reason, string $format = 'csv'): ExportRequest
    {
        $needsApproval = $this->requiresApproval($dataType);

        return ExportRequest::create([
            'export_id' => strtoupper(Str::random(12)),
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'data_type' => $dataType,
            'reason' => $reason ?: null,
            'format' => in_array($format, ['csv', 'xlsx'], true) ? $format : 'csv',
            'sensitivity' => $needsApproval ? 'restricted' : 'internal',
            'status' => $needsApproval ? 'pending' : 'ready',
            'expires_at' => now()->addMinutes((int) config('security.export.signed_url_minutes', 30)),
            'max_downloads' => (int) config('security.export.max_downloads', 3),
            'ip' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 500),
        ]);
    }

    public function generate(ExportRequest $export): ExportRequest
    {
        $source = static::SOURCES[$export->data_type] ?? null;

        if (! $source) {
            $export->update(['status' => 'failed']);
            SecurityEventService::record('export', 'export_failed', "Export {$export->export_id} failed: unknown data type", 'medium');

            return $export;
        }

        try {
            $rows = $this->rows($source, $export);
        } catch (\Throwable $e) {
            report($e);
            $export->update(['status' => 'failed']);
            SecurityEventService::record('export', 'export_failed', "Export {$export->export_id} failed", 'medium');

            return $export;
        }

        $name = 'exports/'.$export->export_id.'/'.$export->export_id.'.csv';
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, array_map(fn ($c) => str_replace('_', ' ', ucfirst($c)), $source['columns']));

        foreach ($rows as $row) {
            fputcsv($handle, array_map(fn ($c) => $this->redact($export->data_type, $c, $row[$c] ?? null), $source['columns']));
        }

        rewind($handle);
        Storage::disk('local')->put($name, stream_get_contents($handle));
        fclose($handle);

        $export->update([
            'storage_path' => $name,
            'record_count' => count($rows),
            'status' => 'ready',
        ]);

        return $export;
    }

    public function approve(ExportRequest $export, User $approver): void
    {
        $export->update([
            'status' => 'ready',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }

    public function reject(ExportRequest $export, User $approver): void
    {
        $export->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }

    public function signedDownloadUrl(ExportRequest $export): ?string
    {
        if ($export->status !== 'ready' || ! $export->storage_path) {
            return null;
        }

        if ($export->expires_at && $export->expires_at->isPast()) {
            return null;
        }

        if ($export->download_count >= $export->max_downloads) {
            return null;
        }

        return Storage::disk('local')->temporaryUrl($export->storage_path, now()->addMinutes((int) config('security.export.signed_url_minutes', 30)));
    }

    protected function rows(array $source, ExportRequest $export): array
    {
        $model = $source['model'];
        $columns = $source['columns'];

        $query = $model::query()->select($columns);

        if (method_exists($model, 'scopeOfCompany')) {
            $query->ofCompany($export->company_id);
        }

        return $query
            ->limit((int) config('security.export.max_records_per_export', 25000))
            ->get()
            ->map(fn ($row) => $row->toArray())
            ->all();
    }

    /**
     * Strip fields that must never leave the system AND scan for PII patterns
     * in cell values regardless of column name.
     */
    protected function redact(string $dataType, string $column, mixed $value): mixed
    {
        $never = (array) config('security.export.never_export', []);

        foreach ($never as $pattern) {
            if (str_contains(strtolower($column), strtolower($pattern))) {
                return '[REDACTED]';
            }
        }

        if ($value === null || $value === '') {
            return $value;
        }

        $stringValue = (string) $value;

        foreach (self::PII_PATTERNS as $piiType => $regex) {
            if (preg_match($regex, $stringValue)) {
                SecurityEventService::record('export', 'pii_detected', "PII pattern '{$piiType}' detected in column '{$column}' during export of '{$dataType}'", 'high');

                return '[REDACTED-PII]';
            }
        }

        return $value;
    }
}

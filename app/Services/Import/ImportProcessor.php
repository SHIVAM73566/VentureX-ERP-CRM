<?php

namespace App\Services\Import;

use App\Models\Customer;
use App\Models\Import;
use App\Models\ImportRow;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Facades\Log;

class ImportProcessor
{
    protected ValidationEngine $validator;

    protected DuplicateDetector $detector;

    public function __construct()
    {
        $this->validator = new ValidationEngine;
        $this->detector = new DuplicateDetector;
    }

    public function execute(Import $import, array $rows, array $mappings, int $companyId): array
    {
        $import->update(['status' => 'processing', 'started_at' => now()]);
        $results = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []];
        $batchSize = 100;

        $chunks = array_chunk($rows, $batchSize);
        $processed = 0;

        foreach ($chunks as $chunk) {
            foreach ($chunk as $index => $row) {
                $mapped = $this->validator->mapRow($row, $mappings);
                $rowNum = $processed + 1;

                if (empty($mapped)) {
                    $results['skipped']++;
                    $this->saveRow($import, $rowNum, $row, null, 'skipped', 'No mappable data');
                    $processed++;

                    continue;
                }

                try {
                    $result = $this->importRecord($import->destination, $mapped, $companyId, $import->duplicate_strategy);
                    $results[$result['status']]++;
                    $this->saveRow($import, $rowNum, $row, $result['id'] ?? null, $result['status'], $result['message'] ?? null);
                } catch (\Throwable $e) {
                    $results['failed']++;
                    $results['errors'][] = ['row' => $rowNum, 'error' => $e->getMessage()];
                    $this->saveRow($import, $rowNum, $row, null, 'failed', $e->getMessage());
                    Log::warning('Import row failed', ['import_id' => $import->id, 'row' => $rowNum, 'error' => $e->getMessage()]);
                }

                $processed++;
            }

            $import->update([
                'processed_rows' => $processed,
                'created_rows' => $results['created'],
                'updated_rows' => $results['updated'],
                'skipped_rows' => $results['skipped'],
                'failed_rows' => $results['failed'],
            ]);
        }

        $status = $results['failed'] > 0 ? 'completed_with_errors' : 'completed';
        $import->update([
            'status' => $status,
            'completed_at' => now(),
            'error_summary' => ! empty($results['errors']) ? json_encode($results['errors']) : null,
        ]);

        return $results;
    }

    protected function importRecord(string $destination, array $data, int $companyId, string $duplicateStrategy): array
    {
        return match ($destination) {
            'customers' => $this->importCustomer($data, $companyId, $duplicateStrategy),
            'suppliers' => $this->importSupplier($data, $companyId, $duplicateStrategy),
            'products' => $this->importProduct($data, $companyId, $duplicateStrategy),
            'leads' => $this->importLead($data, $companyId, $duplicateStrategy),
            default => ['status' => 'skipped', 'message' => "Import for [{$destination}] is not yet implemented."],
        };
    }

    protected function importCustomer(array $data, int $companyId, string $strategy): array
    {
        $existing = null;
        if (($data['email'] ?? '') !== '') {
            $existing = Customer::where('company_id', $companyId)->where('email', $data['email'])->first();
        }
        if (! $existing && ($data['phone'] ?? '') !== '') {
            $existing = Customer::where('company_id', $companyId)->where('phone', $data['phone'])->first();
        }

        if ($existing) {
            return match ($strategy) {
                'skip' => ['status' => 'skipped', 'message' => 'Duplicate customer: '.($data['email'] ?? $data['phone'] ?? '')],
                'update' => $this->updateRecord($existing, $data),
                default => ['status' => 'skipped', 'message' => 'Duplicate detected, using skip strategy'],
            };
        }

        $data['company_id'] = $companyId;
        $data['is_active'] = true;
        $customer = Customer::create($this->cleanData($data, Customer::class));

        return ['status' => 'created', 'id' => $customer->id, 'message' => null];
    }

    protected function importSupplier(array $data, int $companyId, string $strategy): array
    {
        $existing = null;
        if (($data['email'] ?? '') !== '') {
            $existing = Supplier::where('company_id', $companyId)->where('email', $data['email'])->first();
        }

        if ($existing) {
            return match ($strategy) {
                'skip' => ['status' => 'skipped', 'message' => 'Duplicate supplier'],
                'update' => $this->updateRecord($existing, $data),
                default => ['status' => 'skipped', 'message' => 'Duplicate detected'],
            };
        }

        $data['company_id'] = $companyId;
        $data['is_active'] = true;
        $supplier = Supplier::create($this->cleanData($data, Supplier::class));

        return ['status' => 'created', 'id' => $supplier->id, 'message' => null];
    }

    protected function importProduct(array $data, int $companyId, string $strategy): array
    {
        $existing = null;
        if (($data['sku'] ?? '') !== '') {
            $existing = Product::where('company_id', $companyId)->where('sku', $data['sku'])->first();
        }

        if ($existing) {
            return match ($strategy) {
                'skip' => ['status' => 'skipped', 'message' => 'Duplicate product'],
                'update' => $this->updateRecord($existing, $data),
                default => ['status' => 'skipped', 'message' => 'Duplicate detected'],
            };
        }

        $data['company_id'] = $companyId;
        $data['is_active'] = true;
        $product = Product::create($this->cleanData($data, Product::class));

        return ['status' => 'created', 'id' => $product->id, 'message' => null];
    }

    protected function importLead(array $data, int $companyId, string $strategy): array
    {
        $existing = null;
        if (($data['email'] ?? '') !== '') {
            $existing = Lead::where('company_id', $companyId)->where('email', $data['email'])->first();
        }

        if ($existing) {
            return match ($strategy) {
                'skip' => ['status' => 'skipped', 'message' => 'Duplicate lead'],
                'update' => $this->updateRecord($existing, $data),
                default => ['status' => 'skipped', 'message' => 'Duplicate detected'],
            };
        }

        $data['company_id'] = $companyId;
        $data['status'] = $data['status'] ?? 'new';
        $lead = Lead::create($this->cleanData($data, Lead::class));

        return ['status' => 'created', 'id' => $lead->id, 'message' => null];
    }

    protected function updateRecord($model, array $data): array
    {
        $cleaned = $this->cleanData($data, get_class($model));
        $model->forceFill($cleaned)->save();

        return ['status' => 'updated', 'id' => $model->id, 'message' => null];
    }

    protected function cleanData(array $data, string $modelClass): array
    {
        $instance = new $modelClass;
        $fillable = $instance->getFillable();

        return array_filter($data, fn ($key) => in_array($key, $fillable), ARRAY_FILTER_USE_KEY);
    }

    protected function saveRow(Import $import, int $rowNum, array $raw, ?int $recordId, string $status, ?string $message): void
    {
        ImportRow::create([
            'import_id' => $import->id,
            'row_number' => $rowNum,
            'raw_data' => $raw,
            'status' => $status,
            'errors' => $message,
            'imported_record_id' => $recordId,
            'imported_record_type' => match ($import->destination) {
                'customers' => Customer::class,
                'suppliers' => Supplier::class,
                'products' => Product::class,
                'leads' => Lead::class,
                default => null,
            },
        ]);
    }
}

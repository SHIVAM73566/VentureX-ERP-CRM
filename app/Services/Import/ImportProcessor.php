<?php

namespace App\Services\Import;

use App\Models\Customer;
use App\Models\Import;
use App\Models\ImportRow;
use App\Models\JournalEntry;
use App\Models\Lead;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
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

        if ($import->destination === 'opening_balances') {
            return $this->executeOpeningBalances($import, $rows, $mappings, $companyId);
        }

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

                // Rows are revalidated at execute time, not only during the
                // preview step. A stale session mapping cannot bypass rules.
                $validation = $this->validator->validateRow($import->destination, $mapped);
                if ($validation['errors'] !== []) {
                    $message = implode(' ', $validation['errors']);
                    $results['failed']++;
                    $results['errors'][] = ['row' => $rowNum, 'error' => $message];
                    $this->saveRow($import, $rowNum, $row, null, 'failed', $message);
                    $processed++;

                    continue;
                }

                try {
                    $result = DB::transaction(fn () => $this->importRecord(
                        $import->destination,
                        $mapped,
                        $companyId,
                        $import->duplicate_strategy
                    ));
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

    /**
     * Opening balances are all-or-nothing: the service creates one balanced
     * journal or posts nothing at all.
     */
    protected function executeOpeningBalances(Import $import, array $rows, array $mappings, int $companyId): array
    {
        $result = (new OpeningBalanceImporter($this->validator))->process($import, $rows, $mappings, $companyId);
        $statuses = $result['statuses'] ?? [];
        $failure = $result['errors'][0]['error'] ?? 'Opening balance import failed.';

        foreach ($rows as $index => $row) {
            $status = $statuses[$index] ?? ['status' => 'failed', 'id' => null, 'message' => $failure];
            $this->saveRow(
                $import,
                $index + 1,
                $row,
                $status['id'] ?? null,
                $status['status'],
                $status['message'] ?? null
            );
        }

        unset($result['statuses']);

        $import->update([
            'processed_rows' => count($rows),
            'created_rows' => $result['created'],
            'updated_rows' => $result['updated'],
            'skipped_rows' => $result['skipped'],
            'failed_rows' => $result['failed'],
            'status' => $result['failed'] > 0 ? 'completed_with_errors' : 'completed',
            'completed_at' => now(),
            'error_summary' => $result['errors'] !== [] ? json_encode($result['errors']) : null,
        ]);

        return $result;
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
        // Opening-stock columns are import-only; they create a stock movement
        // and are never written to the product table.
        $openingQuantity = $data['opening_quantity'] ?? null;
        $unitCost = $data['unit_cost'] ?? null;
        $warehouseCode = $data['warehouse_code'] ?? null;
        $valuationDate = $data['valuation_date'] ?? null;

        $existing = null;
        if (($data['sku'] ?? '') !== '') {
            $existing = Product::where('company_id', $companyId)->where('sku', $data['sku'])->first();
        }

        if ($existing) {
            $result = match ($strategy) {
                'skip' => ['status' => 'skipped', 'message' => 'Duplicate product'],
                'update' => $this->updateRecord($existing, $data),
                default => ['status' => 'skipped', 'message' => 'Duplicate detected'],
            };

            if ($result['status'] === 'skipped') {
                return $result;
            }

            $product = $existing;
        } else {
            $data['company_id'] = $companyId;
            $data['is_active'] = true;
            $product = Product::create($this->cleanData($data, Product::class));
            $result = ['status' => 'created', 'id' => $product->id, 'message' => null];
        }

        $stockMessage = $this->createOpeningStock(
            $product,
            $companyId,
            $openingQuantity,
            $unitCost,
            $warehouseCode,
            $valuationDate
        );

        if ($stockMessage !== null) {
            $result['message'] = $result['message'] === null
                ? $stockMessage
                : $result['message'].' '.$stockMessage;
        }

        return $result;
    }

    protected function createOpeningStock(
        Product $product,
        int $companyId,
        mixed $quantity,
        mixed $unitCost,
        mixed $warehouseCode,
        mixed $valuationDate
    ): ?string {
        if ($quantity === null || (float) $quantity == 0.0) {
            return null;
        }

        $warehouse = null;
        if ($warehouseCode !== null && trim((string) $warehouseCode) !== '') {
            $warehouse = Warehouse::ofCompany($companyId)
                ->where('code', $warehouseCode)
                ->first();
        }
        $warehouse ??= Warehouse::ofCompany($companyId)->where('is_default', true)->first();

        if (! $warehouse) {
            return 'Opening stock skipped: no matching warehouse was found.';
        }

        StockMovement::create([
            'company_id' => $companyId,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'in',
            'quantity' => (float) $quantity,
            'unit_cost' => $unitCost === null ? $product->purchase_price : (float) $unitCost,
            'reference_type' => 'opening_balance',
            'note' => 'Opening stock import'.($valuationDate !== null ? ' (valuation '.$valuationDate.')' : ''),
            'created_by' => auth()->id(),
        ]);

        return null;
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
                'opening_balances' => JournalEntry::class,
                default => null,
            },
        ]);
    }
}

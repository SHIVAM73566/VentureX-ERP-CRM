<?php

namespace App\Services\Import;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Supplier;

class DuplicateDetector
{
    public function detect(array $rows, array $mappings, string $destination, int $companyId): array
    {
        $model = $this->getModelClass($destination);
        if ($model === null) {
            return array_fill_keys(array_keys($rows), []);
        }

        $uniqueFields = $this->getUniqueFields($destination);
        $existing = $this->loadExistingValues($model, $uniqueFields, $companyId);
        $duplicates = [];

        foreach ($rows as $index => $row) {
            $rowDupes = [];
            foreach ($mappings as $m) {
                if (! in_array($m['field'] ?? '', $uniqueFields)) {
                    continue;
                }
                $value = strtolower(trim($row[$m['column']] ?? ''));
                if ($value !== '' && isset($existing[$m['field']][$value])) {
                    $rowDupes[] = [
                        'field' => $m['field'],
                        'value' => $value,
                        'existing_id' => $existing[$m['field']][$value],
                    ];
                }
            }
            $duplicates[$index] = $rowDupes;
        }

        return $duplicates;
    }

    protected function getModelClass(string $destination): ?string
    {
        return match ($destination) {
            'customers' => Customer::class,
            'suppliers' => Supplier::class,
            'leads' => Lead::class,
            default => null,
        };
    }

    protected function getUniqueFields(string $destination): array
    {
        return match ($destination) {
            'customers' => ['email', 'phone', 'customer_code', 'tax_id'],
            'suppliers' => ['email', 'phone', 'supplier_code', 'tax_id'],
            'products' => ['sku'],
            'leads' => ['email'],
            default => [],
        };
    }

    protected function loadExistingValues(string $model, array $fields, int $companyId): array
    {
        $existing = [];
        foreach ($fields as $field) {
            $dbColumn = match ($field) {
                'customer_code' => 'customer_code',
                'supplier_code' => 'supplier_code',
                default => $field,
            };

            $query = $model::where('company_id', $companyId)
                ->whereNotNull($dbColumn)
                ->where($dbColumn, '!=', '')
                ->pluck('id', $dbColumn);

            $existing[$field] = [];
            foreach ($query as $val => $id) {
                $existing[$field][strtolower(trim((string) $val))] = $id;
            }
        }

        return $existing;
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Import;

class ValidationEngine
{
    /**
     * @return array{errors: array<int, array<int, string>>, warnings: array<int, array<int, string>>}
     */
    public function validateRows(string $destination, array $rows, array $mappings): array
    {
        $errors = [];
        $warnings = [];

        foreach ($rows as $index => $row) {
            $mapped = $this->mapRow($row, $mappings);
            $result = $this->validateRow($destination, $mapped);

            $errors[$index] = $result['errors'];
            $warnings[$index] = $result['warnings'];
        }

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Execute-time validation. Callers must revalidate mapped rows immediately
     * before writing so a stale session mapping cannot bypass the rules.
     *
     * @return array{errors: array<int, string>, warnings: array<int, string>}
     */
    public function validateRow(string $destination, array $mapped): array
    {
        $errors = [];
        $warnings = [];

        foreach ($this->getRequiredFields($destination) as $field) {
            if ($this->isBlank($mapped[$field] ?? null)) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)).' is required.';
            }
        }

        if (! $this->isBlank($mapped['email'] ?? null) && ! filter_var($mapped['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        }

        if (! $this->isBlank($mapped['phone'] ?? null) && preg_match('/[^0-9+\-\s()]/', (string) $mapped['phone'])) {
            $warnings[] = 'Phone number contains unusual characters.';
        }

        $numericFields = match ($destination) {
            'products' => ['sell_price', 'cost_price', 'unit_cost', 'opening_quantity', 'min_stock', 'max_stock', 'tax_rate'],
            'opening_balances' => ['debit', 'credit'],
            'payments' => ['amount'],
            'invoices' => ['total'],
            'leads' => ['value'],
            default => [],
        };

        foreach ($numericFields as $field) {
            $value = $mapped[$field] ?? null;
            if (! $this->isBlank($value) && ! is_numeric($value)) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)).' must be a number.';
            }
        }

        if ($destination === 'opening_balances') {
            $debit = $this->amount($mapped['debit'] ?? null);
            $credit = $this->amount($mapped['credit'] ?? null);

            if ($debit > 0 && $credit > 0) {
                $errors[] = 'Use either debit or credit, not both.';
            }
            if ($debit <= 0 && $credit <= 0) {
                $errors[] = 'A debit or credit amount is required.';
            }

            $date = $mapped['as_of_date'] ?? null;
            if (! $this->isBlank($date) && ! $this->isDate($date)) {
                $errors[] = 'As of date is invalid.';
            }

            $currency = $mapped['currency_code'] ?? null;
            if (! $this->isBlank($currency) && preg_match('/^[A-Za-z]{3}$/', (string) $currency) !== 1) {
                $errors[] = 'Currency code must be a three-letter ISO code.';
            }
        }

        if ($destination === 'products' && ! $this->isBlank($mapped['valuation_date'] ?? null) && ! $this->isDate($mapped['valuation_date'])) {
            $errors[] = 'Valuation date is invalid.';
        }

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    public function mapRow(array $row, array $mappings): array
    {
        $mapped = [];
        foreach ($mappings as $m) {
            if (empty($m['field']) || ($m['status'] ?? '') === 'ignored') {
                continue;
            }
            $value = $row[$m['column']] ?? null;
            $mapped[$m['field']] = $this->transformValue($value, $m['field']);
        }

        return $mapped;
    }

    public function transformValue(?string $value, string $field): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim($value);

        if (in_array($field, ['email', 'phone', 'tax_id', 'sku', 'invoice_number', 'account_code', 'warehouse_code', 'currency_code', 'reference'], true)) {
            return $value;
        }

        if (str_contains($field, 'date') && $value !== '') {
            return $this->parseDate($value);
        }

        if (in_array($field, [
            'sell_price', 'cost_price', 'total', 'amount', 'salary', 'value', 'tax_rate',
            'min_stock', 'max_stock', 'unit_cost', 'opening_quantity', 'debit', 'credit',
        ], true)) {
            $cleaned = preg_replace('/[^0-9.\-]/', '', $value);

            return is_numeric($cleaned) ? (string) $cleaned : $value;
        }

        return $value;
    }

    protected function parseDate(string $value): string
    {
        $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-M-Y', 'M d, Y', 'd.m.Y'];
        foreach ($formats as $format) {
            $parsed = \DateTime::createFromFormat($format, $value);
            if ($parsed !== false) {
                return $parsed->format('Y-m-d');
            }
        }

        return $value;
    }

    protected function isDate(mixed $value): bool
    {
        if ($this->isBlank($value)) {
            return false;
        }

        return strtotime((string) $value) !== false;
    }

    protected function isBlank(mixed $value): bool
    {
        return $value === null || trim((string) $value) === '';
    }

    protected function amount(mixed $value): float
    {
        return $this->isBlank($value) ? 0.0 : (float) $value;
    }

    public function getRequiredFields(string $destination): array
    {
        return match ($destination) {
            'customers' => ['name'],
            'suppliers' => ['name'],
            'products' => ['name'],
            'leads' => ['contact_name'],
            'invoices' => ['invoice_number'],
            'payments' => ['amount'],
            'employees' => ['name'],
            'opening_balances' => ['account_code', 'as_of_date'],
            default => ['name'],
        };
    }
}

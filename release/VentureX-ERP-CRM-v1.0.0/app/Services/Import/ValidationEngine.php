<?php

namespace App\Services\Import;

class ValidationEngine
{
    public function validateRows(string $destination, array $rows, array $mappings): array
    {
        $errors = [];
        $warnings = [];
        $required = $this->getRequiredFields($destination);

        foreach ($rows as $index => $row) {
            $rowErrors = [];
            $rowWarnings = [];
            $mapped = $this->mapRow($row, $mappings);

            foreach ($required as $field) {
                if (empty($mapped[$field])) {
                    $rowErrors[] = ucfirst(str_replace('_', ' ', $field)).' is required.';
                }
            }

            if (! empty($mapped['email']) && ! filter_var($mapped['email'], FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = 'Invalid email address.';
            }

            if (isset($mapped['phone']) && $mapped['phone'] !== '' && preg_match('/[^0-9+\-\s()]/', (string) $mapped['phone'])) {
                $rowWarnings[] = 'Phone number contains unusual characters.';
            }

            $errors[$index] = $rowErrors;
            $warnings[$index] = $rowWarnings;
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

        if (in_array($field, ['email', 'phone', 'tax_id', 'sku', 'invoice_number'])) {
            return $value;
        }

        if (str_contains($field, 'date') && $value !== '') {
            return $this->parseDate($value);
        }

        if (in_array($field, ['sell_price', 'cost_price', 'total', 'amount', 'salary', 'value', 'tax_rate', 'min_stock', 'max_stock'])) {
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
            default => ['name'],
        };
    }
}

<?php

namespace App\Services\Import;

use Illuminate\Http\UploadedFile;

class ImportService
{
    public function uploadFile(UploadedFile $file, int $companyId): array
    {
        $allowed = ['csv', 'json', 'txt'];
        $ext = strtolower($file->getClientOriginalExtension());

        if (! in_array($ext, $allowed)) {
            throw new \InvalidArgumentException("File type [{$ext}] is not supported. Allowed: ".implode(', ', $allowed));
        }

        $path = $file->store("imports/{$companyId}", 'local');
        $rows = $this->parseFromContent($file->getContent(), $ext);

        return [
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $ext,
            'file_size' => $file->getSize(),
            'columns' => ! empty($rows) ? array_keys($rows[0]) : [],
            'total_rows' => count($rows),
            'preview' => array_slice($rows, 0, 5),
            'all_rows' => $rows,
        ];
    }

    public function parseFromContent(string $content, string $ext): array
    {
        return match ($ext) {
            'csv', 'txt' => $this->parseCsvContent($content),
            'json' => $this->parseJsonContent($content),
            default => [],
        };
    }

    public function parseFile(string $filePath, string $ext): array
    {
        return match ($ext) {
            'csv', 'txt' => $this->parseCsv($filePath),
            'json' => $this->parseJson($filePath),
            default => [],
        };
    }

    protected function parseCsvContent(string $content): array
    {
        $lines = explode("\n", trim($content));
        if (count($lines) < 2) {
            return [];
        }

        $delimiters = [',', ';', "\t"];
        $sample = $lines[0];
        $bestDelim = ',';
        $bestCount = 0;
        foreach ($delimiters as $d) {
            $c = substr_count($sample, $d);
            if ($c > $bestCount) {
                $bestCount = $c;
                $bestDelim = $d;
            }
        }

        $handle = fopen('php://memory', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        $headers = fgetcsv($handle, 0, $bestDelim);
        if ($headers === false) {
            fclose($handle);

            return [];
        }

        $headers = array_map(fn ($h) => trim(str_replace(["\xC2\xA0", '"'], '', $h)), $headers);
        $rows = [];

        while (($data = fgetcsv($handle, 0, $bestDelim)) !== false) {
            if (count($data) === count($headers)) {
                $rows[] = array_combine($headers, $data);
            }
        }

        fclose($handle);

        return $rows;
    }

    protected function parseJsonContent(string $content): array
    {
        $data = json_decode($content, true);
        if (! is_array($data)) {
            return [];
        }

        return isset($data[0]) && is_array($data[0]) ? $data : [$data];
    }

    protected function parseCsv(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return [];
        }

        return $this->parseCsvContent($content);
    }

    protected function parseJson(string $filePath): array
    {
        $content = file_get_contents($filePath);

        return $this->parseJsonContent($content);
    }

    public function detectDestination(array $columns): array
    {
        $colStr = strtolower(implode(' ', $columns));

        $patterns = [
            'customers' => ['customer', 'client', 'buyer', 'billing', 'shipping'],
            'suppliers' => ['supplier', 'vendor', 'seller', 'procurement'],
            'products' => ['product', 'item', 'sku', 'inventory', 'stock', 'price'],
            'leads' => ['lead', 'prospect', 'funnel'],
            'invoices' => ['invoice', 'bill', 'amount due', 'total due'],
            'payments' => ['payment', 'paid', 'transaction', 'receipt'],
            'employees' => ['employee', 'staff', 'department', 'salary', 'hire_date'],
            'contacts' => ['contact', 'person', 'mobile', 'telephone'],
            'opportunities' => ['opportunity', 'deal', 'pipeline', 'stage'],
            'purchase_orders' => ['purchase order', 'po number', 'po#'],
            'quotations' => ['quotation', 'quote', 'estimate'],
            'sales_orders' => ['sales order', 'so number', 'so#'],
        ];

        $scores = [];
        foreach ($patterns as $entity => $keywords) {
            $score = 0;
            foreach ($keywords as $kw) {
                if (str_contains($colStr, $kw)) {
                    $score += 20;
                }
            }
            if ($score > 0) {
                $scores[$entity] = $score;
            }
        }

        arsort($scores);
        $best = array_key_first($scores) ?? 'customers';

        return [
            'destination' => $best,
            'confidence' => min(($scores[$best] ?? 0) + 40, 99),
            'alternatives' => array_slice(array_keys($scores), 1, 3),
        ];
    }
}

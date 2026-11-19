<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Services\UploadSecurityService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;
use Throwable;
use ZipArchive;

/**
 * @author Shivam Chaturvedi <chaturvedishivam179@gmail.com>
 */
class ImportService
{
    public const ALLOWED_EXTENSIONS = ['csv', 'xlsx'];

    public const MAX_BYTES = 52428800; // 50 MB

    public function __construct(protected XlsxReader $xlsx) {}

    public function uploadFile(UploadedFile $file, int $companyId): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $this->assertAllowed($file, $ext);

        // The local disk resolves to storage/app/private, never public/.
        $path = $file->store("imports/{$companyId}", 'local');
        $absolutePath = Storage::disk('local')->path($path);

        try {
            $rows = $this->parseFile($absolutePath, $ext);
        } catch (Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

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

    /**
     * Extension, MIME type and magic bytes must all agree. A renamed .exe or a
     * JSON/TXT upload is rejected even if Laravel's mimes rule was bypassed.
     */
    public function assertAllowed(UploadedFile $file, string $ext): void
    {
        if (! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            throw new InvalidArgumentException('Only .csv and .xlsx files are supported.');
        }

        $size = $file->getSize();
        if ($size !== null && $size > self::MAX_BYTES) {
            throw new InvalidArgumentException('The import file must be 50 MB or smaller.');
        }

        // Reuse the application's upload security service when it is available.
        // Its signature table checks .xlsx magic bytes and rejects embedded PHP
        // in text-like files such as .csv. The importer's own 50 MB limit is
        // applied temporarily so its narrower policy is not weakened here.
        if (class_exists(UploadSecurityService::class)) {
            $previousMax = config('security.upload.max_size_kb');
            config()->set('security.upload.max_size_kb', (int) ceil(self::MAX_BYTES / 1024));

            try {
                app(UploadSecurityService::class)->validate($file, $ext);
            } catch (ValidationException) {
                throw new InvalidArgumentException('The uploaded file failed security validation.');
            } catch (Throwable) {
                throw new InvalidArgumentException('The uploaded file could not be validated.');
            } finally {
                config()->set('security.upload.max_size_kb', $previousMax);
            }
        }

        $content = (string) @file_get_contents($file->getRealPath() ?: '');
        $mime = strtolower((string) $file->getMimeType());

        if ($ext === 'csv') {
            $allowedMimes = ['text/csv', 'application/csv', 'text/plain', 'application/vnd.ms-excel', 'application/octet-stream'];
            if (! in_array($mime, $allowedMimes, true)) {
                throw new InvalidArgumentException('The uploaded file is not a valid CSV file.');
            }

            if (preg_match('/^(PK\x03\x04|%PDF-|\x89PNG|\xFF\xD8\xFF|GIF8)/', $content) === 1
                || str_contains(substr($content, 0, 512), "\0")) {
                throw new InvalidArgumentException('The uploaded file is not a valid CSV file.');
            }

            return;
        }

        $allowedMimes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
            'application/octet-stream',
        ];
        if (! in_array($mime, $allowedMimes, true) || ! str_starts_with($content, 'PK')) {
            throw new InvalidArgumentException('The uploaded file is not a valid XLSX workbook.');
        }

        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('XLSX imports require the zip PHP extension.');
        }

        $zip = new ZipArchive;
        if ($zip->open($file->getRealPath() ?: '') !== true) {
            throw new InvalidArgumentException('The uploaded file is not a valid XLSX workbook.');
        }

        $hasWorkbook = $zip->locateName('xl/workbook.xml') !== false;
        $zip->close();

        if (! $hasWorkbook) {
            throw new InvalidArgumentException('The uploaded file is not a valid XLSX workbook.');
        }
    }

    public function parseFromContent(string $content, string $ext): array
    {
        return match ($ext) {
            'csv' => $this->parseCsvContent($content),
            default => [],
        };
    }

    public function parseFile(string $filePath, string $ext): array
    {
        return match ($ext) {
            'csv' => $this->parseCsv($filePath),
            'xlsx' => $this->xlsx->read($filePath),
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

    protected function parseCsv(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return [];
        }

        return $this->parseCsvContent($content);
    }

    public function detectDestination(array $columns): array
    {
        $colStr = strtolower(implode(' ', $columns));

        $patterns = [
            'customers' => ['customer', 'client', 'buyer', 'billing', 'shipping'],
            'suppliers' => ['supplier', 'vendor', 'seller', 'procurement'],
            'opening_balances' => ['opening balance', 'account_code', 'chart of account', 'as_of_date', 'debit', 'credit'],
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

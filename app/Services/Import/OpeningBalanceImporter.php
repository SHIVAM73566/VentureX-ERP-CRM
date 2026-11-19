<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Models\Account;
use App\Models\Import;
use App\Models\JournalEntry;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

/**
 * Imports opening balances as one balanced, posted journal entry.
 *
 * Every row resolves an account by its chart-of-accounts code. The batch is
 * rejected in full if any row is invalid or if total debits do not equal total
 * credits; all writes happen inside a single transaction.
 */
class OpeningBalanceImporter
{
    private const TOLERANCE = 0.0001;

    public function __construct(protected ValidationEngine $validator) {}

    /**
     * @return array{created: int, updated: int, skipped: int, failed: int, errors: array<int, array<string, mixed>>, statuses: array<int, array<string, mixed>>}
     */
    public function process(Import $import, array $rows, array $mappings, int $companyId): array
    {
        CompanyContext::set($companyId);

        try {
            return DB::transaction(fn (): array => $this->importRows($import, $rows, $mappings, $companyId));
        } catch (Throwable $e) {
            return [
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'failed' => count($rows),
                'errors' => [[
                    'row' => 0,
                    'error' => $e->getMessage(),
                ]],
                'statuses' => [],
            ];
        }
    }

    /**
     * @return array{created: int, updated: int, skipped: int, failed: int, errors: array<int, array<string, mixed>>, statuses: array<int, array<string, mixed>>}
     */
    protected function importRows(Import $import, array $rows, array $mappings, int $companyId): array
    {
        if ($rows === []) {
            return ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => [], 'statuses' => []];
        }

        $lines = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $asOfDate = null;

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 1;
            $mapped = $this->validator->mapRow($row, $mappings);
            $validation = $this->validator->validateRow('opening_balances', $mapped);

            if ($validation['errors'] !== []) {
                throw new InvalidArgumentException("Row {$rowNumber}: ".implode(' ', $validation['errors']));
            }

            $account = Account::ofCompany($companyId)
                ->where('code', $mapped['account_code'])
                ->first();

            if (! $account) {
                throw new InvalidArgumentException("Row {$rowNumber}: account code [{$mapped['account_code']}] was not found in the chart of accounts.");
            }

            $debit = round((float) ($mapped['debit'] ?? 0), 4);
            $credit = round((float) ($mapped['credit'] ?? 0), 4);
            $totalDebit += $debit;
            $totalCredit += $credit;
            $asOfDate ??= $mapped['as_of_date'];

            $lines[] = [
                'account_id' => $account->id,
                'debit' => $debit,
                'credit' => $credit,
                'description' => mb_substr((string) ($mapped['description'] ?? ($mapped['reference'] ?? 'Opening balance import')), 0, 255),
            ];
        }

        if (abs($totalDebit - $totalCredit) > self::TOLERANCE) {
            throw new InvalidArgumentException(sprintf(
                'Opening balances are not balanced: debits %.4f, credits %.4f.',
                $totalDebit,
                $totalCredit
            ));
        }

        $entry = JournalEntry::create([
            'company_id' => $companyId,
            'entry_number' => 'OB-'.now()->format('Ymd').'-'.strtoupper(bin2hex(random_bytes(4))),
            'date' => $asOfDate ?? now()->toDateString(),
            'description' => 'Opening balance import: '.$import->file_name,
            'status' => 'posted',
            'created_by' => auth()->id(),
        ]);

        foreach ($lines as $line) {
            $entry->lines()->create($line + ['journal_entry_id' => $entry->id]);
        }

        AuditLogger::log('opening_balance_import', 'finance', $entry, null, [
            'lines' => count($lines),
            'total_debit' => round($totalDebit, 4),
            'total_credit' => round($totalCredit, 4),
        ]);

        $statuses = [];
        foreach ($rows as $index => $row) {
            $statuses[$index] = [
                'status' => 'created',
                'id' => $entry->id,
                'message' => 'Posted in journal '.$entry->entry_number,
            ];
        }

        return [
            'created' => count($rows),
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => [],
            'statuses' => $statuses,
        ];
    }
}

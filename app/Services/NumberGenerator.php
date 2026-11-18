<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class NumberGenerator
{
    /**
     * Generate the next sequential number for a document table within the
     * current company. The sequence row is locked FOR UPDATE inside a
     * transaction, so concurrent requests can never issue the same number.
     *
     * Format: {PREFIX}-{YYYYMMDD}-{NNNN}
     */
    public static function next(string $prefix, string $table): string
    {
        $date = now()->format('Ymd');
        $companyId = (int) (CompanyContext::id() ?? 0);
        $type = mb_substr($table.'|'.$date, 0, 128);

        $number = DB::transaction(function () use ($companyId, $type): int {
            $query = DB::table('document_sequences')
                ->where('company_id', $companyId)
                ->where('type', $type);

            $sequence = (clone $query)->lockForUpdate()->first();

            if (! $sequence) {
                // insertOrIgnore keeps concurrent first-attempts safe; the
                // unique (company_id, type) index is the source of truth.
                DB::table('document_sequences')->insertOrIgnore([
                    'company_id' => $companyId,
                    'type' => $type,
                    'current_value' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $sequence = $query->lockForUpdate()->first();
            }

            $next = (int) ($sequence?->current_value ?? 0) + 1;

            DB::table('document_sequences')
                ->where('company_id', $companyId)
                ->where('type', $type)
                ->update(['current_value' => $next, 'updated_at' => now()]);

            return $next;
        }, 3);

        return $prefix.'-'.$date.'-'.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }
}

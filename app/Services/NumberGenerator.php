<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class NumberGenerator
{
    /**
     * Generate the next sequential number for a prefix within the current company.
     * Uses Cache lock for atomicity — no two concurrent requests can produce the same number.
     *
     * Format: {PREFIX}-{YYYYMMDD}-{NNNN}
     */
    public static function next(string $prefix, string $table): string
    {
        $date = now()->format('Ymd');
        $key = "number:{$prefix}:{$date}:".CompanyContext::id();

        $number = Cache::lock("lock:{$key}", 10)->block(5, function () use ($key) {
            $seq = (int) Cache::get($key, 0) + 1;
            Cache::put($key, $seq, now()->addHours(25));

            return $seq;
        });

        return $prefix.'-'.$date.'-'.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }
}

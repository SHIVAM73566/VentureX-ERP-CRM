<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Validation\Rules\Exists;

class CompanyContext
{
    protected static ?int $resolvedCompanyId = null;

    public static function id(): ?int
    {
        if (static::$resolvedCompanyId !== null) {
            return static::$resolvedCompanyId;
        }

        $user = auth()->user();

        return $user?->company_id;
    }

    public static function set(?int $companyId): void
    {
        static::$resolvedCompanyId = $companyId;
    }

    public static function clear(): void
    {
        static::$resolvedCompanyId = null;
    }

    public static function current(): ?Company
    {
        return static::id() ? Company::find(static::id()) : null;
    }

    /**
     * Create a company-scoped exists validation rule.
     * Prevents cross-company FK references.
     */
    public static function existsInCompany(string $table, string $column = 'id'): Exists
    {
        return (new Exists($table, $column))->where('company_id', static::id());
    }
}

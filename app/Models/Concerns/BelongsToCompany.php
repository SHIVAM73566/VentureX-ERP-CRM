<?php

namespace App\Models\Concerns;

use App\Models\Company;
use App\Services\CompanyContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    /**
     * Tenant scoping must fail closed: without a company there is no such
     * thing as "all rows" — returning them would be a cross-tenant leak.
     */
    public function scopeOfCompany(Builder $query, ?int $companyId = null): Builder
    {
        $id = $companyId ?? CompanyContext::id();

        abort_if($id === null, 403, 'No company context available.');

        return $query->where($this->qualifyColumn('company_id'), $id);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public static function bootBelongsToCompany(): void
    {
        static::creating(function ($model) {
            $companyId = CompanyContext::id();

            // An explicit null (global pipeline definitions, shared settings)
            // is a deliberate choice. A *missing* company_id with no resolved
            // tenant is not: persisting it would create an orphan row that
            // every tenant scope below would then refuse to surface.
            if (array_key_exists('company_id', $model->getAttributes())) {
                return;
            }

            abort_if($companyId === null, 403, 'No company context available to create '.static::class.'.');

            $model->company_id = $companyId;
        });
    }
}

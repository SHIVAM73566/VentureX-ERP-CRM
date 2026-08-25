<?php

namespace App\Models\Concerns;

use App\Services\CompanyContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    public function scopeOfCompany(Builder $query, ?int $companyId = null): Builder
    {
        $id = $companyId ?? CompanyContext::id();

        return $id ? $query->where($this->qualifyColumn('company_id'), $id) : $query;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public static function bootBelongsToCompany(): void
    {
        static::creating(function ($model) {
            if (! isset($model->company_id) && CompanyContext::id()) {
                $model->company_id = CompanyContext::id();
            }
        });
    }
}

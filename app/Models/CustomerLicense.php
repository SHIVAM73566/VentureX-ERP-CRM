<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id', 'license_key', 'tier', 'status', 'activated_at', 'expires_at',
    'domain', 'ip_address', 'app_version', 'max_users', 'max_companies',
    'features', 'last_check_at',
])]
class CustomerLicense extends Model
{
    use BelongsToCompany;

    public const TIERS = [
        'starter' => 'Starter',
        'professional' => 'Professional',
        'enterprise' => 'Enterprise',
    ];

    public const STATUSES = [
        'active' => 'Active',
        'expired' => 'Expired',
        'suspended' => 'Suspended',
        'revoked' => 'Revoked',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
            'max_users' => 'integer',
            'max_companies' => 'integer',
            'features' => 'array',
            'last_check_at' => 'datetime',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ! $this->isExpired();
    }
}

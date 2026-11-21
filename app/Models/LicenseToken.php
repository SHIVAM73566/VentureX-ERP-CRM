<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseToken extends Model
{
    protected $fillable = [
        'company_id',
        'token',
        'payload',
        'verified_at',
        'expires_at',
        'grace_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'verified_at' => 'datetime',
            'expires_at' => 'datetime',
            'grace_expires_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

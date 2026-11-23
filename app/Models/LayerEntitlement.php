<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LayerEntitlement extends Model
{
    protected $fillable = [
        'company_id',
        'layer_key',
        'stripe_subscription_id',
        'stripe_customer_id',
        'status',
        'starts_at',
        'expires_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

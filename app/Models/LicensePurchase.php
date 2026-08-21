<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'email', 'name', 'tier', 'reference', 'amount', 'currency',
    'license_key', 'status', 'purchased_at',
])]
class LicensePurchase extends Model
{
    public const STATUSES = [
        'pending' => 'Pending',
        'completed' => 'Completed',
        'refunded' => 'Refunded',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'purchased_at' => 'datetime',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'rfq_id', 'supplier_id', 'response_number', 'amount',
    'delivery_time_days', 'valid_until', 'status', 'notes',
])]
class RfqResponse extends Model
{
    public const STATUSES = [
        'submitted' => 'Submitted',
        'awarded' => 'Awarded',
        'rejected' => 'Rejected',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'valid_until' => 'date',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}

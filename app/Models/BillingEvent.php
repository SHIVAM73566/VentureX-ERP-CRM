<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingEvent extends Model
{
    protected $fillable = [
        'event_id',
        'event_type',
        'event_created_at',
        'status',
        'claimed_at',
        'payload',
        'error',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'event_created_at' => 'datetime',
            'claimed_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }
}

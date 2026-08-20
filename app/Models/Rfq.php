<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'rfq_number', 'title', 'description', 'status',
    'issued_at', 'closes_at', 'created_by',
])]
class Rfq extends Model
{
    use BelongsToCompany, SoftDeletes;

    public const STATUSES = [
        'draft' => 'Draft',
        'sent' => 'Sent',
        'open' => 'Open',
        'awarded' => 'Awarded',
        'cancelled' => 'Cancelled',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'closes_at' => 'date',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(RfqItem::class)->orderBy('sort');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(RfqResponse::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'branch_id', 'name', 'customer_id', 'lead_id', 'expected_value',
    'currency_code', 'stage', 'probability', 'expected_close_date', 'assigned_to',
    'source', 'notes', 'created_by',
])]
class Opportunity extends Model
{
    use BelongsToCompany, SoftDeletes;

    public const STAGES = [
        'qualification' => 'Qualification',
        'needs_analysis' => 'Needs Analysis',
        'proposal' => 'Proposal',
        'negotiation' => 'Negotiation',
        'won' => 'Won',
        'lost' => 'Lost',
    ];

    protected function casts(): array
    {
        return [
            'expected_value' => 'decimal:4',
            'probability' => 'decimal:4',
            'expected_close_date' => 'date',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

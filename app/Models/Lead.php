<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'branch_id', 'lead_number', 'company_name', 'contact_name', 'email',
    'phone', 'country_id', 'source', 'industry', 'product_interest',
    'estimated_value', 'currency_code', 'status', 'score', 'assigned_to',
    'website', 'next_follow_up', 'notes', 'created_by',
])]
class Lead extends Model
{
    use BelongsToCompany, SoftDeletes;

    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'qualified' => 'Qualified',
        'proposal' => 'Proposal',
        'won' => 'Won',
        'lost' => 'Lost',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:4',
            'score' => 'integer',
            'next_follow_up' => 'date',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function opportunity()
    {
        return $this->hasOne(Opportunity::class);
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'subject');
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'reference', 'period_start', 'period_end', 'status',
    'gross_total', 'deductions_total', 'net_total', 'employee_count', 'notes',
    'created_by', 'approved_by', 'approved_at', 'paid_at',
])]
class PayrollRun extends Model
{
    use BelongsToCompany, SoftDeletes;

    public const STATUSES = [
        'draft' => 'Draft',
        'processing' => 'Processing',
        'approved' => 'Approved',
        'paid' => 'Paid',
        'cancelled' => 'Cancelled',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'gross_total' => 'decimal:4',
            'deductions_total' => 'decimal:4',
            'net_total' => 'decimal:4',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function employees()
    {
        return $this->hasManyThrough(Employee::class, PayrollItem::class, 'payroll_run_id', 'id', 'id', 'employee_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function recalculate(): void
    {
        $this->gross_total = (float) $this->items()->sum('gross');
        $this->deductions_total = (float) $this->items()->sum('deductions');
        $this->net_total = (float) $this->items()->sum('net');
        $this->employee_count = $this->items()->count();
        $this->save();
    }
}

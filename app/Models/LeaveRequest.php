<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'employee_id', 'type', 'start_date', 'end_date', 'days',
    'half_day', 'reason', 'status', 'approved_by', 'decided_at', 'decision_notes',
])]
class LeaveRequest extends Model
{
    use BelongsToCompany, SoftDeletes;

    public const TYPES = [
        'annual' => 'Annual Leave',
        'sick' => 'Sick Leave',
        'casual' => 'Casual Leave',
        'unpaid' => 'Unpaid Leave',
        'maternity' => 'Maternity Leave',
        'paternity' => 'Paternity Leave',
        'compassionate' => 'Compassionate Leave',
        'other' => 'Other',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'cancelled' => 'Cancelled',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'days' => 'decimal:2',
            'half_day' => 'boolean',
            'decided_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}

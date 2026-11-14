<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'employee_id', 'title', 'category', 'due_date', 'status',
    'assigned_to', 'sort', 'completed_at', 'notes',
])]
class OnboardingTask extends Model
{
    use BelongsToCompany;

    public const CATEGORIES = [
        'documentation' => 'Documentation',
        'it_access' => 'IT & Access',
        'payroll' => 'Payroll',
        'compliance' => 'Compliance',
        'orientation' => 'Orientation',
        'general' => 'General',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'blocked' => 'Blocked',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}

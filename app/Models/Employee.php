<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'branch_id', 'department_id', 'user_id', 'manager_id',
    'employee_number', 'first_name', 'last_name', 'email', 'phone', 'job_title',
    'employment_type', 'status', 'hire_date', 'termination_date', 'date_of_birth',
    'gender', 'address', 'emergency_contact_name', 'emergency_contact_phone',
    'base_salary', 'currency_code', 'pay_frequency', 'notes', 'created_by',
])]
class Employee extends Model
{
    use BelongsToCompany, SoftDeletes;

    public const STATUSES = [
        'active' => 'Active',
        'probation' => 'Probation',
        'on_leave' => 'On Leave',
        'notice' => 'Notice Period',
        'terminated' => 'Terminated',
    ];

    public const EMPLOYMENT_TYPES = [
        'full_time' => 'Full Time',
        'part_time' => 'Part Time',
        'contract' => 'Contract',
        'intern' => 'Intern',
        'consultant' => 'Consultant',
    ];

    public const PAY_FREQUENCIES = [
        'monthly' => 'Monthly',
        'biweekly' => 'Bi-weekly',
        'weekly' => 'Weekly',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'termination_date' => 'date',
            'date_of_birth' => 'date',
            'base_salary' => 'decimal:4',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payrollItems(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function performanceReviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class);
    }

    public function onboardingTasks(): HasMany
    {
        return $this->hasMany(OnboardingTask::class)->orderBy('sort');
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name) ?: $this->employee_number;
    }

    public function initials(): string
    {
        return mb_strtoupper(mb_substr($this->first_name, 0, 1).mb_substr($this->last_name, 0, 1));
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'probation', 'on_leave', 'notice']);
    }

    public function monthlySalary(): float
    {
        $salary = (float) $this->base_salary;

        return match ($this->pay_frequency) {
            'weekly' => $salary * 52 / 12,
            'biweekly' => $salary * 26 / 12,
            default => $salary,
        };
    }
}

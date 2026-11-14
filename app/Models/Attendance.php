<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'employee_id', 'attendance_date', 'check_in', 'check_out',
    'status', 'worked_hours', 'overtime_hours', 'notes', 'recorded_by',
])]
class Attendance extends Model
{
    use BelongsToCompany;

    public const STATUSES = [
        'present' => 'Present',
        'remote' => 'Remote',
        'late' => 'Late',
        'half_day' => 'Half Day',
        'absent' => 'Absent',
        'leave' => 'On Leave',
        'holiday' => 'Holiday',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'worked_hours' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}

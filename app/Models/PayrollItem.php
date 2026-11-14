<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'payroll_run_id', 'employee_id', 'base_salary', 'allowances', 'overtime',
    'bonus', 'gross', 'tax', 'social_security', 'other_deductions', 'deductions',
    'net', 'notes',
])]
class PayrollItem extends Model
{
    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:4',
            'allowances' => 'decimal:4',
            'overtime' => 'decimal:4',
            'bonus' => 'decimal:4',
            'gross' => 'decimal:4',
            'tax' => 'decimal:4',
            'social_security' => 'decimal:4',
            'other_deductions' => 'decimal:4',
            'deductions' => 'decimal:4',
            'net' => 'decimal:4',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

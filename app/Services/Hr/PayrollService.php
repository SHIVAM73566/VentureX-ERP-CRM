<?php

namespace App\Services\Hr;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Build (or rebuild) payroll items for every active employee in the run
     * period. Rebuilding is idempotent: existing items for the run are
     * replaced inside a single transaction.
     */
    public function generate(PayrollRun $run, array $overrides = []): PayrollRun
    {
        return DB::transaction(function () use ($run, $overrides) {
            $run->items()->delete();

            $employees = Employee::ofCompany($run->company_id)
                ->active()
                ->orderBy('first_name')
                ->get();

            foreach ($employees as $employee) {
                $override = $overrides[$employee->id] ?? [];
                $item = $this->buildItem($run, $employee, $override);
                PayrollItem::create($item);
            }

            $run->recalculate();

            return $run->fresh('items.employee');
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function buildItem(PayrollRun $run, Employee $employee, array $override = []): array
    {
        $base = (float) ($override['base_salary'] ?? $employee->monthlySalary());
        $allowances = (float) ($override['allowances'] ?? 0);
        $bonus = (float) ($override['bonus'] ?? 0);

        $overtimeHours = (float) Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$run->period_start, $run->period_end])
            ->sum('overtime_hours');

        $hourly = $base / max(1, (float) config('hr.payroll.standard_monthly_hours', 176));
        $overtime = $overtimeHours * $hourly * (float) config('hr.payroll.overtime_multiplier', 1.5);

        $gross = $base + $allowances + $bonus + $overtime;

        $tax = (float) ($override['tax'] ?? round($gross * ((float) config('hr.payroll.tax_rate', 10) / 100), 2));
        $social = (float) ($override['social_security'] ?? round($gross * ((float) config('hr.payroll.social_security_rate', 5) / 100), 2));
        $other = (float) ($override['other_deductions'] ?? 0);

        $deductions = $tax + $social + $other;

        return [
            'payroll_run_id' => $run->id,
            'employee_id' => $employee->id,
            'base_salary' => round($base, 4),
            'allowances' => round($allowances, 4),
            'overtime' => round($overtime, 4),
            'bonus' => round($bonus, 4),
            'gross' => round($gross, 4),
            'tax' => round($tax, 4),
            'social_security' => round($social, 4),
            'other_deductions' => round($other, 4),
            'deductions' => round($deductions, 4),
            'net' => round($gross - $deductions, 4),
            'notes' => $override['notes'] ?? null,
        ];
    }

    public function approve(PayrollRun $run, ?int $userId = null): PayrollRun
    {
        $run->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        return $run;
    }

    public function markPaid(PayrollRun $run): PayrollRun
    {
        $run->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return $run;
    }
}

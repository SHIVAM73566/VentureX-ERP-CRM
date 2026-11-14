<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PayrollRun;
use App\Models\PerformanceReview;
use App\Models\User;
use App\Services\Ai\AgenticEngine;
use App\Services\CompanyContext;
use App\Services\Hr\OnboardingService;
use App\Services\Hr\PayrollService;
use App\Services\NumberGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Demo data for the HR suite and the AERO X Agentic Engine.
 *
 * Every record is idempotent (matched on a stable business key) so the
 * seeder can run on an existing installation without duplicating rows.
 */
class HrDemoSeeder extends Seeder
{
    public function run(): void
    {
        $company = CompanyContext::current() ?? Company::query()->orderBy('id')->first();

        if (! $company) {
            $this->command?->warn('No company found; skipping HR demo data.');

            return;
        }

        CompanyContext::set($company->id);

        $admin = User::ofCompany($company->id)->orderBy('id')->first();
        $departments = $this->departments($company->id);

        $employees = $this->employees($company->id, $departments, $admin?->id);

        $this->attendance($company->id, $employees, $admin?->id);
        $this->leave($company->id, $employees, $admin?->id);
        $this->payroll($company->id, $employees, $admin?->id);
        $this->reviews($company->id, $employees, $admin?->id);
        $this->onboarding($employees);

        app(AgenticEngine::class)->seedDefaults($company->id);

        $this->command?->info(sprintf(
            'HR demo data ready: %d employees, %d departments.',
            count($employees),
            count($departments),
        ));
    }

    /**
     * @return array<string, Department>
     */
    protected function departments(int $companyId): array
    {
        $definitions = [
            'OPS' => 'Operations',
            'HR' => 'People Ops',
            'PROC' => 'Procurement',
            'SALES' => 'Sales',
            'LOG' => 'Logistics',
            'FIN' => 'Finance',
        ];

        $departments = [];

        foreach ($definitions as $code => $name) {
            $departments[$code] = Department::firstOrCreate(
                ['company_id' => $companyId, 'code' => $code],
                ['name' => $name, 'is_active' => true],
            );
        }

        return $departments;
    }

    /**
     * @param  array<string, Department>  $departments
     * @return array<string, Employee>
     */
    protected function employees(int $companyId, array $departments, ?int $userId): array
    {
        $definitions = [
            ['EMP-1001', 'Aarav', 'Mehta', 'aarav.mehta@jainmetal.example', 'Plant Manager', 'OPS', 95000, '2019-04-01', 'active'],
            ['EMP-1002', 'Priya', 'Sharma', 'priya.sharma@jainmetal.example', 'HR Manager', 'HR', 72000, '2020-07-13', 'active'],
            ['EMP-1003', 'Rohan', 'Iyer', 'rohan.iyer@jainmetal.example', 'Procurement Lead', 'PROC', 68000, '2021-02-08', 'active'],
            ['EMP-1004', 'Sneha', 'Kulkarni', 'sneha.kulkarni@jainmetal.example', 'Sales Manager', 'SALES', 75000, '2018-11-19', 'active'],
            ['EMP-1005', 'Vikram', 'Singh', 'vikram.singh@jainmetal.example', 'Warehouse Supervisor', 'LOG', 48000, '2022-06-06', 'active'],
            ['EMP-1006', 'Ananya', 'Rao', 'ananya.rao@jainmetal.example', 'Finance Analyst', 'FIN', 62000, '2023-01-16', 'active'],
            ['EMP-1007', 'Imran', 'Khan', 'imran.khan@jainmetal.example', 'Machine Operator', 'OPS', 32000, '2024-03-04', 'probation'],
            ['EMP-1008', 'Kavya', 'Nair', 'kavya.nair@jainmetal.example', 'Customer Success Executive', 'SALES', 38000, now()->subDays(9)->toDateString(), 'active'],
        ];

        $employees = [];

        foreach ($definitions as [$number, $first, $last, $email, $title, $dept, $salary, $hire, $status]) {
            $employees[$number] = Employee::firstOrCreate(
                ['company_id' => $companyId, 'employee_number' => $number],
                [
                    'department_id' => $departments[$dept]->id,
                    'first_name' => $first,
                    'last_name' => $last,
                    'email' => $email,
                    'job_title' => $title,
                    'employment_type' => 'full_time',
                    'status' => $status,
                    'hire_date' => $hire,
                    'base_salary' => $salary,
                    'currency_code' => 'INR',
                    'pay_frequency' => 'monthly',
                    'created_by' => $userId,
                ],
            );
        }

        return $employees;
    }

    /**
     * @param  array<string, Employee>  $employees
     */
    protected function attendance(int $companyId, array $employees, ?int $userId): void
    {
        $statuses = ['present', 'present', 'remote', 'present', 'late', 'present', 'present', 'remote'];
        $index = 0;

        foreach ($employees as $employee) {
            $status = $statuses[$index % count($statuses)];

            Attendance::updateOrCreate(
                ['employee_id' => $employee->id, 'attendance_date' => now()->startOfDay()],
                [
                    'company_id' => $companyId,
                    'check_in' => $status === 'late' ? '10:12' : '09:05',
                    'check_out' => '18:10',
                    'status' => $status,
                    'worked_hours' => $status === 'late' ? 7.5 : 8.5,
                    'overtime_hours' => $index % 3 === 0 ? 0.5 : 0,
                    'recorded_by' => $userId,
                ],
            );

            $index++;
        }
    }

    /**
     * @param  array<string, Employee>  $employees
     */
    protected function leave(int $companyId, array $employees, ?int $userId): void
    {
        $definitions = [
            ['EMP-1007', 'sick', now()->addDays(2), now()->addDays(3), 2, 'pending', 'Viral fever, doctor advised rest.'],
            ['EMP-1008', 'annual', now()->addDays(10), now()->addDays(14), 5, 'pending', 'Family function out of town.'],
            ['EMP-1005', 'casual', now()->subDays(6), now()->subDays(6), 1, 'approved', 'Personal errand.'],
        ];

        foreach ($definitions as [$number, $type, $start, $end, $days, $status, $reason]) {
            $employee = $employees[$number] ?? null;
            if (! $employee) {
                continue;
            }

            LeaveRequest::firstOrCreate(
                [
                    'company_id' => $companyId,
                    'employee_id' => $employee->id,
                    'start_date' => $start->copy()->startOfDay(),
                    'type' => $type,
                ],
                [
                    'end_date' => $end->toDateString(),
                    'days' => $days,
                    'reason' => $reason,
                    'status' => $status,
                    'approved_by' => $status === 'approved' ? $userId : null,
                    'decided_at' => $status === 'approved' ? now() : null,
                    'decision_notes' => $status === 'approved' ? 'Approved by HR.' : null,
                ],
            );
        }
    }

    /**
     * @param  array<string, Employee>  $employees
     */
    protected function payroll(int $companyId, array $employees, ?int $userId): void
    {
        $start = now()->subMonthNoOverflow()->startOfMonth();
        $end = now()->subMonthNoOverflow()->endOfMonth();

        if (PayrollRun::where('company_id', $companyId)->whereDate('period_start', $start->toDateString())->exists()) {
            return;
        }

        $run = PayrollRun::create([
            'company_id' => $companyId,
            'reference' => NumberGenerator::next('PR', 'payroll_runs'),
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'status' => 'draft',
            'created_by' => $userId,
        ]);

        $payroll = app(PayrollService::class);
        $payroll->generate($run);
        $payroll->approve($run, $userId);
    }

    /**
     * @param  array<string, Employee>  $employees
     */
    protected function reviews(int $companyId, array $employees, ?int $userId): void
    {
        $definitions = [
            ['EMP-1001', '2026 Q3', 4.5, 4.5, 4.5, 'submitted', 'Exceptional plant throughput and safety record.', 'Delegate more to shift supervisors.', 'Lead the automation upgrade programme.'],
            ['EMP-1004', '2026 Q3', 4.0, 4.0, 4.0, 'submitted', 'Grew key-account revenue by 18%.', 'Tighten forecast hygiene.', 'Onboard two enterprise accounts.'],
            ['EMP-1003', '2026 Q3', 3.5, 3.5, 3.5, 'draft', 'Reliable supplier negotiations.', 'Speed up RFQ turnaround.', 'Cut landed cost by 6%.'],
        ];

        foreach ($definitions as [$number, $period, $overall, $goals, $competency, $status, $strengths, $improvements, $targets]) {
            $employee = $employees[$number] ?? null;
            if (! $employee) {
                continue;
            }

            PerformanceReview::firstOrCreate(
                [
                    'company_id' => $companyId,
                    'employee_id' => $employee->id,
                    'period' => $period,
                ],
                [
                    'reviewer_id' => $userId,
                    'review_date' => Carbon::now()->subDays(14)->toDateString(),
                    'overall_score' => $overall,
                    'goals_score' => $goals,
                    'competency_score' => $competency,
                    'status' => $status,
                    'strengths' => $strengths,
                    'improvements' => $improvements,
                    'goals' => $targets,
                ],
            );
        }
    }

    /**
     * @param  array<string, Employee>  $employees
     */
    protected function onboarding(array $employees): void
    {
        $service = app(OnboardingService::class);

        foreach (['EMP-1008', 'EMP-1007'] as $number) {
            if (isset($employees[$number])) {
                $service->seedFor($employees[$number]);
            }
        }
    }
}

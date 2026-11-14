<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureTwoFactor;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OnboardingTask;
use App\Models\PayrollRun;
use App\Models\PerformanceReview;
use App\Models\User;
use App\Services\Hr\OnboardingService;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * End-to-end coverage for the professional HR suite: directory, attendance,
 * leave, payroll, performance reviews, automated onboarding and departments.
 */
class HrModuleTest extends TestCase
{
    protected Company $company;

    protected User $admin;

    protected User $viewer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureTwoFactor::class);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->company = Company::create([
            'name' => 'HR Test Corp',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        $actions = ['view', 'create', 'edit', 'delete', 'export', 'import', 'approve'];
        foreach (['hr', 'customers', 'settings', 'automation'] as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$module}.{$action}", 'guard_name' => 'web']);
            }
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $viewerRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewerRole->syncPermissions(['customers.view']);

        $this->admin = User::create([
            'name' => 'HR Admin',
            'email' => 'hr-admin@test.com',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
        $this->admin->assignRole($superAdmin);

        $this->viewer = User::create([
            'name' => 'HR Viewer',
            'email' => 'hr-viewer@test.com',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
        $this->viewer->assignRole($viewerRole);
    }

    protected function actingAdmin()
    {
        return $this->actingAs($this->admin)
            ->withSession([
                'two_factor_verified_at' => now()->timestamp,
                'step_up_verified_at' => now()->timestamp,
            ]);
    }

    protected function makeEmployee(array $overrides = []): Employee
    {
        return Employee::create(array_merge([
            'company_id' => $this->company->id,
            'employee_number' => 'EMP-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@test.com',
            'job_title' => 'Engineer',
            'employment_type' => 'full_time',
            'status' => 'active',
            'hire_date' => now()->toDateString(),
            'base_salary' => 1200,
            'currency_code' => 'USD',
            'pay_frequency' => 'monthly',
            'created_by' => $this->admin->id,
        ], $overrides));
    }

    public function test_hr_dashboard_renders_for_authorized_users(): void
    {
        $this->makeEmployee();

        $response = $this->actingAdmin()->get(route('hr.dashboard'));

        $response->assertOk();
        $response->assertSee('Headcount', false);
    }

    public function test_hr_module_is_blocked_for_users_without_hr_permission(): void
    {
        $response = $this->actingAs($this->viewer)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->get(route('hr.employees.index'));

        $response->assertForbidden();
    }

    public function test_employee_directory_and_create_form_load(): void
    {
        $this->makeEmployee();

        $this->actingAdmin()->get(route('hr.employees.index'))
            ->assertOk()
            ->assertSee('Ada', false);

        $this->actingAdmin()->get(route('hr.employees.create'))
            ->assertOk()
            ->assertSee('New Employee', false);
    }

    public function test_employee_store_creates_record_and_onboarding_checklist(): void
    {
        $response = $this->actingAdmin()->post(route('hr.employees.store'), [
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'email' => 'grace@test.com',
            'job_title' => 'CTO',
            'employment_type' => 'full_time',
            'status' => 'active',
            'hire_date' => now()->toDateString(),
            'base_salary' => 5000,
            'currency_code' => 'USD',
            'pay_frequency' => 'monthly',
        ]);

        $employee = Employee::where('email', 'grace@test.com')->firstOrFail();

        $response->assertRedirect(route('hr.employees.show', $employee));

        $this->assertSame('Grace', $employee->first_name);
        $this->assertStringStartsWith('EMP-', $employee->employee_number);

        $this->assertSame(
            count(config('hr.onboarding')),
            OnboardingTask::where('employee_id', $employee->id)->count()
        );
    }

    public function test_employee_store_validates_required_fields(): void
    {
        $this->actingAdmin()
            ->from(route('hr.employees.create'))
            ->post(route('hr.employees.store'), [])
            ->assertSessionHasErrors(['first_name', 'last_name', 'employment_type', 'status', 'pay_frequency']);
    }

    public function test_employee_update_and_show_work(): void
    {
        $employee = $this->makeEmployee();

        $this->actingAdmin()->get(route('hr.employees.show', $employee))
            ->assertOk()
            ->assertSee('Ada', false);

        $this->actingAdmin()->put(route('hr.employees.update', $employee), [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'job_title' => 'Principal Engineer',
            'employment_type' => 'full_time',
            'status' => 'probation',
            'pay_frequency' => 'monthly',
        ])->assertRedirect(route('hr.employees.show', $employee));

        $this->assertSame('probation', $employee->fresh()->status);
        $this->assertSame('Principal Engineer', $employee->fresh()->job_title);
    }

    public function test_attendance_is_recorded_and_duplicates_are_rejected(): void
    {
        $employee = $this->makeEmployee();

        $payload = [
            'employee_id' => $employee->id,
            'attendance_date' => now()->toDateString(),
            'check_in' => '09:00',
            'check_out' => '18:00',
            'status' => 'present',
            'worked_hours' => 8,
            'overtime_hours' => 1,
        ];

        $this->actingAdmin()->post(route('hr.attendance.store'), $payload)
            ->assertRedirect();

        $this->assertSame(1, Attendance::where('employee_id', $employee->id)->count());

        $this->actingAdmin()
            ->from(route('hr.attendance.create'))
            ->post(route('hr.attendance.store'), $payload)
            ->assertSessionHas('error');

        $this->assertSame(1, Attendance::where('employee_id', $employee->id)->count());
    }

    public function test_leave_request_lifecycle_approve_and_reject(): void
    {
        $employee = $this->makeEmployee();

        $this->actingAdmin()->post(route('hr.leave.store'), [
            'employee_id' => $employee->id,
            'type' => 'annual',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'reason' => 'Family trip',
        ])->assertRedirect(route('hr.leave.index'));

        $leave = LeaveRequest::firstOrFail();
        $this->assertSame('pending', $leave->status);
        $this->assertSame(3.0, (float) $leave->days);

        $this->actingAdmin()->post(route('hr.leave.approve', $leave), [
            'decision_notes' => 'Approved by HR',
        ])->assertRedirect();

        $leave->refresh();
        $this->assertSame('approved', $leave->status);
        $this->assertSame($this->admin->id, $leave->approved_by);

        $second = LeaveRequest::create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
            'type' => 'sick',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'days' => 1,
            'status' => 'pending',
        ]);

        $this->actingAdmin()->post(route('hr.leave.reject', $second))->assertRedirect();

        $this->assertSame('rejected', $second->fresh()->status);
    }

    public function test_payroll_run_generates_items_and_can_be_approved(): void
    {
        $this->makeEmployee(['base_salary' => 1000]);

        $this->actingAdmin()->post(route('hr.payroll.store'), [
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
        ])->assertRedirect();

        $run = PayrollRun::with('items')->firstOrFail();

        $this->assertSame('draft', $run->status);
        $this->assertCount(1, $run->items);

        $item = $run->items->first();
        $this->assertEqualsWithDelta(1000.0, (float) $item->gross, 0.01);
        // 10% tax + 5% social security.
        $this->assertEqualsWithDelta(150.0, (float) $item->deductions, 0.01);
        $this->assertEqualsWithDelta(850.0, (float) $item->net, 0.01);
        $this->assertSame(1, $run->employee_count);

        $this->actingAdmin()->post(route('hr.payroll.approve', $run))->assertRedirect();

        $run->refresh();
        $this->assertSame('approved', $run->status);
        $this->assertSame($this->admin->id, $run->approved_by);
    }

    public function test_performance_review_is_created_with_computed_overall_score(): void
    {
        $employee = $this->makeEmployee();

        $this->actingAdmin()->post(route('hr.reviews.store'), [
            'employee_id' => $employee->id,
            'period' => '2026 Q4',
            'review_date' => now()->toDateString(),
            'goals_score' => 4,
            'competency_score' => 2,
            'status' => 'submitted',
            'strengths' => 'Ships reliably',
        ])->assertRedirect();

        $review = PerformanceReview::firstOrFail();

        $this->assertSame($employee->id, $review->employee_id);
        $this->assertEqualsWithDelta(3.0, (float) $review->overall_score, 0.01);
        $this->assertSame($this->admin->id, $review->reviewer_id);

        $this->actingAdmin()->get(route('hr.reviews.show', $review))
            ->assertOk()
            ->assertSee('Ships reliably', false);
    }

    public function test_onboarding_task_can_be_completed_and_reopened(): void
    {
        $employee = $this->makeEmployee();
        app(OnboardingService::class)->seedFor($employee);

        $task = OnboardingTask::where('employee_id', $employee->id)->firstOrFail();

        $this->actingAdmin()->post(route('hr.onboarding.complete', $task))->assertRedirect();
        $this->assertSame('completed', $task->fresh()->status);
        $this->assertNotNull($task->fresh()->completed_at);

        $this->actingAdmin()->post(route('hr.onboarding.reopen', $task))->assertRedirect();
        $this->assertSame('pending', $task->fresh()->status);
        $this->assertNull($task->fresh()->completed_at);
    }

    public function test_onboarding_seed_is_idempotent_for_existing_employees(): void
    {
        $employee = $this->makeEmployee();

        $this->actingAdmin()->post(route('hr.onboarding.seed', $employee))->assertRedirect();
        $firstCount = OnboardingTask::where('employee_id', $employee->id)->count();
        $this->assertSame(count(config('hr.onboarding')), $firstCount);

        $this->actingAdmin()->post(route('hr.onboarding.seed', $employee))
            ->assertSessionHas('success');

        $this->assertSame($firstCount, OnboardingTask::where('employee_id', $employee->id)->count());
    }

    public function test_departments_can_be_created_and_listed(): void
    {
        $this->actingAdmin()->post(route('hr.departments.store'), [
            'name' => 'Engineering',
            'code' => 'ENG',
            'is_active' => 1,
        ])->assertRedirect(route('hr.departments.index'));

        $this->assertDatabaseHas('departments', [
            'company_id' => $this->company->id,
            'name' => 'Engineering',
            'code' => 'ENG',
        ]);

        $this->actingAdmin()->get(route('hr.departments.index'))
            ->assertOk()
            ->assertSee('Engineering', false);
    }

    public function test_department_with_employees_cannot_be_deleted(): void
    {
        $department = Department::create([
            'company_id' => $this->company->id,
            'name' => 'Operations',
            'code' => 'OPS',
            'is_active' => true,
        ]);

        $this->makeEmployee(['department_id' => $department->id]);

        $this->actingAdmin()
            ->from(route('hr.departments.index'))
            ->delete(route('hr.departments.destroy', $department))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('departments', ['id' => $department->id]);
    }

    public function test_every_hr_screen_renders_without_errors(): void
    {
        $department = Department::create([
            'company_id' => $this->company->id,
            'name' => 'People Ops',
            'code' => 'POP',
            'is_active' => true,
        ]);

        $employee = $this->makeEmployee(['department_id' => $department->id]);

        $attendance = Attendance::create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
            'attendance_date' => now()->toDateString(),
            'status' => 'present',
            'worked_hours' => 8,
            'overtime_hours' => 0,
            'recorded_by' => $this->admin->id,
        ]);

        $leave = LeaveRequest::create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
            'type' => 'annual',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'days' => 2,
            'status' => 'pending',
        ]);

        $this->actingAdmin()->post(route('hr.payroll.store'), [
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
        ])->assertRedirect();

        $run = PayrollRun::firstOrFail();

        $review = PerformanceReview::create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
            'period' => '2026 Q4',
            'review_date' => now()->toDateString(),
            'overall_score' => 4,
            'status' => 'draft',
            'reviewer_id' => $this->admin->id,
        ]);

        $task = OnboardingTask::create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
            'title' => 'Sign contract',
            'category' => 'documentation',
            'due_date' => now()->addDays(3)->toDateString(),
            'status' => 'pending',
            'sort' => 1,
        ]);

        $screens = [
            route('hr.dashboard'),
            route('hr.employees.index'),
            route('hr.employees.create'),
            route('hr.employees.show', $employee),
            route('hr.employees.edit', $employee),
            route('hr.attendance.index'),
            route('hr.attendance.create'),
            route('hr.attendance.edit', $attendance),
            route('hr.leave.index'),
            route('hr.leave.create'),
            route('hr.payroll.index'),
            route('hr.payroll.create'),
            route('hr.payroll.show', $run),
            route('hr.reviews.index'),
            route('hr.reviews.create'),
            route('hr.reviews.show', $review),
            route('hr.reviews.edit', $review),
            route('hr.onboarding.index'),
            route('hr.departments.index'),
            route('hr.departments.create'),
            route('hr.departments.edit', $department),
        ];

        foreach ($screens as $url) {
            $this->actingAdmin()->get($url)->assertOk();
        }

        $this->assertNotNull($leave->id);
        $this->assertNotNull($task->id);
    }
}

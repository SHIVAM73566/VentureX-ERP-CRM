<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use App\Services\Hr\OnboardingService;
use App\Services\NumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(protected OnboardingService $onboarding) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Employee::class);

        $employees = Employee::ofCompany()
            ->with('department', 'manager')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('job_title', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('department'), fn ($q) => $q->where('department_id', $request->integer('department')))
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => Employee::ofCompany()->count(),
            'active' => Employee::ofCompany()->active()->count(),
            'on_leave' => Employee::ofCompany()->where('status', 'on_leave')->count(),
            'departments' => Department::ofCompany()->count(),
        ];

        return view('hr.employees.index', [
            'employees' => $employees,
            'summary' => $summary,
            'departments' => Department::ofCompany()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Employee::class);

        return view('hr.employees.form', $this->formData(new Employee));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Employee::class);

        $data = $request->validate($this->rules());

        try {
            $employee = DB::transaction(function () use ($data) {
                $employee = Employee::create([
                    ...$data,
                    'company_id' => CompanyContext::id(),
                    'employee_number' => $data['employee_number']
                        ?? NumberGenerator::next('EMP', 'employees'),
                    'created_by' => auth()->id(),
                ]);

                $this->onboarding->seedFor($employee);

                return $employee;
            });

            AuditLogger::created($employee);

            return redirect()->route('hr.employees.show', $employee)
                ->with('success', 'Employee created and onboarding checklist generated.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create employee: '.$e->getMessage());
        }
    }

    public function show(Employee $employee): View
    {
        $this->authorize('view', $employee);
        $this->ensureCompany($employee);

        $employee->load([
            'department', 'branch', 'manager', 'subordinates',
            'onboardingTasks.assignee', 'performanceReviews.reviewer',
            'leaveRequests' => fn ($q) => $q->latest('start_date')->limit(10),
            'attendances' => fn ($q) => $q->latest('attendance_date')->limit(14),
            'payrollItems.run',
        ]);

        return view('hr.employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        $this->authorize('update', $employee);
        $this->ensureCompany($employee);

        return view('hr.employees.form', $this->formData($employee));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);
        $this->ensureCompany($employee);

        $data = $request->validate($this->rules($employee));

        try {
            $employee->update($data);
            AuditLogger::updated($employee);

            return redirect()->route('hr.employees.show', $employee)->with('success', 'Employee updated.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update employee: '.$e->getMessage());
        }
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('delete', $employee);
        $this->ensureCompany($employee);

        $employee->delete();
        AuditLogger::deleted($employee);

        return redirect()->route('hr.employees.index')->with('success', 'Employee archived.');
    }

    protected function ensureCompany(Employee $employee): void
    {
        if ((int) $employee->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(Employee $employee): array
    {
        return [
            'employee' => $employee,
            'departments' => Department::ofCompany()->orderBy('name')->get(),
            'branches' => Branch::ofCompany()->orderBy('name')->get(),
            'managers' => Employee::ofCompany()->active()
                ->when($employee->exists, fn ($q) => $q->whereKeyNot($employee->id))
                ->orderBy('first_name')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(?Employee $employee = null): array
    {
        return [
            'employee_number' => ['nullable', 'string', 'max:32'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'job_title' => ['nullable', 'string', 'max:160'],
            'department_id' => ['nullable', CompanyContext::existsInCompany('departments')],
            'branch_id' => ['nullable', CompanyContext::existsInCompany('branches')],
            'manager_id' => ['nullable', CompanyContext::existsInCompany('employees')],
            'employment_type' => ['required', 'in:'.implode(',', array_keys(Employee::EMPLOYMENT_TYPES))],
            'status' => ['required', 'in:'.implode(',', array_keys(Employee::STATUSES))],
            'hire_date' => ['nullable', 'date'],
            'termination_date' => ['nullable', 'date', 'after_or_equal:hire_date'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'max:24'],
            'address' => ['nullable', 'string', 'max:1000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:160'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:40'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'string', 'max:8'],
            'pay_frequency' => ['required', 'in:'.implode(',', array_keys(Employee::PAY_FREQUENCIES))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

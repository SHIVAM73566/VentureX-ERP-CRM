<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\OnboardingTask;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use App\Services\Hr\OnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function __construct(protected OnboardingService $onboarding) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', OnboardingTask::class);

        $tasks = OnboardingTask::ofCompany()
            ->with('employee', 'assignee')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('employee'), fn ($q) => $q->where('employee_id', $request->integer('employee')))
            ->orderBy('due_date')
            ->orderBy('sort')
            ->paginate(25)
            ->withQueryString();

        $summary = [
            'pending' => OnboardingTask::ofCompany()->where('status', 'pending')->count(),
            'in_progress' => OnboardingTask::ofCompany()->where('status', 'in_progress')->count(),
            'completed' => OnboardingTask::ofCompany()->where('status', 'completed')->count(),
            'blocked' => OnboardingTask::ofCompany()->where('status', 'blocked')->count(),
        ];

        return view('hr.onboarding.index', [
            'tasks' => $tasks,
            'summary' => $summary,
            'employees' => Employee::ofCompany()->active()->orderBy('first_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', OnboardingTask::class);

        $data = $request->validate([
            'employee_id' => ['required', CompanyContext::existsInCompany('employees')],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:'.implode(',', array_keys(OnboardingTask::CATEGORIES))],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'in:'.implode(',', array_keys(OnboardingTask::STATUSES))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $task = OnboardingTask::create([
            ...$data,
            'company_id' => CompanyContext::id(),
            'assigned_to' => auth()->id(),
            'sort' => (int) OnboardingTask::ofCompany()->where('employee_id', $data['employee_id'])->max('sort') + 1,
        ]);

        AuditLogger::created($task);

        return back()->with('success', 'Onboarding task added.');
    }

    public function complete(OnboardingTask $task): RedirectResponse
    {
        $this->authorize('update', $task);
        $this->ensureCompany($task);

        $this->onboarding->complete($task);
        AuditLogger::updated($task);

        return back()->with('success', 'Task completed.');
    }

    public function reopen(OnboardingTask $task): RedirectResponse
    {
        $this->authorize('update', $task);
        $this->ensureCompany($task);

        $this->onboarding->reopen($task);
        AuditLogger::updated($task);

        return back()->with('success', 'Task reopened.');
    }

    public function seed(Employee $employee): RedirectResponse
    {
        $this->authorize('create', OnboardingTask::class);

        if ((int) $employee->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $created = $this->onboarding->seedFor($employee);

        return back()->with('success', $created > 0
            ? "{$created} onboarding tasks created."
            : 'This employee already has an onboarding checklist.');
    }

    public function destroy(OnboardingTask $task): RedirectResponse
    {
        $this->authorize('delete', $task);
        $this->ensureCompany($task);

        $task->delete();
        AuditLogger::deleted($task);

        return back()->with('success', 'Task removed.');
    }

    protected function ensureCompany(OnboardingTask $task): void
    {
        if ((int) $task->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }
    }
}

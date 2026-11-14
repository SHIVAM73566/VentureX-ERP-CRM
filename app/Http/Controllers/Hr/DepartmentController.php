<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Department::class);

        $departments = Department::ofCompany()
            ->with('branch', 'parent')
            ->withCount('employees')
            ->orderBy('name')
            ->paginate(25);

        return view('hr.departments.index', [
            'departments' => $departments,
            'summary' => [
                'total' => Department::ofCompany()->count(),
                'active' => Department::ofCompany()->where('is_active', true)->count(),
                'employees' => Department::ofCompany()->withCount('employees')->get()->sum('employees_count'),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Department::class);

        return view('hr.departments.form', $this->formData(new Department(['is_active' => true])));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Department::class);

        $department = Department::create([
            ...$request->validate($this->rules()),
            'company_id' => CompanyContext::id(),
        ]);

        AuditLogger::created($department);

        return redirect()->route('hr.departments.index')->with('success', 'Department created.');
    }

    public function edit(Department $department): View
    {
        $this->authorize('update', $department);
        $this->ensureCompany($department);

        return view('hr.departments.form', $this->formData($department));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $this->authorize('update', $department);
        $this->ensureCompany($department);

        $department->update($request->validate($this->rules($department)));
        AuditLogger::updated($department);

        return redirect()->route('hr.departments.index')->with('success', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->authorize('delete', $department);
        $this->ensureCompany($department);

        if ($department->employees()->exists()) {
            return back()->with('error', 'Move employees out of this department before deleting it.');
        }

        $department->delete();
        AuditLogger::deleted($department);

        return redirect()->route('hr.departments.index')->with('success', 'Department deleted.');
    }

    protected function ensureCompany(Department $department): void
    {
        if ((int) $department->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(Department $department): array
    {
        return [
            'department' => $department,
            'branches' => Branch::ofCompany()->orderBy('name')->get(),
            'parents' => Department::ofCompany()
                ->when($department->exists, fn ($q) => $q->whereKeyNot($department->id))
                ->orderBy('name')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(?Department $department = null): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'code' => ['nullable', 'string', 'max:16'],
            'branch_id' => ['nullable', CompanyContext::existsInCompany('branches')],
            'parent_id' => ['nullable', CompanyContext::existsInCompany('departments')],
            'is_active' => ['boolean'],
        ];
    }
}

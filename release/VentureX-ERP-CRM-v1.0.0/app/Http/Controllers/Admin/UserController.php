<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use App\Rules\StrongPassword;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use App\Services\PasswordPolicyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->with('company', 'branch', 'department', 'roles')
            ->when(! auth()->user()->hasRole('super_admin'), fn ($q) => $q->where('company_id', CompanyContext::id()))
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('company_id'), fn ($q) => $q->where('company_id', $request->integer('company_id')))
            ->when($request->filled('role'), fn ($q) => $q->role($request->string('role')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(),
            'companies' => Company::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.form', $this->formData(new User));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $isSuper = auth()->user()->hasRole('super_admin');
        $companyId = $isSuper ? $request->input('company_id') : CompanyContext::id();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', new StrongPassword],
            'company_id' => ['required', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'job_title' => ['nullable', 'string', 'max:128'],
            'phone' => ['nullable', 'string', 'max:32'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        // SECURITY: Only super_admin can assign super_admin role
        if (! $isSuper) {
            $superAdminRole = \App\Models\Role::where('name', 'super_admin')->first();
            if ($superAdminRole && in_array($superAdminRole->id, $data['roles'])) {
                abort(403, 'Only super administrators can assign the super_admin role.');
            }
        }

        $this->assertCompanyOwnedBranch($data);
        $data['company_id'] = $companyId;

        $user = User::create($data);
        $user->syncRoles($data['roles']);

        PasswordPolicyService::remember($user, $user->password);
        $user->forceFill(['password_changed_at' => now()])->save();

        AuditLogger::log('create', 'users', $user, null, ['roles' => $data['roles']]);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $user->load('roles');

        return view('admin.users.form', $this->formData($user));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $isSuper = auth()->user()->hasRole('super_admin');
        $companyId = $isSuper ? $request->input('company_id') : CompanyContext::id();

        if (! $isSuper && (int) $user->company_id !== (int) $companyId) {
            abort(403, 'Users may only be edited within your company.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'string', 'confirmed', new StrongPassword],
            'company_id' => ['required', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'job_title' => ['nullable', 'string', 'max:128'],
            'phone' => ['nullable', 'string', 'max:32'],
            'is_active' => ['sometimes', 'boolean'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $oldRoles = $user->roles->pluck('id')->all();
        $changingPassword = ! empty($data['password']);

        if ($changingPassword && PasswordPolicyService::reused($user, $data['password'])) {
            return back()->withErrors([
                'password' => 'You recently used this password. Choose a different one.',
            ])->withInput();
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $this->assertCompanyOwnedBranch($data);
        $data['company_id'] = $companyId;

        $user->update($data);

        // SECURITY: Only super_admin can assign roles — prevent privilege escalation
        if ($isSuper) {
            $user->syncRoles($data['roles']);
        } else {
            // Non-super_admin: only sync non-privileged roles
            $superAdminRoleId = \App\Models\Role::where('name', 'super_admin')->first()?->id;
            $safeRoles = array_filter($data['roles'], fn ($id) => $id != $superAdminRoleId);
            $user->syncRoles($safeRoles);
        }

        if ($changingPassword) {
            PasswordPolicyService::remember($user, $user->password);
            $user->forceFill(['password_changed_at' => now()])->save();

            if (config('security.password.revoke_sessions_on_change', true)) {
                DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->where('id', '!=', session()->getId())
                    ->delete();
            }
        }

        AuditLogger::updated($user, ['roles' => $oldRoles]);

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if ($user->is(auth()->user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        AuditLogger::deleted($user);

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    protected function formData(User $user): array
    {
        $isSuper = auth()->user()->hasRole('super_admin');
        $companyId = CompanyContext::id();

        $companies = $isSuper ? Company::orderBy('name')->get() : Company::whereKey($companyId)->get();
        $branches = $isSuper ? Branch::orderBy('name')->get() : Branch::where('company_id', $companyId)->orderBy('name')->get();
        $departments = $isSuper ? Department::orderBy('name')->get() : Department::where('company_id', $companyId)->orderBy('name')->get();

        return [
            'user' => $user,
            'companies' => $companies,
            'branches' => $branches,
            'departments' => $departments,
            'roles' => Role::orderBy('name')->get(),
        ];
    }

    protected function assertCompanyOwnedBranch(array $data): void
    {
        if (! empty($data['branch_id'])) {
            $branch = Branch::find($data['branch_id']);
            if ($branch && (int) $branch->company_id !== (int) $data['company_id']) {
                abort(422, 'The selected branch does not belong to the selected company.');
            }
        }

        if (! empty($data['department_id'])) {
            $department = Department::find($data['department_id']);
            if ($department && (int) $department->company_id !== (int) $data['company_id']) {
                abort(422, 'The selected department does not belong to the selected company.');
            }
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Role::class);

        return view('admin.roles.index', ['roles' => Role::withCount('permissions', 'users')->paginate(10)]);
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        return view('admin.roles.form', [
            'role' => new Role,
            'permissionGroups' => $this->groupedPermissions(),
            'selected' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'regex:/^[a-z0-9_\-\.]+$/', 'unique:roles,name'],
            'label' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        AuditLogger::log('create', 'roles', $role, null, ['permissions' => $data['permissions'] ?? []]);

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        return view('admin.roles.form', [
            'role' => $role,
            'permissionGroups' => $this->groupedPermissions(),
            'selected' => $role->permissions->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $data = $request->validate([
            'name' => ['required', 'string', 'regex:/^[a-z0-9_\-\.]+$/', 'unique:roles,name,'.$role->id],
            'label' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $old = $role->permissions->pluck('id')->all();
        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);
        AuditLogger::updated($role, ['permissions' => $old]);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        if ($role->users()->exists()) {
            return back()->with('error', 'Cannot delete a role assigned to users.');
        }

        $role->delete();
        AuditLogger::deleted($role);

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }

    protected function groupedPermissions(): array
    {
        $groups = [];

        foreach (Permission::orderBy('name')->get() as $permission) {
            $parts = explode('_', $permission->name);
            $module = $parts[0] ?? 'general';
            $groups[$module][] = $permission;
        }

        return $groups;
    }
}

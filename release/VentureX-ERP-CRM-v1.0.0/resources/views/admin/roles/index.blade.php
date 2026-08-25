<x-layouts.app title="Roles" :breadcrumbs="[['label' => 'Administration'], ['label' => 'Roles']]">

    <x-slot name="actions">
        @can('create', Spatie\Permission\Models\Role::class)
            <a href="{{ route('admin.roles.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Role
            </a>
        @endcan
    </x-slot>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Permissions</th>
                        <th>Users</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td class="font-semibold text-ink-800">{{ $role->label }}</td>
                            <td><span class="badge-gray">{{ $role->permissions_count }} permissions</span></td>
                            <td>{{ $role->users_count }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.roles.edit', $role) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-ink-400">No roles found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $roles->links() }}</div>
</x-layouts.app>

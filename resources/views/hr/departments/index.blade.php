<x-layouts.app title="Departments" :breadcrumbs="[['label' => 'Human Resources', 'url' => route('hr.dashboard')], ['label' => 'Departments']]">

    <x-slot name="actions">
        @can('create', App\Models\Department::class)
            <a href="{{ route('hr.departments.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Department
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-3">
        <x-dashboard.stat-card label="Departments" :value="$summary['total']" icon="building" color="blue" />
        <x-dashboard.stat-card label="Active" :value="$summary['active']" icon="badge" color="green" />
        <x-dashboard.stat-card label="Assigned Employees" :value="$summary['employees']" icon="users" color="violet" />
    </div>

    <div class="card mt-6">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Department</th><th>Code</th><th>Branch</th><th>Parent</th><th>Employees</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($departments as $department)
                        <tr>
                            <td class="font-medium text-ink-800 dark:text-ink-100">{{ $department->name }}</td>
                            <td class="font-mono text-xs">{{ $department->code ?? '—' }}</td>
                            <td>{{ $department->branch?->name ?? '—' }}</td>
                            <td>{{ $department->parent?->name ?? '—' }}</td>
                            <td>{{ $department->employees_count }}</td>
                            <td><span class="badge-{{ $department->is_active ? 'green' : 'gray' }}">{{ $department->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-right">
                                <div class="flex justify-end gap-3">
                                    @can('update', $department)
                                        <a href="{{ route('hr.departments.edit', $department) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">Edit</a>
                                    @endcan
                                    @can('delete', $department)
                                        <form method="POST" action="{{ route('hr.departments.destroy', $department) }}" onsubmit="return confirm('Delete this department?')">@csrf @method('DELETE')
                                            <button class="text-sm font-medium text-ink-400 hover:text-red-600">Delete</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-ink-400">No departments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $departments->links() }}</div>
</x-layouts.app>

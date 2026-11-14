<x-layouts.app title="Employees" :breadcrumbs="[['label' => 'Human Resources', 'url' => route('hr.dashboard')], ['label' => 'Employees']]">

    <x-slot name="actions">
        @can('create', App\Models\Employee::class)
            <a href="{{ route('hr.employees.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Employee
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Total Employees" :value="$summary['total']" icon="users" color="blue" />
        <x-dashboard.stat-card label="Active" :value="$summary['active']" icon="badge" color="green" />
        <x-dashboard.stat-card label="On Leave" :value="$summary['on_leave']" icon="clock" color="amber" />
        <x-dashboard.stat-card label="Departments" :value="$summary['departments']" icon="building" color="violet" />
    </div>

    <form method="GET" action="{{ route('hr.employees.index') }}" class="mt-6 mb-4 flex flex-wrap gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name, number, email or title..." class="input max-w-md" />
        <select name="department" class="input max-w-[12rem]" onchange="this.form.submit()">
            <option value="">All departments</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected((string) request('department') === (string) $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (\App\Models\Employee::STATUSES as $key => $label)
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-secondary">Filter</button>
    </form>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Employee</th><th>Number</th><th>Department</th><th>Job Title</th><th>Type</th><th>Hire Date</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-navy-100 text-xs font-bold text-navy-700">{{ $employee->initials() }}</span>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-ink-800">{{ $employee->fullName() }}</p>
                                        <p class="text-xs text-ink-400">{{ $employee->email ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="font-mono text-xs">{{ $employee->employee_number }}</td>
                            <td>{{ $employee->department?->name ?? '—' }}</td>
                            <td>{{ $employee->job_title ?? '—' }}</td>
                            <td>{{ \App\Models\Employee::EMPLOYMENT_TYPES[$employee->employment_type] ?? $employee->employment_type }}</td>
                            <td>{{ $employee->hire_date?->format('d M Y') ?? '—' }}</td>
                            <td><span class="badge-{{ in_array($employee->status, ['active', 'probation']) ? 'green' : ($employee->status === 'on_leave' ? 'amber' : 'gray') }}">{{ \App\Models\Employee::STATUSES[$employee->status] ?? $employee->status }}</span></td>
                            <td class="text-right"><a href="{{ route('hr.employees.show', $employee) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-ink-400">No employees found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $employees->links() }}</div>
</x-layouts.app>

<x-layouts.app title="Attendance" :breadcrumbs="[['label' => 'Human Resources', 'url' => route('hr.dashboard')], ['label' => 'Attendance']]">

    <x-slot name="actions">
        @can('create', App\Models\Attendance::class)
            <a href="{{ route('hr.attendance.create', ['date' => $date]) }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Record Attendance
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Present" :value="$summary['present']" icon="badge" color="green" />
        <x-dashboard.stat-card label="Late" :value="$summary['late']" icon="clock" color="amber" />
        <x-dashboard.stat-card label="Absent" :value="$summary['absent']" icon="alert" color="red" />
        <x-dashboard.stat-card label="On Leave" :value="$summary['leave']" icon="calendar" color="violet" />
    </div>

    <form method="GET" action="{{ route('hr.attendance.index') }}" class="mt-6 mb-4 flex flex-wrap gap-2">
        <input type="date" name="date" value="{{ $date }}" class="input max-w-[12rem]" onchange="this.form.submit()" />
        <select name="employee" class="input max-w-[14rem]" onchange="this.form.submit()">
            <option value="">All employees</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected((string) request('employee') === (string) $employee->id)>{{ $employee->fullName() }}</option>
            @endforeach
        </select>
        <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (\App\Models\Attendance::STATUSES as $key => $label)
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-secondary">Filter</button>
    </form>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Employee</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Worked</th><th>Overtime</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td class="font-medium text-ink-800">{{ $record->employee?->fullName() ?? '—' }}</td>
                            <td>{{ $record->attendance_date?->format('d M Y') }}</td>
                            <td>{{ $record->check_in ?? '—' }}</td>
                            <td>{{ $record->check_out ?? '—' }}</td>
                            <td>{{ number_format((float) $record->worked_hours, 2) }}h</td>
                            <td>{{ number_format((float) $record->overtime_hours, 2) }}h</td>
                            <td><span class="badge-{{ in_array($record->status, ['present', 'remote']) ? 'green' : ($record->status === 'absent' ? 'red' : 'amber') }}">{{ \App\Models\Attendance::STATUSES[$record->status] ?? $record->status }}</span></td>
                            <td class="text-right">
                                @can('update', $record)
                                    <a href="{{ route('hr.attendance.edit', $record) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-ink-400">No attendance recorded for this date.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $records->links() }}</div>
</x-layouts.app>

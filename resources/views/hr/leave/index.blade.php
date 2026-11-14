<x-layouts.app title="Leave Requests" :breadcrumbs="[['label' => 'Human Resources', 'url' => route('hr.dashboard')], ['label' => 'Leave']]">

    <x-slot name="actions">
        @can('create', App\Models\LeaveRequest::class)
            <a href="{{ route('hr.leave.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Leave Request
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Pending" :value="$summary['pending']" icon="clock" color="amber" />
        <x-dashboard.stat-card label="Approved" :value="$summary['approved']" icon="badge" color="green" />
        <x-dashboard.stat-card label="Rejected" :value="$summary['rejected']" icon="alert" color="red" />
        <x-dashboard.stat-card label="Days Approved (YTD)" :value="number_format($summary['days_this_year'], 1)" icon="calendar" color="violet" />
    </div>

    <form method="GET" action="{{ route('hr.leave.index') }}" class="mt-6 mb-4 flex flex-wrap gap-2">
        <select name="employee" class="input max-w-[14rem]" onchange="this.form.submit()">
            <option value="">All employees</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected((string) request('employee') === (string) $employee->id)>{{ $employee->fullName() }}</option>
            @endforeach
        </select>
        <select name="type" class="input max-w-[12rem]" onchange="this.form.submit()">
            <option value="">All types</option>
            @foreach (\App\Models\LeaveRequest::TYPES as $key => $label)
                <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (\App\Models\LeaveRequest::STATUSES as $key => $label)
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-secondary">Filter</button>
    </form>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Employee</th><th>Type</th><th>Dates</th><th>Days</th><th>Reason</th><th>Status</th><th>Decided</th><th></th></tr></thead>
                <tbody>
                    @forelse ($requests as $leave)
                        <tr>
                            <td class="font-medium text-ink-800">{{ $leave->employee?->fullName() ?? '—' }}</td>
                            <td>{{ \App\Models\LeaveRequest::TYPES[$leave->type] ?? $leave->type }}</td>
                            <td>{{ $leave->start_date?->format('d M') }} – {{ $leave->end_date?->format('d M Y') }}</td>
                            <td>{{ number_format((float) $leave->days, 1) }}</td>
                            <td class="max-w-[16rem] truncate text-ink-500" title="{{ $leave->reason }}">{{ $leave->reason ?? '—' }}</td>
                            <td><span class="badge-{{ $leave->status === 'approved' ? 'green' : ($leave->status === 'pending' ? 'amber' : ($leave->status === 'rejected' ? 'red' : 'gray')) }}">{{ \App\Models\LeaveRequest::STATUSES[$leave->status] ?? $leave->status }}</span></td>
                            <td class="text-xs text-ink-400">{{ $leave->decided_at?->format('d M Y') ?? '—' }}@if($leave->approver)<br>{{ $leave->approver->displayName() }}@endif</td>
                            <td class="text-right">
                                @if ($leave->isPending())
                                    @can('update', $leave)
                                        <div class="flex justify-end gap-2">
                                            <form method="POST" action="{{ route('hr.leave.approve', $leave) }}">@csrf
                                                <button class="text-xs font-semibold text-emerald-600 hover:text-emerald-500">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('hr.leave.reject', $leave) }}">@csrf
                                                <button class="text-xs font-semibold text-red-600 hover:text-red-500">Reject</button>
                                            </form>
                                        </div>
                                    @endcan
                                @else
                                    @can('delete', $leave)
                                        <form method="POST" action="{{ route('hr.leave.destroy', $leave) }}" onsubmit="return confirm('Remove this leave request?')">@csrf @method('DELETE')
                                            <button class="text-xs font-medium text-ink-400 hover:text-red-600">Remove</button>
                                        </form>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-ink-400">No leave requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $requests->links() }}</div>
</x-layouts.app>

<x-layouts.app title="HR Dashboard" :breadcrumbs="[['label' => 'Human Resources'], ['label' => 'Dashboard']]">

    <x-slot name="actions">
        @can('create', App\Models\Employee::class)
            <a href="{{ route('hr.employees.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Employee
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Headcount" :value="$summary['headcount']" icon="users" color="blue" :sub="$summary['total_employees'].' total records'" :url="route('hr.employees.index')" />
        <x-dashboard.stat-card label="Present Today" :value="$summary['present_today']" icon="badge" color="green" :sub="$summary['on_leave'].' on leave'" :url="route('hr.attendance.index')" />
        <x-dashboard.stat-card label="Pending Leave" :value="$summary['pending_leave']" icon="clock" color="amber" :url="route('hr.leave.index')" />
        <x-dashboard.stat-card label="Avg. Performance" :value="number_format($summary['avg_performance'], 2).' / 5'" icon="chart" color="violet" :url="route('hr.reviews.index')" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Leave requests awaiting approval</h2>
                <a href="{{ route('hr.leave.index') }}" class="text-xs font-medium text-navy-600 hover:text-navy-500">View all</a>
            </div>
            <div class="divide-y divide-ink-100 dark:divide-ink-800">
                @forelse ($pendingRequests as $request)
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-ink-800 dark:text-ink-100">{{ $request->employee?->fullName() ?? 'Unknown employee' }}</p>
                            <p class="text-xs text-ink-500">{{ \App\Models\LeaveRequest::TYPES[$request->type] ?? $request->type }} · {{ $request->start_date?->format('d M') }} – {{ $request->end_date?->format('d M Y') }} · {{ number_format((float) $request->days, 1) }} day(s)</p>
                        </div>
                        @can('update', $request)
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('hr.leave.approve', $request) }}">@csrf
                                    <button class="badge-green border-0">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('hr.leave.reject', $request) }}">@csrf
                                    <button class="badge-red border-0">Reject</button>
                                </form>
                            </div>
                        @endcan
                    </div>
                @empty
                    <p class="p-6 text-center text-sm text-ink-400">No pending leave requests.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Onboarding in progress</h2>
                <a href="{{ route('hr.onboarding.index') }}" class="text-xs font-medium text-navy-600 hover:text-navy-500">Checklist</a>
            </div>
            <div class="divide-y divide-ink-100 dark:divide-ink-800">
                @forelse ($onboarding as $task)
                    <div class="flex items-center justify-between gap-3 p-4">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-ink-800 dark:text-ink-100">{{ $task->title }}</p>
                            <p class="text-xs text-ink-500">{{ $task->employee?->fullName() ?? '—' }} · due {{ $task->due_date?->format('d M Y') ?? '—' }}</p>
                        </div>
                        <span class="badge-{{ $task->status === 'blocked' ? 'red' : ($task->status === 'completed' ? 'green' : 'amber') }}">{{ \App\Models\OnboardingTask::STATUSES[$task->status] ?? $task->status }}</span>
                    </div>
                @empty
                    <p class="p-6 text-center text-sm text-ink-400">No open onboarding tasks.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Department distribution</h2>
                <a href="{{ route('hr.departments.index') }}" class="text-xs font-medium text-navy-600 hover:text-navy-500">Manage</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Department</th><th>Code</th><th>Employees</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($departments as $department)
                            <tr>
                                <td class="font-medium text-ink-800">{{ $department->name }}</td>
                                <td>{{ $department->code ?? '—' }}</td>
                                <td>{{ $department->employees_count }}</td>
                                <td><span class="badge-{{ $department->is_active ? 'green' : 'gray' }}">{{ $department->is_active ? 'Active' : 'Inactive' }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-ink-400">No departments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Latest payroll</h2>
                <a href="{{ route('hr.payroll.index') }}" class="text-xs font-medium text-navy-600 hover:text-navy-500">Payroll</a>
            </div>
            <div class="space-y-3 p-5 text-sm">
                @if ($lastPayroll)
                    <div class="flex justify-between"><span class="text-ink-400">Reference</span><span class="font-mono text-ink-800">{{ $lastPayroll->reference }}</span></div>
                    <div class="flex justify-between"><span class="text-ink-400">Period</span><span>{{ $lastPayroll->period_start?->format('d M') }} – {{ $lastPayroll->period_end?->format('d M Y') }}</span></div>
                    <div class="flex justify-between"><span class="text-ink-400">Employees</span><span>{{ $lastPayroll->employee_count }}</span></div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><span class="font-semibold text-ink-700">Net total</span><span class="font-bold text-ink-900">{{ number_format((float) $lastPayroll->net_total, 2) }}</span></div>
                    <span class="badge-{{ $lastPayroll->status === 'paid' ? 'green' : ($lastPayroll->status === 'approved' ? 'blue' : 'amber') }}">{{ \App\Models\PayrollRun::STATUSES[$lastPayroll->status] ?? $lastPayroll->status }}</span>
                @else
                    <p class="text-ink-400">No payroll runs yet.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-6 card">
        <div class="card-header">
            <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Recent performance reviews</h2>
            <a href="{{ route('hr.reviews.index') }}" class="text-xs font-medium text-navy-600 hover:text-navy-500">All reviews</a>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Employee</th><th>Period</th><th>Score</th><th>Rating</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse ($recentReviews as $review)
                        <tr>
                            <td class="font-medium text-ink-800">{{ $review->employee?->fullName() ?? '—' }}</td>
                            <td>{{ $review->period }}</td>
                            <td>{{ number_format((float) $review->overall_score, 2) }}</td>
                            <td>{{ $review->rating() }}</td>
                            <td><span class="badge-{{ $review->status === 'acknowledged' ? 'green' : ($review->status === 'submitted' ? 'blue' : 'gray') }}">{{ \App\Models\PerformanceReview::STATUSES[$review->status] ?? $review->status }}</span></td>
                            <td>{{ $review->review_date?->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-ink-400">No reviews yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>

<x-layouts.app
    :title="$employee->fullName()"
    :breadcrumbs="[['label' => 'Human Resources', 'url' => route('hr.dashboard')], ['label' => 'Employees', 'url' => route('hr.employees.index')], ['label' => $employee->fullName()]]">

    <x-slot name="actions">
        @can('update', $employee)
            <a href="{{ route('hr.employees.edit', $employee) }}" class="btn-secondary">Edit</a>
        @endcan
        @can('create', App\Models\OnboardingTask::class)
            <form method="POST" action="{{ route('hr.onboarding.seed', $employee) }}">@csrf
                <button class="btn-secondary">Generate Onboarding</button>
            </form>
        @endcan
        @can('delete', $employee)
            <form method="POST" action="{{ route('hr.employees.destroy', $employee) }}" onsubmit="return confirm('Archive this employee?')">@csrf @method('DELETE')
                <button class="btn-secondary text-red-600">Archive</button>
            </form>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-navy-100 text-lg font-bold text-navy-700">{{ $employee->initials() }}</span>
                    <div class="min-w-0">
                        <h2 class="truncate text-lg font-bold text-ink-900">{{ $employee->fullName() }}</h2>
                        <p class="text-sm text-ink-500">{{ $employee->job_title ?? '—' }}</p>
                        <span class="badge-{{ in_array($employee->status, ['active', 'probation']) ? 'green' : ($employee->status === 'on_leave' ? 'amber' : 'gray') }} mt-1">{{ \App\Models\Employee::STATUSES[$employee->status] ?? $employee->status }}</span>
                    </div>
                </div>
                <dl class="mt-5 space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Employee #</dt><dd class="font-mono">{{ $employee->employee_number }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Department</dt><dd>{{ $employee->department?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Branch</dt><dd>{{ $employee->branch?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Manager</dt><dd>{{ $employee->manager?->fullName() ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Email</dt><dd class="truncate">{{ $employee->email ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Phone</dt><dd>{{ $employee->phone ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Hire Date</dt><dd>{{ $employee->hire_date?->format('d M Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Employment</dt><dd>{{ \App\Models\Employee::EMPLOYMENT_TYPES[$employee->employment_type] ?? $employee->employment_type }}</dd></div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="text-ink-400">Base Salary</dt><dd class="font-semibold">{{ number_format((float) $employee->base_salary, 2) }} {{ $employee->currency_code }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Pay Frequency</dt><dd>{{ \App\Models\Employee::PAY_FREQUENCIES[$employee->pay_frequency] ?? $employee->pay_frequency }}</dd></div>
                </dl>
            </div>

            <div class="card">
                <h3 class="mb-3 text-sm font-bold text-ink-800">Emergency Contact</h3>
                <p class="text-sm text-ink-700">{{ $employee->emergency_contact_name ?? 'Not provided' }}</p>
                <p class="text-sm text-ink-500">{{ $employee->emergency_contact_phone ?? '' }}</p>
                @if ($employee->notes)
                    <p class="mt-3 whitespace-pre-line border-t border-ink-100 pt-3 text-sm text-ink-600">{{ $employee->notes }}</p>
                @endif
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-sm font-bold text-ink-800">Onboarding checklist</h3>
                    <a href="{{ route('hr.onboarding.index', ['employee' => $employee->id]) }}" class="text-xs font-medium text-navy-600 hover:text-navy-500">Manage</a>
                </div>
                <div class="divide-y divide-ink-100">
                    @forelse ($employee->onboardingTasks as $task)
                        <div class="flex items-center justify-between gap-3 px-5 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-ink-800">{{ $task->title }}</p>
                                <p class="text-xs text-ink-400">{{ \App\Models\OnboardingTask::CATEGORIES[$task->category] ?? $task->category }} · due {{ $task->due_date?->format('d M Y') ?? '—' }}</p>
                            </div>
                            @can('update', $task)
                                <form method="POST" action="{{ route($task->status === 'completed' ? 'hr.onboarding.reopen' : 'hr.onboarding.complete', $task) }}">@csrf
                                    <button class="badge-{{ $task->status === 'completed' ? 'green' : 'amber' }} border-0">
                                        {{ $task->status === 'completed' ? 'Completed' : 'Mark complete' }}
                                    </button>
                                </form>
                            @endcan
                        </div>
                    @empty
                        <p class="p-5 text-center text-sm text-ink-400">No onboarding tasks yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div class="card">
                    <h3 class="mb-3 text-sm font-bold text-ink-800">Recent leave</h3>
                    <div class="divide-y divide-ink-100">
                        @forelse ($employee->leaveRequests as $leave)
                            <div class="flex items-center justify-between gap-2 py-2">
                                <div>
                                    <p class="text-sm font-medium text-ink-800">{{ \App\Models\LeaveRequest::TYPES[$leave->type] ?? $leave->type }}</p>
                                    <p class="text-xs text-ink-400">{{ $leave->start_date?->format('d M') }} – {{ $leave->end_date?->format('d M Y') }}</p>
                                </div>
                                <span class="badge-{{ $leave->status === 'approved' ? 'green' : ($leave->status === 'pending' ? 'amber' : 'red') }}">{{ ucfirst($leave->status) }}</span>
                            </div>
                        @empty
                            <p class="py-4 text-sm text-ink-400">No leave records.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card">
                    <h3 class="mb-3 text-sm font-bold text-ink-800">Recent attendance</h3>
                    <div class="divide-y divide-ink-100">
                        @forelse ($employee->attendances as $attendance)
                            <div class="flex items-center justify-between gap-2 py-2">
                                <div>
                                    <p class="text-sm font-medium text-ink-800">{{ $attendance->attendance_date?->format('d M Y') }}</p>
                                    <p class="text-xs text-ink-400">{{ $attendance->check_in ?? '—' }} → {{ $attendance->check_out ?? '—' }} · {{ number_format((float) $attendance->worked_hours, 2) }}h</p>
                                </div>
                                <span class="badge-{{ in_array($attendance->status, ['present', 'remote']) ? 'green' : ($attendance->status === 'absent' ? 'red' : 'amber') }}">{{ \App\Models\Attendance::STATUSES[$attendance->status] ?? $attendance->status }}</span>
                            </div>
                        @empty
                            <p class="py-4 text-sm text-ink-400">No attendance records.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="text-sm font-bold text-ink-800">Performance reviews</h3>
                    @can('create', App\Models\PerformanceReview::class)
                        <a href="{{ route('hr.reviews.create', ['employee' => $employee->id]) }}" class="text-xs font-medium text-navy-600 hover:text-navy-500">New review</a>
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Period</th><th>Score</th><th>Rating</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            @forelse ($employee->performanceReviews as $review)
                                <tr>
                                    <td>{{ $review->period }}</td>
                                    <td>{{ number_format((float) $review->overall_score, 2) }}</td>
                                    <td>{{ $review->rating() }}</td>
                                    <td><span class="badge-{{ $review->status === 'acknowledged' ? 'green' : ($review->status === 'submitted' ? 'blue' : 'gray') }}">{{ \App\Models\PerformanceReview::STATUSES[$review->status] ?? $review->status }}</span></td>
                                    <td>{{ $review->review_date?->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-ink-400">No reviews yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

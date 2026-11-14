<x-layouts.app title="Onboarding" :breadcrumbs="[['label' => 'Human Resources', 'url' => route('hr.dashboard')], ['label' => 'Onboarding']]">

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Pending" :value="$summary['pending']" icon="clock" color="amber" />
        <x-dashboard.stat-card label="In Progress" :value="$summary['in_progress']" icon="arrow-up" color="blue" />
        <x-dashboard.stat-card label="Completed" :value="$summary['completed']" icon="badge" color="green" />
        <x-dashboard.stat-card label="Blocked" :value="$summary['blocked']" icon="alert" color="red" />
    </div>

    <form method="GET" action="{{ route('hr.onboarding.index') }}" class="mt-6 mb-4 flex flex-wrap gap-2">
        <select name="employee" class="input max-w-[14rem]" onchange="this.form.submit()">
            <option value="">All employees</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected((string) request('employee') === (string) $employee->id)>{{ $employee->fullName() }}</option>
            @endforeach
        </select>
        <select name="category" class="input max-w-[12rem]" onchange="this.form.submit()">
            <option value="">All categories</option>
            @foreach (\App\Models\OnboardingTask::CATEGORIES as $key => $label)
                <option value="{{ $key }}" @selected(request('category') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (\App\Models\OnboardingTask::STATUSES as $key => $label)
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-secondary">Filter</button>
    </form>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Checklist tasks</h2>
                <span class="text-xs text-ink-400">{{ $tasks->total() }} task(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Task</th><th>Employee</th><th>Category</th><th>Due</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($tasks as $task)
                            <tr>
                                <td>
                                    <p class="font-medium text-ink-800 dark:text-ink-100">{{ $task->title }}</p>
                                    @if ($task->notes)
                                        <p class="text-xs text-ink-400">{{ \Illuminate\Support\Str::limit($task->notes, 70) }}</p>
                                    @endif
                                </td>
                                <td>{{ $task->employee?->fullName() ?? '—' }}</td>
                                <td>{{ \App\Models\OnboardingTask::CATEGORIES[$task->category] ?? $task->category }}</td>
                                <td class="{{ $task->due_date && $task->due_date->isPast() && $task->status !== 'completed' ? 'font-semibold text-red-600' : '' }}">{{ $task->due_date?->format('d M Y') ?? '—' }}</td>
                                <td><span class="badge-{{ $task->status === 'completed' ? 'green' : ($task->status === 'blocked' ? 'red' : ($task->status === 'in_progress' ? 'blue' : 'amber')) }}">{{ \App\Models\OnboardingTask::STATUSES[$task->status] ?? $task->status }}</span></td>
                                <td class="text-right">
                                    @can('update', $task)
                                        <div class="flex justify-end gap-2">
                                            <form method="POST" action="{{ route($task->status === 'completed' ? 'hr.onboarding.reopen' : 'hr.onboarding.complete', $task) }}">@csrf
                                                <button class="text-xs font-semibold text-navy-600 hover:text-navy-500">{{ $task->status === 'completed' ? 'Reopen' : 'Complete' }}</button>
                                            </form>
                                            @can('delete', $task)
                                                <form method="POST" action="{{ route('hr.onboarding.destroy', $task) }}" onsubmit="return confirm('Remove this task?')">@csrf @method('DELETE')
                                                    <button class="text-xs font-medium text-ink-400 hover:text-red-600">Remove</button>
                                                </form>
                                            @endcan
                                        </div>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-8 text-center text-ink-400">No onboarding tasks match this filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3">{{ $tasks->links() }}</div>
        </div>

        <div class="card h-fit">
            <h2 class="mb-4 text-sm font-bold text-ink-800 dark:text-ink-100">Add a task</h2>
            @can('create', App\Models\OnboardingTask::class)
                <form method="POST" action="{{ route('hr.onboarding.store') }}" class="space-y-3">
                    @csrf
                    <x-form.select label="Employee *" name="employee_id" :options="$employees->mapWithKeys(fn ($e) => [$e->id => $e->fullName()])" :value="request('employee')" required />
                    <x-form.input label="Title *" name="title" required />
                    <x-form.select label="Category *" name="category" :options="\App\Models\OnboardingTask::CATEGORIES" value="general" required />
                    <x-form.input label="Due date" name="due_date" type="date" />
                    <x-form.select label="Status *" name="status" :options="\App\Models\OnboardingTask::STATUSES" value="pending" required />
                    <x-form.textarea label="Notes" name="notes" rows="2" />
                    <button type="submit" class="btn-accent w-full">Add Task</button>
                </form>
            @else
                <p class="text-sm text-ink-400">You do not have permission to add onboarding tasks.</p>
            @endcan
        </div>
    </div>
</x-layouts.app>

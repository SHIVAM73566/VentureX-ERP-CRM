<x-layouts.app title="Activities" :breadcrumbs="[['label' => 'CRM'], ['label' => 'Activities']]">

    <x-slot name="actions">
        @can('create', App\Models\Activity::class)
            <a href="{{ route('crm.activities.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Activity
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Open" :value="$summary['open']" icon="alert" color="blue" />
        <x-dashboard.stat-card label="Due Today" :value="$summary['due_today']" icon="clock" color="amber" />
        <x-dashboard.stat-card label="Overdue" :value="$summary['overdue']" icon="clock" color="red" />
        <x-dashboard.stat-card label="Completed" :value="$summary['completed']" icon="badge" color="green" />
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('crm.activities.index') }}" class="flex flex-1 flex-wrap items-center gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search title..." class="input max-w-xs" />
            <select name="type" class="input max-w-[9rem]" onchange="this.form.submit()">
                <option value="">All types</option>
                @foreach (\App\Models\Activity::TYPES as $key => $label)
                    <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="input max-w-[9rem]" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <option value="open" @selected(request('status') === 'open')>Open</option>
                <option value="completed" @selected(request('status') === 'completed')>Completed</option>
            </select>
            <select name="assigned_to" class="input max-w-[12rem]" onchange="this.form.submit()">
                <option value="">All assignees</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(request('assigned_to') == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
            <label class="flex items-center gap-2 text-sm text-ink-600">
                <input type="checkbox" name="mine" value="1" @checked(request('mine')) onchange="this.form.submit()" class="rounded border-ink-300">
                Assigned to me
            </label>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Activity</th><th>Type</th><th>Related To</th><th>Assignee</th><th>Due</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($activities as $activity)
                        <tr>
                            <td class="font-semibold text-ink-800">{{ $activity->title }}</td>
                            <td><span class="badge-blue">{{ \App\Models\Activity::TYPES[$activity->type] ?? $activity->type }}</span></td>
                            <td>
                                @if ($activity->subject)
                                    @switch($activity->subject_type)
                                        @case('customer')
                                            <a href="{{ route('customers.show', $activity->subject) }}" class="text-navy-600 hover:text-navy-500">{{ $activity->subject->name }}</a>
                                            @break
                                        @case('lead')
                                            <a href="{{ route('leads.show', $activity->subject) }}" class="text-navy-600 hover:text-navy-500">{{ $activity->subject->contact_name }}</a>
                                            @break
                                        @case('opportunity')
                                            <a href="{{ route('opportunities.show', $activity->subject) }}" class="text-navy-600 hover:text-navy-500">{{ $activity->subject->name }}</a>
                                            @break
                                    @endswitch
                                @else
                                    <span class="text-ink-400">—</span>
                                @endif
                            </td>
                            <td>{{ $activity->assignedTo?->name ?? '—' }}</td>
                            <td>
                                @if ($activity->due_at)
                                    <span class="{{ $activity->status === 'open' && $activity->due_at->isPast() ? 'font-semibold text-red-600' : '' }}">{{ $activity->due_at->format('d M Y H:i') }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td><span class="badge-{{ $activity->status === 'completed' ? 'green' : 'amber' }}">{{ ucfirst($activity->status) }}</span></td>
                            <td class="text-right whitespace-nowrap">
                                @can('update', $activity)
                                    @if ($activity->status !== 'completed')
                                        <form method="POST" action="{{ route('crm.activities.complete', $activity) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-sm font-medium text-emerald-600 hover:text-emerald-500">Complete</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('crm.activities.reopen', $activity) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-sm font-medium text-amber-600 hover:text-amber-500">Reopen</button>
                                        </form>
                                    @endif
                                @endcan
                                @can('delete', $activity)
                                    <form method="POST" action="{{ route('crm.activities.destroy', $activity) }}" class="inline" onsubmit="return confirm('Delete this activity?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ml-2 text-sm font-medium text-red-600 hover:text-red-500">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-ink-400">No activities yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $activities->links() }}</div>
</x-layouts.app>

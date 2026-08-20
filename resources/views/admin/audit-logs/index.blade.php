<x-layouts.app title="Audit Logs" :breadcrumbs="[['label' => 'Admin'], ['label' => 'Audit Logs']]">

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="flex flex-1 flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search module, event or user..." class="input max-w-xs" />
            <select name="module" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All modules</option>
                @foreach ($modules as $module)
                    <option value="{{ $module }}" @selected(request('module') === $module)>{{ $module }}</option>
                @endforeach
            </select>
            <select name="event" class="input max-w-[8rem]" onchange="this.form.submit()">
                <option value="">All events</option>
                @foreach ($events as $event)
                    <option value="{{ $event }}" @selected(request('event') === $event)>{{ $event }}</option>
                @endforeach
            </select>
            <select name="user_id" class="input max-w-[12rem]" onchange="this.form.submit()">
                <option value="">All users</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
            <input type="date" name="date" value="{{ request('date') }}" class="input max-w-[10rem]" onchange="this.form.submit()" />
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Event</th><th>Module</th><th>Record</th><th>User</th><th>IP</th><th>When</th><th></th></tr></thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td><span class="badge-{{ $log->event === 'create' ? 'green' : ($log->event === 'update' ? 'blue' : ($log->event === 'delete' ? 'red' : 'amber')) }}">{{ $log->event }}</span></td>
                            <td class="font-medium text-ink-800">{{ $log->module }}</td>
                            <td><span class="text-xs">{{ $log->record_type }}<span class="text-ink-400">#{{ $log->record_id ?? '—' }}</span></span></td>
                            <td>{{ $log->user?->name ?? 'System' }}</td>
                            <td class="font-mono text-xs text-ink-400">{{ $log->ip ?? '—' }}</td>
                            <td>{{ $log->created_at?->format('d M Y H:i') }}</td>
                            <td class="text-right"><a href="{{ route('admin.audit-logs.show', $log) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-ink-400">No audit logs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
</x-layouts.app>

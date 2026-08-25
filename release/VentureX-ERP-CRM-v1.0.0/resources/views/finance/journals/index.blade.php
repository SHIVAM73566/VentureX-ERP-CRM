<x-layouts.app title="Journal Entries" :breadcrumbs="[['label' => 'Finance'], ['label' => 'Journal Entries']]">

    <x-slot name="actions">
        @can('create', App\Models\JournalEntry::class)
            <a href="{{ route('finance.journals.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Entry
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-dashboard.stat-card label="Total Entries" :value="$summary['total']" icon="document" color="blue" />
        <x-dashboard.stat-card label="Posted" :value="$summary['posted']" icon="badge" color="green" />
        <x-dashboard.stat-card label="Draft" :value="$summary['draft']" icon="pencil" color="amber" />
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('finance.journals.index') }}" class="flex flex-1 flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search entry number or description..." class="input max-w-md" />
            <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach (\App\Models\JournalEntry::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Entry Number</th><th>Date</th><th>Description</th><th>Lines</th><th>Created By</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td class="font-semibold text-ink-800">{{ $entry->entry_number }}</td>
                            <td>{{ $entry->date?->format('d M Y') }}</td>
                            <td class="max-w-xs truncate">{{ $entry->description ?? '—' }}</td>
                            <td>{{ $entry->lines->count() }}</td>
                            <td>{{ $entry->createdBy?->name ?? '—' }}</td>
                            <td><span class="badge-{{ $entry->status === 'posted' ? 'green' : 'amber' }}">{{ \App\Models\JournalEntry::STATUSES[$entry->status] ?? $entry->status }}</span></td>
                            <td class="text-right"><a href="{{ route('finance.journals.show', $entry) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-ink-400">No journal entries yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $entries->links() }}</div>
</x-layouts.app>

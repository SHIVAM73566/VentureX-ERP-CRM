<x-layouts.app title="Leads" :breadcrumbs="[['label' => 'CRM'], ['label' => 'Leads']]">

    <x-slot name="actions">
        @can('create', App\Models\Lead::class)
            <a href="{{ route('leads.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Lead
            </a>
        @endcan
    </x-slot>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('leads.index') }}" class="flex flex-1 gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search leads..." class="input max-w-md" />
            <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach ($statuses as $key => $status)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $status }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Search</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Company</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Next Follow-up</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leads as $lead)
                        <tr>
                            <td>
                                <p class="font-semibold text-ink-800 dark:text-ink-100">{{ $lead->contact_name }}</p>
                                <p class="text-xs text-ink-400 dark:text-ink-500">{{ $lead->lead_number }} • {{ $lead->source ?? '—' }}</p>
                            </td>
                            <td>{{ $lead->company_name ?? '—' }}</td>
                            <td>@if ($lead->estimated_value) {{ $lead->estimated_value }} @else — @endif</td>
                            <td><span class="badge-{{ $lead->status === 'won' ? 'green' : ($lead->status === 'lost' ? 'red' : ($lead->status === 'new' ? 'amber' : 'blue')) }}">{{ $statuses[$lead->status] }}</span></td>
                            <td>{{ $lead->assignedTo?->name ?? '—' }}</td>
                            <td>{{ $lead->next_follow_up?->format('d M Y') ?? '—' }}</td>
                            <td class="text-right">
                                <a href="{{ route('leads.show', $lead) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500 dark:text-navy-400 dark:hover:text-navy-300">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-ink-400 dark:text-ink-500">No leads found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $leads->links() }}</div>
</x-layouts.app>

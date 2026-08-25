<x-layouts.app title="Opportunities" :breadcrumbs="[['label' => 'CRM'], ['label' => 'Opportunities']]">

    <x-slot name="actions">
        @can('create', App\Models\Opportunity::class)
            <a href="{{ route('opportunities.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Opportunity
            </a>
        @endcan
    </x-slot>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('opportunities.index') }}" class="flex flex-1 gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search opportunities..." class="input max-w-md" />
            <select name="stage" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All stages</option>
                @foreach ($stages as $key => $stage)
                    <option value="{{ $key }}" @selected(request('stage') === $key)>{{ $stage }}</option>
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
                        <th>Opportunity</th>
                        <th>Customer</th>
                        <th>Stage</th>
                        <th>Value</th>
                        <th>Probability</th>
                        <th>Close</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($opportunities as $opportunity)
                        <tr>
                            <td class="font-semibold text-ink-800">{{ $opportunity->name }}</td>
                            <td>{{ $opportunity->customer?->name ?? '—' }}</td>
                            <td>
                                <span class="badge-{{ $opportunity->stage === 'won' ? 'green' : ($opportunity->stage === 'lost' ? 'red' : 'blue') }}">{{ $stages[$opportunity->stage] }}</span>
                            </td>
                            <td>{{ number_format((float) $opportunity->expected_value, 2) }}</td>
                            <td>{{ $opportunity->probability ? round((float) $opportunity->probability, 0).'%' : '—' }}</td>
                            <td>{{ $opportunity->expected_close_date?->format('d M Y') ?? '—' }}</td>
                            <td class="text-right">
                                <a href="{{ route('opportunities.show', $opportunity) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-ink-400">No opportunities found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $opportunities->links() }}</div>
</x-layouts.app>

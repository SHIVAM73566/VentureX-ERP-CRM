<x-layouts.app title="Search" :breadcrumbs="[['label' => 'Search']]">

    <div class="mx-auto max-w-4xl">
        <form method="GET" action="{{ route('search') }}" class="mb-8 flex gap-2">
            <input type="search" name="q" value="{{ $q }}" placeholder="Search customers, leads, suppliers, offers, documents…" class="input flex-1" autofocus />
            <button type="submit" class="btn-accent">Search</button>
        </form>

        @if (strlen($q) >= 2)
            @if (collect($results)->flatten()->isEmpty())
                <p class="text-center text-ink-400">No results for “{{ $q }}”.</p>
            @else
                <div class="space-y-6">
                    @foreach ($results as $group => $items)
                        @if ($items->isNotEmpty())
                            <div>
                                <h2 class="mb-2 text-xs font-bold uppercase tracking-wide text-ink-400">{{ ucfirst($group) }} ({{ $items->count() }})</h2>
                                <div class="card divide-y divide-ink-100">
                                    @foreach ($items as $item)
                                        @php
                                            $route = match ($group) {
                                                'customers' => route('customers.show', $item),
                                                'leads' => route('leads.show', $item),
                                                'opportunities' => route('opportunities.show', $item),
                                                'suppliers' => route('suppliers.show', $item),
                                                'offers' => route('supplier-offers.show', $item),
                                                'documents' => null,
                                            };
                                        @endphp
                                        <a href="{{ $route ?? '#' }}" class="flex items-center justify-between px-4 py-3 hover:bg-ink-50">
                                            <div>
                                                <p class="font-medium text-ink-800">
                                                    {{ $item->name ?? $item->contact_name ?? $item->material_description ?? $item->title ?? '—' }}
                                                </p>
                                                <p class="text-xs text-ink-400">{{ $item->email ?? $item->company_name ?? $item->supplier?->name ?? '—' }}</p>
                                            </div>
                                            <svg class="h-4 w-4 text-ink-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        @else
            <p class="text-center text-ink-400">Type at least 2 characters to search.</p>
        @endif
    </div>
</x-layouts.app>

<x-layouts.app title="RFQs" :breadcrumbs="[['label' => 'Procurement'], ['label' => 'RFQs']]">

    <x-slot name="actions">
        @can('create', App\Models\Rfq::class)
            <a href="{{ route('procurement.rfqs.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New RFQ
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Total RFQs" :value="$summary['total']" icon="document" color="blue" />
        <x-dashboard.stat-card label="Open" :value="$summary['open']" icon="alert" color="amber" />
        <x-dashboard.stat-card label="Awarded" :value="$summary['awarded']" icon="badge" color="green" />
        <x-dashboard.stat-card label="Supplier Responses" :value="$summary['responses']" icon="users" color="violet" />
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('procurement.rfqs.index') }}" class="flex flex-1 flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search RFQ number or title..." class="input max-w-md" />
            <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach (\App\Models\Rfq::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>RFQ Number</th><th>Title</th><th>Items</th><th>Responses</th><th>Closes</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($rfqs as $rfq)
                        <tr>
                            <td class="font-semibold text-ink-800">{{ $rfq->rfq_number }}</td>
                            <td class="max-w-xs truncate">{{ $rfq->title }}</td>
                            <td>{{ $rfq->items->count() }}</td>
                            <td>{{ $rfq->responses->count() }}</td>
                            <td>{{ $rfq->closes_at?->format('d M Y') ?? '—' }}</td>
                            <td>@php $sc = ['draft' => 'gray', 'sent' => 'blue', 'open' => 'amber', 'awarded' => 'green', 'cancelled' => 'red']; @endphp
                                <span class="badge-{{ $sc[$rfq->status] ?? 'gray' }}">{{ \App\Models\Rfq::STATUSES[$rfq->status] ?? $rfq->status }}</span>
                            </td>
                            <td class="text-right"><a href="{{ route('procurement.rfqs.show', $rfq) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-ink-400">No RFQs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $rfqs->links() }}</div>
</x-layouts.app>

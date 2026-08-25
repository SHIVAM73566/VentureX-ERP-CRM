<x-layouts.app title="Quotations" :breadcrumbs="[['label' => 'Sales'], ['label' => 'Quotations']]">

    <x-slot name="actions">
        @can('create', App\Models\Quotation::class)
            <a href="{{ route('sales.quotations.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Quotation
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Total Quotations" :value="$summary['total']" icon="file" color="blue" />
        <x-dashboard.stat-card label="Open (Draft/Sent)" :value="$summary['open']" icon="target" color="amber" />
        <x-dashboard.stat-card label="Accepted" :value="$summary['accepted']" icon="badge" color="green" />
        <x-dashboard.stat-card label="Total Value" :value="number_format($summary['value'], 2)" icon="wallet" color="violet" />
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('sales.quotations.index') }}" class="flex flex-1 gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by number or customer..." class="input max-w-md" />
            <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach (\App\Models\Quotation::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Quotation</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Valid Until</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quotations as $quotation)
                        <tr>
                            <td><p class="font-semibold text-ink-800">{{ $quotation->quotation_number }}</p><p class="text-xs text-ink-400">{{ $quotation->items_count }} items</p></td>
                            <td>{{ $quotation->customer?->name ?? '—' }}</td>
                            <td>{{ $quotation->created_at?->format('d M Y') }}</td>
                            <td>{{ $quotation->valid_until?->format('d M Y') ?? '—' }}</td>
                            <td>{{ number_format((float) $quotation->total, 2) }}</td>
                            <td>
                                @php $c = ['draft' => 'gray', 'sent' => 'amber', 'accepted' => 'green', 'rejected' => 'red', 'expired' => 'gray', 'converted' => 'blue']; @endphp
                                <span class="badge-{{ $c[$quotation->status] ?? 'gray' }}">{{ \App\Models\Quotation::STATUSES[$quotation->status] ?? $quotation->status }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('sales.quotations.show', $quotation) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-ink-400">No quotations yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $quotations->links() }}</div>
</x-layouts.app>

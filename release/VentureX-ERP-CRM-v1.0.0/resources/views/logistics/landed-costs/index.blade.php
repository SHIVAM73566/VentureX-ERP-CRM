<x-layouts.app title="Landed Costs" :breadcrumbs="[['label' => 'Logistics'], ['label' => 'Landed Costs']]">

    <x-slot name="actions">
        @can('create', App\Models\LandedCost::class)
            <a href="{{ route('logistics.landed-costs.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Record Cost
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Total Costs" :value="number_format($summary['total'], 2)" icon="wallet" color="violet" />
        <x-dashboard.stat-card label="Freight" :value="number_format($summary['freight'], 2)" icon="truck" color="blue" />
        <x-dashboard.stat-card label="Customs Duty" :value="number_format($summary['duties'], 2)" icon="document" color="amber" />
        <x-dashboard.stat-card label="Entries" :value="$summary['count']" icon="receipt" color="green" />
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('logistics.landed-costs.index') }}" class="flex flex-1 flex-wrap gap-2">
            <select name="cost_type" class="input max-w-[12rem]" onchange="this.form.submit()">
                <option value="">All cost types</option>
                @foreach (\App\Models\LandedCost::COST_TYPES as $key => $label)
                    <option value="{{ $key }}" @selected(request('cost_type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Cost Type</th><th>Description</th><th>Shipment</th><th>Container</th><th>Paid To</th><th>Paid At</th><th class="text-right">Amount</th><th></th></tr></thead>
                <tbody>
                    @forelse ($costs as $cost)
                        <tr>
                            <td><span class="badge-{{ $cost->cost_type === 'freight' ? 'blue' : ($cost->cost_type === 'customs_duty' ? 'amber' : 'gray') }}">{{ \App\Models\LandedCost::COST_TYPES[$cost->cost_type] ?? $cost->cost_type }}</span></td>
                            <td>{{ $cost->description ?? '—' }}</td>
                            <td>@if ($cost->shipment)<a href="{{ route('logistics.shipments.show', $cost->shipment) }}" class="text-navy-600 hover:text-navy-500">{{ $cost->shipment->shipment_number }}</a>@else — @endif</td>
                            <td>{{ $cost->container?->container_number ?? '—' }}</td>
                            <td>{{ $cost->paid_to ?? '—' }}</td>
                            <td>{{ $cost->paid_at?->format('d M Y') ?? '—' }}</td>
                            <td class="text-right font-semibold text-ink-800">{{ number_format((float) $cost->amount, 2) }} {{ $cost->currency ? strtoupper($cost->currency) : 'INR' }}</td>
                            <td class="text-right">
                                @can('update', $cost)
                                    <a href="{{ route('logistics.landed-costs.edit', $cost) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">Edit</a>
                                @endcan
                                @can('delete', $cost)
                                    <form method="POST" action="{{ route('logistics.landed-costs.destroy', $cost) }}" class="inline" onsubmit="return confirm('Delete this landed cost?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ml-2 text-sm font-medium text-red-600 hover:text-red-500">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-ink-400">No landed costs recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $costs->links() }}</div>
</x-layouts.app>

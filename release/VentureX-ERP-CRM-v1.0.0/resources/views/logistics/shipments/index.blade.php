<x-layouts.app title="Shipments" :breadcrumbs="[['label' => 'Logistics'], ['label' => 'Shipments']]">

    <x-slot name="actions">
        @can('create', App\Models\Shipment::class)
            <a href="{{ route('logistics.shipments.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Shipment
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Total Shipments" :value="$summary['total']" icon="truck" color="blue" />
        <x-dashboard.stat-card label="In Transit" :value="$summary['in_transit']" icon="alert" color="amber" />
        <x-dashboard.stat-card label="Delivered" :value="$summary['delivered']" icon="badge" color="green" />
        <x-dashboard.stat-card label="Delayed" :value="$summary['delayed']" icon="clock" color="red" />
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('logistics.shipments.index') }}" class="flex flex-1 flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search number, tracking, origin, destination..." class="input max-w-md" />
            <select name="type" class="input max-w-[9rem]" onchange="this.form.submit()">
                <option value="">All types</option>
                @foreach (\App\Models\Shipment::TYPES as $key => $label)
                    <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach (\App\Models\Shipment::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Shipment</th><th>Type</th><th>Mode</th><th>Route</th><th>Carrier</th><th>Container</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($shipments as $shipment)
                        <tr>
                            <td>
                                <p class="font-semibold text-ink-800">{{ $shipment->shipment_number }}</p>
                                <p class="text-xs text-ink-400">{{ $shipment->tracking_number ?? '' }}</p>
                            </td>
                            <td><span class="badge-{{ $shipment->type === 'outbound' ? 'blue' : 'violet' }}">{{ \App\Models\Shipment::TYPES[$shipment->type] ?? $shipment->type }}</span></td>
                            <td>{{ \App\Models\Shipment::MODES[$shipment->mode] ?? $shipment->mode }}</td>
                            <td>{{ $shipment->origin ?? '—' }} → {{ $shipment->destination ?? '—' }}</td>
                            <td>{{ $shipment->carrier ?? '—' }}</td>
                            <td>{{ $shipment->container?->container_number ?? '—' }}</td>
                            <td>@php $sc = ['draft' => 'gray', 'scheduled' => 'blue', 'in_transit' => 'amber', 'customs' => 'violet', 'delivered' => 'green', 'delayed' => 'red', 'cancelled' => 'gray']; @endphp
                                <span class="badge-{{ $sc[$shipment->status] ?? 'gray' }}">{{ \App\Models\Shipment::STATUSES[$shipment->status] ?? $shipment->status }}</span>
                            </td>
                            <td class="text-right"><a href="{{ route('logistics.shipments.show', $shipment) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-ink-400">No shipments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $shipments->links() }}</div>
</x-layouts.app>

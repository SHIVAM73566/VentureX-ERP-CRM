<x-layouts.app
    :title="'Shipment — '.($shipment->shipment_number ?? 'New')"
    :breadcrumbs="[['label' => 'Logistics', 'url' => route('logistics.shipments.index')], ['label' => 'Shipments', 'url' => route('logistics.shipments.index')], ['label' => $shipment->shipment_number]]">

    <x-slot name="actions">
        @can('update', $shipment)
            <a href="{{ route('logistics.shipments.edit', $shipment) }}" class="btn-secondary">Edit</a>
        @endcan
        @can('delete', $shipment)
            <form method="POST" action="{{ route('logistics.shipments.destroy', $shipment) }}" onsubmit="return confirm('Delete this shipment?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-secondary text-red-600 hover:bg-red-50">Delete</button>
            </form>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Shipment Summary</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd>{{ $shipment->shipment_number }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Type</dt><dd><span class="badge-{{ $shipment->type === 'outbound' ? 'blue' : 'violet' }}">{{ \App\Models\Shipment::TYPES[$shipment->type] ?? $shipment->type }}</span></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Mode</dt><dd>{{ \App\Models\Shipment::MODES[$shipment->mode] ?? $shipment->mode }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                    <dd>@php $sc = ['draft' => 'gray', 'scheduled' => 'blue', 'in_transit' => 'amber', 'customs' => 'violet', 'delivered' => 'green', 'delayed' => 'red', 'cancelled' => 'gray']; @endphp
                        <span class="badge-{{ $sc[$shipment->status] ?? 'gray' }}">{{ \App\Models\Shipment::STATUSES[$shipment->status] ?? $shipment->status }}</span></dd>
                </div>
                <div class="flex justify-between"><dt class="text-ink-400">Carrier</dt><dd>{{ $shipment->carrier ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Tracking</dt><dd>{{ $shipment->tracking_number ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Container</dt><dd>{{ $shipment->container?->container_number ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Customer</dt><dd>{{ $shipment->customer?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Supplier</dt><dd>{{ $shipment->supplier?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Sales Order</dt>
                    <dd>@if ($shipment->salesOrder)<a href="{{ route('sales.orders.show', $shipment->salesOrder) }}" class="text-navy-600 hover:text-navy-500">{{ $shipment->salesOrder->order_number }}</a>@else — @endif</dd>
                </div>
                <div class="flex justify-between"><dt class="text-ink-400">Purchase Order</dt>
                    <dd>@if ($shipment->purchaseOrder)<a href="{{ route('procurement.orders.show', $shipment->purchaseOrder) }}" class="text-navy-600 hover:text-navy-500">{{ $shipment->purchaseOrder->po_number }}</a>@else — @endif</dd>
                </div>
                <div class="flex justify-between"><dt class="text-ink-400">Route</dt><dd class="text-right">{{ $shipment->origin ?? '—' }} → {{ $shipment->destination ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Departure</dt><dd>{{ $shipment->departure_date?->format('d M Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Arrival</dt><dd>{{ $shipment->arrival_date?->format('d M Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Weight / Volume</dt><dd>{{ $shipment->weight !== null ? number_format((float) $shipment->weight, 2).' kg' : '—' }} / {{ $shipment->volume !== null ? number_format((float) $shipment->volume, 2).' m³' : '—' }}</dd></div>
            </dl>
        </div>
    </div>
</x-layouts.app>

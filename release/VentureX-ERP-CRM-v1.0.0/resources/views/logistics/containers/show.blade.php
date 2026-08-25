<x-layouts.app
    :title="'Container — '.($container->container_number ?? 'New')"
    :breadcrumbs="[['label' => 'Logistics', 'url' => route('logistics.containers.index')], ['label' => 'Containers', 'url' => route('logistics.containers.index')], ['label' => $container->container_number]]">

    <x-slot name="actions">
        @can('update', $container)
            <a href="{{ route('logistics.containers.edit', $container) }}" class="btn-secondary">Edit</a>
        @endcan
        @can('delete', $container)
            <form method="POST" action="{{ route('logistics.containers.destroy', $container) }}" onsubmit="return confirm('Delete this container?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-secondary text-red-600 hover:bg-red-50">Delete</button>
            </form>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Container Summary</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd>{{ $container->container_number }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Size</dt><dd>{{ $container->size ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Seal Number</dt><dd>{{ $container->seal_number ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                    <dd><span class="badge-{{ $container->status === 'available' ? 'green' : ($container->status === 'returned' ? 'gray' : 'amber') }}">{{ \App\Models\Container::STATUSES[$container->status] ?? $container->status }}</span></dd>
                </div>
                <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Shipments</dt><dd class="font-bold text-ink-900">{{ $container->shipments->count() }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Landed Costs</dt><dd>{{ $container->landedCosts->count() }}</dd></div>
            </dl>

            @if ($container->notes)
                <div class="mt-4 border-t border-ink-100 pt-3"><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Notes</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700">{{ $container->notes }}</p></div>
            @endif
        </div>

        <div class="card lg:col-span-2">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Shipments</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Shipment</th><th>Type</th><th>Route</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($container->shipments as $shipment)
                            <tr>
                                <td><a href="{{ route('logistics.shipments.show', $shipment) }}" class="font-semibold text-navy-600 hover:text-navy-500">{{ $shipment->shipment_number }}</a></td>
                                <td><span class="badge-{{ $shipment->type === 'outbound' ? 'blue' : 'violet' }}">{{ \App\Models\Shipment::TYPES[$shipment->type] ?? $shipment->type }}</span></td>
                                <td>{{ $shipment->origin ?? '—' }} → {{ $shipment->destination ?? '—' }}</td>
                                <td><span class="badge-{{ $shipment->status === 'delivered' ? 'green' : ($shipment->status === 'in_transit' ? 'amber' : 'gray') }}">{{ \App\Models\Shipment::STATUSES[$shipment->status] ?? $shipment->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-ink-400">No shipments for this container.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>

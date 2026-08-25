<x-layouts.app
    :title="$shipment->exists ? 'Edit Shipment' : 'New Shipment'"
    :breadcrumbs="[['label' => 'Logistics', 'url' => route('logistics.shipments.index')], ['label' => 'Shipments', 'url' => route('logistics.shipments.index')], ['label' => $shipment->exists ? 'Edit' : 'New']]">

    <div class="mx-auto max-w-4xl">
        <form method="POST" action="{{ $shipment->exists ? route('logistics.shipments.update', $shipment) : route('logistics.shipments.store') }}" class="space-y-6">
            @csrf
            @if ($shipment->exists)
                @method('PUT')
            @endif

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Shipment Details</h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-form.select label="Type *" name="type" :options="\App\Models\Shipment::TYPES" :value="$shipment->type ?? 'outbound'" required />
                    <x-form.select label="Mode *" name="mode" :options="\App\Models\Shipment::MODES" :value="$shipment->mode ?? 'sea'" required />
                    <x-form.select label="Status *" name="status" :options="\App\Models\Shipment::STATUSES" :value="$shipment->status ?? 'draft'" required />
                    <x-form.select label="Container" name="container_id" :options="$containers->pluck('container_number', 'id')" :value="$shipment->container_id" />
                    <x-form.select label="Customer" name="customer_id" :options="$customers->pluck('name', 'id')" :value="$shipment->customer_id" />
                    <x-form.select label="Supplier" name="supplier_id" :options="$suppliers->pluck('name', 'id')" :value="$shipment->supplier_id" />
                    <x-form.select label="Sales Order" name="sales_order_id" :options="$salesOrders->pluck('order_number', 'id')" :value="$shipment->sales_order_id" />
                    <x-form.select label="Purchase Order" name="purchase_order_id" :options="$purchaseOrders->pluck('po_number', 'id')" :value="$shipment->purchase_order_id" />
                    <x-form.input label="Tracking Number" name="tracking_number" :value="$shipment->tracking_number" />
                    <x-form.input label="Carrier" name="carrier" :value="$shipment->carrier" />
                    <x-form.input label="Origin" name="origin" :value="$shipment->origin" />
                    <x-form.input label="Destination" name="destination" :value="$shipment->destination" />
                    <x-form.input label="Departure Date" name="departure_date" type="date" :value="$shipment->departure_date?->format('Y-m-d')" />
                    <x-form.input label="Arrival Date" name="arrival_date" type="date" :value="$shipment->arrival_date?->format('Y-m-d')" />
                    <x-form.input label="Shipped At" name="shipped_at" type="date" :value="$shipment->shipped_at?->format('Y-m-d')" />
                    <x-form.input label="Delivered At" name="delivered_at" type="date" :value="$shipment->delivered_at?->format('Y-m-d')" />
                    <x-form.input label="Weight (kg)" name="weight" type="number" step="0.01" min="0" :value="$shipment->weight" />
                    <x-form.input label="Volume (m³)" name="volume" type="number" step="0.01" min="0" :value="$shipment->volume" />
                </div>
            </div>

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Notes</h2>
                <x-form.textarea label="Notes" name="notes" :value="$shipment->notes" rows="3" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('logistics.shipments.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">{{ $shipment->exists ? 'Save Changes' : 'Create Shipment' }}</button>
            </div>
        </form>
    </div>
</x-layouts.app>

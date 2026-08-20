<x-layouts.app
    :title="$cost->exists ? 'Edit Landed Cost' : 'Record Landed Cost'"
    :breadcrumbs="[['label' => 'Logistics', 'url' => route('logistics.landed-costs.index')], ['label' => 'Landed Costs', 'url' => route('logistics.landed-costs.index')], ['label' => $cost->exists ? 'Edit' : 'New']]">

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ $cost->exists ? route('logistics.landed-costs.update', $cost) : route('logistics.landed-costs.store') }}" class="space-y-6">
            @csrf
            @if ($cost->exists)
                @method('PUT')
            @endif

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Cost Details</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.select label="Cost Type *" name="cost_type" :options="\App\Models\LandedCost::COST_TYPES" :value="$cost->cost_type" required />
                    <x-form.input label="Description" name="description" :value="$cost->description" placeholder="e.g. Ocean freight — Shanghai → Mundra" />
                    <x-form.select label="Shipment" name="shipment_id" :options="$shipments->pluck('shipment_number', 'id')" :value="$cost->shipment_id" />
                    <x-form.select label="Container" name="container_id" :options="$containers->pluck('container_number', 'id')" :value="$cost->container_id" />
                    <x-form.input label="Amount *" name="amount" type="number" step="0.01" min="0.01" :value="$cost->amount" required />
                    <x-form.input label="Currency" name="currency" :value="$cost->currency ?? 'INR'" />
                    <x-form.input label="Paid To" name="paid_to" :value="$cost->paid_to" />
                    <x-form.input label="Paid At" name="paid_at" type="date" :value="$cost->paid_at?->format('Y-m-d')" />
                </div>
                <x-form.textarea label="Notes" name="notes" :value="$cost->notes" rows="2" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('logistics.landed-costs.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">{{ $cost->exists ? 'Save Changes' : 'Record Cost' }}</button>
            </div>
        </form>
    </div>
</x-layouts.app>

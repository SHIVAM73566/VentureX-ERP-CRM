<x-layouts.app
    :title="$warehouse->exists ? 'Edit Warehouse' : 'New Warehouse'"
    :breadcrumbs="[['label' => 'Inventory', 'url' => route('inventory.warehouses.index')], ['label' => 'Warehouses', 'url' => route('inventory.warehouses.index')], ['label' => $warehouse->exists ? 'Edit' : 'New']]">

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ $warehouse->exists ? route('inventory.warehouses.update', $warehouse) : route('inventory.warehouses.store') }}" class="space-y-6">
            @csrf
            @if ($warehouse->exists)
                @method('PUT')
            @endif

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Warehouse Details</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input label="Code *" name="code" :value="$warehouse->code" required />
                    <x-form.input label="Name *" name="name" :value="$warehouse->name" required />
                    <x-form.input label="Location" name="location" :value="$warehouse->location" />
                    <x-form.select label="Manager" name="manager_id" :options="$managers->pluck('name', 'id')" :value="$warehouse->manager_id" />
                    <x-form.input label="Capacity" name="capacity" type="number" step="0.01" min="0" :value="$warehouse->capacity" />
                    <x-form.select label="Status" name="status" :options="\App\Models\Warehouse::STATUSES" :value="$warehouse->status ?? 'active'" />
                </div>
                <x-form.checkbox name="is_default" label="Default Warehouse" description="Set as the primary warehouse for this company." :checked="$warehouse->is_default" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('inventory.warehouses.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">{{ $warehouse->exists ? 'Save Changes' : 'Create Warehouse' }}</button>
            </div>
        </form>
    </div>
</x-layouts.app>

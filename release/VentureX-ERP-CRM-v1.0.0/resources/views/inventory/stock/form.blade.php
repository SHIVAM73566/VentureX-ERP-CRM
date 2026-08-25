<x-layouts.app
    :title="'Record Stock Movement'"
    :breadcrumbs="[['label' => 'Inventory', 'url' => route('inventory.stock.index')], ['label' => 'Stock', 'url' => route('inventory.stock.index')], ['label' => 'New']]">

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('inventory.stock.store') }}" class="space-y-6">
            @csrf

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Movement Details</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.select label="Product *" name="product_id" :options="$products->pluck('name', 'id')" :value="request('product_id')" required />
                    <x-form.select label="Warehouse *" name="warehouse_id" :options="$warehouses->pluck('name', 'id')" :value="request('warehouse_id')" required />
                    <x-form.select label="Type *" name="type" :options="\App\Models\StockMovement::TYPES" required />
                    <x-form.input label="Quantity *" name="quantity" type="number" step="0.0001" min="0.0001" required />
                    <x-form.input label="Unit Cost" name="unit_cost" type="number" step="0.01" min="0" />
                </div>
                <x-form.textarea label="Note" name="note" rows="2" placeholder="e.g. Received against PO-…, shipped to customer, manual adjustment" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('inventory.stock.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">Record Movement</button>
            </div>
        </form>
    </div>
</x-layouts.app>

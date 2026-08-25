<x-layouts.app
    :title="$product->exists ? 'Edit Product' : 'New Product'"
    :breadcrumbs="[['label' => 'Inventory', 'url' => route('inventory.products.index')], ['label' => 'Products', 'url' => route('inventory.products.index')], ['label' => $product->exists ? 'Edit' : 'New']]">

    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ $product->exists ? route('inventory.products.update', $product) : route('inventory.products.store') }}" class="space-y-6">
            @csrf
            @if ($product->exists)
                @method('PUT')
            @endif

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Product Details</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input label="Name *" name="name" :value="$product->name" required />
                    <x-form.input label="SKU" name="sku" :value="$product->sku" />
                    <x-form.input label="Category" name="category" :value="$product->category" />
                    <x-form.select label="Unit" name="unit_id" :options="$units->pluck('name', 'id')" :value="$product->unit_id" />
                    <x-form.select label="Tax Rate" name="tax_rate_id" :options="$taxRates->pluck('name', 'id')" :value="$product->tax_rate_id" />
                    <x-form.select label="Preferred Supplier" name="supplier_id" :options="$suppliers->pluck('name', 'id')" :value="$product->supplier_id" />
                    <x-form.input label="Purchase Price" name="purchase_price" type="number" step="0.01" min="0" :value="$product->purchase_price" />
                    <x-form.input label="Selling Price" name="selling_price" type="number" step="0.01" min="0" :value="$product->selling_price" />
                    <x-form.input label="Reorder Level" name="reorder_level" type="number" step="0.0001" min="0" :value="$product->reorder_level" help="Alerts when stock falls to this level." />
                    <x-form.select label="Status" name="status" :options="\App\Models\Product::STATUSES" :value="$product->status ?? 'active'" />
                </div>
                <x-form.textarea label="Description" name="description" :value="$product->description" rows="3" />
                <x-form.textarea label="Notes" name="notes" :value="$product->notes" rows="2" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('inventory.products.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">{{ $product->exists ? 'Save Changes' : 'Create Product' }}</button>
            </div>
        </form>
    </div>
</x-layouts.app>

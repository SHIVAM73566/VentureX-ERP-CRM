<x-layouts.app
    :title="'Product — '.($product->name ?? 'New')"
    :breadcrumbs="[['label' => 'Inventory', 'url' => route('inventory.products.index')], ['label' => 'Products', 'url' => route('inventory.products.index')], ['label' => $product->name]]">

    <x-slot name="actions">
        @can('viewAny', App\Models\AiRun::class)
            <x-ai.action label="AI stock insight" :url="route('ai.actions.inventory')" :payload="['question' => 'Analyse the current stock position and reorder situation for this product.', 'product_id' => $product->id]" />
        @endcan
        @can('create', App\Models\StockMovement::class)
            <a href="{{ route('inventory.stock.create') }}" class="btn-accent">+ Stock Movement</a>
        @endcan
        @can('update', $product)
            <a href="{{ route('inventory.products.edit', $product) }}" class="btn-secondary">Edit</a>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Product Summary</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">SKU</dt><dd>{{ $product->sku ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Category</dt><dd>{{ $product->category ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Unit</dt><dd>{{ $product->unit?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Purchase Price</dt><dd>{{ number_format((float) $product->purchase_price, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Selling Price</dt><dd>{{ number_format((float) $product->selling_price, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Tax</dt><dd>{{ $product->taxRate?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Supplier</dt><dd class="text-right">{{ $product->supplier?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Reorder Level</dt><dd>{{ $product->reorder_level !== null ? number_format((float) $product->reorder_level, 4) : '—' }}</dd></div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Available Stock</dt><dd class="font-bold text-ink-900">{{ number_format($product->availableStock(), 4) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                        <dd>@if ($product->isLowStock())<span class="badge-red">Low Stock</span>@else<span class="badge-{{ $product->status === 'active' ? 'green' : 'gray' }}">{{ \App\Models\Product::STATUSES[$product->status] ?? $product->status }}</span>@endif</dd>
                    </div>
                </dl>
            </div>

            @if ($product->description || $product->notes)
                <div class="card space-y-3">
                    @if ($product->description)<div><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Description</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700">{{ $product->description }}</p></div>@endif
                    @if ($product->notes)<div><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Notes</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700">{{ $product->notes }}</p></div>@endif
                </div>
            @endif
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Stock Movements</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Type</th><th>Qty</th><th>Warehouse</th><th>Unit Cost</th><th>Note</th><th>Date</th></tr></thead>
                        <tbody>
                            @forelse ($product->stockMovements as $movement)
                                <tr>
                                    <td><span class="badge-{{ $movement->type === 'in' ? 'green' : ($movement->type === 'out' ? 'red' : 'gray') }}">{{ \App\Models\StockMovement::TYPES[$movement->type] ?? $movement->type }}</span></td>
                                    <td class="font-semibold">{{ number_format((float) $movement->quantity, 4) }}</td>
                                    <td>{{ $movement->warehouse?->name ?? '—' }}</td>
                                    <td>{{ $movement->unit_cost !== null ? number_format((float) $movement->unit_cost, 2) : '—' }}</td>
                                    <td>{{ $movement->note ?? '—' }}</td>
                                    <td>{{ $movement->created_at?->format('d M Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-6 text-center text-ink-400">No stock movements yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

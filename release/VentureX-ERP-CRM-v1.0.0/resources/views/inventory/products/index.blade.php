<x-layouts.app title="Products" :breadcrumbs="[['label' => 'Inventory'], ['label' => 'Products']]">

    <x-slot name="actions">
        @can('create', App\Models\Product::class)
            <a href="{{ route('inventory.products.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Product
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Total Products" :value="$summary['total']" icon="box" color="blue" />
        <x-dashboard.stat-card label="Active" :value="$summary['active']" icon="badge" color="green" />
        <x-dashboard.stat-card label="Low Stock Items" :value="$summary['low_stock']" icon="alert" color="red" />
        <x-dashboard.stat-card label="Stock Value" :value="number_format($summary['stock_value'], 2)" icon="wallet" color="violet" />
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('inventory.products.index') }}" class="flex flex-1 flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name, SKU or category..." class="input max-w-md" />
            <select name="category" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
            <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach (\App\Models\Product::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <label class="flex items-center gap-2 text-sm text-ink-600">
                <input type="checkbox" name="low" value="1" @checked(request('low')) onchange="this.form.submit()" class="rounded border-ink-300">
                Low stock only
            </label>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Product</th><th>SKU</th><th>Category</th><th>Unit</th><th>Buy / Sell</th><th>Movements</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td><p class="font-semibold text-ink-800">{{ $product->name }}</p><p class="text-xs text-ink-400">{{ $product->supplier?->name ?? '' }}</p></td>
                            <td>{{ $product->sku ?? '—' }}</td>
                            <td>{{ $product->category ?? '—' }}</td>
                            <td>{{ $product->unit?->code ?? $product->unit?->name ?? '—' }}</td>
                            <td>{{ number_format((float) $product->purchase_price, 2) }} / {{ number_format((float) $product->selling_price, 2) }}</td>
                            <td>{{ $product->stock_movements_count }}</td>
                            <td>
                                @if ($product->reorder_level !== null && (float) $product->availableStock() <= (float) $product->reorder_level)
                                    <span class="badge-red">Low</span>
                                @else
                                    <span class="badge-{{ $product->status === 'active' ? 'green' : 'gray' }}">{{ \App\Models\Product::STATUSES[$product->status] ?? $product->status }}</span>
                                @endif
                            </td>
                            <td class="text-right"><a href="{{ route('inventory.products.show', $product) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-ink-400">No products yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $products->links() }}</div>
</x-layouts.app>

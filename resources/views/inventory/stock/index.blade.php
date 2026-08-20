<x-layouts.app title="Stock" :breadcrumbs="[['label' => 'Inventory'], ['label' => 'Stock']]">

    <x-slot name="actions">
        @can('create', App\Models\StockMovement::class)
            <a href="{{ route('inventory.stock.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Record Movement
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Stock In (total)" :value="number_format($summary['total_in'], 2)" icon="arrow-down" color="green" />
        <x-dashboard.stat-card label="Stock Out (total)" :value="number_format($summary['total_out'], 2)" icon="arrow-up" color="red" />
        <x-dashboard.stat-card label="Products" :value="$summary['products']" icon="box" color="blue" />
        <x-dashboard.stat-card label="Adjustments" :value="$summary['adjustments']" icon="calculator" color="amber" />
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('inventory.stock.index') }}" class="flex flex-1 flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search product..." class="input max-w-md" />
            <select name="type" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All types</option>
                @foreach (\App\Models\StockMovement::TYPES as $key => $label)
                    <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="warehouse_id" class="input max-w-[12rem]" onchange="this.form.submit()">
                <option value="">All warehouses</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Product</th><th>Type</th><th>Qty</th><th>Warehouse</th><th>Unit Cost</th><th>Note</th><th>Recorded</th></tr></thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr>
                            <td>
                                <a href="{{ route('inventory.products.show', $movement->product) }}" class="font-semibold text-navy-600 hover:text-navy-500">{{ $movement->product?->name }}</a>
                                <p class="text-xs text-ink-400">{{ $movement->product?->sku }}</p>
                            </td>
                            <td><span class="badge-{{ $movement->type === 'in' ? 'green' : ($movement->type === 'out' ? 'red' : 'gray') }}">{{ \App\Models\StockMovement::TYPES[$movement->type] ?? $movement->type }}</span></td>
                            <td class="font-semibold text-ink-800">{{ number_format((float) $movement->quantity, 4) }}</td>
                            <td>{{ $movement->warehouse?->name ?? '—' }}</td>
                            <td>{{ $movement->unit_cost !== null ? number_format((float) $movement->unit_cost, 2) : '—' }}</td>
                            <td>{{ $movement->note ?? '—' }}</td>
                            <td>{{ $movement->created_at?->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-ink-400">No stock movements yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $movements->links() }}</div>
</x-layouts.app>

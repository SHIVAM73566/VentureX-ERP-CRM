<x-layouts.app title="Warehouses" :breadcrumbs="[['label' => 'Inventory'], ['label' => 'Warehouses']]">

    <x-slot name="actions">
        @can('create', App\Models\Warehouse::class)
            <a href="{{ route('inventory.warehouses.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Warehouse
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Total Warehouses" :value="$warehouses->count()" icon="building" color="blue" />
        <x-dashboard.stat-card label="Active" :value="$warehouses->where('status', 'active')->count()" icon="badge" color="green" />
        <x-dashboard.stat-card label="Total Capacity" :value="number_format($warehouses->sum('capacity'), 0)" icon="box" color="violet" />
        <x-dashboard.stat-card label="Default" :value="$warehouses->where('is_default', true)->count()" icon="target" color="amber" />
    </div>

    <div class="mt-6 card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Warehouse</th><th>Location</th><th>Manager</th><th>Capacity</th><th>Movements</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($warehouses as $warehouse)
                        <tr>
                            <td>
                                <p class="font-semibold text-ink-800">{{ $warehouse->name }}@if ($warehouse->is_default) <span class="badge-blue">Default</span>@endif</p>
                                <p class="text-xs text-ink-400">{{ $warehouse->code }}</p>
                            </td>
                            <td>{{ $warehouse->location ?? '—' }}</td>
                            <td>{{ $warehouse->manager?->name ?? '—' }}</td>
                            <td>{{ $warehouse->capacity !== null ? number_format((float) $warehouse->capacity, 0) : '—' }}</td>
                            <td>{{ $warehouse->stock_movements_count }}</td>
                            <td><span class="badge-{{ $warehouse->status === 'active' ? 'green' : 'gray' }}">{{ \App\Models\Warehouse::STATUSES[$warehouse->status] ?? $warehouse->status }}</span></td>
                            <td class="text-right">
                                @can('update', $warehouse)
                                    <a href="{{ route('inventory.warehouses.edit', $warehouse) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-ink-400">No warehouses yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>

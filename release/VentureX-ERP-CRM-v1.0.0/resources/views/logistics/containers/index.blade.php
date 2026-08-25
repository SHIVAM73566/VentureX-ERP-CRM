<x-layouts.app title="Containers" :breadcrumbs="[['label' => 'Logistics'], ['label' => 'Containers']]">

    <x-slot name="actions">
        @can('create', App\Models\Container::class)
            <a href="{{ route('logistics.containers.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Register Container
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Total Containers" :value="$summary['total']" icon="box" color="blue" />
        <x-dashboard.stat-card label="Available" :value="$summary['available']" icon="badge" color="green" />
        <x-dashboard.stat-card label="In Use / Transit" :value="$summary['in_use']" icon="truck" color="amber" />
        <x-dashboard.stat-card label="Returned" :value="$summary['returned']" icon="clock" color="gray" />
    </div>

    <div class="mt-6 card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Container Number</th><th>Size</th><th>Seal Number</th><th>Shipments</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($containers as $container)
                        <tr>
                            <td class="font-semibold text-ink-800">{{ $container->container_number }}</td>
                            <td>{{ $container->size ?? '—' }}</td>
                            <td>{{ $container->seal_number ?? '—' }}</td>
                            <td>{{ $container->shipments_count }}</td>
                            <td><span class="badge-{{ $container->status === 'available' ? 'green' : ($container->status === 'returned' ? 'gray' : 'amber') }}">{{ \App\Models\Container::STATUSES[$container->status] ?? $container->status }}</span></td>
                            <td class="text-right">
                                <a href="{{ route('logistics.containers.show', $container) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a>
                                @can('update', $container)
                                    <a href="{{ route('logistics.containers.edit', $container) }}" class="ml-2 text-sm font-medium text-navy-600 hover:text-navy-500">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-ink-400">No containers registered yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $containers->links() }}</div>
</x-layouts.app>

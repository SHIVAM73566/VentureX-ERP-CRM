<x-layouts.app title="Suppliers" :breadcrumbs="[['label' => 'Procurement'], ['label' => 'Suppliers']]">

    <x-slot name="actions">
        @can('create', App\Models\Supplier::class)
            <a href="{{ route('suppliers.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Supplier
            </a>
        @endcan
    </x-slot>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('suppliers.index') }}" class="flex flex-1 gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search suppliers..." class="input max-w-md" />
            <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach (\App\Models\Supplier::STATUSES as $key => $status)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $status }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Search</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Contact</th>
                        <th>Offers</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td>
                                <p class="font-semibold text-ink-800">{{ $supplier->name }}</p>
                                <p class="text-xs text-ink-400">{{ $supplier->city ?? '—' }}{{ $supplier->country ? ', '.$supplier->country->name : '' }}</p>
                            </td>
                            <td class="text-sm text-ink-600">{{ $supplier->email ?? $supplier->phone ?? '—' }}</td>
                            <td>{{ $supplier->offers_count }}</td>
                            <td>
                                <span class="badge-{{ $supplier->status === 'approved' ? 'green' : ($supplier->status === 'blocked' || $supplier->status === 'rejected' ? 'red' : 'amber') }}">{{ \App\Models\Supplier::STATUSES[$supplier->status] }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('suppliers.show', $supplier) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-ink-400">No suppliers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $suppliers->links() }}</div>
</x-layouts.app>

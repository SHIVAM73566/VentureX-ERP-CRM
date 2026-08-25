<x-layouts.app title="Purchase Orders" :breadcrumbs="[['label' => 'Procurement'], ['label' => 'Orders']]">

    <x-slot name="actions">
        @can('create', App\Models\PurchaseOrder::class)
            <a href="{{ route('procurement.orders.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Purchase Order
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Total Orders" :value="$summary['total']" icon="document" color="blue" />
        <x-dashboard.stat-card label="Open" :value="$summary['open']" icon="alert" color="amber" />
        <x-dashboard.stat-card label="Value (excl. cancelled)" :value="number_format($summary['value'], 2)" icon="wallet" color="violet" />
        <x-dashboard.stat-card label="Payable" :value="number_format($summary['payable'], 2)" icon="receipt" color="red" />
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('procurement.orders.index') }}" class="flex flex-1 flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search PO number or supplier..." class="input max-w-md" />
            <select name="status" class="input max-w-[12rem]" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach (\App\Models\PurchaseOrder::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>PO Number</th><th>Supplier</th><th>Order Date</th><th class="text-right">Total</th><th class="text-right">Outstanding</th><th>Payment</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="font-semibold text-ink-800">{{ $order->po_number }}</td>
                            <td>{{ $order->supplier?->name ?? '—' }}</td>
                            <td>{{ $order->order_date?->format('d M Y') }}</td>
                            <td class="text-right font-semibold text-ink-800">{{ number_format((float) $order->total, 2) }}</td>
                            <td class="text-right {{ (float) $order->outstandingBalance() > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ number_format((float) $order->outstandingBalance(), 2) }}</td>
                            <td><span class="badge-{{ $order->payment_status === 'paid' ? 'green' : ($order->payment_status === 'partial' ? 'amber' : 'gray') }}">{{ \App\Models\PurchaseOrder::PAYMENT_STATUSES[$order->payment_status] ?? $order->payment_status }}</span></td>
                            <td>@php $sc = ['draft' => 'gray', 'pending' => 'amber', 'approved' => 'blue', 'ordered' => 'violet', 'partially_received' => 'amber', 'received' => 'green', 'cancelled' => 'red']; @endphp
                                <span class="badge-{{ $sc[$order->status] ?? 'gray' }}">{{ \App\Models\PurchaseOrder::STATUSES[$order->status] ?? $order->status }}</span>
                            </td>
                            <td class="text-right"><a href="{{ route('procurement.orders.show', $order) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-ink-400">No purchase orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</x-layouts.app>

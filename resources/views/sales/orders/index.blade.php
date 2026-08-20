<x-layouts.app title="Sales Orders" :breadcrumbs="[['label' => 'Sales'], ['label' => 'Orders']]">

    <x-slot name="actions">
        @can('create', App\Models\SalesOrder::class)
            <a href="{{ route('sales.orders.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Order
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Total Orders" :value="$summary['total']" icon="clipboard" color="blue" />
        <x-dashboard.stat-card label="Open Orders" :value="$summary['open']" icon="target" color="amber" />
        <x-dashboard.stat-card label="Order Value" :value="number_format($summary['value'], 2)" icon="wallet" color="violet" />
        <x-dashboard.stat-card label="Outstanding" :value="number_format($summary['outstanding'], 2)" icon="alert" color="red" />
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('sales.orders.index') }}" class="flex flex-1 gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by number or customer..." class="input max-w-md" />
            <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach (\App\Models\SalesOrder::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Order</th><th>Customer</th><th>Order Date</th><th>Total</th><th>Status</th><th>Payment</th><th></th></tr></thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><p class="font-semibold text-ink-800">{{ $order->order_number }}</p><p class="text-xs text-ink-400">{{ $order->created_at?->format('d M Y') }}</p></td>
                            <td>{{ $order->customer?->name ?? '—' }}</td>
                            <td>{{ $order->order_date?->format('d M Y') }}</td>
                            <td>{{ number_format((float) $order->total, 2) }}</td>
                            <td>
                                @php $c = ['draft' => 'gray', 'confirmed' => 'blue', 'processing' => 'amber', 'shipped' => 'violet', 'completed' => 'green', 'cancelled' => 'red']; @endphp
                                <span class="badge-{{ $c[$order->status] ?? 'gray' }}">{{ \App\Models\SalesOrder::STATUSES[$order->status] ?? $order->status }}</span>
                            </td>
                            <td>
                                @php $p = ['unpaid' => 'red', 'partial' => 'amber', 'paid' => 'green']; @endphp
                                <span class="badge-{{ $p[$order->payment_status] ?? 'gray' }}">{{ \App\Models\SalesOrder::PAYMENT_STATUSES[$order->payment_status] ?? $order->payment_status }}</span>
                            </td>
                            <td class="text-right"><a href="{{ route('sales.orders.show', $order) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-ink-400">No sales orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</x-layouts.app>

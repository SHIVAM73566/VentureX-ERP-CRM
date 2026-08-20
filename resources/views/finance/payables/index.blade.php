<x-layouts.app title="Payables" :breadcrumbs="[['label' => 'Finance'], ['label' => 'Payables']]">

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Payable" :value="number_format($payable, 2)" icon="receipt" color="amber" />
        <x-dashboard.stat-card label="Due (past expected date)" :value="number_format($due, 2)" icon="clock" color="red" />
        <x-dashboard.stat-card label="Total Spend" :value="number_format($spend, 2)" icon="wallet" color="violet" />
        <x-dashboard.stat-card label="Landed Costs" :value="number_format($landedCosts, 2)" icon="truck" color="blue" />
    </div>

    <div class="card mt-6">
        <h2 class="mb-3 text-lg font-bold text-ink-900">Aging Buckets</h2>
        <div class="grid gap-3 sm:grid-cols-5">
            @foreach ([['Current', $agingBuckets['current'], 'green'], ['1–30 days', $agingBuckets['0_30'], 'amber'], ['31–60 days', $agingBuckets['31_60'], 'amber'], ['61–90 days', $agingBuckets['61_90'], 'red'], ['90+ days', $agingBuckets['90_plus'], 'red']] as [$label, $value, $color])
                <div class="rounded-lg border border-ink-200 p-3">
                    <p class="text-xs uppercase tracking-wide text-ink-400">{{ $label }}</p>
                    <p class="mt-1 text-lg font-bold {{ $color === 'red' ? 'text-red-600' : ($color === 'amber' ? 'text-amber-600' : 'text-emerald-600') }}">{{ number_format((float) $value, 2) }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('finance.payables') }}" class="flex flex-1 flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search PO or supplier..." class="input max-w-md" />
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>PO Number</th><th>Supplier</th><th>Order Date</th><th>Expected</th><th class="text-right">Total</th><th class="text-right">Paid</th><th class="text-right">Payable</th><th></th></tr></thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><a href="{{ route('procurement.orders.show', $order) }}" class="font-semibold text-navy-600 hover:text-navy-500">{{ $order->po_number }}</a></td>
                            <td>{{ $order->supplier?->name ?? '—' }}</td>
                            <td>{{ $order->order_date?->format('d M Y') }}</td>
                            <td>
                                {{ $order->expected_date?->format('d M Y') ?? '—' }}
                                @if ($order->expected_date?->isPast())
                                    <span class="badge-red">Past</span>
                                @endif
                            </td>
                            <td class="text-right">{{ number_format((float) $order->total, 2) }}</td>
                            <td class="text-right text-emerald-600">{{ number_format((float) $order->paid_amount, 2) }}</td>
                            <td class="text-right font-semibold text-red-600">{{ number_format((float) $order->outstandingBalance(), 2) }}</td>
                            <td class="text-right"><a href="{{ route('procurement.orders.show', $order) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-ink-400">No outstanding payables.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</x-layouts.app>

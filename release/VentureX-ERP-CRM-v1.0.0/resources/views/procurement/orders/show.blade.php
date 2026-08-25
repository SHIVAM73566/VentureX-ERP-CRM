<x-layouts.app
    :title="'Purchase Order — '.($order->po_number ?? 'New')"
    :breadcrumbs="[['label' => 'Procurement', 'url' => route('procurement.orders.index')], ['label' => 'Orders', 'url' => route('procurement.orders.index')], ['label' => $order->po_number]]">

    <x-slot name="actions">
        @can('update', $order)
            <a href="{{ route('procurement.orders.edit', $order) }}" class="btn-secondary">Edit</a>
        @endcan
        @can('delete', $order)
            <form method="POST" action="{{ route('procurement.orders.destroy', $order) }}" onsubmit="return confirm('Delete this purchase order?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-secondary text-red-600 hover:bg-red-50">Delete</button>
            </form>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Order Summary</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd>{{ $order->po_number }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Supplier</dt><dd class="text-right">{{ $order->supplier?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Source RFQ</dt>
                        <dd>@if ($order->rfq)<a href="{{ route('procurement.rfqs.show', $order->rfq) }}" class="text-navy-600 hover:text-navy-500">{{ $order->rfq->rfq_number }}</a>@else — @endif</dd>
                    </div>
                    <div class="flex justify-between"><dt class="text-ink-400">Order Date</dt><dd>{{ $order->order_date?->format('d M Y') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Expected</dt><dd>{{ $order->expected_date?->format('d M Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                        <dd>@php $sc = ['draft' => 'gray', 'pending' => 'amber', 'approved' => 'blue', 'ordered' => 'violet', 'partially_received' => 'amber', 'received' => 'green', 'cancelled' => 'red']; @endphp
                            <span class="badge-{{ $sc[$order->status] ?? 'gray' }}">{{ \App\Models\PurchaseOrder::STATUSES[$order->status] ?? $order->status }}</span></dd>
                    </div>
                    <div class="flex justify-between"><dt class="text-ink-400">Payment</dt><dd><span class="badge-{{ $order->payment_status === 'paid' ? 'green' : ($order->payment_status === 'partial' ? 'amber' : 'gray') }}">{{ \App\Models\PurchaseOrder::PAYMENT_STATUSES[$order->payment_status] ?? $order->payment_status }}</span></dd></div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Subtotal</dt><dd>{{ number_format((float) $order->subtotal, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Discount</dt><dd>{{ number_format((float) $order->discount, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Tax</dt><dd>{{ number_format((float) $order->tax, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Shipping</dt><dd>{{ number_format((float) $order->shipping, 2) }}</dd></div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-bold text-ink-900">Total</dt><dd class="font-bold text-ink-900">{{ number_format((float) $order->total, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Paid</dt><dd class="font-semibold text-emerald-600">{{ number_format((float) $order->paid_amount, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Outstanding</dt><dd class="font-semibold {{ (float) $order->outstandingBalance() > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ number_format((float) $order->outstandingBalance(), 2) }}</dd></div>
                </dl>
            </div>

            @if ($order->payment_terms || $order->notes)
                <div class="card space-y-3">
                    @if ($order->payment_terms)<div><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Payment Terms</h3><p class="mt-1 text-sm text-ink-700">{{ $order->payment_terms }}</p></div>@endif
                    @if ($order->notes)<div><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Notes</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700">{{ $order->notes }}</p></div>@endif
                </div>
            @endif
        </div>

        <div class="card lg:col-span-2">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Line Items</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Item</th><th class="text-right">Qty</th><th>Unit</th><th class="text-right">Unit Price</th><th class="text-right">Tax</th><th class="text-right">Line Total</th></tr></thead>
                    <tbody>
                        @forelse ($order->items as $item)
                            <tr>
                                <td><p class="font-medium text-ink-800">{{ $item->description }}</p>@if ($item->product)<p class="text-xs text-ink-400">{{ $item->product->sku ?? '' }} {{ $item->product->name }}</p>@endif</td>
                                <td class="text-right">{{ number_format((float) $item->quantity, 4) }}</td>
                                <td>{{ $item->unit ?? '—' }}</td>
                                <td class="text-right">{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="text-right">{{ (float) $item->tax_rate }}%</td>
                                <td class="text-right font-semibold text-ink-800">{{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 text-center text-ink-400">No items.</td></tr>
                        @endforelse
                        <tr class="border-t-2 border-ink-200">
                            <td colspan="5" class="text-right font-bold text-ink-900">Total</td>
                            <td class="text-right font-bold text-ink-900">{{ number_format((float) $order->total, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>

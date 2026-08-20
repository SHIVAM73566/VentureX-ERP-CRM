<x-layouts.app
    :title="'Sales Order — '.($order->order_number ?? 'New')"
    :breadcrumbs="[['label' => 'Sales', 'url' => route('sales.orders.index')], ['label' => 'Orders', 'url' => route('sales.orders.index')], ['label' => $order->order_number]]">

    <x-slot name="actions">
        @can('create', App\Models\Invoice::class)
            <a href="{{ route('sales.invoices.create') }}?sales_order_id={{ $order->id }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Invoice
            </a>
        @endcan
        @can('update', $order)
            <a href="{{ route('sales.orders.edit', $order) }}" class="btn-secondary">Edit</a>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Order Summary</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd>{{ $order->order_number }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Customer</dt><dd class="text-right">{{ $order->customer?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Order Date</dt><dd>{{ $order->order_date?->format('d M Y') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Delivery</dt><dd>{{ $order->delivery_date?->format('d M Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                        <dd>@php $c = ['draft' => 'gray', 'confirmed' => 'blue', 'processing' => 'amber', 'shipped' => 'violet', 'completed' => 'green', 'cancelled' => 'red']; @endphp
                            <span class="badge-{{ $c[$order->status] ?? 'gray' }}">{{ \App\Models\SalesOrder::STATUSES[$order->status] ?? $order->status }}</span>
                        </dd>
                    </div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Total</dt><dd class="font-bold text-ink-900">{{ number_format((float) $order->total, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Paid</dt><dd class="font-semibold text-emerald-600">{{ number_format((float) $order->paid_amount, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Outstanding</dt><dd class="font-semibold {{ (float) $order->outstandingBalance() > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ number_format((float) $order->outstandingBalance(), 2) }}</dd></div>
                </dl>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Line Items</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Item</th><th>Qty</th><th>Unit Price</th><th>Tax</th><th class="text-right">Line Total</th></tr></thead>
                        <tbody>
                            @forelse ($order->items as $item)
                                <tr>
                                    <td><p class="font-medium text-ink-800">{{ $item->description }}</p>@if ($item->product)<p class="text-xs text-ink-400">{{ $item->product->sku ?? '' }} {{ $item->product->name }}</p>@endif</td>
                                    <td>{{ number_format((float) $item->quantity, 3) }}</td>
                                    <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td>{{ (float) $item->tax_rate }}%</td>
                                    <td class="text-right font-semibold text-ink-800">{{ number_format((float) $item->line_total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-ink-400">No items.</td></tr>
                            @endforelse
                            <tr class="border-t-2 border-ink-200">
                                <td colspan="4" class="text-right font-bold text-ink-900">Total</td>
                                <td class="text-right font-bold text-ink-900">{{ number_format((float) $order->total, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Invoices</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Invoice</th><th>Date</th><th>Total</th><th>Paid</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($order->invoices as $invoice)
                                <tr>
                                    <td class="font-medium text-ink-800">{{ $invoice->invoice_number }}</td>
                                    <td>{{ $invoice->issue_date?->format('d M Y') }}</td>
                                    <td>{{ number_format((float) $invoice->total, 2) }}</td>
                                    <td>{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                                    <td>@php $c = ['draft' => 'gray', 'sent' => 'amber', 'partial' => 'violet', 'paid' => 'green', 'overdue' => 'red', 'cancelled' => 'gray']; @endphp
                                        <span class="badge-{{ $c[$invoice->status] ?? 'gray' }}">{{ \App\Models\Invoice::STATUSES[$invoice->status] ?? $invoice->status }}</span>
                                    </td>
                                    <td class="text-right"><a href="{{ route('sales.invoices.show', $invoice) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-6 text-center text-ink-400">No invoices yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($order->notes)
                <div class="card"><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Notes</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700">{{ $order->notes }}</p></div>
            @endif
        </div>
    </div>
</x-layouts.app>

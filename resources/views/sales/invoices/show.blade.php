<x-layouts.app
    :title="'Invoice — '.($invoice->invoice_number ?? 'New')"
    :breadcrumbs="[['label' => 'Sales', 'url' => route('sales.invoices.index')], ['label' => 'Invoices', 'url' => route('sales.invoices.index')], ['label' => $invoice->invoice_number]]">

    <x-slot name="actions">
        <a href="{{ route('sales.invoices.pdf', $invoice) }}" class="btn-secondary" target="_blank">Download PDF</a>
        @can('viewAny', App\Models\AiRun::class)
            <x-ai.action label="AI summary" :url="route('ai.actions.invoice-summary')" :payload="['invoice_id' => $invoice->id]" />
        @endcan
        @can('create', App\Models\Payment::class)
            <a href="{{ route('sales.payments.create') }}?invoice_id={{ $invoice->id }}" class="btn-accent">Record Payment</a>
        @endcan
        @can('update', $invoice)
            <a href="{{ route('sales.invoices.edit', $invoice) }}" class="btn-secondary">Edit</a>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Invoice Summary</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd>{{ $invoice->invoice_number }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Customer</dt><dd class="text-right">{{ $invoice->customer?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Issue Date</dt><dd>{{ $invoice->issue_date?->format('d M Y') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Due Date</dt><dd>{{ $invoice->due_date?->format('d M Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                        <dd>@php $c = ['draft' => 'gray', 'sent' => 'amber', 'partial' => 'violet', 'paid' => 'green', 'overdue' => 'red', 'cancelled' => 'gray']; @endphp
                            <span class="badge-{{ $c[$invoice->status] ?? 'gray' }}">{{ \App\Models\Invoice::STATUSES[$invoice->status] ?? $invoice->status }}</span>
                        </dd>
                    </div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Total</dt><dd class="font-bold text-ink-900">{{ number_format((float) $invoice->total, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Paid</dt><dd class="font-semibold text-emerald-600">{{ number_format((float) $invoice->paid_amount, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Outstanding</dt><dd class="font-semibold {{ (float) $invoice->outstandingBalance() > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ number_format((float) $invoice->outstandingBalance(), 2) }}</dd></div>
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
                            @forelse ($invoice->items as $item)
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
                                <td class="text-right font-bold text-ink-900">{{ number_format((float) $invoice->total, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Payments</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Payment</th><th>Date</th><th>Method</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($invoice->payments as $payment)
                                <tr>
                                    <td class="font-medium text-ink-800">{{ $payment->payment_number }}</td>
                                    <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                                    <td>{{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }}</td>
                                    <td>{{ number_format((float) $payment->amount, 2) }}</td>
                                    <td>@php $c = ['pending' => 'amber', 'completed' => 'green', 'failed' => 'red', 'refunded' => 'gray']; @endphp
                                        <span class="badge-{{ $c[$payment->status] ?? 'gray' }}">{{ \App\Models\Payment::STATUSES[$payment->status] ?? $payment->status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-ink-400">No payments recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($invoice->notes)
                <div class="card"><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Notes</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700">{{ $invoice->notes }}</p></div>
            @endif
        </div>
    </div>
</x-layouts.app>

<x-layouts.app
    :title="'Payment — '.($payment->payment_number ?? 'New')"
    :breadcrumbs="[['label' => 'Sales', 'url' => route('sales.payments.index')], ['label' => 'Payments', 'url' => route('sales.payments.index')], ['label' => $payment->payment_number]]">

    <x-slot name="actions">
        @can('update', $payment)
            <a href="{{ route('sales.payments.edit', $payment) }}" class="btn-secondary">Edit</a>
        @endcan
        @can('delete', $payment)
            <form method="POST" action="{{ route('sales.payments.destroy', $payment) }}" onsubmit="return confirm('Delete this payment? It will be reversed from the invoice.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-secondary text-red-600 hover:bg-red-50">Delete</button>
            </form>
        @endcan
    </x-slot>

    <div class="mx-auto max-w-2xl">
        <div class="card">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Payment Summary</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd>{{ $payment->payment_number }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Customer</dt><dd>{{ $payment->customer?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Invoice</dt>
                    <dd>@if ($payment->invoice)<a href="{{ route('sales.invoices.show', $payment->invoice) }}" class="text-navy-600 hover:text-navy-500">{{ $payment->invoice->invoice_number }}</a>@else — @endif</dd>
                </div>
                <div class="flex justify-between"><dt class="text-ink-400">Date</dt><dd>{{ $payment->payment_date?->format('d M Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Method</dt><dd>{{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Reference</dt><dd>{{ $payment->reference ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                    <dd>@php $c = ['pending' => 'amber', 'completed' => 'green', 'failed' => 'red', 'refunded' => 'gray']; @endphp
                        <span class="badge-{{ $c[$payment->status] ?? 'gray' }}">{{ \App\Models\Payment::STATUSES[$payment->status] ?? $payment->status }}</span>
                    </dd>
                </div>
                <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Amount</dt><dd class="font-bold text-ink-900">{{ number_format((float) $payment->amount, 2) }}</dd></div>
            </dl>

            @if ($payment->notes)
                <div class="mt-4 border-t border-ink-100 pt-3"><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Notes</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700">{{ $payment->notes }}</p></div>
            @endif
        </div>
    </div>
</x-layouts.app>

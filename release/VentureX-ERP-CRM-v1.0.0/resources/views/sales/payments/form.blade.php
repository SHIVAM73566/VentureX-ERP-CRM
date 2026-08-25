<x-layouts.app
    :title="$payment->exists ? 'Edit Payment' : 'Record Payment'"
    :breadcrumbs="[['label' => 'Sales', 'url' => route('sales.payments.index')], ['label' => 'Payments', 'url' => route('sales.payments.index')], ['label' => $payment->exists ? 'Edit' : 'New']]">

    @php $presetInvoice = $payment->exists ? $payment->invoice_id : ($presetInvoice ?? null)?->id; @endphp

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ $payment->exists ? route('sales.payments.update', $payment) : route('sales.payments.store') }}" class="space-y-6">
            @csrf
            @if ($payment->exists)
                @method('PUT')
            @endif

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Payment Details</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.select label="Customer *" name="customer_id" :options="$customers->pluck('name', 'id')" :value="$payment->customer_id" required />
                    <x-form.select label="Invoice" name="invoice_id" :options="$invoices->pluck('invoice_number', 'id')" :value="$presetInvoice" />
                    <x-form.input label="Payment Date *" name="payment_date" type="date" :value="$payment->payment_date?->format('Y-m-d') ?? now()->format('Y-m-d')" required />
                    <x-form.input label="Amount *" name="amount" type="number" step="0.01" min="0.01" :value="$payment->amount" required />
                    <x-form.select label="Method *" name="method" :options="\App\Models\Payment::METHODS" :value="$payment->method ?? 'bank'" required />
                    <x-form.input label="Reference" name="reference" :value="$payment->reference" placeholder="Cheque / UPI / Txn ID" />
                    <x-form.select label="Status" name="status" :options="\App\Models\Payment::STATUSES" :value="$payment->status ?? 'completed'" />
                </div>
            </div>

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Notes</h2>
                <x-form.textarea label="Notes" name="notes" :value="$payment->notes" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('sales.payments.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">{{ $payment->exists ? 'Save Changes' : 'Record Payment' }}</button>
            </div>
        </form>
    </div>
</x-layouts.app>

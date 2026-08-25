<x-layouts.app title="Payments" :breadcrumbs="[['label' => 'Sales'], ['label' => 'Payments']]">

    <x-slot name="actions">
        @can('create', App\Models\Payment::class)
            <a href="{{ route('sales.payments.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Record Payment
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-dashboard.stat-card label="Total Collected" :value="number_format($summary['total'], 2)" icon="wallet" color="green" />
        <x-dashboard.stat-card label="Completed Payments" :value="$summary['count']" icon="receipt" color="blue" />
        <x-dashboard.stat-card label="Pending Amount" :value="number_format($summary['pending'], 2)" icon="alert" color="amber" />
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('sales.payments.index') }}" class="flex flex-1 gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by number, customer or invoice..." class="input max-w-md" />
            <select name="method" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All methods</option>
                @foreach (\App\Models\Payment::METHODS as $key => $label)
                    <option value="{{ $key }}" @selected(request('method') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach (\App\Models\Payment::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Payment</th><th>Customer</th><th>Invoice</th><th>Date</th><th>Method</th><th>Amount</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="font-semibold text-ink-800">{{ $payment->payment_number }}</td>
                            <td>{{ $payment->customer?->name ?? '—' }}</td>
                            <td>{{ $payment->invoice?->invoice_number ?? '—' }}</td>
                            <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                            <td>{{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }}</td>
                            <td class="font-semibold text-ink-800">{{ number_format((float) $payment->amount, 2) }}</td>
                            <td>@php $c = ['pending' => 'amber', 'completed' => 'green', 'failed' => 'red', 'refunded' => 'gray']; @endphp
                                <span class="badge-{{ $c[$payment->status] ?? 'gray' }}">{{ \App\Models\Payment::STATUSES[$payment->status] ?? $payment->status }}</span>
                            </td>
                            <td class="text-right"><a href="{{ route('sales.payments.show', $payment) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-ink-400">No payments recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>
</x-layouts.app>

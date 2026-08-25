<x-layouts.app title="Invoices" :breadcrumbs="[['label' => 'Sales'], ['label' => 'Invoices']]">

    <x-slot name="actions">
        @can('create', App\Models\Invoice::class)
            <a href="{{ route('sales.invoices.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Invoice
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Total Invoices" :value="$summary['total']" icon="receipt" color="blue" />
        <x-dashboard.stat-card label="Open" :value="$summary['open']" icon="target" color="amber" />
        <x-dashboard.stat-card label="Invoice Value" :value="number_format($summary['value'], 2)" icon="wallet" color="violet" />
        <x-dashboard.stat-card label="Outstanding" :value="number_format($summary['outstanding'], 2)" icon="alert" color="red" />
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('sales.invoices.index') }}" class="flex flex-1 gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by number or customer..." class="input max-w-md" />
            <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach (\App\Models\Invoice::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Invoice</th><th>Customer</th><th>Issue Date</th><th>Due Date</th><th>Total</th><th>Outstanding</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td><p class="font-semibold text-ink-800 dark:text-ink-100">{{ $invoice->invoice_number }}</p><p class="text-xs text-ink-400 dark:text-ink-500">{{ $invoice->created_at?->format('d M Y') }}</p></td>
                            <td>{{ $invoice->customer?->name ?? '—' }}</td>
                            <td>{{ $invoice->issue_date?->format('d M Y') }}</td>
                            <td>{{ $invoice->due_date?->format('d M Y') ?? '—' }}</td>
                            <td>{{ number_format((float) $invoice->total, 2) }}</td>
                            <td class="font-semibold {{ (float) $invoice->outstandingBalance() > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ number_format((float) $invoice->outstandingBalance(), 2) }}</td>
                            <td>
                                @php $c = ['draft' => 'gray', 'sent' => 'amber', 'partial' => 'violet', 'paid' => 'green', 'overdue' => 'red', 'cancelled' => 'gray']; @endphp
                                <span class="badge-{{ $c[$invoice->status] ?? 'gray' }}">{{ \App\Models\Invoice::STATUSES[$invoice->status] ?? $invoice->status }}</span>
                            </td>
                            <td class="text-right"><a href="{{ route('sales.invoices.show', $invoice) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500 dark:text-navy-400 dark:hover:text-navy-300">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-ink-400 dark:text-ink-500">No invoices yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $invoices->links() }}</div>
</x-layouts.app>

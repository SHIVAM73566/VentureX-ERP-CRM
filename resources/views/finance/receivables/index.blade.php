<x-layouts.app title="Receivables" :breadcrumbs="[['label' => 'Finance'], ['label' => 'Receivables']]">

    <div class="grid gap-6 lg:grid-cols-3">
        <x-dashboard.stat-card label="Outstanding" :value="number_format($outstanding, 2)" icon="wallet" color="amber" />
        <x-dashboard.stat-card label="Overdue" :value="number_format($overdue, 2)" icon="clock" color="red" />
        <x-dashboard.stat-card label="Collected" :value="number_format($collected, 2)" icon="banknotes" color="green" />
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
        <form method="GET" action="{{ route('finance.receivables') }}" class="flex flex-1 flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search invoice or customer..." class="input max-w-md" />
            <select name="aging" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All ages</option>
                <option value="0" @selected(request('aging') === '0')>Current</option>
                <option value="30" @selected(request('aging') === '30')>Over 30 days</option>
                <option value="60" @selected(request('aging') === '60')>Over 60 days</option>
                <option value="90" @selected(request('aging') === '90')>Over 90 days</option>
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Invoice</th><th>Customer</th><th>Issue Date</th><th>Due Date</th><th class="text-right">Total</th><th class="text-right">Outstanding</th><th></th></tr></thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td><a href="{{ route('sales.invoices.show', $invoice) }}" class="font-semibold text-navy-600 hover:text-navy-500">{{ $invoice->invoice_number }}</a></td>
                            <td>{{ $invoice->customer?->name ?? '—' }}</td>
                            <td>{{ $invoice->issue_date?->format('d M Y') }}</td>
                            <td>
                                {{ $invoice->due_date?->format('d M Y') }}
                                @if ($invoice->due_date?->isPast())
                                    <span class="badge-red">Overdue {{ $invoice->due_date->diffInDays(now()) }}d</span>
                                @endif
                            </td>
                            <td class="text-right">{{ number_format((float) $invoice->total, 2) }}</td>
                            <td class="text-right font-semibold text-red-600">{{ number_format((float) $invoice->outstandingBalance(), 2) }}</td>
                            <td class="text-right">
                                @can('create', App\Models\Payment::class)
                                    <a href="{{ route('sales.payments.create') }}?invoice_id={{ $invoice->id }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">Record Payment</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-ink-400">No outstanding receivables.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $invoices->links() }}</div>
</x-layouts.app>

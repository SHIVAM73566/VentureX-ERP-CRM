<x-layouts.app title="Finance Dashboard" :breadcrumbs="[['label' => 'Finance'], ['label' => 'Dashboard']]">

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Receivables (outstanding)" :value="number_format($totalReceivable, 2)" icon="wallet" color="amber" url="{{ route('finance.receivables') }}" />
        <x-dashboard.stat-card label="Payables (outstanding)" :value="number_format($totalPayable, 2)" icon="receipt" color="red" url="{{ route('finance.payables') }}" />
        <x-dashboard.stat-card label="Cash Received" :value="number_format($cashReceived, 2)" icon="banknotes" color="green" />
        <x-dashboard.stat-card label="Landed Costs" :value="number_format($landedCosts, 2)" icon="truck" color="violet" />
    </div>

    <div class="grid gap-6 lg:grid-cols-2 mt-6">
        <div class="card">
            <h2 class="mb-3 text-lg font-bold text-ink-900 dark:text-ink-50">Trial Balance</h2>
            <div class="space-y-3">
                @foreach ([['label' => 'Assets', 'value' => $assets, 'color' => 'text-emerald-600 dark:text-emerald-400'], ['label' => 'Liabilities', 'value' => $liabilities, 'color' => 'text-red-600 dark:text-red-400'], ['label' => 'Equity', 'value' => $equity, 'color' => 'text-ink-800 dark:text-ink-200'], ['label' => 'Income', 'value' => $income, 'color' => 'text-emerald-600 dark:text-emerald-400'], ['label' => 'Expenses', 'value' => $expenses, 'color' => 'text-red-600 dark:text-red-400']] as $row)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-ink-500 dark:text-ink-400">{{ $row['label'] }}</span>
                        <span class="font-semibold {{ $row['color'] }}">{{ number_format((float) $row['value'], 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <h2 class="mb-3 text-lg font-bold text-ink-900 dark:text-ink-50">Top Accounts</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Account</th><th>Code</th><th>Entries</th></tr></thead>
                    <tbody>
                        @forelse ($topAccounts as $account)
                            <tr>
                                <td><a href="{{ route('finance.accounts.show', $account) }}" class="font-medium text-navy-600 hover:text-navy-500 dark:text-navy-400 dark:hover:text-navy-300">{{ $account->name }}</a></td>
                                <td>{{ $account->code }}</td>
                                <td>{{ $account->lines_count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-6 text-center text-ink-400 dark:text-ink-500">No accounts yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-6">
        <h2 class="mb-3 text-lg font-bold text-ink-900 dark:text-ink-50">Invoiced Value — Last 6 Months</h2>
        @if ($monthly->count())
            <div class="flex items-end gap-3 overflow-x-auto pb-2">
                @foreach ($monthly as $month => $value)
                    @php $max = max(1, (float) $monthly->max()); $pct = ((float) $value / $max) * 100; @endphp
                    <div class="flex min-w-[4rem] flex-col items-center gap-1">
                        <span class="text-xs font-semibold text-ink-700 dark:text-ink-300">{{ number_format((float) $value, 0) }}</span>
                        <div class="h-28 w-10 rounded-t-lg bg-gradient-to-t from-navy-700 to-navy-400" style="height: {{ $pct }}%"></div>
                        <span class="text-xs text-ink-400 dark:text-ink-500">{{ \Illuminate\Support\Carbon::parse($month . '-01')->format('M y') }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-ink-400 dark:text-ink-500">No invoice data for the last 6 months.</p>
        @endif
    </div>

    <div class="card mt-6">
        <h2 class="mb-3 text-lg font-bold text-ink-900 dark:text-ink-50">Recent Journal Entries</h2>
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Entry</th><th>Date</th><th>Description</th><th>Created By</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($recentEntries as $entry)
                        <tr>
                            <td class="font-semibold text-ink-800 dark:text-ink-100">{{ $entry->entry_number }}</td>
                            <td>{{ $entry->date?->format('d M Y') }}</td>
                            <td>{{ $entry->description ?? '—' }}</td>
                            <td>{{ $entry->createdBy?->name ?? '—' }}</td>
                            <td><span class="badge-{{ $entry->status === 'posted' ? 'green' : 'amber' }}">{{ \App\Models\JournalEntry::STATUSES[$entry->status] ?? $entry->status }}</span></td>
                            <td class="text-right"><a href="{{ route('finance.journals.show', $entry) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500 dark:text-navy-400 dark:hover:text-navy-300">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-ink-400 dark:text-ink-500">No journal entries yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>

<x-layouts.app title="Payroll" :breadcrumbs="[['label' => 'Human Resources', 'url' => route('hr.dashboard')], ['label' => 'Payroll']]">

    <x-slot name="actions">
        @can('create', App\Models\PayrollRun::class)
            <a href="{{ route('hr.payroll.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Payroll Run
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Payroll Runs" :value="$summary['runs']" icon="receipt" color="blue" />
        <x-dashboard.stat-card label="Draft" :value="$summary['draft']" icon="document" color="amber" />
        <x-dashboard.stat-card label="Approved" :value="$summary['approved']" icon="badge" color="green" />
        <x-dashboard.stat-card label="Net Paid (YTD)" :value="number_format($summary['net_this_year'], 2)" icon="wallet" color="violet" />
    </div>

    <div class="card mt-6">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Reference</th><th>Period</th><th>Employees</th><th>Gross</th><th>Deductions</th><th>Net</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($runs as $run)
                        <tr>
                            <td class="font-mono text-xs">{{ $run->reference }}</td>
                            <td>{{ $run->period_start?->format('d M') }} – {{ $run->period_end?->format('d M Y') }}</td>
                            <td>{{ $run->employee_count }} <span class="text-xs text-ink-400">({{ $run->items_count }})</span></td>
                            <td>{{ number_format((float) $run->gross_total, 2) }}</td>
                            <td>{{ number_format((float) $run->deductions_total, 2) }}</td>
                            <td class="font-semibold text-ink-800">{{ number_format((float) $run->net_total, 2) }}</td>
                            <td><span class="badge-{{ $run->status === 'paid' ? 'green' : ($run->status === 'approved' ? 'blue' : ($run->status === 'cancelled' ? 'gray' : 'amber')) }}">{{ \App\Models\PayrollRun::STATUSES[$run->status] ?? $run->status }}</span></td>
                            <td class="text-right"><a href="{{ route('hr.payroll.show', $run) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-ink-400">No payroll runs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $runs->links() }}</div>
</x-layouts.app>

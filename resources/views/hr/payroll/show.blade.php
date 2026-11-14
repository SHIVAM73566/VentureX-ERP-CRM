<x-layouts.app
    :title="'Payroll '.$run->reference"
    :breadcrumbs="[['label' => 'Human Resources', 'url' => route('hr.dashboard')], ['label' => 'Payroll', 'url' => route('hr.payroll.index')], ['label' => $run->reference]]">

    <x-slot name="actions">
        @if ($run->status === 'draft')
            @can('update', $run)
                <form method="POST" action="{{ route('hr.payroll.regenerate', $run) }}">@csrf
                    <button class="btn-secondary">Recalculate</button>
                </form>
            @endcan
            @can('approve', $run)
                <form method="POST" action="{{ route('hr.payroll.approve', $run) }}">@csrf
                    <button class="btn-accent">Approve Payroll</button>
                </form>
            @endcan
        @elseif ($run->status === 'approved')
            @can('approve', $run)
                <form method="POST" action="{{ route('hr.payroll.paid', $run) }}">@csrf
                    <button class="btn-accent">Mark as Paid</button>
                </form>
            @endcan
        @endif
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Employees" :value="$run->employee_count" icon="users" color="blue" />
        <x-dashboard.stat-card label="Gross Total" :value="number_format((float) $run->gross_total, 2)" icon="wallet" color="green" />
        <x-dashboard.stat-card label="Deductions" :value="number_format((float) $run->deductions_total, 2)" icon="arrow-down" color="amber" />
        <x-dashboard.stat-card label="Net Total" :value="number_format((float) $run->net_total, 2)" icon="banknotes" color="violet" />
    </div>

    <div class="card mt-6">
        <div class="card-header flex-wrap gap-2">
            <div>
                <h2 class="text-sm font-bold text-ink-800">Payroll items</h2>
                <p class="text-xs text-ink-400">{{ $run->period_start?->format('d M Y') }} – {{ $run->period_end?->format('d M Y') }}</p>
            </div>
            <span class="badge-{{ $run->status === 'paid' ? 'green' : ($run->status === 'approved' ? 'blue' : 'amber') }}">{{ \App\Models\PayrollRun::STATUSES[$run->status] ?? $run->status }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Employee</th><th>Base</th><th>Allowances</th><th>Overtime</th><th>Bonus</th><th>Gross</th><th>Tax</th><th>Social</th><th>Other</th><th>Net</th></tr></thead>
                <tbody>
                    @forelse ($run->items as $item)
                        <tr>
                            <td>
                                <p class="font-medium text-ink-800">{{ $item->employee?->fullName() ?? '—' }}</p>
                                <p class="text-xs text-ink-400">{{ $item->employee?->department?->name ?? '' }}</p>
                            </td>
                            <td>{{ number_format((float) $item->base_salary, 2) }}</td>
                            <td>{{ number_format((float) $item->allowances, 2) }}</td>
                            <td>{{ number_format((float) $item->overtime, 2) }}</td>
                            <td>{{ number_format((float) $item->bonus, 2) }}</td>
                            <td class="font-semibold text-ink-800">{{ number_format((float) $item->gross, 2) }}</td>
                            <td>{{ number_format((float) $item->tax, 2) }}</td>
                            <td>{{ number_format((float) $item->social_security, 2) }}</td>
                            <td>{{ number_format((float) $item->other_deductions, 2) }}</td>
                            <td class="font-semibold text-emerald-700">{{ number_format((float) $item->net, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="py-8 text-center text-ink-400">No payroll items generated.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($run->notes)
        <div class="card mt-6"><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Notes</h3><p class="mt-1 whitespace-pre-line text-sm text-ink-700">{{ $run->notes }}</p></div>
    @endif

    @can('delete', $run)
        <div class="mt-6 flex justify-end">
            <form method="POST" action="{{ route('hr.payroll.destroy', $run) }}" onsubmit="return confirm('Archive this payroll run?')">@csrf @method('DELETE')
                <button class="btn-secondary text-red-600">Archive Run</button>
            </form>
        </div>
    @endcan
</x-layouts.app>

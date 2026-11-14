<x-layouts.app
    title="New Payroll Run"
    :breadcrumbs="[['label' => 'Human Resources', 'url' => route('hr.dashboard')], ['label' => 'Payroll', 'url' => route('hr.payroll.index')], ['label' => 'New']]">

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('hr.payroll.store') }}" class="space-y-6">
            @csrf

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Payroll Period</h2>
                <p class="text-sm text-ink-500">The engine builds one line per active employee using base salary, attendance overtime and the configured statutory rates.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input label="Period Start *" name="period_start" type="date" :value="$run->period_start?->toDateString()" required />
                    <x-form.input label="Period End *" name="period_end" type="date" :value="$run->period_end?->toDateString()" required />
                </div>
                <x-form.textarea label="Notes" name="notes" :value="$run->notes" rows="2" />
                <div class="rounded-lg bg-ink-50 p-3 text-xs text-ink-500">
                    Tax rate: {{ config('hr.payroll.tax_rate') }}% ·
                    Social security: {{ config('hr.payroll.social_security_rate') }}% ·
                    Overtime multiplier: {{ config('hr.payroll.overtime_multiplier') }}×
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('hr.payroll.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">Generate Payroll</button>
            </div>
        </form>
    </div>
</x-layouts.app>

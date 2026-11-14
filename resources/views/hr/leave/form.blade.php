<x-layouts.app
    title="New Leave Request"
    :breadcrumbs="[['label' => 'Human Resources', 'url' => route('hr.dashboard')], ['label' => 'Leave', 'url' => route('hr.leave.index')], ['label' => 'New']]">

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('hr.leave.store') }}" class="space-y-6">
            @csrf

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Leave Request</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.select label="Employee *" name="employee_id" :options="$employees->mapWithKeys(fn ($e) => [$e->id => $e->fullName().' ('.$e->employee_number.')'])" :value="$leave->employee_id" required />
                    <x-form.select label="Leave Type *" name="type" :options="\App\Models\LeaveRequest::TYPES" :value="$leave->type" required />
                    <x-form.input label="Start Date *" name="start_date" type="date" :value="$leave->start_date?->toDateString()" required />
                    <x-form.input label="End Date *" name="end_date" type="date" :value="$leave->end_date?->toDateString()" required />
                    <x-form.input label="Days" name="days" type="number" step="0.5" min="0.5" :value="$leave->days" help="Leave blank to calculate from the date range." />
                    <x-form.checkbox label="Half day" name="half_day" :checked="$leave->half_day" />
                </div>
                <x-form.textarea label="Reason" name="reason" :value="$leave->reason" rows="3" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('hr.leave.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">Submit Request</button>
            </div>
        </form>
    </div>
</x-layouts.app>

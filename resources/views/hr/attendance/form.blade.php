<x-layouts.app
    :title="$attendance->exists ? 'Edit Attendance' : 'Record Attendance'"
    :breadcrumbs="[['label' => 'Human Resources', 'url' => route('hr.dashboard')], ['label' => 'Attendance', 'url' => route('hr.attendance.index')], ['label' => $attendance->exists ? 'Edit' : 'New']]">

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ $attendance->exists ? route('hr.attendance.update', $attendance) : route('hr.attendance.store') }}" class="space-y-6">
            @csrf
            @if ($attendance->exists)
                @method('PUT')
            @endif

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Attendance Record</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.select label="Employee *" name="employee_id" :options="$employees->mapWithKeys(fn ($e) => [$e->id => $e->fullName().' ('.$e->employee_number.')'])" :value="$attendance->employee_id" required />
                    <x-form.input label="Date *" name="attendance_date" type="date" :value="$attendance->attendance_date?->toDateString()" required />
                    <x-form.input label="Check In" name="check_in" type="time" :value="$attendance->check_in" />
                    <x-form.input label="Check Out" name="check_out" type="time" :value="$attendance->check_out" />
                    <x-form.input label="Worked Hours" name="worked_hours" type="number" step="0.01" min="0" max="24" :value="$attendance->worked_hours" />
                    <x-form.input label="Overtime Hours" name="overtime_hours" type="number" step="0.01" min="0" max="24" :value="$attendance->overtime_hours" />
                    <x-form.select label="Status *" name="status" :options="\App\Models\Attendance::STATUSES" :value="$attendance->status ?? 'present'" required />
                </div>
                <x-form.textarea label="Notes" name="notes" :value="$attendance->notes" rows="2" />
            </div>

            <div class="flex items-center justify-between gap-3">
                @if ($attendance->exists)
                    @can('delete', $attendance)
                        <button type="submit" form="delete-attendance" class="btn-secondary text-red-600">Delete</button>
                    @endcan
                @endif
                <div class="ml-auto flex gap-3">
                    <a href="{{ route('hr.attendance.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-accent">{{ $attendance->exists ? 'Save Changes' : 'Record' }}</button>
                </div>
            </div>
        </form>

        @if ($attendance->exists)
            @can('delete', $attendance)
                <form id="delete-attendance" method="POST" action="{{ route('hr.attendance.destroy', $attendance) }}" onsubmit="return confirm('Delete this attendance record?')">
                    @csrf @method('DELETE')
                </form>
            @endcan
        @endif
    </div>
</x-layouts.app>

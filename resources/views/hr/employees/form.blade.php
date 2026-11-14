<x-layouts.app
    :title="$employee->exists ? 'Edit Employee' : 'New Employee'"
    :breadcrumbs="[['label' => 'Human Resources', 'url' => route('hr.dashboard')], ['label' => 'Employees', 'url' => route('hr.employees.index')], ['label' => $employee->exists ? 'Edit' : 'New']]">

    <div class="mx-auto max-w-4xl">
        <form method="POST" action="{{ $employee->exists ? route('hr.employees.update', $employee) : route('hr.employees.store') }}" class="space-y-6">
            @csrf
            @if ($employee->exists)
                @method('PUT')
            @endif

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Personal Details</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input label="First Name *" name="first_name" :value="$employee->first_name" required />
                    <x-form.input label="Last Name *" name="last_name" :value="$employee->last_name" required />
                    <x-form.input label="Employee Number" name="employee_number" :value="$employee->employee_number" help="Leave blank to auto-generate." />
                    <x-form.input label="Email" name="email" type="email" :value="$employee->email" />
                    <x-form.input label="Phone" name="phone" :value="$employee->phone" />
                    <x-form.input label="Date of Birth" name="date_of_birth" type="date" :value="$employee->date_of_birth?->toDateString()" />
                    <x-form.input label="Gender" name="gender" :value="$employee->gender" />
                    <x-form.input label="Address" name="address" :value="$employee->address" />
                </div>
            </div>

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Employment</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.select label="Department" name="department_id" :options="$departments->pluck('name', 'id')" :value="$employee->department_id" />
                    <x-form.select label="Branch" name="branch_id" :options="$branches->pluck('name', 'id')" :value="$employee->branch_id" />
                    <x-form.select label="Manager" name="manager_id" :options="$managers->mapWithKeys(fn ($m) => [$m->id => $m->fullName()])" :value="$employee->manager_id" />
                    <x-form.input label="Job Title" name="job_title" :value="$employee->job_title" />
                    <x-form.select label="Employment Type *" name="employment_type" :options="\App\Models\Employee::EMPLOYMENT_TYPES" :value="$employee->employment_type ?? 'full_time'" required />
                    <x-form.select label="Status *" name="status" :options="\App\Models\Employee::STATUSES" :value="$employee->status ?? 'active'" required />
                    <x-form.input label="Hire Date" name="hire_date" type="date" :value="$employee->hire_date?->toDateString()" />
                    <x-form.input label="Termination Date" name="termination_date" type="date" :value="$employee->termination_date?->toDateString()" />
                </div>
            </div>

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Compensation & Emergency Contact</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input label="Base Salary" name="base_salary" type="number" step="0.01" min="0" :value="$employee->base_salary" />
                    <x-form.input label="Currency" name="currency_code" :value="$employee->currency_code ?? 'INR'" />
                    <x-form.select label="Pay Frequency *" name="pay_frequency" :options="\App\Models\Employee::PAY_FREQUENCIES" :value="$employee->pay_frequency ?? 'monthly'" required />
                    <x-form.input label="Emergency Contact Name" name="emergency_contact_name" :value="$employee->emergency_contact_name" />
                    <x-form.input label="Emergency Contact Phone" name="emergency_contact_phone" :value="$employee->emergency_contact_phone" />
                </div>
                <x-form.textarea label="Notes" name="notes" :value="$employee->notes" rows="3" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('hr.employees.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">{{ $employee->exists ? 'Save Changes' : 'Create Employee' }}</button>
            </div>
        </form>
    </div>
</x-layouts.app>

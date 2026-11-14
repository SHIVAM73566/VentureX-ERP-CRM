<x-layouts.app
    :title="$department->exists ? 'Edit Department' : 'New Department'"
    :breadcrumbs="[['label' => 'Human Resources', 'url' => route('hr.dashboard')], ['label' => 'Departments', 'url' => route('hr.departments.index')], ['label' => $department->exists ? 'Edit' : 'New']]">

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ $department->exists ? route('hr.departments.update', $department) : route('hr.departments.store') }}" class="space-y-6">
            @csrf
            @if ($department->exists)
                @method('PUT')
            @endif

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Department Details</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input label="Name *" name="name" :value="$department->name" required />
                    <x-form.input label="Code" name="code" :value="$department->code" help="Short code used in reports." />
                    <x-form.select label="Branch" name="branch_id" :options="$branches->pluck('name', 'id')" :value="$department->branch_id" />
                    <x-form.select label="Parent Department" name="parent_id" :options="$parents->pluck('name', 'id')" :value="$department->parent_id" />
                </div>
                <x-form.checkbox label="Active" name="is_active" :checked="$department->is_active" description="Inactive departments are hidden from new employee records." />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('hr.departments.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">{{ $department->exists ? 'Save Changes' : 'Create Department' }}</button>
            </div>
        </form>
    </div>
</x-layouts.app>

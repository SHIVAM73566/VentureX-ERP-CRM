<x-layouts.app
    :title="$user->exists ? 'Edit User' : 'New User'"
    :breadcrumbs="[['label' => 'Administration', 'url' => '#'], ['label' => 'Users', 'url' => route('admin.users.index')], ['label' => $user->exists ? 'Edit' : 'New']]">

    <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @if ($user->exists)
            @method('PUT')
        @endif

        <div class="card space-y-4">
            <h2 class="text-lg font-bold text-ink-900">User Details</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-form.input label="Full Name" name="name" :value="$user->name" required />
                <x-form.input label="Email" name="email" type="email" :value="$user->email" required />
                @if ($user->exists)
                    <x-form.input label="New Password (leave blank to keep)" name="password" type="password" help="At least {{ config('security.password.min_length') }} characters with upper, lower, number and special character." />
                @else
                    <x-form.input label="Password" name="password" type="password" required help="At least {{ config('security.password.min_length') }} characters with upper, lower, number and special character." />
                @endif
                <x-form.input label="Job Title" name="job_title" :value="$user->job_title" />
                <x-form.input label="Phone" name="phone" :value="$user->phone" />
                <x-form.select label="Company" name="company_id" :options="$companies->pluck('name', 'id')" :value="$user->company_id" required />
                <x-form.select label="Branch" name="branch_id" :options="$branches->pluck('name', 'id')" :value="$user->branch_id" />
                <x-form.select label="Department" name="department_id" :options="$departments->pluck('name', 'id')" :value="$user->department_id" />
            </div>

            <div>
                <x-form.checkbox name="is_active" label="Active" description="Inactive users cannot sign in." :checked="$user->is_active ?? true" />
            </div>
        </div>

        <div class="card space-y-4">
            <h2 class="text-lg font-bold text-ink-900">Roles</h2>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach ($roles as $role)
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-ink-200 px-3 py-2 text-sm text-ink-700 hover:bg-ink-50">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked($user->roles->contains($role->id)) class="h-4 w-4 rounded border-ink-300 text-accent-600 focus:ring-accent-500">
                        {{ $role->label }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-accent">{{ $user->exists ? 'Save Changes' : 'Create User' }}</button>
        </div>
    </form>
</x-layouts.app>

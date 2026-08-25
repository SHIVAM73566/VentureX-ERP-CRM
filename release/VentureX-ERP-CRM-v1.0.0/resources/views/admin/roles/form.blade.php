<x-layouts.app
    :title="$role->exists ? 'Edit Role' : 'New Role'"
    :breadcrumbs="[['label' => 'Administration', 'url' => '#'], ['label' => 'Roles', 'url' => route('admin.roles.index')], ['label' => $role->exists ? 'Edit' : 'New']]">

    <form method="POST" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}" class="mx-auto max-w-4xl space-y-6">
        @csrf
        @if ($role->exists)
            @method('PUT')
        @endif

        <div class="card space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-form.input label="Role Name" name="name" :value="$role->name" required help="Internal key e.g. procurement_manager" />
                <x-form.input label="Display Label" name="label" :value="$role->label" required help="Shown across the UI." />
                <x-form.textarea label="Description" name="description" :value="$role->description" rows="2" />
            </div>
        </div>

        <div class="card space-y-4">
            <h2 class="text-lg font-bold text-ink-900">Permissions</h2>

            @foreach ($permissionGroups as $group => $groupPermissions)
                <div>
                    <p class="mb-2 text-xs font-bold uppercase tracking-wide text-ink-400">{{ $group }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($groupPermissions as $permission)
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-ink-200 bg-white px-3 py-2 text-sm text-ink-700 hover:bg-ink-50">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked($role->permissions->contains($permission->id)) class="h-4 w-4 rounded border-ink-300 text-accent-600 focus:ring-accent-500">
                                {{ $permission->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.roles.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-accent">{{ $role->exists ? 'Save Changes' : 'Create Role' }}</button>
        </div>
    </form>
</x-layouts.app>

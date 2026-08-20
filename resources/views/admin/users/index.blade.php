<x-layouts.app title="Users" :breadcrumbs="[['label' => 'Administration'], ['label' => 'Users']]">

    <x-slot name="actions">
        @can('create', App\Models\User::class)
            <a href="{{ route('admin.users.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New User
            </a>
        @endcan
    </x-slot>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="avatar">{{ str($user->displayName())->initials() }}</span>
                                    <div>
                                        <p class="font-semibold text-ink-800">{{ $user->displayName() }}</p>
                                        <p class="text-xs text-ink-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->roles->pluck('label')->implode(', ') ?: '—' }}</td>
                            <td>{{ $user->department?->name ?? '—' }}</td>
                            <td>
                                <span class="{{ $user->is_active ? 'badge-green' : 'badge-gray' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="text-right">
                                @can('update', $user)
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-ink-400">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.app>

<x-layouts.app title="Contacts" :breadcrumbs="[['label' => 'CRM'], ['label' => 'Contacts']]">

    <x-slot name="actions">
        @can('create', App\Models\Contact::class)
            <a href="{{ route('crm.contacts.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Contact
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-dashboard.stat-card label="Total Contacts" :value="$summary['total']" icon="users" color="blue" />
        <x-dashboard.stat-card label="Primary" :value="$summary['primary']" icon="target" color="amber" />
        <x-dashboard.stat-card label="Active" :value="$summary['active']" icon="badge" color="green" />
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('crm.contacts.index') }}" class="flex flex-1 flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name, email or company..." class="input max-w-md" />
            <select name="customer_id" class="input max-w-[14rem]" onchange="this.form.submit()">
                <option value="">All customers</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>{{ $customer->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Contact</th><th>Company</th><th>Title</th><th>Email</th><th>Phone</th><th></th></tr></thead>
                <tbody>
                    @forelse ($contacts as $contact)
                        <tr>
                            <td>
                                <p class="font-semibold text-ink-800">
                                    {{ $contact->fullName() }}
                                    @if ($contact->is_primary) <span class="badge-amber">Primary</span>@endif
                                </p>
                                <p class="text-xs text-ink-400">{{ $contact->is_active ? 'Active' : 'Inactive' }}</p>
                            </td>
                            <td>{{ $contact->customer?->name ?? '—' }}</td>
                            <td>{{ $contact->title ?? '—' }}</td>
                            <td>{{ $contact->email ?? '—' }}</td>
                            <td>{{ $contact->phone ?: $contact->mobile ?: '—' }}</td>
                            <td class="text-right">
                                @can('update', $contact)
                                    <a href="{{ route('crm.contacts.edit', $contact) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">Edit</a>
                                @endcan
                                @can('delete', $contact)
                                    <form method="POST" action="{{ route('crm.contacts.destroy', $contact) }}" class="inline" onsubmit="return confirm('Delete this contact?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ml-2 text-sm font-medium text-red-600 hover:text-red-500">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-ink-400">No contacts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $contacts->links() }}</div>
</x-layouts.app>

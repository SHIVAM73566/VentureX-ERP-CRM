<x-layouts.app title="Customers" :breadcrumbs="[['label' => 'CRM'], ['label' => 'Customers']]">

    <x-slot name="actions">
        @can('create', App\Models\Customer::class)
            <a href="{{ route('customers.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Customer
            </a>
        @endcan
    </x-slot>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('customers.index') }}" class="flex flex-1 gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search customers..." class="input max-w-md" />
            <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                <option value="prospect" @selected(request('status') === 'prospect')>Prospect</option>
            </select>
            <button type="submit" class="btn-secondary">Search</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Contacts</th>
                        <th>Opportunities</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td>
                                <p class="font-semibold text-ink-800 dark:text-ink-100">{{ $customer->name }}</p>
                                <p class="text-xs text-ink-400 dark:text-ink-500">{{ $customer->tax_id ? 'VAT: '.$customer->tax_id : '—' }}</p>
                            </td>
                            <td class="text-sm text-ink-600 dark:text-ink-400">{{ $customer->email ?? $customer->phone ?? '—' }}</td>
                            <td>{{ $customer->contacts_count }}</td>
                            <td>{{ $customer->opportunities_count }}</td>
                            <td>
                                <span class="{{ $customer->status === 'active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($customer->status) }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('customers.show', $customer) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500 dark:text-navy-400 dark:hover:text-navy-300">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-ink-400 dark:text-ink-500">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $customers->links() }}</div>
</x-layouts.app>

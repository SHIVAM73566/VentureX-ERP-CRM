<x-layouts.app
    :title="'Customer Management'"
    :breadcrumbs="[['label' => 'Administration', 'url' => route('admin.control-center.index')], ['label' => 'Control Center', 'url' => route('admin.control-center.index')], ['label' => 'Customers']]">

    <div class="mx-auto max-w-7xl space-y-6">

        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800">Customers</h2>
                <form method="GET" action="{{ route('admin.control-center.customers') }}" class="flex items-center gap-2">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search company..." class="input max-w-xs">
                    <button type="submit" class="btn-secondary">Search</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Users</th>
                            <th>Open Tickets</th>
                            <th>Total Tickets</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($companies as $company)
                            <tr>
                                <td class="font-medium text-ink-800">{{ $company->name }}</td>
                                <td class="text-center text-ink-600">{{ $company->users_count ?? 0 }}</td>
                                <td class="text-center text-ink-600">{{ $company->open_ticket_count ?? 0 }}</td>
                                <td class="text-center text-ink-600">{{ $company->ticket_count ?? 0 }}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.control-center.customer', $company) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-sm text-ink-400">No customers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $companies->withQueryString()->links() }}</div>
        </div>
    </div>
</x-layouts.app>

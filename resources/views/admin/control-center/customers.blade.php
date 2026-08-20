<x-layouts.app
    :title="'Customer Management'"
    :breadcrumbs="[['label' => 'Administration', 'url' => route('admin.control-center.index')], ['label' => 'Control Center', 'url' => route('admin.control-center.index')], ['label' => 'Customers']]">

    @php
        $licenseTierColor = fn ($tier) => match($tier) {
            'enterprise' => 'badge-violet',
            'professional' => 'badge-blue',
            'starter' => 'badge-green',
            default => 'badge-gray',
        };

        $licenseStatusColor = fn ($status) => match($status) {
            'active' => 'badge-green',
            'trial' => 'badge-blue',
            'expired' => 'badge-red',
            'suspended' => 'badge-gray',
            default => 'badge-gray',
        };
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">

        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800">Customers</h2>
                <form method="GET" action="{{ route('admin.control-center.customers') }}" class="flex items-center gap-2">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search company..." class="input max-w-xs">
                    <select name="license_status" class="input max-w-[10rem]" onchange="this.form.submit()">
                        <option value="">All statuses</option>
                        @foreach (['active', 'trial', 'expired', 'suspended'] as $s)
                            <option value="{{ $s }}" @selected(request('license_status') === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <select name="license_tier" class="input max-w-[10rem]" onchange="this.form.submit()">
                        <option value="">All tiers</option>
                        @foreach (['enterprise', 'professional', 'starter'] as $t)
                            <option value="{{ $t }}" @selected(request('license_tier') === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-secondary">Filter</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>License Tier</th>
                            <th>License Status</th>
                            <th>Installations</th>
                            <th>Tickets</th>
                            <th>Errors</th>
                            <th>Last Active</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            <tr>
                                <td class="font-medium text-ink-800">{{ $customer->name }}</td>
                                <td><span class="{{ $licenseTierColor($customer->license?->tier) }}">{{ ucfirst($customer->license?->tier ?? '--') }}</span></td>
                                <td><span class="{{ $licenseStatusColor($customer->license?->status) }}">{{ ucfirst($customer->license?->status ?? '--') }}</span></td>
                                <td class="text-center text-ink-600">{{ $customer->installations_count ?? $customer->installations->count() ?? 0 }}</td>
                                <td class="text-center text-ink-600">{{ $customer->tickets_count ?? 0 }}</td>
                                <td class="text-center text-ink-600">{{ $customer->errors_count ?? 0 }}</td>
                                <td class="text-xs text-ink-400">{{ $customer->last_active_at?->diffForHumans() ?? '--' }}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.control-center.customer', $customer) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-sm text-ink-400">No customers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $customers->withQueryString()->links() }}</div>
        </div>
    </div>
</x-layouts.app>

<x-layouts.app title="Companies" :breadcrumbs="[['label' => 'Administration'], ['label' => 'Companies']]">

    <x-slot name="actions">
        @can('create', App\Models\Company::class)
            <a href="{{ route('admin.companies.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Company
            </a>
        @endcan
    </x-slot>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Tax ID</th>
                        <th>Users</th>
                        <th>Currency</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companies as $company)
                        <tr>
                            <td>
                                <p class="font-semibold text-ink-800">{{ $company->name }}</p>
                                <p class="text-xs text-ink-400">{{ $company->city }}{{ $company->state ? ', '.$company->state : '' }}</p>
                            </td>
                            <td>{{ $company->tax_id ?? '—' }}</td>
                            <td>{{ $company->users_count }}</td>
                            <td>{{ $company->currency_code ?? '—' }}</td>
                            <td>
                                <span class="{{ $company->is_active ? 'badge-green' : 'badge-gray' }}">{{ $company->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.companies.edit', $company) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-ink-400">No companies found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $companies->links() }}</div>
</x-layouts.app>

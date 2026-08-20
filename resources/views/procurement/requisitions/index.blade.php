<x-layouts.app title="Purchase Requisitions" :breadcrumbs="[['label' => 'Procurement'], ['label' => 'Requisitions']]">

    <x-slot name="actions">
        @can('create', App\Models\PurchaseRequisition::class)
            <a href="{{ route('procurement.requisitions.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Requisition
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Total" :value="$summary['total']" icon="document" color="blue" />
        <x-dashboard.stat-card label="Pending Approval" :value="$summary['pending']" icon="alert" color="amber" />
        <x-dashboard.stat-card label="Approved" :value="$summary['approved']" icon="badge" color="green" />
        <x-dashboard.stat-card label="Ordered" :value="$summary['ordered']" icon="shopping" color="violet" />
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('procurement.requisitions.index') }}" class="flex flex-1 flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search PR number..." class="input max-w-md" />
            <select name="status" class="input max-w-[12rem]" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach (\App\Models\PurchaseRequisition::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="priority" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All priorities</option>
                @foreach (\App\Models\PurchaseRequisition::PRIORITIES as $key => $label)
                    <option value="{{ $key }}" @selected(request('priority') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>PR Number</th><th>Department</th><th>Requester</th><th>Items</th><th>Priority</th><th>Required</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($requisitions as $requisition)
                        <tr>
                            <td class="font-semibold text-ink-800">{{ $requisition->pr_number }}</td>
                            <td>{{ $requisition->department?->name ?? '—' }}</td>
                            <td>{{ $requisition->requester?->name ?? '—' }}</td>
                            <td>{{ $requisition->items_count ?? $requisition->items->count() }}</td>
                            <td>@php $pc = ['low' => 'gray', 'medium' => 'blue', 'high' => 'amber', 'critical' => 'red']; @endphp
                                <span class="badge-{{ $pc[$requisition->priority] ?? 'gray' }}">{{ \App\Models\PurchaseRequisition::PRIORITIES[$requisition->priority] ?? $requisition->priority }}</span>
                            </td>
                            <td>{{ $requisition->required_date?->format('d M Y') ?? '—' }}</td>
                            <td>@php $sc = ['draft' => 'gray', 'pending_approval' => 'amber', 'approved' => 'green', 'rejected' => 'red', 'ordered' => 'violet']; @endphp
                                <span class="badge-{{ $sc[$requisition->status] ?? 'gray' }}">{{ \App\Models\PurchaseRequisition::STATUSES[$requisition->status] ?? $requisition->status }}</span>
                            </td>
                            <td class="text-right"><a href="{{ route('procurement.requisitions.show', $requisition) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-ink-400">No requisitions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $requisitions->links() }}</div>
</x-layouts.app>

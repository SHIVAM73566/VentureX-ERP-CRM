<x-layouts.app
    :title="$company->name"
    :breadcrumbs="[['label' => 'Administration', 'url' => route('admin.control-center.index')], ['label' => 'Control Center', 'url' => route('admin.control-center.index')], ['label' => 'Customers', 'url' => route('admin.control-center.customers')], ['label' => $company->name]]">

    @php
        $statusColor = fn ($status) => match($status) {
            'open' => 'badge-blue',
            'in_progress' => 'badge-amber',
            'resolved' => 'badge-green',
            'closed' => 'badge-gray',
            default => 'badge-gray',
        };
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6">
                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Company Info</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-ink-400">Name</dt><dd class="font-medium text-ink-800">{{ $company->name }}</dd></div>
                        @if ($company->email)<div class="flex justify-between"><dt class="text-ink-400">Email</dt><dd class="text-ink-600">{{ $company->email }}</dd></div>@endif
                        @if ($company->phone)<div class="flex justify-between"><dt class="text-ink-400">Phone</dt><dd class="text-ink-600">{{ $company->phone }}</dd></div>@endif
                        @if ($company->website)<div class="flex justify-between"><dt class="text-ink-400">Website</dt><dd class="text-ink-600">{{ $company->website }}</dd></div>@endif
                        @if ($company->country)<div class="flex justify-between"><dt class="text-ink-400">Country</dt><dd class="text-ink-600">{{ $company->country->name }}</dd></div>@endif
                        <div class="flex justify-between"><dt class="text-ink-400">Users</dt><dd class="text-ink-600">{{ $company->users_count ?? $company->users->count() }}</dd></div>
                    </dl>
                </div>

                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Ticket Summary</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg border border-blue-200 bg-blue-50 p-3 text-center">
                            <p class="text-xl font-bold text-blue-600">{{ $tickets->total() ?? 0 }}</p>
                            <p class="text-xs font-semibold text-blue-700">Total</p>
                        </div>
                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-center">
                            <p class="text-xl font-bold text-amber-600">{{ $tickets->where('status', 'open')->count() ?? 0 }}</p>
                            <p class="text-xs font-semibold text-amber-700">Open</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6 lg:col-span-2">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-bold text-ink-800">Ticket History ({{ $tickets->total() }})</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Created</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tickets as $ticket)
                                    <tr>
                                        <td class="font-mono text-xs text-ink-500">{{ $ticket->ticket_number }}</td>
                                        <td class="font-medium text-ink-800">{{ Str::limit($ticket->subject, 40) }}</td>
                                        <td><span class="{{ $statusColor($ticket->status) }}">{{ str_replace('_', ' ', ucfirst($ticket->status)) }}</span></td>
                                        <td><span class="{{ match($ticket->priority) { 'urgent' => 'badge-red', 'high' => 'badge-amber', 'medium' => 'badge-yellow', 'low' => 'badge-green', default => 'badge-gray' } }}">{{ ucfirst($ticket->priority) }}</span></td>
                                        <td class="text-xs text-ink-400">{{ $ticket->created_at->diffForHumans() }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.support.tickets.show', $ticket) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-sm text-ink-400">No tickets.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $tickets->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

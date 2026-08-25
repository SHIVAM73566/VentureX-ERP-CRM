<x-layouts.app
    :title="'My Tickets'"
    :breadcrumbs="[
        ['label' => 'Support Center', 'url' => route('support.index')],
        ['label' => 'My Tickets'],
    ]"
    :actions="'<a href=\'' . route('support.tickets.create') . '\' class=\'inline-flex items-center gap-2 rounded-lg bg-navy-600 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-navy-700\'><svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 4v16m8-8H4\'/></svg> New Ticket</a>'">

    <div class="mx-auto max-w-6xl space-y-6">

        {{-- Status Filter --}}
        <div class="card">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-medium text-ink-600">Status:</span>
                @php
                    $statusFilters = [
                        '' => ['All', 'bg-ink-100 text-ink-600'],
                        'open' => ['Open', 'bg-blue-100 text-blue-700'],
                        'investigating' => ['Investigating', 'bg-amber-100 text-amber-700'],
                        'in_progress' => ['In Progress', 'bg-indigo-100 text-indigo-700'],
                        'resolved' => ['Resolved', 'bg-green-100 text-green-700'],
                        'closed' => ['Closed', 'bg-ink-200 text-ink-500'],
                    ];
                    $activeStatus = request('status', '');
                @endphp
                @foreach ($statusFilters as $value => [$label, $colors])
                    <a href="{{ route('support.tickets.index', array_filter(['status' => $value ?: null])) }}"
                        class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ $activeStatus === $value ? 'bg-navy-600 text-white' : $colors }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Tickets Table --}}
        @if (isset($tickets) && $tickets->count())
            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th class="hidden md:table-cell">Module</th>
                                <th class="hidden lg:table-cell">Created</th>
                                <th class="hidden lg:table-cell">Last Reply</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tickets as $ticket)
                                <tr>
                                    <td class="font-mono text-xs font-semibold text-navy-600">
                                        #{{ $ticket->ticket_number ?? $ticket->id }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('support.tickets.show', $ticket) }}" class="font-medium text-ink-800 hover:text-navy-600">{{ $ticket->subject }}</a>
                                    </td>
                                    <td class="whitespace-nowrap">
                                        @php
                                            $statusStyles = match($ticket->status) {
                                                'open' => 'badge-blue',
                                                'investigating' => 'badge-amber',
                                                'in_progress' => 'badge-violet',
                                                'resolved' => 'badge-green',
                                                'closed' => 'badge-gray',
                                                default => 'badge-gray',
                                            };
                                        @endphp
                                        <span class="{{ $statusStyles }}">{{ str_replace('_', ' ', ucfirst($ticket->status)) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $priorityStyles = match($ticket->priority) {
                                                'urgent' => 'badge-red',
                                                'high' => 'badge-amber',
                                                'medium' => 'badge-yellow',
                                                'low' => 'badge-green',
                                                default => 'badge-gray',
                                            };
                                        @endphp
                                        <span class="{{ $priorityStyles }}">{{ ucfirst($ticket->priority) }}</span>
                                    </td>
                                    <td class="hidden text-ink-500 md:table-cell">{{ ucfirst($ticket->module ?? 'General') }}</td>
                                    <td class="hidden text-ink-400 lg:table-cell">{{ $ticket->created_at?->format('M d, Y') ?? '' }}</td>
                                    <td class="hidden text-ink-400 lg:table-cell">{{ $ticket->last_reply_at?->format('M d, Y') ?? '—' }}</td>
                                    <td>
                                        <a href="{{ route('support.tickets.show', $ticket) }}" class="inline-flex items-center gap-1 text-xs font-medium text-navy-600 hover:text-navy-800">
                                            View
                                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $tickets->withQueryString()->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="card flex flex-col items-center justify-center py-16 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-ink-100 text-ink-400">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h3 class="mt-3 text-sm font-semibold text-ink-800">No tickets found</h3>
                <p class="mt-1 text-sm text-ink-500">
                    @if ($activeStatus)
                        No tickets match the selected status. Try a different filter.
                    @else
                        You haven't created any support tickets yet.
                    @endif
                </p>
                <a href="{{ route('support.tickets.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-navy-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-navy-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Create Your First Ticket
                </a>
            </div>
        @endif
    </div>
</x-layouts.app>

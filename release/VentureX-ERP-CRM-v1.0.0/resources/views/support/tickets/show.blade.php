<x-layouts.app
    :title="'Ticket #' . ($ticket->ticket_number ?? $ticket->id)"
    :breadcrumbs="[
        ['label' => 'Support Center', 'url' => route('support.index')],
        ['label' => 'My Tickets', 'url' => route('support.tickets.index')],
        ['label' => 'Ticket #' . ($ticket->ticket_number ?? $ticket->id)],
    ]">

    <div class="mx-auto max-w-4xl space-y-6">

        {{-- Ticket Header --}}
        <div class="card">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-mono text-xs font-semibold text-navy-600">#{{ $ticket->ticket_number ?? $ticket->id }}</span>
                        @php
                            $statusStyles = match($ticket->status) {
                                'open' => 'bg-blue-100 text-blue-700',
                                'investigating' => 'bg-amber-100 text-amber-700',
                                'in_progress' => 'bg-indigo-100 text-indigo-700',
                                'resolved' => 'bg-green-100 text-green-700',
                                'closed' => 'bg-ink-200 text-ink-500',
                                default => 'bg-ink-100 text-ink-600',
                            };
                            $priorityStyles = match($ticket->priority) {
                                'urgent' => 'bg-red-100 text-red-700',
                                'high' => 'bg-orange-100 text-orange-700',
                                'medium' => 'bg-yellow-100 text-yellow-700',
                                'low' => 'bg-green-100 text-green-700',
                                default => 'bg-ink-100 text-ink-600',
                            };
                        @endphp
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusStyles }}">{{ str_replace('_', ' ', ucfirst($ticket->status)) }}</span>
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $priorityStyles }}">{{ ucfirst($ticket->priority) }}</span>
                    </div>
                    <h2 class="mt-2 text-xl font-bold text-ink-900">{{ $ticket->subject }}</h2>
                </div>

                @if ($ticket->status === 'resolved')
                    <form action="{{ route('support.tickets.update', $ticket) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="closed">
                        <button type="submit" onclick="return confirm('Are you sure you want to close this ticket?')" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Close Ticket
                        </button>
                    </form>
                @endif
            </div>

            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 border-t border-ink-100 pt-4 text-xs text-ink-500">
                <span><strong class="text-ink-600">Category:</strong> {{ ucfirst(str_replace('_', ' ', $ticket->category ?? 'General')) }}</span>
                <span><strong class="text-ink-600">Module:</strong> {{ ucfirst($ticket->module ?? 'General') }}</span>
                <span><strong class="text-ink-600">Created:</strong> {{ $ticket->created_at?->format('M d, Y \a\t g:i A') ?? '' }}</span>
                <span><strong class="text-ink-600">Updated:</strong> {{ $ticket->updated_at?->diffForHumans() ?? '' }}</span>
            </div>
        </div>

        {{-- Replies Thread --}}
        <div class="space-y-4">
            <h3 class="text-sm font-bold text-ink-700">Conversation</h3>

            @forelse ($ticket->replies->filter(fn ($r) => !$r->is_internal) as $reply)
                @php $isAdmin = $reply->user->hasRole('super_admin') || $reply->user->hasRole('admin'); @endphp
                <div class="flex {{ $isAdmin ? 'justify-start' : 'justify-end' }}">
                    <div class="max-w-[80%] {{ $isAdmin ? 'bg-ink-100' : 'bg-navy-600 text-white' }} rounded-2xl px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            @if ($isAdmin)
                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-navy-600 text-[10px] font-bold text-white">S</div>
                            @endif
                            <span class="text-xs font-semibold {{ $isAdmin ? 'text-ink-600' : 'text-navy-200' }}">{{ $reply->user->displayName() ?? 'Support' }}</span>
                            <span class="text-xs {{ $isAdmin ? 'text-ink-400' : 'text-navy-300' }}">{{ $reply->created_at?->diffForHumans() ?? '' }}</span>
                        </div>
                        <p class="mt-2 text-sm {{ $isAdmin ? 'text-ink-700' : 'text-white' }}">{!! nl2br(e($reply->message)) !!}</p>
                        @if ($reply->attachments && count($reply->attachments) > 0)
                            @foreach ($reply->attachments as $attachment)
                                <a href="{{ asset('storage/' . $attachment) }}" target="_blank" class="mt-2 inline-flex items-center gap-1 text-xs {{ $isAdmin ? 'text-navy-600' : 'text-navy-200' }} hover:underline">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    View attachment
                                </a>
                            @endforeach
                        @endif
                    </div>
                </div>
            @empty
                <div class="card py-8 text-center text-sm text-ink-400">
                    No replies yet. Submit a message below to start the conversation.
                </div>
            @endforelse
        </div>

        {{-- Original Ticket Message --}}
        @if ($ticket->description)
            <div class="card border-l-4 border-navy-400">
                <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Your original message</p>
                <p class="mt-2 text-sm text-ink-700">{!! nl2br(e($ticket->description)) !!}</p>
            </div>
        @endif

        {{-- Reply Form --}}
        @if (!in_array($ticket->status, ['closed']))
            <form action="{{ route('support.tickets.reply', $ticket) }}" method="POST">
                @csrf
                <div class="card">
                    <h3 class="mb-3 text-sm font-bold text-ink-700">Reply</h3>
                    <textarea name="message" rows="3" required
                        placeholder="Type your reply here..."
                        class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-700 placeholder-ink-400 focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20"></textarea>
                    <div class="mt-3 flex items-center justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-navy-600 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-navy-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Send Reply
                        </button>
                    </div>
                </div>
            </form>
        @else
            <div class="card py-8 text-center text-sm text-ink-400">
                This ticket is closed. If you need further assistance, please <a href="{{ route('support.tickets.create') }}" class="font-semibold text-navy-600 hover:underline">create a new ticket</a>.
            </div>
        @endif
    </div>
</x-layouts.app>

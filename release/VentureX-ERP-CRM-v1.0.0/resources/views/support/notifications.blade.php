<x-layouts.app
    :title="'Support Notifications'"
    :breadcrumbs="[
        ['label' => 'Support Center', 'url' => route('support.index')],
        ['label' => 'Notifications'],
    ]">

    <div class="mx-auto max-w-5xl space-y-6">

        {{-- Filters --}}
        <div class="card">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-medium text-ink-600">Filter:</span>
                @php
                    $filters = [
                        '' => 'All',
                        'ticket_created' => 'Ticket Created',
                        'ticket_replied' => 'Ticket Replied',
                        'ticket_resolved' => 'Ticket Resolved',
                        'announcement' => 'Announcements',
                        'update_available' => 'Updates Available',
                    ];
                    $active = request('type', '');
                @endphp
                @foreach ($filters as $value => $label)
                    <a href="{{ route('support.notifications', array_filter(['type' => $value ?: null])) }}"
                        class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ $active === $value ? 'bg-navy-600 text-white' : 'bg-ink-100 text-ink-600 hover:bg-ink-200' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Notification List --}}
        @if (isset($notifications) && $notifications->count())
            <div class="card divide-y divide-ink-100">
                @foreach ($notifications as $notification)
                    <div class="flex items-start gap-4 px-5 py-4 {{ $notification->read_at ? '' : 'bg-navy-50/40' }}">
                        {{-- Icon --}}
                        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full
                            {{ match($notification->type ?? 'default') {
                                'ticket_created' => 'bg-navy-100 text-navy-600',
                                'ticket_replied' => 'bg-emerald-100 text-emerald-600',
                                'ticket_resolved' => 'bg-green-100 text-green-600',
                                'announcement' => 'bg-amber-100 text-amber-600',
                                'update_available' => 'bg-violet-100 text-violet-600',
                                default => 'bg-ink-100 text-ink-500',
                            } }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </div>

                        {{-- Content --}}
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-ink-800">{{ $notification->title ?? 'Notification' }}</p>
                            <p class="mt-0.5 text-sm text-ink-500">{{ $notification->message ?? $notification->data ?? '' }}</p>
                            <p class="mt-1 text-xs text-ink-400">{{ $notification->created_at?->diffForHumans() ?? '' }}</p>
                        </div>

                        {{-- Actions --}}
                        <div class="flex shrink-0 items-center gap-2">
                            @if (!$notification->read_at)
                                <form action="{{ route('support.notifications.mark-read', $notification) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="Mark as read" class="rounded-lg p-1.5 text-ink-400 hover:bg-ink-100 hover:text-ink-600">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('support.notifications.mark-unread', $notification) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="Mark as unread" class="rounded-lg p-1.5 text-ink-400 hover:bg-ink-100 hover:text-ink-600">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $notifications->withQueryString()->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="card flex flex-col items-center justify-center py-16 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-ink-100 text-ink-400">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <h3 class="mt-3 text-sm font-semibold text-ink-800">No notifications</h3>
                <p class="mt-1 text-sm text-ink-500">You're all caught up! Support notifications will appear here.</p>
                <a href="{{ route('support.index') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-navy-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-navy-700">
                    Back to Support Center
                </a>
            </div>
        @endif
    </div>
</x-layouts.app>

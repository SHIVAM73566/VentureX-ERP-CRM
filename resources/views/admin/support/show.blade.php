<x-layouts.app
    :title="'Ticket ' . $ticket->ticket_number"
    :breadcrumbs="[['label' => 'Administration', 'url' => route('admin.support.index')], ['label' => 'Support', 'url' => route('admin.support.index')], ['label' => $ticket->ticket_number]]">

    @php
        $statusColor = fn ($status) => match($status) {
            'open' => 'badge-blue',
            'in_progress' => 'badge-amber',
            'resolved' => 'badge-green',
            'closed' => 'badge-gray',
            default => 'badge-gray',
        };

        $priorityColor = fn ($priority) => match($priority) {
            'urgent' => 'badge-red',
            'high' => 'badge-amber',
            'medium' => 'badge-yellow',
            'low' => 'badge-green',
            default => 'badge-gray',
        };
    @endphp

    <div class="mx-auto max-w-6xl space-y-6" x-data="{ replyForm: false, noteForm: false }">

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">

                <div class="card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-ink-900">{{ $ticket->subject }}</h2>
                            <p class="mt-1 text-sm text-ink-500">Created {{ $ticket->created_at->format('d M Y, H:i') }} by {{ $ticket->user?->name ?? 'System' }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="{{ $priorityColor($ticket->priority) }}">{{ ucfirst($ticket->priority) }}</span>
                            <span class="{{ $statusColor($ticket->status) }}">{{ str_replace('_', ' ', ucfirst($ticket->status)) }}</span>
                        </div>
                    </div>
                    <div class="mt-4 rounded-lg border border-ink-100 bg-ink-50/50 p-4">
                        <p class="whitespace-pre-wrap text-sm text-ink-700">{{ $ticket->description }}</p>
                    </div>
                </div>

                @if ($ticket->errorReport)
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-sm font-bold text-ink-800">Attached Error Report</h3>
                            <a href="{{ route('admin.errors.show', $ticket->errorReport) }}" class="text-xs font-medium text-navy-600 hover:text-navy-500">View in Error Center</a>
                        </div>
                        <div class="p-5 space-y-2">
                            <div class="flex items-center gap-3 text-sm">
                                <span class="text-ink-400">Error:</span>
                                <span class="badge badge-red">{{ $ticket->errorReport->error_type }}</span>
                                <span class="text-ink-600">{{ Str::limit($ticket->errorReport->error_message, 60) }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <span class="text-ink-400">Occurrences:</span>
                                <span class="text-ink-800 font-medium">{{ $ticket->errorReport->occurrence_count }}</span>
                                <span class="text-ink-400 ml-4">Last seen:</span>
                                <span class="text-ink-600">{{ $ticket->errorReport->last_seen_at?->diffForHumans() ?? '--' }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-bold text-ink-800">Conversation</h3>
                    </div>
                    <div class="divide-y divide-ink-100">
                        @forelse ($ticket->replies as $reply)
                            <div class="p-5 {{ $reply->is_internal ? 'bg-amber-50/40' : '' }}">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $reply->is_admin ? 'bg-navy-100 text-navy-700' : 'bg-ink-100 text-ink-600' }}">
                                            {{ substr($reply->user?->name ?? 'S', 0, 1) }}
                                        </span>
                                        <span class="text-sm font-semibold text-ink-800">{{ $reply->user?->name ?? 'System' }}</span>
                                        @if ($reply->is_admin)
                                            <span class="badge badge-blue text-[10px]">Admin</span>
                                        @endif
                                        @if ($reply->is_internal)
                                            <span class="badge badge-yellow text-[10px]">Internal Note</span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-ink-400">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="mt-2 whitespace-pre-wrap pl-9 text-sm text-ink-700">{{ $reply->body }}</div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-sm text-ink-400">No replies yet.</div>
                        @endforelse
                    </div>
                </div>

                <div class="card">
                    <div class="p-5">
                        <div x-show="!replyForm" x-cloak>
                            <button @click="replyForm = true; noteForm = false" class="btn-primary">Add Reply</button>
                            <button @click="noteForm = true; replyForm = false" class="btn-secondary ml-2">Add Internal Note</button>
                        </div>

                        <div x-show="replyForm" x-cloak>
                            <h4 class="mb-3 text-sm font-bold text-ink-800">Add Reply</h4>
                            <form method="POST" action="{{ route('admin.support.replies.store', $ticket) }}">
                                @csrf
                                <input type="hidden" name="is_internal" value="0">
                                <textarea name="body" class="input" rows="4" placeholder="Type your reply..." required></textarea>
                                @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                <div class="mt-3 flex items-center gap-2">
                                    <button type="submit" class="btn-primary">Send Reply</button>
                                    <button type="button" @click="replyForm = false" class="btn-secondary">Cancel</button>
                                </div>
                            </form>
                        </div>

                        <div x-show="noteForm" x-cloak>
                            <h4 class="mb-3 text-sm font-bold text-ink-800">Add Internal Note</h4>
                            <form method="POST" action="{{ route('admin.support.replies.store', $ticket) }}">
                                @csrf
                                <input type="hidden" name="is_internal" value="1">
                                <textarea name="body" class="input border-amber-300 focus:border-amber-500 focus:ring-amber-500/20" rows="4" placeholder="Internal note (only visible to admins)..." required></textarea>
                                @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                <div class="mt-3 flex items-center gap-2">
                                    <button type="submit" class="btn-accent">Save Note</button>
                                    <button type="button" @click="noteForm = false" class="btn-secondary">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Ticket Details</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd class="font-mono text-ink-800">{{ $ticket->ticket_number }}</dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Status</dt><dd><span class="{{ $statusColor($ticket->status) }}">{{ str_replace('_', ' ', ucfirst($ticket->status)) }}</span></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Priority</dt><dd><span class="{{ $priorityColor($ticket->priority) }}">{{ ucfirst($ticket->priority) }}</span></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Category</dt><dd class="text-ink-700">{{ $ticket->category ?? '--' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Module</dt><dd><span class="badge badge-gray">{{ $ticket->module ?? 'General' }}</span></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Created by</dt><dd class="text-ink-700">{{ $ticket->creator?->name ?? '--' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Assigned to</dt><dd class="text-ink-700">{{ $ticket->assignee?->name ?? 'Unassigned' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Replies</dt><dd class="text-ink-700">{{ $ticket->replies_count ?? $ticket->replies->count() }}</dd></div>
                    </dl>
                </div>

                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Update Status</h3>
                    <form method="POST" action="{{ route('admin.support.update', $ticket) }}">
                        @csrf
                        @method('PUT')
                        <div class="space-y-3">
                            <div>
                                <label for="status" class="label">Status</label>
                                <select id="status" name="status" class="input">
                                    @foreach (['open', 'in_progress', 'resolved', 'closed'] as $status)
                                        <option value="{{ $status }}" @selected($ticket->status === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="assignee_id" class="label">Assign to</label>
                                <select id="assignee_id" name="assignee_id" class="input">
                                    <option value="">Unassigned</option>
                                    @foreach ($admins as $admin)
                                        <option value="{{ $admin->id }}" @selected($ticket->assignee_id === $admin->id)>{{ $admin->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn-primary w-full">Update Ticket</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

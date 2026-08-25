<x-layouts.app
    :title="'Active Sessions'"
    :breadcrumbs="[['label' => 'Account'], ['label' => 'Active Sessions']]">

    <div class="mx-auto max-w-3xl space-y-4">
        <div class="card">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-ink-900">Your active sessions</h2>
                    <p class="text-sm text-ink-500">Devices currently signed in to your account. Revoke any session you do not recognize.</p>
                </div>
                <form method="POST" action="{{ route('sessions.revoke-all') }}" onsubmit="return confirm('Sign out from all other devices?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-secondary">Sign out all other devices</button>
                </form>
            </div>

            <div class="divide-y divide-ink-100">
                @forelse ($sessions as $session)
                    <div class="flex items-center justify-between gap-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-ink-100 text-ink-500">
                                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-ink-800">
                                    {{ $session->ip_address }}
                                    @if ($session->current)
                                        <span class="badge badge-blue ml-1">This device</span>
                                    @endif
                                </p>
                                <p class="text-xs text-ink-400">{{ \Illuminate\Support\Str::limit($session->user_agent, 80) }}</p>
                                <p class="text-xs text-ink-400">Last active {{ \Illuminate\Support\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</p>
                            </div>
                        </div>
                        @unless ($session->current)
                            <form method="POST" action="{{ route('sessions.destroy', $session->id) }}" onsubmit="return confirm('Terminate this session?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger">Revoke</button>
                            </form>
                        @endunless
                    </div>
                @empty
                    <p class="py-8 text-center text-sm text-ink-400">No sessions found.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>

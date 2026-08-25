<x-layouts.app
    :title="'Security Center'"
    :breadcrumbs="[['label' => 'Administration'], ['label' => 'Security Center']]">

    <div class="mx-auto max-w-5xl space-y-6">
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="card flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink-900">{{ $score['score'] }}<span class="text-sm font-semibold text-ink-400">/100</span></p>
                    <p class="text-xs text-ink-500">Security score</p>
                </div>
            </div>

            <div class="card flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $alerts > 0 ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold {{ $alerts > 0 ? 'text-red-600' : 'text-ink-900' }}">{{ $alerts }}</p>
                    <p class="text-xs text-ink-500">High/Critical alerts (24h)</p>
                </div>
            </div>

            <div class="card flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $lockdown ? 'bg-red-50 text-red-600' : 'bg-ink-100 text-ink-500' }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold {{ $lockdown ? 'text-red-600' : 'text-ink-900' }}">{{ $lockdown ? 'ACTIVE' : 'Off' }}</p>
                    <p class="text-xs text-ink-500">Emergency lockdown</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-ink-900">Emergency lockdown</h2>
                    <p class="text-sm text-ink-500">Blocks all non-administrative access immediately. Super admins can always reach this page to lift it.</p>
                </div>
                @if ($lockdown)
                    <form method="POST" action="{{ route('security.lockdown') }}" onsubmit="return confirm('Lift emergency lockdown?')">
                        @csrf
                        <input type="hidden" name="active" value="0">
                        <button type="submit" class="btn-secondary">Lift lockdown</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('security.lockdown') }}" onsubmit="return confirm('Activate emergency lockdown? All normal access will be blocked.')">
                        @csrf
                        <input type="hidden" name="active" value="1">
                        <button type="submit" class="btn-danger">Activate lockdown</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card">
            <h2 class="mb-4 text-lg font-bold text-ink-900">Security checkpoints</h2>
            <div class="divide-y divide-ink-100">
                @foreach ($score['checkpoints'] as $checkpoint)
                    <div class="flex items-center justify-between gap-3 py-2.5">
                        <div class="flex items-center gap-3">
                            @if ($checkpoint['passed'])
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            @else
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01"/></svg>
                                </span>
                            @endif
                            <span class="text-sm {{ $checkpoint['passed'] ? 'text-ink-800' : 'text-ink-600' }}">{{ $checkpoint['label'] }}</span>
                        </div>
                        <span class="text-xs font-semibold {{ $checkpoint['passed'] ? 'text-emerald-600' : 'text-amber-600' }}">+{{ $checkpoint['weight'] }} pts</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <h2 class="mb-4 text-lg font-bold text-ink-900">Recent security events</h2>
            <div class="divide-y divide-ink-100">
                @forelse ($recentEvents as $event)
                    <div class="flex items-center justify-between gap-3 py-2.5">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-ink-800">{{ $event->title }}</p>
                            <p class="text-xs text-ink-400">{{ $event->event }} · {{ $event->ip ?? '—' }} · {{ $event->user?->email ?? 'system' }} · {{ $event->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="badge {{ $event->severity === 'high' || $event->severity === 'critical' ? 'badge-red' : ($event->severity === 'medium' ? 'badge-amber' : 'badge-gray') }}">{{ $event->severity }}</span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-ink-400">No security events recorded yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>

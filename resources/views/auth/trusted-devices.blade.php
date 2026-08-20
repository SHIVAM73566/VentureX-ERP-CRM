<x-layouts.app
    :title="'Trusted Devices'"
    :breadcrumbs="[['label' => 'Account', 'url' => route('devices.index')], ['label' => 'Trusted Devices']]">

    <div class="mx-auto max-w-3xl space-y-4">
        <div class="card">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-ink-900">Your trusted devices</h2>
                    <p class="text-sm text-ink-500">Devices you have explicitly trusted. Removing a trusted device will require re-verification on next login from that device.</p>
                </div>
            </div>

            <div class="divide-y divide-ink-100">
                @forelse ($devices as $device)
                    <div class="flex items-center justify-between gap-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $device->is_trusted ? 'bg-emerald-100 text-emerald-600' : 'bg-ink-100 text-ink-500' }}">
                                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-ink-800">
                                    {{ $device->device_name }}
                                    @if ($device->is_trusted)
                                        <span class="badge badge-green ml-1">Trusted</span>
                                    @endif
                                </p>
                                <p class="text-xs text-ink-400">IP: {{ $device->last_ip ?? $device->ip_address }}</p>
                                <p class="text-xs text-ink-400">Last seen {{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('devices.remove', $device->id) }}" onsubmit="return confirm('Remove this trusted device? You will need to re-verify on next login.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger">Remove</button>
                        </form>
                    </div>
                @empty
                    <p class="py-8 text-center text-sm text-ink-400">No trusted devices found.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>

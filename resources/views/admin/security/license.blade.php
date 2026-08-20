<x-layouts.app
    :title="'License Information'"
    :breadcrumbs="[['label' => 'Administration', 'url' => route('security.dashboard')], ['label' => 'License Information']]">

    <div class="mx-auto max-w-3xl space-y-6">
        <div class="card">
            <h2 class="mb-4 text-lg font-bold text-ink-900">License Details</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-medium text-ink-500">Reference Number</p>
                    <p class="font-mono text-sm text-ink-900">{{ $license['reference'] ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-ink-500">Version</p>
                    <p class="text-sm text-ink-900">{{ $license['version'] ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-ink-500">Status</p>
                    @if ($license['licensed'] ?? false)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Licensed
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Unlicensed
                        </span>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-medium text-ink-500">License Tier</p>
                    <p class="text-sm font-semibold text-ink-900">{{ ucfirst($license['tier'] ?? 'none') }}</p>
                </div>
            </div>

            @if ($license['message'] ?? null)
                <div class="mt-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-800">
                    {{ $license['message'] }}
                </div>
            @endif
        </div>

        <div class="card">
            <h2 class="mb-2 text-lg font-bold text-ink-900">Activate License</h2>
            <p class="mb-4 text-sm text-ink-500">Enter your license key to activate your purchase and receive updates.</p>

            <form method="POST" action="{{ route('security.license.activate') }}">
                @csrf
                <div class="flex gap-3">
                    <input
                        type="text"
                        name="license_key"
                        placeholder="TWIT-ERP-XXXX-XXXX-XXXX"
                        class="input flex-1"
                        value="{{ old('license_key') }}"
                    >
                    <button type="submit" class="btn-primary">Activate</button>
                </div>
            </form>
        </div>

        @if ($license['purchase_url'] ?? null)
            <div class="card">
                <h2 class="mb-2 text-lg font-bold text-ink-900">Need a License?</h2>
                <p class="mb-3 text-sm text-ink-500">Purchase a license to unlock all features, receive updates, and get support.</p>
                <a href="{{ $license['purchase_url'] }}" target="_blank" rel="noopener" class="btn-primary">
                    Purchase License
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        @endif
    </div>
</x-layouts.app>

@props(['layerKey' => null, 'message' => 'Grow without re-platforming: add only the capability your team needs.'])

@php
    $shown = session('upgrade_prompt_shown_this_session', false);
@endphp

@if(!$shown)
<div x-data="{ open: true, busy: false }" x-show="open" data-overlay class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20" role="status">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-sm font-medium text-amber-900 dark:text-amber-200">{{ $message }}</p>
            @if($layerKey)
                <p class="mt-1 text-xs text-amber-800 dark:text-amber-300">Related capability: <span class="font-mono">{{ $layerKey }}</span>.</p>
            @endif
            <p class="mt-1 text-xs text-amber-800/80 dark:text-amber-300/80">Exact price, tax and renewal terms are shown by Stripe before payment. Launch-window terms can change; nothing is charged here.</p>
            @auth
                @if(auth()->user()?->hasRole('super_admin'))
                    <a href="{{ route('admin.settings.index', ['tab' => 'plan']) }}" class="mt-2 inline-flex text-xs font-semibold text-amber-900 underline hover:no-underline dark:text-amber-200">Review optional add-ons →</a>
                @endif
            @endauth
        </div>
        <button type="button"
                @click="open = false; fetch('{{ route('upgrade-prompt.dismiss') }}', { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}', 'Accept': 'application/json' } })"
                data-close-overlay
                class="shrink-0 text-amber-700 hover:text-amber-900 dark:text-amber-300 text-sm font-medium"
                aria-label="Dismiss upgrade suggestion">Dismiss</button>
    </div>
</div>
@endif

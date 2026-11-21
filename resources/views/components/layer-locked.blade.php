@props(['layerKey', 'title' => null, 'description' => null])

@php
    $layer = config("layers.layers.$layerKey");
    $title = $title ?? ($layer['name'] ?? 'Premium feature');
    $description = $description ?? 'Unlock this feature by enabling the related layer in your plan.';
@endphp

<div class="rounded-lg border border-dashed border-ink-300 bg-ink-50 p-4 dark:border-ink-600 dark:bg-ink-900/30">
    <div class="flex items-start gap-3">
        <div class="mt-0.5 text-amber-600" aria-hidden="true">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-ink-900 dark:text-ink-100">{{ $title }}</p>
            <p class="mt-1 text-sm text-ink-600 dark:text-ink-400">{{ $description }}</p>
            @if(($layer['status'] ?? '') === 'coming_soon')
                <p class="mt-2 text-xs font-medium text-ink-500">Coming soon — join the waitlist (see marketing materials).</p>
            @else
                <p class="mt-2 text-xs text-ink-500">Contact your administrator or visit Settings → Plan &amp; Layers (when available) to upgrade.</p>
            @endif
        </div>
    </div>
</div>

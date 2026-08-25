@props(['label', 'value', 'icon' => 'trending', 'color' => 'blue', 'url' => null, 'sub' => null])

@php
    $colors = [
        'blue' => ['bg' => 'bg-navy-50', 'text' => 'text-navy-600'],
        'green' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
        'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
        'violet' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600'],
        'red' => ['bg' => 'bg-red-50', 'text' => 'text-red-600'],
        'gray' => ['bg' => 'bg-ink-100', 'text' => 'text-ink-600'],
    ];

    $palette = $colors[$color] ?? $colors['blue'];

    $icons = [
        'users' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        'target' => 'M12 2a10 10 0 100 20 10 10 0 000-20zM12 6a6 6 0 100 12 6 6 0 000-12zm0 4a2 2 0 100 4 2 2 0 000-4z',
        'trending' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
        'wallet' => 'M3 10h18M7 15h2m-5 4h16a1 1 0 001-1V6a1 1 0 00-1-1H4a1 1 0 00-1 1v12a1 1 0 001 1z',
        'box' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        'ship' => 'M3 13l9 5 9-5M3 13l9-5 9 5M3 13v5l9 5 9-5v-5M12 3v5m0 0l2 1.5M12 8l-2 1.5',
        'chart' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        'spark' => 'M5 3l.8 2.2L8 6l-2.2.8L5 9l-.8-2.2L2 6l2.2-.8L5 3zm11 0l.8 2.2L19 6l-2.2.8L16 9l-.8-2.2L13 6l2.2-.8L16 3zm-5 5l.8 2.2L14 11l-2.2.8L11 14l-.8-2.2L8 11l2.2-.8L11 8zm6 7l.8 2.2L20 18l-2.2.8L17 21l-.8-2.2L14 18l2.2-.8L17 15z',
        'alert' => 'M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z',
        'document' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'receipt' => 'M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16l-3-2-2 2-2-2-2 2-2-2-3 2zM9 7h6m-6 4h6m-6 4h4',
        'clock' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'truck' => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM3 5h10v10H3zM13 9h4.5L21 12.5V15h-8V9z',
        'calculator' => 'M9 7h6m-6 4h2m4 0h-2m-4 4h2m4 0h-2m-8 5h12a1 1 0 001-1V4a1 1 0 00-1-1H6a1 1 0 00-1 1v16a1 1 0 001 1z',
        'banknotes' => 'M2 9a2 2 0 012-2h16a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V9zm2 1v4m0 0h16m-16 0a2 2 0 002-2m-2 2a2 2 0 00-2-2',
        'building' => 'M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 12h.01M9 15h.01M12 9h.01M12 12h.01M12 15h.01M15 9h.01M15 12h.01M15 15h.01',
        'badge' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'folder' => 'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z',
        'flag' => 'M4 21V4c0-1 1-1.5 2-1l6 2 6-2c1-.5 2 0 2 1v11c0 1-1 1.5-2 1l-6-2-6 2c-1 .5-2 0-2-1z',
        'arrow-down' => 'M12 19V5m0 0l-7 7m7-7l7 7',
        'arrow-up' => 'M12 5v14m0 0l7-7m-7 7l-7-7',
        'pencil' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125',
        'shopping' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
    ];
@endphp

<div class="stat-card">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-ink-400 dark:text-ink-500">{{ $label }}</p>
            <p class="mt-1.5 text-2xl font-bold text-ink-900 dark:text-ink-50">{{ $value }}</p>
            @if ($sub)
                <p class="mt-0.5 text-xs text-ink-400 dark:text-ink-500">{{ $sub }}</p>
            @endif
        </div>
        <span class="stat-icon {{ $palette['bg'] }} {{ $palette['text'] }}">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$icon] ?? $icons['trending'] }}"/></svg>
        </span>
    </div>
    @if ($url)
        <a href="{{ $url }}" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-navy-600 hover:text-navy-500 dark:text-navy-400 dark:hover:text-navy-300">
            View all
            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
    @endif
</div>

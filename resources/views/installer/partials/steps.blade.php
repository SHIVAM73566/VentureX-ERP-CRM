@php
    $steps = [
        1 => 'Requirements',
        2 => 'Mode',
        3 => 'Database',
        4 => 'Environment',
        5 => 'Company & Admin',
        6 => 'Install',
    ];
    $current = (int) ($current ?? 1);
@endphp

<nav class="mb-6 flex flex-wrap items-center justify-center gap-2 text-xs font-semibold text-ink-400 dark:text-ink-500" aria-label="Installation progress">
    @foreach ($steps as $number => $label)
        @if ($number > 1)
            <span class="h-px w-4 bg-ink-200 dark:bg-ink-700" aria-hidden="true"></span>
        @endif

        <span class="flex items-center gap-1.5 {{ $number === $current ? 'text-navy-800 dark:text-ink-100' : '' }}">
            <span class="flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-bold
                {{ $number < $current ? 'bg-emerald-600 text-white' : ($number === $current ? 'bg-navy-800 text-white' : 'bg-ink-200 text-ink-500 dark:bg-ink-700 dark:text-ink-300') }}">
                {{ $number < $current ? '&#10003;' : $number }}
            </span>
            {{ $label }}
        </span>
    @endforeach
</nav>

@props([
    'lines' => 1,
    'class' => '',
    'circle' => false,
])
<div {{ $attributes->merge(['class' => 'animate-pulse']) }}>
    @if($circle)
        <div class="rounded-full bg-ink-200 h-10 w-10 {{ $class }}"></div>
    @else
        @for($i = 0; $i < $lines; $i++)
            <div class="h-4 bg-ink-200 rounded {{ $class }} {{ $i < $lines - 1 ? 'mb-2' : '' }}" style="width: {{ rand(60, 100) }}%"></div>
        @endfor
    @endif
</div>

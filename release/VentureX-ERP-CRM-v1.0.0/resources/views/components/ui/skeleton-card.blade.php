@props(['lines' => 3])
<div class="card animate-pulse">
    <div class="h-5 bg-ink-200 rounded w-1/3 mb-4"></div>
    <div class="h-8 bg-ink-200 rounded w-1/2 mb-2"></div>
    @for($i = 0; $i < $lines; $i++)
        <div class="h-3 bg-ink-100 rounded mb-1" style="width: {{ rand(50, 90) }}%"></div>
    @endfor
</div>

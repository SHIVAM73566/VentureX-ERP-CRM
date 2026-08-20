@props(['rows' => 5, 'cols' => 4])
<div class="animate-pulse">
    <div class="bg-ink-200 h-10 rounded-t-lg w-full"></div>
    @for($i = 0; $i < $rows; $i++)
        <div class="flex gap-4 py-3 {{ $i < $rows - 1 ? 'border-b border-ink-100' : '' }}">
            @for($j = 0; $j < $cols; $j++)
                <div class="h-4 bg-ink-100 rounded flex-1"></div>
            @endfor
        </div>
    @endfor
</div>

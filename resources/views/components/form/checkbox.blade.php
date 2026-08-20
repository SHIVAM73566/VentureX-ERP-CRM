@props(['name', 'label' => null, 'checked' => false, 'description' => null])

<label class="flex items-start gap-3 rounded-lg border border-ink-200 bg-white p-3 hover:bg-ink-50">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked)) class="mt-0.5 h-4 w-4 rounded border-ink-300 text-accent-600 focus:ring-accent-500">
    <span>
        @if ($label)
            <span class="block text-sm font-medium text-ink-800">{{ $label }}</span>
        @endif
        @if ($description)
            <span class="block text-xs text-ink-400">{{ $description }}</span>
        @endif
    </span>
</label>

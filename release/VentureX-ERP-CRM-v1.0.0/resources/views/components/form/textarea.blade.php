@props(['label' => null, 'name', 'value' => null, 'rows' => 3, 'help' => null, 'required' => false])

<div>
    @if ($label)
        <label for="{{ $name }}" class="label">{{ $label }}@if ($required) <span class="text-red-500">*</span>@endif</label>
    @endif
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" {{ $required ? 'required' : '' }} class="input" @error($name) aria-invalid="true" @enderror>{{ old($name, $value) }}</textarea>
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
    @if ($help)
        <p class="mt-1 text-xs text-ink-400">{{ $help }}</p>
    @endif
</div>

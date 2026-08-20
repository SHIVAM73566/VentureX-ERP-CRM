@props(['label' => null, 'name', 'type' => 'text', 'value' => null, 'help' => null, 'placeholder' => null, 'required' => false, 'disabled' => false])

<div>
    @if ($label)
        <label for="{{ $name }}" class="label">{{ $label }}@if ($required) <span class="text-red-500">*</span>@endif</label>
    @endif
    <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }} {{ $disabled ? 'disabled' : '' }} class="input" @error($name) aria-invalid="true" @enderror>
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
    @if ($help)
        <p class="mt-1 text-xs text-ink-400">{{ $help }}</p>
    @endif
</div>

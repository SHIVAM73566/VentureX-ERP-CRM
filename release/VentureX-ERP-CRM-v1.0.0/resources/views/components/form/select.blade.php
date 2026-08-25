@props(['label' => null, 'name', 'options' => [], 'value' => null, 'placeholder' => '— Select —', 'required' => false])

<div>
    @if ($label)
        <label for="{{ $name }}" class="label">{{ $label }}@if ($required) <span class="text-red-500">*</span>@endif</label>
    @endif
    <select id="{{ $name }}" name="{{ $name }}" {{ $required ? 'required' : '' }} class="input" @error($name) aria-invalid="true" @enderror>
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $key => $option)
            <option value="{{ $key }}" @selected(old($name, $value) == $key)>{{ $option }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

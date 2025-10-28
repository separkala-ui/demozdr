@props([
    'label' => null,
    'name' => null,
    'value' => null,
    'placeholder' => '',
    'hint' => null,
    'required' => false,
    'disabled' => false,
    'min' => null,
    'max' => null,
])
<div>
    @if($label)
        <label class="form-label" for="{{ $name }}">{{ $label }}</label>
    @endif
    <input
        type="text"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder ?: __('مثال: 1404-07-27') }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->class(['form-control', 'jalali-date-input']) }}
        x-data
        x-init="window.initJalaliDatepicker($el, { enableTime: false })"
        autocomplete="off"
    >
    @if($hint)
        <div class="text-xs text-gray-400 mt-1">{{ $hint }}</div>
    @endif
</div>

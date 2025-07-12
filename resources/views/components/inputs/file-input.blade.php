@props([
    'label' => 'File',
    'name' => 'file',
    'id' => null,
    'multiple' => false,
    'existingAttachment' => null,
    'existingAltText' => '',
    'removeCheckboxName' => 'remove_featured_image',
    'removeCheckboxLabel' => null,
])

@php
    $id = $id ?? $name;
@endphp

<div {{ $attributes->merge(['class' => 'mb-4']) }}>
    <label for="{{ $id }}"
        class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ $label }}</label>
    @if ($existingAttachment)
        <div class="mb-4">
            <img src="{{ $existingAttachment }}" alt="{{ $existingAltText }}" class="max-h-48 rounded-lg">

            @if($removeCheckboxLabel)
                <div class="mt-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="{{ $removeCheckboxName }}" id="{{ $removeCheckboxName }}"
                            class="mr-2">
                        <span
                            class="text-sm text-gray-700 dark:text-gray-400">{{ $removeCheckboxLabel }}</span>
                    </label>
                </div>
            @endif
        </div>
    @endif
    <input type="file" name="{{ $name }}" id="{{ $id }}" {{ $multiple ? 'multiple' : '' }}
        class="form-control-file">
    {{ $slot }}
</div>

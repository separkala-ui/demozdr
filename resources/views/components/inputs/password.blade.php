@props([
    'name',
    'label' => '',
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'class' => '',
    'id' => null,
    'autocomplete' => 'new-password',
    'autogenerate' => false,
    'showTooltip' => __('Show password'),
])

@php
    $inputId = $id ?? $name;
@endphp

<div class="w-full flex flex-col gap-1">
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
    @endif

    <div
        x-data="{
            showPassword: false,
            password: '{{ $value }}',
            autogenerate() {
                // 16-char random password: upper, lower, digit, symbol
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=';
                let pass = '';
                for (let i = 0; i < 16; i++) {
                    pass += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                this.password = pass;
            }
        }"
        class="relative"
    >
        <input
            :type="showPassword ? 'text' : 'password'"
            name="{{ $name }}"
            id="{{ $inputId }}"
            x-model="password"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            class="form-control {{ $class }}"
            autocomplete="{{ $autocomplete }}"
        />

        <div class="absolute right-4 top-1/2 -translate-y-1/2 flex gap-2 z-30">
            <x-tooltip :title="$showTooltip">
                <button type="button" @click="showPassword = !showPassword" class="text-gray-500 cursor-pointer dark:text-gray-300 flex items-center justify-center w-6 h-6">
                    <iconify-icon x-show="!showPassword" icon="lucide:eye" width="20" height="20" class="text-[#98A2B3]"></iconify-icon>
                    <iconify-icon x-show="showPassword" icon="lucide:eye-off" width="20" height="20" class="text-[#98A2B3]" style="display: none;"></iconify-icon>
                </button>
            </x-tooltip>
            @if($autogenerate)
            <x-tooltip title="{{ __('Autogenerate password') }}">
                <button type="button" @click="autogenerate" class="text-gray-500 cursor-pointer dark:text-gray-300 flex items-center justify-center w-6 h-6">
                    <iconify-icon icon="lucide:wand-sparkles" width="20" height="20" class="text-[#98A2B3]"></iconify-icon>
                </button>
            </x-tooltip>
            @endif
        </div>
    </div>
</div>

@props([
    'submitLabel' => __('Save'),
    'showSubmit' => true,
    'cancelLabel' => __('Cancel'),
    'cancelUrl' => null,
    'id' => null,
    'showIcon' => true,
    'classNames' => [
        'wrapper' => 'flex justify-start gap-4',
        'primary' => 'btn-primary',
        'cancel' => 'btn-default',
    ],
])

<div class="{{ $classNames['wrapper'] ?? 'mt-6 flex justify-start gap-4' }}">
    @if ($showSubmit)
        <button type="submit" @if (!empty($id)) id="{{ $id }}" @endif
            class="{{ $classNames['primary'] ?? 'btn-primary' }}">
            @if ($showIcon)
                <i class="bi bi-check-circle mr-2"></i>
            @endif

            @if (!empty($submitLabel))
                {{ $submitLabel }}
            @endif

            {{-- Fallback for when submitLabel and icon both are empty --}}
            @if (empty($submitLabel) && $showIcon)
                {{ __('Save') }}
            @endif
        </button>
    @endif

    @if (!empty($cancelLabel) && !empty($cancelUrl))
        <a href="{{ $cancelUrl }}" class="{{ $classNames['cancel'] ?? 'btn-default' }}">
            @if ($showIcon)
                <i class="bi bi-x-circle mr-2"></i>
            @endif

            {{ $cancelLabel }}
        </a>
    @endif
</div>

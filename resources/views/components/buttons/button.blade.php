@props([
	'type' => 'button', // button, submit, reset
	'variant' => 'default', // primary, success, danger, warning, info, secondary, default
	'class' => '',
	'disabled' => false,
	'as' => 'button', // button or a (for link)
	'href' => null,
	'icon' => null,
	'iconPosition' => 'left', // left or right
	'loading' => false, // show loading spinner instead of text
	'loadingText' => null, // optional loading text to show alongside spinner
])

@php
	$typeClass = match($variant) {
		'primary' => 'btn-primary',
		'success' => 'btn-success',
		'danger' => 'btn-danger',
		'warning' => 'btn-warning',
		'info' => 'btn-info',
		'secondary' => 'btn-secondary',
		default => 'btn-default',
	};
	
	// Define spinner colors based on button variant
	$spinnerColor = match($variant) {
		'primary' => 'text-white',
		'success' => 'text-white',
		'danger' => 'text-white', 
		'warning' => 'text-white',
		'info' => 'text-white',
		'secondary' => 'text-gray-600 dark:text-gray-300',
		default => 'text-gray-600 dark:text-gray-300',
	};
	
	$classes = trim("btn $typeClass $class");
	$isDisabled = $disabled || $loading;
@endphp

@if($as === 'a' && $href)
	<a href="{{ $href }}" class="{{ $classes }}" @if($isDisabled) aria-disabled="true" tabindex="-1" @endif>
		@if($loading)
			<span class="flex items-center">
				<svg class="size-5 animate-spin {{ $spinnerColor }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
					<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
				</svg>
				@if($loadingText)
					<span class="ml-2">{{ $loadingText }}</span>
				@endif
			</span>
		@else
			@if($icon && $iconPosition === 'left')
				<span class="mr-2">{!! $icon !!}</span>
			@endif
			{{ $slot }}
			@if($icon && $iconPosition === 'right')
				<span class="ml-2">{!! $icon !!}</span>
			@endif
		@endif
	</a>
@else
	<button type="{{ $type }}" class="{{ $classes }}" @if($isDisabled) disabled @endif>
		@if($loading)
			<span class="flex items-center">
				<svg class="size-5 animate-spin {{ $spinnerColor }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
					<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
				</svg>
				@if($loadingText)
					<span class="ml-2">{{ $loadingText }}</span>
				@endif
			</span>
		@else
			@if($icon && $iconPosition === 'left')
				<span class="mr-2">{!! $icon !!}</span>
			@endif
			{{ $slot }}
			@if($icon && $iconPosition === 'right')
				<span class="ml-2">{!! $icon !!}</span>
			@endif
		@endif
	</button>
@endif

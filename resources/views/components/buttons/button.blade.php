@props([
	'type' => 'default', // primary, success, danger, warning, info, secondary, default
	'class' => '',
	'disabled' => false,
	'as' => 'button', // button or a (for link)
	'href' => null,
	'icon' => null,
])

@php
	$typeClass = match($type) {
		'primary' => 'btn-primary',
		'success' => 'btn-success',
		'danger' => 'btn-danger',
		'warning' => 'btn-warning',
		'info' => 'btn-info',
		'secondary' => 'btn-secondary',
		default => 'btn-default',
	};
	$classes = trim("btn $typeClass $class");
@endphp

@if($as === 'a' && $href)
	<a href="{{ $href }}" class="{{ $classes }}" @if($disabled) aria-disabled="true" tabindex="-1" @endif>
		@if($icon)
			<span class="mr-2">{!! $icon !!}</span>
		@endif
		{{ $slot }}
	</a>
@else
	<button type="button" class="{{ $classes }}" @if($disabled) disabled @endif>
		@if($icon)
			<span class="mr-2">{!! $icon !!}</span>
		@endif
		{{ $slot }}
	</button>
@endif

@props([
    'id' => null,
    'title' => '',
    'description' => '',
    'position' => 'top', // top, bottom, left, right
    'width' => '',
    'arrowAlign' => 'center', // left, center, right
])

@php
$positions = [
    'top' => 'bottom-full mb-2 left-1/2 -translate-x-1/2',
    'bottom' => 'top-full mt-2 left-1/2 -translate-x-1/2',
    'left' => 'right-full mr-2 top-1/2 -translate-y-1/2',
    'right' => 'left-full ml-2 top-1/2 -translate-y-1/2',
];
$positionClass = $positions[$position] ?? $positions['top'];

$arrowAlignClass = [
    'left' => 'left-4',
    'center' => 'left-1/2 -translate-x-1/2',
    'right' => 'right-4',
][$arrowAlign] ?? 'left-1/2 -translate-x-1/2';
@endphp

<div
    x-data="{
        open: false,
        arrowVisible: false,
        show() {
            this.open = true;
            this.arrowVisible = true;
        },
        hide() {
            this.arrowVisible = false;
            setTimeout(() => { this.open = false }, 120);
        }
    }"
    class="relative {{ !$width ? 'w-fit' : '' }}"
    style="{{ $width ? "width: {$width};" : '' }}"
>
    <div
        @mouseenter="show()"
        @mouseleave="hide()"
        @focus="show()"
        @blur="hide()"
        tabindex="0"
        aria-describedby="{{ $id }}"
        class="outline-none"
    >
        {{ $slot }}
    </div>

    <div
        id="{{ $id }}"
        x-show="open"
        x-transition.opacity
        class="{{ $positionClass }} absolute z-10 inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 invisible tooltip dark:bg-gray-700 text-center"
        :class="{ 'opacity-100 visible': open, 'opacity-0 invisible': !open }"
        role="tooltip"
        style="min-width: 150px;"
    >
        @if($title)
            <span class="text-sm font-medium text-white">{{ $title }}</span>
        @endif

        @if($description)
            <p class="text-balance text-white/90">{{ $description }}</p>
        @endif

        <div
            x-show="arrowVisible"
            x-transition.opacity
            class="tooltip-arrow transition-opacity duration-100 absolute {{ $arrowAlignClass }}"
            :class="{ 'opacity-100 visible': arrowVisible, 'opacity-0 invisible': !arrowVisible }"
            data-popper-arrow
        ></div>
    </div>
</div>

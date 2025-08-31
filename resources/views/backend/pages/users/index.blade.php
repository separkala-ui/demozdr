<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <x-slot name="breadcrumbsData">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs">
            <x-slot name="title_after">
                @if (request('role'))
                    <span class="badge">{{ ucfirst(request('role')) }}</span>
                @endif
            </x-slot>
        </x-breadcrumbs>
    </x-slot>

    {!! ld_apply_filters('users_after_breadcrumbs', '') !!}
    @livewire('datatable.user-datatable', ['lazy' => true])
</x-layouts.backend-layout>

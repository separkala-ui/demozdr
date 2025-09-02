<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters('terms_after_breadcrumbs', '', $taxonomyModel) !!}

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-6">
            @include('backend.pages.terms.partials.form')
        </div>

        <div class="lg:col-span-2 space-y-6">
            @livewire('datatable.term-datatable', ['taxonomy' => $taxonomy])
        </div>
    </div>
</x-layouts.backend-layout>
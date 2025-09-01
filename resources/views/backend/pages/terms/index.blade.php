<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters('terms_after_breadcrumbs', '', $taxonomyModel) !!}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <x-card>
                <x-slot name="header">
                    {{ $term ? __("Edit {$taxonomyModel->label_singular}") : __("Add New {$taxonomyModel->label_singular}") }}
                </x-slot>

                <form
                    action="{{ route('admin.terms.store', $taxonomy) }}"
                    method="POST"
                    enctype="multipart/form-data"
                    data-prevent-unsaved-changes
                >
                    @include('backend.pages.terms.partials.form')
                </form>
            </x-card>
        </div>

        <div class="lg:col-span-2 space-y-6">
            @livewire('datatable.term-datatable', ['taxonomy' => $taxonomy])
        </div>
    </div>
</x-layouts.backend-layout>
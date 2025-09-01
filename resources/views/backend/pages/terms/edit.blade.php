<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters('terms_edit_breadcrumbs', '', $taxonomyModel) !!}

    <div class="max-w-4xl mx-auto">
        <x-card>
            <x-slot name="header">
                {{ __('Edit') }} {{ $taxonomyModel->label_singular }}
            </x-slot>

            <form
                action="{{ route('admin.terms.update', [$taxonomy, $term->id]) }}"
                method="POST"
                enctype="multipart/form-data"
                data-prevent-unsaved-changes
            >
                @method('PUT')
                @include('backend.pages.terms.partials.form')
            </form>
        </x-card>

    @push('scripts')
        <x-quill-editor :editor-id="'description'" />
    @endpush
</x-layouts.backend-layout>
<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! ld_apply_filters('posts_create_after_breadcrumbs', '', $postType) !!}

    <x-card>
        <form
            action="{{ route('admin.posts.store', $postType) }}"
            method="POST"
            enctype="multipart/form-data"
            data-prevent-unsaved-changes
        >
            @csrf
            @include('backend.pages.posts.partials.form', [
                'post' => null,
                'selectedTerms' => [],
                'postType' => $postType,
                'postTypeModel' => $postTypeModel,
                'taxonomies' => $taxonomies ?? [],
                'parentPosts' => $parentPosts ?? [],
                'mode' => 'create',
            ])
        </form>
    </x-card>

    {!! ld_apply_filters('after_post_form', '', $postType) !!}

    @push('scripts')
        <x-quill-editor :editor-id="'content'" height="200px" maxHeight="-1" />
    @endpush
</x-layouts.backend-layout>

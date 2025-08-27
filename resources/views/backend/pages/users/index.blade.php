@extends('backend.layouts.app')

@section('title')
   {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<livewire:tables.user lazy />
@endsection

@push('scripts')
<script>
    function handleRoleFilter(value) {
        let currentUrl = new URL(window.location.href);

        // Preserve sort parameter if it exists.
        const sortParam = currentUrl.searchParams.get('sort');

        // Reset the search params but keep the sort if it exists.
        currentUrl.search = '';

        if (value) {
            currentUrl.searchParams.set('role', value);
        }

        // Re-add sort parameter if it existed.
        if (sortParam) {
            currentUrl.searchParams.set('sort', sortParam);
        }

        window.location.href = currentUrl.toString();
    }
</script>
@endpush

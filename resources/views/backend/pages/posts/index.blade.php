<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    @livewire('datatable.post-datatable', ['postType' => $postType, 'lazy' => true])
</x-layouts.backend-layout>
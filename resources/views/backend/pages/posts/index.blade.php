<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    @livewire('datatable.post-datatable', ['postType' => $postType ,'lazy' => true])
    {{-- @livewire('datatable.examples.sample-todo-datatable', ['lazy' => true, 'class' => 'mt-2']) --}}
</x-layouts.backend-layout>
<x-buttons.action-buttons :label="__('Actions')" :show-label="false" align="right">

    @if ($this->getRoutes()['view'] ?? false && $this->getPermissions()['view'] ?? false)
        <x-buttons.action-item
            :href="route($this->getRoutes()['view'] ?? '', $item->id)"
            icon="lucide:eye"
            :label="__('View')"
        />
    @endif

    {!! $this->renderAfterActionView($item) !!}

    @if (auth()->user()->canBeModified($item) && $this->getRoutes()['edit'] ?? false && $this->getPermissions()['edit'] ?? false)
        <x-buttons.action-item
            :href="route($this->getRoutes()['edit'] ?? '', $item->id)"
            icon="lucide:pencil"
            :label="__('Edit')"
        />
    @endif

    {!! $this->renderAfterActionEdit($item) !!}

    @if (auth()->user()->canBeModified($item, $this->getPermissions()['delete'] ?? '') && $this->getRoutes()['delete'] ?? false)
        <div x-data="{ deleteModalOpen: false }">
            <x-buttons.action-item
                type="modal-trigger"
                modal-target="deleteModalOpen"
                icon="lucide:trash"
                :label="__('Delete')"
                class="text-red-600 dark:text-red-400"
            />

            <x-modals.confirm-delete
                id="delete-modal-{{ $item->id }}"
                title="{{ __('Delete :model', ['model' => $this->getModelNameSingular()]) }}"
                content="{{ __('Are you sure you want to delete this :model?', ['model' => $this->getModelNameSingular()]) }}"
                formId="delete-form-{{ $item->id }}"
                formAction="{{ route($this->getRoutes()['delete'] ?? '', $item->id) }}"
                modalTrigger="deleteModalOpen"
                cancelButtonText="{{ __('No, cancel') }}"
                confirmButtonText="{{ __('Yes, Confirm') }}"
            />
        </div>
    @endif

    {!! $this->renderAfterActionDelete($item) !!}
</x-buttons.action-buttons>
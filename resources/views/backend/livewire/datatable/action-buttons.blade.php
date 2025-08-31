<x-buttons.action-buttons
    :label="$this->actionColumnLabel"
    :show-label="$this->showActionColumnLabel"
    :icon="$this->actionColumnIcon"
    :deleteAction="$this->getDeleteAction($item->id)"
    align="right"
>
    {!! $this->renderBeforeActionView($item) !!}

    @if ($this->getRoutes()['view'] ?? false && $this->getPermissions()['view'] ?? false)
        <x-buttons.action-item
            :href="route($this->getRoutes()['view'] ?? '', $item->id)"
            :icon="$this->viewButtonIcon"
            :label="$this->viewButtonLabel"
        />
    @endif

    {!! $this->renderAfterActionView($item) !!}

    @if (auth()->user()->canBeModified($item) && $this->getRoutes()['edit'] ?? false && $this->getPermissions()['edit'] ?? false)
        <x-buttons.action-item
            :href="route($this->getRoutes()['edit'] ?? '', $item->id)"
            :icon="$this->editButtonIcon"
            :label="$this->editButtonLabel"
        />
    @endif

    {!! $this->renderAfterActionEdit($item) !!}

    @if (auth()->user()->canBeModified($item, $this->getPermissions()['delete'] ?? '') && $this->getRoutes()['delete'] ?? false)
        <div x-data="{ deleteModalOpen: false }">
            <x-buttons.action-item
                type="modal-trigger"
                modal-target="deleteModalOpen"
                :icon="$this->deleteButtonIcon"
                :label="$this->deleteButtonLabel"
                class="text-red-600 dark:text-red-400"
            />

            @if($deleteAction['livewire'] ?? false)
                <x-modals.confirm-delete
                    id="delete-modal-{{ $item->id }}"
                    title="{{ __('Delete :model', ['model' => $this->getModelNameSingular()]) }}"
                    content="{{ __('Are you sure you want to delete this :model?', ['model' => $this->getModelNameSingular()]) }}"
                    :wireClick="'deleteItem(' . $item->id . ')'"
                    modalTrigger="deleteModalOpen"
                    cancelButtonText="{{ __('No, cancel') }}"
                    confirmButtonText="{{ __('Yes, Confirm') }}"
                />
            @else
                <x-modals.confirm-delete
                    id="delete-modal-{{ $item->id }}"
                    title="{{ __('Delete :model', ['model' => $this->getModelNameSingular()]) }}"
                    content="{{ __('Are you sure you want to delete this :model?', ['model' => $this->getModelNameSingular()]) }}"
                    formId="delete-form-{{ $item->id }}"
                    formAction="{{ $deleteAction['url'] ?? route($this->getRoutes()['delete'] ?? '', $item->id) }}"
                    modalTrigger="deleteModalOpen"
                    cancelButtonText="{{ __('No, cancel') }}"
                    confirmButtonText="{{ __('Yes, Confirm') }}"
                />
            @endif
        </div>
    @endif

    {!! $this->renderAfterActionDelete($item) !!}
</x-buttons.action-buttons>
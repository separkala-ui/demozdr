<?php

declare(strict_types=1);

namespace App\Concerns\Datatable;

use Illuminate\Support\Str;

trait HasDatatableDelete
{
    public bool $enableLivewireBulkDelete = true;

    public function getBulkDeleteAction(): array
    {
        if ($this->enableLivewireBulkDelete) {
            return [
                'url' => '', // No need to specify a URL for Livewire bulk delete.
                'method' => 'DELETE',
            ];
        }

        return [
            'url' => route('admin.' . Str::lower($this->getModelNamePlural()) . '.bulk-delete'),
            'method' => 'DELETE',
        ];
    }

    public function bulkDelete()
    {
        $ids = $this->selectedItems;
        if (empty($ids)) {
            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Bulk Delete Failed'),
                'message' => __('No items selected for deletion.'),
            ]);
            return;
        }

        $bulkDeleteAction = $this->getBulkDeleteAction();
        if (!empty($bulkDeleteAction['url'])) {
            // If a bulk delete route is defined, redirect or make an HTTP request (could be AJAX in JS, here just emit event)
            $this->dispatch('bulkDeleteRequest', [
                'url' => $bulkDeleteAction['url'],
                'method' => $bulkDeleteAction['method'],
                'ids' => $ids,
            ]);
            return;
        }

        // Otherwise, handle deletion in component (generic, override for custom logic)
        $deletedCount = $this->handleBulkDelete($ids);
        if ($deletedCount > 0) {
            $this->dispatch('notify', [
                'variant' => 'success',
                'title' => __('Bulk Delete Successful'),
                'message' => __(':count items deleted successfully', ['count' => $deletedCount]),
            ]);
        } else {
            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Bulk Delete Failed'),
                'message' => __('No items were deleted. Selected items may include protected records.'),
            ]);
        }

        $this->selectedItems = [];
        $this->dispatch('resetSelectedItems');
        $this->resetPage();
    }

    /**
     * Default bulk delete handler. Override in child for custom logic.
     */
    protected function handleBulkDelete(array $ids): int
    {
        $modelClass = $this->getModelClass();
        $items = $modelClass::whereIn('id', $ids)->get();
        $deletedCount = 0;
        foreach ($items as $item) {
            $item->delete();
            $deletedCount++;
        }
        return $deletedCount;
    }
}

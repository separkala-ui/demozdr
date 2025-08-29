<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use Illuminate\Contracts\Support\Renderable;
use Livewire\Component;
use Livewire\WithPagination;

abstract class Datatable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $searchbarPlaceholder = '';
    public string $newResourceLinkPermission = '';
    public string $newResourceLinkRouteName = '';
    public string $newResourceLinkLabel = '';
    public string $sort = '';
    public string $direction = 'asc';
    public int $page = 1;
    public int|string $perPage = 10;
    public array $perPageOptions = [];
    public array $filters = [];

    public array $queryString = [
        'search' => ['except' => ''],
        'role' => ['except' => ''],
        'sort' => ['except' => 'first_name'],
        'direction' => ['except' => 'asc'],
        'page' => ['except' => 1],
        'perPage' => ['except' => 10],
    ];
    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public array $table = [];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sort === $field) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $field;
            $this->direction = 'asc';
        }
    }

    public function placeholder(): Renderable
    {
        return view('components.datatable.skeleton');
    }

    public function mount(): void
    {
        $this->searchbarPlaceholder = $this->getSearchbarPlaceholder();
        $this->filters = $this->getFilters();
        $this->table = $this->getTable();
        $this->newResourceLinkPermission = $this->getNewResourceLinkPermission();
        $this->newResourceLinkRouteName = $this->getNewResourceLinkRouteName();
        $this->newResourceLinkLabel = $this->getNewResourceLinkLabel();
        $this->perPageOptions = $this->getPerPageOptions();
    }

    protected function getPerPageOptions(): array
    {
        return [10, 20, 50, 100, __('All')];
    }

    protected function getSearchbarPlaceholder(): string
    {
        return __('Search...');
    }

    protected function getNewResourceLinkPermission(): string
    {
        return '';
    }

    protected function getNewResourceLinkRouteName(): string
    {
        return '';
    }

    protected function getNewResourceLinkLabel(): string
    {
        return __('New');
    }

    protected function getFilters(): array
    {
        return [];
    }

    protected function getTable(): array
    {
        return [];
    }

    public function render(): Renderable
    {
        $this->table = $this->getTable();
        return view('components.datatable', [
            'table' => $this->table,
            'data' => $this->data,
            'perPage' => $this->perPage,
            'perPageOptions' => $this->perPageOptions,
        ]);
    }

    public function renderCreatedAtCell($model): string
    {
        if (!array_key_exists('created_at', $model->getAttributes())) {
            return '';
        }

        return $model->created_at->format('Y-m-d H:i:s');
    }

    public function renderUpdatedAtCell($model): string
    {
        if (!array_key_exists('updated_at', $model->getAttributes())) {
            return '';
        }

        return $model->updated_at->format('Y-m-d H:i:s');
    }
}

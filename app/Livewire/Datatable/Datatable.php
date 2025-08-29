<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Str;

abstract class Datatable extends Component
{
    use WithPagination;

    public string $model = '';
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
    public array $permissions = [];

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

    public function sortBy(string $field = '')
    {
        if ($this->sort === $field) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $field;
            $this->direction = 'asc';
        }
    }

    public function sortQuery(QueryBuilder $query): QueryBuilder
    {
        if ($this->sort) {
            return $query->orderBy($this->sort, $this->direction);
        }

        return $query;
    }

    public function placeholder(): Renderable
    {
        return view('components.datatable.skeleton');
    }

    public function mount(): void
    {
        $this->searchbarPlaceholder = $this->getSearchbarPlaceholder();
        $this->filters = $this->getFilters();
        $this->newResourceLinkPermission = $this->getNewResourceLinkPermission();
        $this->newResourceLinkRouteName = $this->getNewResourceLinkRouteName();
        $this->newResourceLinkLabel = $this->getNewResourceLinkLabel();
        $this->perPageOptions = $this->getPerPageOptions();
        $this->table = $this->getTable();
    }

    protected function getModelClass(): string
    {
        return $this->model;
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
        return $this->getPermissions()['create'] ?? '';
    }

    protected function getNewResourceLinkRouteName(): string
    {
        return $this->getRoutes()['create'] ?? '';
    }

    protected function getNewResourceLinkLabel(): string
    {
        return __('New :model', ['model' => $this->getModelNameSingular()]);
    }

    protected function getFilters(): array
    {
        return [];
    }

    protected function getSnakeCaseModel(): string
    {
        return Str::snake(class_basename($this->getModelClass()));
    }

    public function getModelNameSingular(): string
    {
        $class = class_basename($this->getModelClass());

        // Insert spaces before capital letters (except the first)
        return trim(preg_replace('/(?<!^)([A-Z])/', ' $1', $class));
    }

    protected function getModelNamePlural(): string
    {
        return str($this->getModelNameSingular())->plural()->toString();
    }

    protected function getPermissions(): array
    {
        $snakeCaseModel = $this->getSnakeCaseModel();

        return [
            'view' => $snakeCaseModel . '.view',
            'create' => $snakeCaseModel . '.create',
            'edit' => $snakeCaseModel . '.edit',
            'delete' => $snakeCaseModel . '.delete',
        ];
    }

    public function getRoutes(): array
    {
        return [
            'create' => 'admin.' . Str::lower($this->getModelNamePlural()) . '.create',
            'view' => 'admin.' . Str::lower($this->getModelNamePlural()) . '.view',
            'edit' => 'admin.' . Str::lower($this->getModelNamePlural()) . '.edit',
            'delete' => 'admin.' . Str::lower($this->getModelNamePlural()) . '.destroy',
        ];
    }

    public function getBulkDeleteAction(): array
    {
        return [
            'url' => route('admin.' . Str::lower($this->getModelNamePlural()) . '.bulk-delete'),
            'method' => 'DELETE',
        ];
    }

    protected function getTable(): array
    {
        return [];
    }

    protected function getData(): LengthAwarePaginator
    {
        return $this->buildQuery()
            ->paginate($this->perPage == __('All') ? 999999 : $this->perPage);
    }

    protected function buildQuery(): QueryBuilder
    {
        if (empty($this->getModelClass())) {
            throw new \Exception('Model class is not defined in the datatable component.');
        }

        $query = QueryBuilder::for($this->getModelClass());

        if ($this->search) {
            $query->where(function ($q) {
                foreach ($this->table['headers'] as $header) {
                    if (isset($header['searchable']) && $header['searchable'] === true) {
                        $q->orWhere($header['sortBy'] ?? $header['id'], 'like', '%' . $this->search . '%');
                    }
                }
            });
        }

        foreach ($this->filters as $filter) {
            if (! empty($filter['selected'])) {
                $query->where($filter['id'], $filter['selected']);
            }
        }

        if ($this->sort) {
            $query->orderBy($this->sort, $this->direction);
        }

        return $query;
    }

    public function render(): Renderable
    {
        $this->table = $this->getTable();

        return view('backend.livewire.datatable.datatable', [
            'table' => $this->table,
            'data' => $this->getData(),
            'perPage' => $this->perPage,
            'perPageOptions' => $this->perPageOptions,
        ]);
    }

    public function renderIdCell($item): string
    {
        return array_key_exists('id', $item->getAttributes()) ? (string) $item->id : '';
    }

    public function renderCreatedAtCell($item): string
    {
        return array_key_exists('created_at', $item->getAttributes()) ? $item->created_at->format('Y-m-d H:i:s') : '';
    }

    public function renderUpdatedAtCell($item): string
    {
        return array_key_exists('updated_at', $item->getAttributes()) ? $item->updated_at->format('Y-m-d H:i:s') : '';
    }

    public function getActionCellPermissions($item): array
    {
        return [
            'edit' => Auth::user()->canBeModified($item, $this->getPermissions()['edit'] ?? ''),
            'delete' => Auth::user()->canBeModified($item, $this->getPermissions()['delete'] ?? ''),
        ];
    }

    public function showActionItems($item): bool
    {
        $permissions = $this->getActionCellPermissions($item);
        $permissionsCheck = false;

        // Add Or condition permission check.
        foreach ($permissions as $key => $value) {
            if ($value) {
                $permissionsCheck = true;
                break;
            }
        }
        return $permissionsCheck;
    }

    public function renderActionsCell($item): string|Renderable
    {
        if ($this->showActionItems($item) === false) {
            return '';
        }

        return view('backend.livewire.datatable.action-buttons', [
            'item' => $item,
            'permissions' => $this->getActionCellPermissions($item),
        ]);
    }

    public function renderAfterActionEdit($item): string|Renderable
    {
        return '';
    }

    public function renderAfterActionDelete($item): string|Renderable
    {
        return '';
    }

    public function renderAfterActionView($item): string|Renderable
    {
        return '';
    }
}

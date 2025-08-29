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
    public string $sort = 'created_at';
    public string $direction = 'desc';
    public bool $enableLivewireBulkDelete = true;
    public int $page = 1;
    public int|string $perPage = 10;
    public array $perPageOptions = [];
    public array $filters = [];
    public $customFilters = null;
    public array $permissions = [];
    public array $selectedItems = [];
    public array $disabledRoutes = [];
    public array $queryString = [
        'search' => ['except' => ''],
        'sort' => ['except' => 'created_at'],
        'direction' => ['except' => 'asc'],
        'page' => ['except' => 1],
        'perPage' => ['except' => 10],
    ];

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
        if (! empty($bulkDeleteAction['url'])) {
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

        $this->selectedItems = array_values([]);
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
        $this->perPage = request()->per_page ?? config('settings.default_pagination', 10);
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
        $routes = [
            'create' => 'admin.' . Str::lower($this->getModelNamePlural()) . '.create',
            'view' => 'admin.' . Str::lower($this->getModelNamePlural()) . '.view',
            'edit' => 'admin.' . Str::lower($this->getModelNamePlural()) . '.edit',
            'delete' => 'admin.' . Str::lower($this->getModelNamePlural()) . '.destroy',
        ];

        // Exclude the disabled routes.
        if (! empty($this->disabledRoutes)) {
            foreach ($this->disabledRoutes as $disabledRoute) {
                unset($routes[$disabledRoute]);
            }
        }

        return $routes;
    }

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

    public function renderCreatedAtColumn($item): string
    {
        if (! array_key_exists('created_at', $item->getAttributes()) || ! $item->created_at) {
            return '';
        }
        $short = $item->created_at->format('d M Y');
        $full = $item->created_at->format('Y-m-d H:i:s');
        return '<span class="text-sm" title="' . e($full) . '">' . e($short) . '</span>';
    }

    public function renderUpdatedAtColumn($item): string
    {
        if (! array_key_exists('updated_at', $item->getAttributes()) || ! $item->updated_at) {
            return '';
        }
        $short = $item->updated_at->format('d M Y');
        $full = $item->updated_at->format('Y-m-d H:i:s');
        return '<span class="text-sm" title="' . e($full) . '">' . e($short) . '</span>';
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

    public function renderActionsColumn($item): string|Renderable
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

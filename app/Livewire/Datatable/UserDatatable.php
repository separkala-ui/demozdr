<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use App\Services\RolesService;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;

class UserDatatable extends Datatable
{
    public string $role = '';
    public string $model = User::class;

    public function getSearchbarPlaceholder(): string
    {
        return __('Search by name or email');
    }

    public function getRoutes(): array
    {
        $routes = parent::getRoutes();
        unset($routes['view']);
        return $routes;
    }

    public function mount(): void
    {
        parent::mount();


        $this->queryString = array_merge($this->queryString, [
            'role' => ['except' => ''],
        ]);
    }

    public function getFilters(): array
    {
        return [
            [
                'id' => 'role',
                'label' => __('Role'),
                'filterLabel' => __('Filter by Role'),
                'icon' => 'feather:key',
                'allLabel' => __('All Roles'),
                'options' => app(RolesService::class)->getRolesDropdown(),
                'selected' => $this->role,
            ],
        ];
    }

    public function getTable(): array
    {
        return [
            'enableCheckbox' => true,
            'enablePagination' => true,
            'noResultsMessage' => __('No users found.'),
            'headers' => [
                [
                    'id' => 'name',
                    'title' => __('Name'),
                    'width' => null,
                    'sortable' => true,
                    'sortBy' => 'first_name',
                ],
                [
                    'id' => 'email',
                    'title' => __('Email'),
                    'width' => null,
                    'sortable' => true,
                    'sortBy' => 'email',
                ],
                [
                    'id' => 'roles',
                    'title' => __('Roles'),
                    'width' => null,
                    'sortable' => false,
                ],
                [
                    'id' => 'created_at',
                    'title' => __('Created At'),
                    'width' => null,
                    'sortable' => true,
                    'sortBy' => 'created_at',
                ],
                [
                    'id' => 'actions',
                    'title' => __('Actions'),
                    'width' => null,
                    'sortable' => false,
                    'is_action' => true,
                ],
            ],
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        $query = QueryBuilder::for($this->model);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        if ($this->role) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', $this->role);
            });
        }

        $query->with('roles');

        if ($this->sort === '') {
            $this->sort = 'first_name';
            $this->direction = 'asc';
        }

        return $this->sortQuery($query);
    }

    public function renderNameCell($user): Renderable
    {
        return view('backend.pages.users.partials.user-name', compact('user'));
    }

    public function renderRolesCell($user): Renderable
    {
        return view('backend.pages.users.partials.user-roles', compact('user'));
    }

    public function getActionCellPermissions($item): array
    {
        return [
            ...parent::getActionCellPermissions($item),
            'user.login_as' => Auth::user()->canBeModified($item, $this->getPermissions()['login_as'] ?? ''),
        ];
    }

    public function renderAfterActionEdit($user): string|Renderable
    {
        if (! Auth::user()->can('user.login_as') || $user->id === Auth::id()) {
            return '';
        }

        return view('backend.pages.users.partials.action-button-login-as', compact('user'));
    }

    // protected function handleBulkDelete(array $ids): int
    // {
    //     $ids = array_filter($ids, fn ($id) => $id != Auth::id()); // Prevent self-deletion.
    //     $users = User::whereIn('id', $ids)->get();
    //     $deletedCount = 0;
    //     foreach ($users as $user) {
    //         if ($user->hasRole('superadmin')) {
    //             continue;
    //         }
    //         $user->delete();
    //         $deletedCount++;
    //     }

    //     return $deletedCount;
    // }
}

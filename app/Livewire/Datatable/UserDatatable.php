<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use App\Livewire\Datatable\Datatable;
use App\Services\RolesService;
use App\Models\User as UserModel;
use Illuminate\Contracts\Support\Renderable;

class UserDatatable extends Datatable
{
    public string $role = '';

    public function getSearchbarPlaceholder(): string
    {
        return __('Search by name or email');
    }

    protected function getNewResourceLinkPermission(): string
    {
        return 'user.create';
    }

    protected function getNewResourceLinkRouteName(): string
    {
        return 'admin.users.create';
    }

    protected function getNewResourceLinkLabel(): string
    {
        return __('New User');
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
                    'renderContentView' => 'renderNameCell',
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
                    'renderContentView' => 'renderRolesCell',
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
                    'renderContentView' => 'renderActionsCell',
                ],
            ],
        ];
    }

    public function render(): Renderable
    {
        $query = UserModel::query();

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

        $users = $query->orderBy($this->sort, $this->direction)
            ->paginate($this->perPage == __('All') ? 999999 : $this->perPage);

        return view('backend.livewire.datatable.datatable', [
            'data' => $users,
            'table' => $this->table,
        ]);
    }

    public function updatingRole()
    {
        $this->resetPage();
    }

    public function renderActionsCell($user, $header): Renderable
    {
        return view('backend.pages.users.partials.action-buttons', compact('user'));
    }

    public function renderNameCell($user, $header): Renderable
    {
        return view('backend.pages.users.partials.user-name', compact('user'));
    }

    public function renderRolesCell($user, $header): Renderable
    {
        return view('backend.pages.users.partials.user-roles', compact('user'));
    }
}

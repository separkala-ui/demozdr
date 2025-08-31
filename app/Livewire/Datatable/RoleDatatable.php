<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use App\Models\Role;
use App\Services\RolesService;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;

class RoleDatatable extends Datatable
{
    public string $model = Role::class;
    public array $disabledRoutes = ['view'];

    public function getSearchbarPlaceholder(): string
    {
        return __('Search by role name...');
    }

    protected function getHeaders(): array
    {
        return [
            [
                'id' => 'name',
                'title' => __('Name'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'name',
            ],
            [
                'id' => 'users',
                'title' => __('Users'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'users_count',
            ],
            // [
            //     'id' => 'roles',
            //     'title' => __('Roles'),
            //     'width' => null,
            //     'sortable' => false,
            // ],
            // [
            //     'id' => 'created_at',
            //     'title' => __('Created At'),
            //     'width' => null,
            //     'sortable' => true,
            //     'sortBy' => 'created_at',
            // ],
            [
                'id' => 'actions',
                'title' => __('Actions'),
                'width' => null,
                'sortable' => false,
                'is_action' => true,
            ],
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        $query = QueryBuilder::for($this->model);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%");
            });
        }

        $query->withCount('users');

        return $this->sortQuery($query);
    }

    public function renderUsersColumn(Role $role): string
    {
        $url = route('admin.users.index', ['role' => $role->name]);
        return '<a title="' . __('View Users') . '" href="' . $url . '" class="text-primary hover:underline">' . $role->users_count . '</a>';
    }

    protected function handleBulkDelete(array $ids): int
    {
        $ids = array_filter($ids, fn ($id) => $id != Auth::id()); // Prevent self-deletion.
        $users = User::whereIn('id', $ids)->get();
        $deletedCount = 0;
        foreach ($users as $user) {
            if ($user->hasRole(Role::SUPERADMIN) || $user->id === Auth::id()) {
                continue;
            }

            $this->authorize('delete', $user);

            $user->delete();
            $deletedCount++;
        }

        return $deletedCount;
    }

    public function handleRowDelete(Model|User $user): bool
    {
        // Prevent Superadmin deletion.
        if ($user->hasRole(Role::SUPERADMIN)) {
            throw new \Exception(__('You cannot delete a :role account.', ['role' => Role::SUPERADMIN]));
        }

        // Prevent own account deletion.
        if (Auth::id() === $user->id) {
            throw new \Exception(__('You cannot delete your own account.'));
        }

        $this->authorize('delete', $user);

        return $user->delete();
    }
}

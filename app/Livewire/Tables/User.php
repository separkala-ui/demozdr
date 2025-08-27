<?php

declare(strict_types=1);

namespace App\Livewire\Tables;


use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User as UserModel;

class User extends Component
{
    use WithPagination;

    public $search = '';
    public $role = '';
    public $sort = 'first_name';
    public $direction = 'asc';
    public $page = 1;

    protected $queryString = [
        'search' => ['except' => ''],
        'role' => ['except' => ''],
        'sort' => ['except' => 'first_name'],
        'direction' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRole()
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

    public function placeholder()
    {
        return view('backend.livewire.tables.user-skeleton');
    }

    public function render()
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

        $users = $query->orderBy($this->sort, $this->direction)->paginate(10);

        $roles = \Spatie\Permission\Models\Role::pluck('name', 'id');

        $breadcrumbs = [
            'title' => __('Users'),
        ];

        return view('backend.livewire.tables.user', [
            'users' => $users,
            'roles' => $roles,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}

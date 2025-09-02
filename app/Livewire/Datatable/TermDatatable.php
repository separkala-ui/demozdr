<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use App\Models\Term;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;

class TermDatatable extends Datatable
{
    public string $taxonomy;
    public string $model = Term::class;
    public array $disabledRoutes = ['view', 'edit'];

    public function getSearchbarPlaceholder(): string
    {
        return __('Search by :taxonomy name...', ['taxonomy' => $this->taxonomy]);
    }

    protected function getNoResultsMessage(): string
    {
        return __('No :items found.', ['items' => ucfirst($this->taxonomy)]);
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
                'id' => 'parent',
                'title' => __('Parent'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'parent_id',
            ],
            [
                'id' => 'posts_count',
                'title' => __('Posts'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'posts_count',
            ],
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
        $query = QueryBuilder::for($this->model)
            ->where('taxonomy', $this->taxonomy)
            ->with('parent')
            ->withCount('posts');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%");
            });
        }

        return $this->sortQuery($query);
    }

    public function renderParentColumn($term): string
    {
        return $term->parent->name ?? '-';
    }

    public function renderNameColumn($term): string
    {
        return "<a class='text-primary hover:underline'  href=\"".route('admin.terms.edit', [$this->taxonomy, $term->id])."\">{$term->name}</a>";
    }

    public function renderAfterActionEdit($term): string
    {
        if (! Auth::user()->can('term.edit')) {
            return '';
        }
        $route = route('admin.terms.edit', [$this->taxonomy, $term->id]);

        return "<a
                href=\"{$route}\"
                class='flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700'
                role='menuitem'
            >
                <iconify-icon icon='mdi:pencil' class='text-base'></iconify-icon>
                ". __('Edit') ."
            </a>";
    }
}

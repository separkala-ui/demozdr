<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use App\Models\Post;
use App\Services\Content\PostType;
use Illuminate\Contracts\Support\Renderable;
use Spatie\QueryBuilder\QueryBuilder;

class PostDatatable extends Datatable
{
    public string $status = '';
    public string $postType = PostType::POST;
    public array $queryString = [
        ...parent::QUERY_STRING_DEFAULTS,
        'status' => [],
    ];
    public string $model = Post::class;

    public function getSearchbarPlaceholder(): string
    {
        return __('Search by title or content...');
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function getFilters(): array
    {
        return [
            [
                'id' => 'status',
                'label' => __('Status'),
                'filterLabel' => __('Filter by Status'),
                'icon' => 'lucide:sliders',
                'allLabel' => __('All Statuses'),
                'options' => Post::getPostStatuses(),
                'selected' => $this->status,
            ],
        ];
    }

    protected function getRouteParameters(): array
    {
        return ['postType' => $this->postType];
    }

    protected function getItemRouteParameters($item): array
    {
        return [
            'postType' => $this->postType,
            'post' => $item->id,
        ];
    }

    protected function getHeaders(): array
    {
        return [
            [
                'id' => 'title',
                'title' => __('Title'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'title',
            ],
            [
                'id' => 'author',
                'title' => __('Author'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'author',
            ],
            [
                'id' => 'status',
                'title' => __('Status'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'status',
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
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        $query = QueryBuilder::for($this->model)
            ->with('author')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")
                        ->orWhere('excerpt', 'like', "%{$this->search}%")
                        ->orWhere('content', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            });

        return $this->sortQuery($query);
    }

    public function renderStatusColumn(Post $post): string|Renderable
    {
        return "<span class='badge'>" . ucfirst($post->status) . "</span>";
    }

    public function renderAuthorColumn(Post $post): string|Renderable
    {
        return "<span class='badge'>" . ucfirst($post->author->full_name ?? '') . "</span>";
    }
}

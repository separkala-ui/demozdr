<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use App\Models\ActionLog;
use Illuminate\Contracts\View\View;
use Spatie\QueryBuilder\QueryBuilder;

class ActionLogDatatable extends Datatable
{
    public bool $enableCheckbox = false;
    public string $model = ActionLog::class;
    public array $disabledRoutes = ['edit', 'delete'];

    public function getSearchbarPlaceholder(): string
    {
        return __('Search by action log message...');
    }

    protected function getPermissions(): array
    {
        return [
            'view' => 'role.view',
        ];
    }

    protected function getHeaders(): array
    {
        return [
            [
                'id' => 'type',
                'title' => __('Type'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'type',
            ],
            [
                'id' => 'title',
                'title' => __('Title'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'title',
            ],
            [
                'id' => 'action_by',
                'title' => __('Action By'),
                'width' => null,
                'sortable' => false,
                'sortBy' => 'action_by',
            ],
            [
                'id' => 'data',
                'title' => __('Data'),
                'width' => null,
                'sortable' => false,
                'sortBy' => 'data',
            ],
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        $query = QueryBuilder::for($this->model)
            ->with('user');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('type', 'like', "%{$this->search}%");
            });
        }

        return $this->sortQuery($query);
    }

    public function renderTypeColumn(ActionLog $log): string
    {
        return "<span class='badge'>" . ucfirst($log->type) . "</span>";
    }

    public function renderActionByColumn(ActionLog $log): string
    {
        return "<span class='badge'>" . ucfirst($log->user->full_name ?? '') . "</span>";
    }

    public function renderDataColumn(ActionLog $log): View
    {
        return view('backend.pages.action-logs.partials.detail-info', ['log' => $log]);
    }
}

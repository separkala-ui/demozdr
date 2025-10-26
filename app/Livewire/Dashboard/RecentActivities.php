<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Models\ActionLog;
use Livewire\Component;

class RecentActivities extends Component
{
    public $activities = [];

    public int $limit = 10;

    protected $listeners = [
        'refreshActivities' => '$refresh',
    ];

    public function mount(): void
    {
        $this->loadActivities();
    }

    public function loadActivities(): void
    {
        $this->activities = ActionLog::with('user')
            ->latest()
            ->limit($this->limit)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'type' => $log->type,
                    'title' => $log->title,
                    'user_name' => $log->user->name ?? __('سیستم'),
                    'user_avatar' => $log->user->avatar ?? null,
                    'time' => $log->created_at->diffForHumans(),
                    'time_full' => verta($log->created_at)->format('Y/m/d H:i'),
                    'icon' => $this->getIconForType($log->type),
                    'color' => $this->getColorForType($log->type),
                ];
            })
            ->toArray();
    }

    protected function getIconForType(string $type): string
    {
        return match ($type) {
            'login' => 'lucide:log-in',
            'logout' => 'lucide:log-out',
            'create' => 'lucide:plus-circle',
            'update' => 'lucide:edit',
            'delete' => 'lucide:trash-2',
            'approve' => 'lucide:check-circle',
            'reject' => 'lucide:x-circle',
            'charge' => 'lucide:trending-up',
            'expense' => 'lucide:trending-down',
            'settlement' => 'lucide:file-check',
            'backup' => 'lucide:archive',
            'restore' => 'lucide:archive-restore',
            default => 'lucide:activity',
        };
    }

    protected function getColorForType(string $type): string
    {
        return match ($type) {
            'login' => 'blue',
            'logout' => 'slate',
            'create' => 'green',
            'update' => 'amber',
            'delete' => 'red',
            'approve' => 'emerald',
            'reject' => 'rose',
            'charge' => 'cyan',
            'expense' => 'orange',
            'settlement' => 'purple',
            'backup' => 'indigo',
            'restore' => 'teal',
            default => 'slate',
        };
    }

    public function render()
    {
        return view('livewire.dashboard.recent-activities');
    }
}


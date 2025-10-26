<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;

class ToastNotification extends Component
{
    public array $notifications = [];

    protected $listeners = [
        'showToast' => 'addNotification',
    ];

    public function mount(): void
    {
        // Check for session flash messages
        $this->checkSessionFlash();
    }

    public function addNotification(string $message, string $type = 'success', int $duration = 5000): void
    {
        $id = uniqid('toast_', true);
        
        $this->notifications[] = [
            'id' => $id,
            'message' => $message,
            'type' => $type,
            'duration' => $duration,
            'timestamp' => now()->timestamp,
        ];

        // Auto-remove after duration
        $this->dispatch('toast-added', id: $id, duration: $duration);
    }

    public function removeNotification(string $id): void
    {
        $this->notifications = array_filter(
            $this->notifications,
            fn ($notification) => $notification['id'] !== $id
        );
    }

    protected function checkSessionFlash(): void
    {
        // Check for toast flash message
        if (session()->has('toast')) {
            $toast = session('toast');
            $this->addNotification(
                $toast['message'] ?? '',
                $toast['type'] ?? 'success',
                $toast['duration'] ?? 5000
            );
        }

        // Check for standard success/error messages
        if (session()->has('success')) {
            $this->addNotification(session('success'), 'success');
        }

        if (session()->has('error')) {
            $this->addNotification(session('error'), 'error');
        }

        if (session()->has('warning')) {
            $this->addNotification(session('warning'), 'warning');
        }

        if (session()->has('info')) {
            $this->addNotification(session('info'), 'info');
        }
    }

    public function render()
    {
        return view('livewire.toast-notification');
    }
}


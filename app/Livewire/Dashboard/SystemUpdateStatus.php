<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Jobs\RunSystemUpdate;
use App\Models\SystemUpdateLog;
use App\Services\Update\UpdateManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SystemUpdateStatus extends Component
{
    public array $status = [];
    public ?SystemUpdateLog $lastLog = null;
    public bool $isChecking = false;
    public bool $isUpdating = false;
    public ?string $lastCheckedAtLabel = null;

    public function mount(UpdateManager $manager): void
    {
        $this->authorizeAccess();
        $this->loadStatus($manager);
    }

    public function refreshStatus(UpdateManager $manager): void
    {
        $this->authorizeAccess();
        $this->isChecking = true;
        $this->loadStatus($manager, true);
        $this->isChecking = false;
        $this->dispatch('showToast', message: __('وضعیت به‌روزرسانی با موفقیت بروزرسانی شد'), type: 'success');
    }

    public function startUpdate(UpdateManager $manager): void
    {
        $this->authorizeAccess();

        if ($this->isUpdating) {
            $this->dispatch('showToast', message: __('یک فرآیند به‌روزرسانی در حال اجرا است'), type: 'warning');
            return;
        }

        $status = $manager->getUpdateStatus();

        if (! Arr::get($status, 'has_update')) {
            $this->dispatch('showToast', message: __('نسخه جدیدی برای به‌روزرسانی موجود نیست'), type: 'info');
            return;
        }

        $latest = Arr::get($status, 'latest_details');

        if (! $latest || empty($latest['version'])) {
            $this->dispatch('showToast', message: __('امکان شناسایی نسخه جدید وجود ندارد'), type: 'error');
            return;
        }

        $log = $manager->createLog((string) $latest['version'], Auth::id());
        RunSystemUpdate::dispatch($log->id);

        $this->isUpdating = true;
        $this->lastLog = $log;

        $this->dispatch('showToast', message: __('فرآیند به‌روزرسانی آغاز شد. لطفاً صبر کنید.'), type: 'info');
    }

    public function poll(UpdateManager $manager): void
    {
        $this->authorizeAccess();
        $this->loadStatus($manager, false);
    }

    public function render()
    {
        return view('livewire.dashboard.system-update-status');
    }

    protected function loadStatus(UpdateManager $manager, bool $force = false): void
    {
        $this->status = $manager->getUpdateStatus($force);
        $this->lastLog = SystemUpdateLog::latest()->first();
        $this->isUpdating = in_array(optional($this->lastLog)->status, ['queued', 'running'], true);

        $lastCheck = get_setting(UpdateManager::LAST_CHECK_AT_KEY);
        $this->lastCheckedAtLabel = $lastCheck ? Carbon::parse($lastCheck)->format('Y-m-d H:i') : null;
    }

    protected function authorizeAccess(): void
    {
        if (! Auth::check() || ! Auth::user()->hasRole('Superadmin')) {
            abort(403, 'Unauthorized');
        }
    }

    public function translateStatus(?string $status): string
    {
        return match ($status) {
            'queued' => 'در صف',
            'running' => 'در حال اجرا',
            'success' => 'موفق',
            'failed' => 'ناموفق',
            default => 'نامشخص',
        };
    }
}

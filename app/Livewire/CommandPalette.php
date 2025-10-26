<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Route;

class CommandPalette extends Component
{
    public bool $open = false;

    public string $search = '';

    public array $results = [];

    public int $selectedIndex = 0;

    protected $listeners = [
        'openCommandPalette' => 'open',
    ];

    public function mount(): void
    {
        $this->loadResults();
    }

    public function open(): void
    {
        $this->open = true;
        $this->search = '';
        $this->selectedIndex = 0;
        $this->loadResults();
    }

    public function close(): void
    {
        $this->open = false;
        $this->search = '';
        $this->selectedIndex = 0;
    }

    public function updatedSearch(): void
    {
        $this->selectedIndex = 0;
        $this->loadResults();
    }

    public function selectNext(): void
    {
        if ($this->selectedIndex < count($this->results) - 1) {
            $this->selectedIndex++;
        }
    }

    public function selectPrevious(): void
    {
        if ($this->selectedIndex > 0) {
            $this->selectedIndex--;
        }
    }

    public function executeSelected(): void
    {
        if (isset($this->results[$this->selectedIndex])) {
            $selected = $this->results[$this->selectedIndex];
            $this->close();
            $this->redirect($selected['url']);
        }
    }

    protected function loadResults(): void
    {
        $search = strtolower($this->search);
        $user = auth()->user();
        
        $commands = [];

        // صفحات اصلی
        if ($user->can('user-view') && $this->matches($search, 'کاربر|user')) {
            $commands[] = [
                'title' => __('مدیریت کاربران'),
                'description' => __('مشاهده و مدیریت کاربران سیستم'),
                'icon' => 'lucide:users',
                'url' => route('admin.users.index'),
                'category' => __('مدیریت'),
                'shortcut' => null,
            ];
        }

        if ($user->can('user-create') && $this->matches($search, 'کاربر جدید|new user|add user')) {
            $commands[] = [
                'title' => __('افزودن کاربر جدید'),
                'description' => __('ایجاد کاربر جدید در سیستم'),
                'icon' => 'lucide:user-plus',
                'url' => route('admin.users.create'),
                'category' => __('مدیریت'),
                'shortcut' => null,
            ];
        }

        if ($user->can('role-view') && $this->matches($search, 'نقش|role')) {
            $commands[] = [
                'title' => __('مدیریت نقش‌ها'),
                'description' => __('مشاهده و مدیریت نقش‌های کاربری'),
                'icon' => 'lucide:shield',
                'url' => route('admin.roles.index'),
                'category' => __('مدیریت'),
                'shortcut' => null,
            ];
        }

        if ($user->can('permission-view') && $this->matches($search, 'دسترسی|permission')) {
            $commands[] = [
                'title' => __('مدیریت دسترسی‌ها'),
                'description' => __('تنظیم دسترسی‌های سیستم'),
                'icon' => 'lucide:key',
                'url' => route('admin.permissions.index'),
                'category' => __('مدیریت'),
                'shortcut' => null,
            ];
        }

        // تنخواه
        if (($user->can('petty-cash-view') || $user->hasRole(['Admin', 'Superadmin'])) && $this->matches($search, 'تنخواه|petty|cash')) {
            $commands[] = [
                'title' => __('داشبورد تنخواه'),
                'description' => __('مدیریت تنخواه گردان شعب'),
                'icon' => 'lucide:wallet',
                'url' => route('admin.petty-cash.index'),
                'category' => __('مالی'),
                'shortcut' => 'P',
            ];
        }

        if (($user->can('petty-cash-create') || $user->hasRole(['Admin', 'Superadmin'])) && $this->matches($search, 'شعبه جدید|تنخواه جدید|branch|ledger')) {
            $commands[] = [
                'title' => __('ایجاد شعبه تنخواه'),
                'description' => __('افزودن شعبه جدید به سیستم تنخواه'),
                'icon' => 'lucide:plus-circle',
                'url' => route('admin.petty-cash.create'),
                'category' => __('مالی'),
                'shortcut' => null,
            ];
        }

        // مدیا
        if ($user->can('media-view') && $this->matches($search, 'مدیا|رسانه|media')) {
            $commands[] = [
                'title' => __('کتابخانه رسانه'),
                'description' => __('مدیریت فایل‌ها و تصاویر'),
                'icon' => 'lucide:image',
                'url' => route('admin.media.index'),
                'category' => __('محتوا'),
                'shortcut' => 'M',
            ];
        }

        // تنظیمات
        if ($user->can('setting-view') && $this->matches($search, 'تنظیمات|setting')) {
            $commands[] = [
                'title' => __('تنظیمات سیستم'),
                'description' => __('پیکربندی سیستم'),
                'icon' => 'lucide:settings',
                'url' => route('admin.settings.index'),
                'category' => __('سیستم'),
                'shortcut' => ',',
            ];
        }

        // گزارشات
        if ($user->can('action-log-view') && $this->matches($search, 'گزارش|لاگ|log|report')) {
            $commands[] = [
                'title' => __('گزارش فعالیت‌ها'),
                'description' => __('مشاهده لاگ عملیات'),
                'icon' => 'lucide:file-text',
                'url' => route('admin.action-logs.index'),
                'category' => __('گزارش'),
                'shortcut' => null,
            ];
        }

        // ابزارها
        if ($user->hasRole(['Admin', 'Superadmin'])) {
            if ($this->matches($search, 'telescope')) {
                $commands[] = [
                    'title' => 'Laravel Telescope',
                    'description' => __('مانیتورینگ و دیباگ'),
                    'icon' => 'lucide:telescope',
                    'url' => '/telescope',
                    'category' => __('ابزار'),
                    'shortcut' => null,
                ];
            }

            if ($this->matches($search, 'pulse')) {
                $commands[] = [
                    'title' => 'Laravel Pulse',
                    'description' => __('وضعیت سرور و Performance'),
                    'icon' => 'lucide:activity',
                    'url' => '/pulse',
                    'category' => __('ابزار'),
                    'shortcut' => null,
                ];
            }

            if ($this->matches($search, 'کش|cache|clear')) {
                $commands[] = [
                    'title' => __('پاک‌سازی کش'),
                    'description' => __('Clear Cache سیستم'),
                    'icon' => 'lucide:trash-2',
                    'url' => '#',
                    'category' => __('ابزار'),
                    'shortcut' => null,
                    'action' => 'clearCache',
                ];
            }
        }

        // اگر جستجو خالی است، فقط موارد پرکاربرد را نشان بده
        if (empty($search)) {
            $commands = array_slice($commands, 0, 8);
        }

        $this->results = $commands;
    }

    protected function matches(string $search, string $keywords): bool
    {
        if (empty($search)) {
            return true;
        }

        $keywords = explode('|', strtolower($keywords));
        
        foreach ($keywords as $keyword) {
            if (str_contains($keyword, $search)) {
                return true;
            }
        }

        return false;
    }

    public function clearCache(): void
    {
        try {
            \Artisan::call('optimize:clear');
            $this->dispatch('showToast', 
                message: __('کش با موفقیت پاک شد'),
                type: 'success'
            );
            $this->close();
        } catch (\Exception $e) {
            $this->dispatch('showToast',
                message: __('خطا در پاکسازی کش'),
                type: 'error'
            );
        }
    }

    public function render()
    {
        return view('livewire.command-palette');
    }
}


<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Route;

class QuickActions extends Component
{
    public array $actions = [];

    public function mount(): void
    {
        $this->loadActions();
    }

    protected function loadActions(): void
    {
        $this->actions = [];

        // User Management
        if (auth()->user()->can('user-create')) {
            $this->actions[] = [
                'label' => __('کاربر جدید'),
                'icon' => 'lucide:user-plus',
                'route' => 'admin.users.create',
                'color' => 'blue',
                'description' => __('افزودن کاربر'),
            ];
        }

        // Role Management
        if (auth()->user()->can('role-create')) {
            $this->actions[] = [
                'label' => __('نقش جدید'),
                'icon' => 'lucide:shield-plus',
                'route' => 'admin.roles.create',
                'color' => 'purple',
                'description' => __('ایجاد نقش'),
            ];
        }

        // Petty Cash
        if (auth()->user()->can('petty-cash-view') || auth()->user()->hasRole(['Admin', 'Superadmin'])) {
            $this->actions[] = [
                'label' => __('تنخواه گردان'),
                'icon' => 'lucide:wallet',
                'route' => 'admin.petty-cash.index',
                'color' => 'emerald',
                'description' => __('مدیریت تنخواه'),
            ];
        }

        // Media Library
        if (auth()->user()->can('media-view')) {
            $this->actions[] = [
                'label' => __('مدیا'),
                'icon' => 'lucide:image',
                'route' => 'admin.media.index',
                'color' => 'pink',
                'description' => __('کتابخانه رسانه'),
            ];
        }

        // Settings
        if (auth()->user()->can('setting-view')) {
            $this->actions[] = [
                'label' => __('تنظیمات'),
                'icon' => 'lucide:settings',
                'route' => 'admin.settings.index',
                'color' => 'slate',
                'description' => __('پیکربندی سیستم'),
            ];
        }

        // Action Logs
        if (auth()->user()->can('action-log-view')) {
            $this->actions[] = [
                'label' => __('گزارشات'),
                'icon' => 'lucide:file-text',
                'route' => 'admin.action-logs.index',
                'color' => 'amber',
                'description' => __('لاگ فعالیت‌ها'),
            ];
        }

        // Translations
        if (auth()->user()->can('translation-view')) {
            $this->actions[] = [
                'label' => __('ترجمه‌ها'),
                'icon' => 'lucide:languages',
                'route' => 'admin.translations.index',
                'color' => 'green',
                'description' => __('مدیریت زبان‌ها'),
            ];
        }

        // Modules (if user is admin)
        if (auth()->user()->hasRole(['Admin', 'Superadmin'])) {
            $this->actions[] = [
                'label' => __('ماژول‌ها'),
                'icon' => 'lucide:box',
                'route' => 'admin.modules.index',
                'color' => 'indigo',
                'description' => __('مدیریت ماژول‌ها'),
            ];
        }

        // Telescope (if in debug mode and user is admin)
        if (config('app.debug') && auth()->user()->hasRole(['Admin', 'Superadmin'])) {
            $this->actions[] = [
                'label' => __('Telescope'),
                'icon' => 'lucide:telescope',
                'route' => null,
                'url' => '/telescope',
                'color' => 'red',
                'description' => __('مانیتورینگ'),
            ];
        }

        // Pulse (if user is admin)
        if (auth()->user()->hasRole(['Admin', 'Superadmin'])) {
            $this->actions[] = [
                'label' => __('Pulse'),
                'icon' => 'lucide:activity',
                'route' => null,
                'url' => '/pulse',
                'color' => 'cyan',
                'description' => __('وضعیت سرور'),
            ];
        }

        // Cache Clear (admin only)
        if (auth()->user()->hasRole(['Admin', 'Superadmin'])) {
            $this->actions[] = [
                'label' => __('پاک‌سازی Cache'),
                'icon' => 'lucide:trash-2',
                'route' => null,
                'action' => 'clearCache',
                'color' => 'orange',
                'description' => __('پاکسازی کش'),
            ];
        }
    }

    public function clearCache(): void
    {
        try {
            \Artisan::call('optimize:clear');
            
            $this->dispatch('showToast', 
                message: __('کش با موفقیت پاک شد'),
                type: 'success'
            );
        } catch (\Exception $e) {
            $this->dispatch('showToast',
                message: __('خطا در پاکسازی کش'),
                type: 'error'
            );
        }
    }

    public function render()
    {
        return view('livewire.dashboard.quick-actions');
    }
}


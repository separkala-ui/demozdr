@extends('backend.layouts.app')

@section('admin-content')
<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">
                <iconify-icon icon="lucide:sliders" class="inline text-indigo-500"></iconify-icon>
                {{ __('تنظیمات هشدارها') }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ __('مدیریت تنظیمات پویا سیستم هشدارهای تنخواه و تراکنش‌ها') }}
            </p>
        </div>
    </div>

    {{-- Alert Settings Management Component --}}
    @livewire('admin.alert-settings-management')
</div>
@endsection


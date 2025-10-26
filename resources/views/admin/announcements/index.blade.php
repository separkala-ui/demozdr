@extends('backend.layouts.app')

@section('admin-content')
<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">
                <iconify-icon icon="lucide:megaphone" class="inline text-indigo-500"></iconify-icon>
                {{ __('مدیریت اطلاعیه‌ها') }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ __('ایجاد و مدیریت اطلاعیه‌ها برای کاربران سیستم') }}
            </p>
        </div>
    </div>

    {{-- Announcements Management Component --}}
    @livewire('admin.announcements-management')
</div>
@endsection


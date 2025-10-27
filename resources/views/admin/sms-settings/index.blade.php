@extends('backend.layouts.app')

@section('title', __('تنظیمات پیامک'))

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 px-4 py-8">
        <div class="max-w-6xl mx-auto">
            {{-- Header --}}
            <div class="mb-8">
                <div class="flex items-center gap-4 mb-2">
                    <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                        <iconify-icon icon="lucide:message-square" class="text-indigo-600 dark:text-indigo-400 text-3xl"></iconify-icon>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">تنظیمات پیامک</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">مدیریت تنظیمات سیستم ارسال پیامک IPPanel</p>
                    </div>
                </div>
            </div>

            {{-- Livewire Component --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm">
                @livewire(\App\Livewire\Admin\SMSSettingsManagement::class)
            </div>
        </div>
    </div>
@endsection


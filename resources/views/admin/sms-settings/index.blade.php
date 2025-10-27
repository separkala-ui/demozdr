@extends('backend.layouts.app')

@section('title', __('تنظیمات پیامک'))

@section('content')
    <div class="container mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                        <iconify-icon icon="lucide:message-square" class="text-indigo-600 text-4xl"></iconify-icon>
                        <span>تنظیمات پیامک (IPPanel)</span>
                    </h1>
                    <p class="text-gray-600 mt-2">مدیریت تنظیمات سیستم ارسال پیامک</p>
                </div>
            </div>
        </div>

        {{-- Livewire Component --}}
        <livewire:admin.s-m-s-settings-management />
    </div>
@endsection


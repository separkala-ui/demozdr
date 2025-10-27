@extends('backend.layouts.app')

@section('admin-content')
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('فرم‌های عملیاتی') }}</h1>
        <a href="{{ route('admin.operational-forms.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
            {{ __('ایجاد فرم جدید') }}
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-8">
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('هنوز فرمی ایجاد نشده') }}</h3>
            <p class="text-gray-500 mb-6">{{ __('برای ایجاد فرم جدید بر روی دکمه بالا کلیک کنید') }}</p>
            <a href="{{ route('admin.operational-forms.create') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">
                {{ __('ایجاد فرم اول') }}
            </a>
        </div>
    </div>
</div>
@endsection

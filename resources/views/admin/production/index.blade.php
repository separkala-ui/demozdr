@extends('backend.layouts.app')

@section('admin-content')
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('مهندسی تولید') }}</h1>
        <a href="{{ route('admin.production.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">
            {{ __('درخواست جدید') }}
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-8">
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('هنوز درخواستی ثبت نشده') }}</h3>
            <p class="text-gray-500 mb-6">{{ __('برای ثبت درخواست جدید بر روی دکمه بالا کلیک کنید') }}</p>
            <a href="{{ route('admin.production.create') }}" class="inline-block bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg">
                {{ __('ثبت درخواست اول') }}
            </a>
        </div>
    </div>
</div>
@endsection

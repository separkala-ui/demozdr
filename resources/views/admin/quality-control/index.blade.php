@extends('backend.layouts.app')

@section('admin-content')
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('کنترل کیفیت') }}</h1>
        <a href="{{ route('admin.quality-control.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
            {{ __('کنترل کیفیت جدید') }}
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-8">
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('هنوز گزارشی ثبت نشده') }}</h3>
            <p class="text-gray-500 mb-6">{{ __('برای ثبت کنترل کیفیت جدید بر روی دکمه بالا کلیک کنید') }}</p>
            <a href="{{ route('admin.quality-control.create') }}" class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                {{ __('ثبت کنترل اول') }}
            </a>
        </div>
    </div>
</div>
@endsection

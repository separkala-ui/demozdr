@extends('backend.layouts.app')

@section('admin-content')
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('جزئیات بازرسی') }}</h1>
        <a href="{{ route('admin.inspection.index') }}" class="text-blue-600 hover:underline">{{ __('بازگشت') }}</a>
    </div>

    <div class="bg-white rounded-lg shadow p-8">
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('بازرسی یافت نشد') }}</h3>
            <p class="text-gray-500">{{ __('درخواست شده پیدا نشد') }}</p>
        </div>
    </div>
</div>
@endsection

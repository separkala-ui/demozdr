@extends('backend.layouts.app')

@section('admin-content')
<div class="container mx-auto p-6 max-w-2xl">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ __('ایجاد فرم عملیاتی جدید') }}</h1>

    <form action="{{ route('admin.operational-forms.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-6">
        @csrf

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">{{ __('عنوان') }}</label>
            <input type="text" id="title" name="title" class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('title') border-red-500 @enderror" required>
            @error('title') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">{{ __('توضیحات') }}</label>
            <textarea id="description" name="description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
        </div>

        <div>
            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">{{ __('دسته‌بندی') }}</label>
            <select id="category" name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                <option value="general">{{ __('عمومی') }}</option>
                <option value="inspection">{{ __('بازرسی') }}</option>
                <option value="goods_request">{{ __('درخواست کالا') }}</option>
                <option value="quality_control">{{ __('کنترل کیفیت') }}</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">
                {{ __('ذخیره') }}
            </button>
            <a href="{{ route('admin.operational-forms.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg">
                {{ __('لغو') }}
            </a>
        </div>
    </form>
</div>
@endsection

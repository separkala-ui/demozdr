@extends('backend.layouts.master')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('فرم‌های عملیاتی') }}</h1>
        <a href="{{ route('admin.operational-forms.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
            {{ __('ایجاد فرم جدید') }}
        </a>
    </div>

    @if($templates->count())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($templates as $template)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $template->title }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ $template->description }}</p>
                    <div class="flex justify-between items-center">
                        <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded">
                            {{ $template->category }}
                        </span>
                        <span class="text-xs text-gray-500">
                            {{ $template->fields()->count() }} فیلد
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        {{ $templates->links() }}
    @else
        <div class="bg-gray-50 rounded-lg p-8 text-center">
            <p class="text-gray-500">{{ __('فرمی یافت نشد') }}</p>
        </div>
    @endif
</div>
@endsection

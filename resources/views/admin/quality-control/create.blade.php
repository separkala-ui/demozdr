@extends('backend.layouts.master')

@section('content')
<div class="container mx-auto p-6 max-w-2xl">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ __('ثبت کنترل کیفیت جدید') }}</h1>

    <form action="{{ route('admin.quality-control.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-6">
        @csrf

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">{{ __('عنوان') }}</label>
            <input type="text" id="title" name="title" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                {{ __('ثبت') }}
            </button>
            <a href="{{ route('admin.quality-control.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg">
                {{ __('لغو') }}
            </a>
        </div>
    </form>
</div>
@endsection

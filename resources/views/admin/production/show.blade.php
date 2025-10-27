@extends('backend.layouts.master')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ $report->template->title }}</h1>
        <a href="{{ route('admin.production.index') }}" class="text-purple-600 hover:underline">{{ __('بازگشت') }}</a>
    </div>

    <div class="grid grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-600">{{ __('شعبه') }}</p>
            <p class="text-lg font-semibold text-gray-900">{{ $report->ledger->branch_name }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-600">{{ __('درخواست دهنده') }}</p>
            <p class="text-lg font-semibold text-gray-900">{{ $report->reporter->full_name }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('جزئیات درخواست') }}</h3>
        <div class="space-y-3">
            @forelse($report->answers as $answer)
                <div class="border-b pb-3">
                    <p class="font-medium text-gray-900">{{ $answer->field->label }}</p>
                    <p class="text-gray-600">{{ $answer->value }}</p>
                </div>
            @empty
                <p class="text-gray-500">{{ __('اطلاعاتی ثبت نشده') }}</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

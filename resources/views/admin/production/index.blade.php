@extends('backend.layouts.master')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('مهندسی تولید') }}</h1>
        <a href="{{ route('admin.production.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">
            {{ __('درخواست جدید') }}
        </a>
    </div>

    @if($reports->count())
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-sm font-medium text-gray-700">{{ __('قالب') }}</th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-gray-700">{{ __('شعبه') }}</th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-gray-700">{{ __('درخواست دهنده') }}</th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-gray-700">{{ __('وضعیت') }}</th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-gray-700">{{ __('عمل') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $report->template->title }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $report->ledger->branch_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $report->reporter->full_name }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs">{{ $report->status }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('admin.production.show', $report->id) }}" class="text-purple-600 hover:underline">{{ __('مشاهده') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $reports->links() }}
    @else
        <div class="bg-gray-50 rounded-lg p-8 text-center">
            <p class="text-gray-500">{{ __('درخواستی یافت نشد') }}</p>
        </div>
    @endif
</div>
@endsection

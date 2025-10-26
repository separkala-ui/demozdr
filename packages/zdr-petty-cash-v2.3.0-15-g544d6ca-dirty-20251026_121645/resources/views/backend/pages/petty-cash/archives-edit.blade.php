@php
    $summary = $archive->summary ?? [];
    $transactionsSummary = $summary['transactions'] ?? [];
    $balancesSummary = $summary['balances'] ?? [];
@endphp

<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="space-y-6">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-800">
                        <i class="fas fa-edit ml-2 text-indigo-600"></i>
                        {{ __('ویرایش سند بایگانی شماره :id', ['id' => $archive->id]) }}
                    </h1>
                    <p class="text-sm text-slate-500">
                        {{ __('توضیحات مدیر یا فایل گزارش را می‌توانید به‌روزرسانی کنید. تغییرات فقط برای این سند اعمال می‌شود.') }}
                    </p>
                </div>
                <div class="text-xs text-slate-500">
                    <p>{{ __('شعبه') }}: <span class="font-semibold text-slate-700">{{ $archive->ledger->branch_name ?? __('نامشخص') }}</span></p>
                    <p>{{ __('دوره') }}: {{ $archive->opened_at ? verta($archive->opened_at)->format('Y/m/d H:i') : '—' }} → {{ $archive->closed_at ? verta($archive->closed_at)->format('Y/m/d H:i') : '—' }}</p>
                </div>
            </div>

            <form method="post" action="{{ route('admin.petty-cash.archives.update', $archive->id) }}" class="mt-6 space-y-4">
                @csrf
                @method('put')

                <div>
                    <label class="text-xs font-semibold text-slate-500">{{ __('یادداشت مدیر') }}</label>
                    <textarea name="closing_note" rows="4" class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('توضیحات درباره این تسویه را وارد کنید...') }}">{{ old('closing_note', $archive->closing_note) }}</textarea>
                    @error('closing_note')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                    <h2 class="text-sm font-semibold text-slate-700">{{ __('خلاصه سند') }}</h2>
                    <div class="mt-3 grid grid-cols-1 gap-3 text-xs text-slate-600 md:grid-cols-3">
                        <div>
                            <p class="text-slate-500">{{ __('تعداد تراکنش') }}</p>
                            <p class="font-semibold text-slate-700">{{ $archive->transactions_count ?? $transactionsSummary['count'] ?? 0 }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">{{ __('جمع ورودی‌ها') }}</p>
                            <p class="font-semibold text-emerald-600">{{ number_format($archive->total_charges ?? $transactionsSummary['total_charges'] ?? 0) }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">{{ __('جمع خروجی‌ها') }}</p>
                            <p class="font-semibold text-rose-600">{{ number_format($archive->total_expenses ?? $transactionsSummary['total_expenses'] ?? 0) }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">{{ __('مانده ابتدای دوره') }}</p>
                            <p class="font-semibold text-slate-700">{{ number_format($archive->opening_balance ?? $balancesSummary['opening'] ?? 0) }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">{{ __('مانده پایان دوره') }}</p>
                            <p class="font-semibold text-slate-700">{{ number_format($archive->closing_balance ?? $balancesSummary['closing'] ?? 0) }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">{{ __('تایید کننده') }}</p>
                            <p class="font-semibold text-slate-700">{{ optional($archive->closer)->full_name ?? optional($archive->closer)->name ?? __('نامشخص') }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600 shadow-sm">
                    <input type="checkbox" id="regenerate_report" name="regenerate_report" value="1" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="regenerate_report">{{ __('تولید مجدد فایل Excel با اطلاعات فعلی') }}</label>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('admin.petty-cash.archives.index') }}" class="inline-flex items-center gap-2 rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        <i class="fas fa-arrow-right"></i>
                        {{ __('بازگشت') }}
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                        <i class="fas fa-save"></i>
                        {{ __('ذخیره تغییرات') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.backend-layout>

@php
    $isAdminUser = isset($isAdminUser) ? (bool) $isAdminUser : (bool) (auth()->user()?->hasRole(['Superadmin', 'Admin']) ?? false);
    $pendingChargeRequests = isset($pendingChargeRequests) ? collect($pendingChargeRequests) : collect();
    $statusOptions = [
        'draft' => __('پیش‌نویس'),
        'submitted' => __('ارسال‌شده'),
        'approved' => __('تایید‌شده'),
        'rejected' => __('رد‌شده'),
        'needs_changes' => __('نیاز به اصلاح'),
        'under_review' => __('در حال بررسی'),
    ];
@endphp

<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="space-y-6">
        <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-indigo-900">
                        <i class="fas fa-paper-plane ml-2 text-indigo-600"></i>
                        {{ __('درخواست شارژ تنخواه برای :branch', ['branch' => $ledger->branch_name]) }}
                    </h1>
                    <p class="text-sm text-indigo-700">
                        {{ __('مبلغ مورد نیاز را وارد کنید یا از گزینه‌های سریع استفاده کنید. پس از ارسال، درخواست برای مدیر سیستم ارسال می‌شود.') }}
                    </p>
                </div>
                <a href="{{ route('admin.petty-cash.index', ['ledger' => $ledger->id]) }}"
                   class="inline-flex items-center gap-2 rounded-md border border-indigo-400 px-4 py-2 text-sm font-semibold text-indigo-700 shadow-sm hover:bg-indigo-100">
                    <i class="fas fa-arrow-right"></i>
                    {{ __('بازگشت به صفحه تنخواه') }}
                </a>
            </div>
        </div>

        @if($isAdminUser && $pendingChargeRequests->isNotEmpty())
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-amber-800">{{ __('درخواست‌های در انتظار تایید') }}</h2>
                    <span class="text-xs text-amber-700">{{ __('تعداد کل: :count', ['count' => $pendingChargeRequests->count()]) }}</span>
                </div>

                <div class="mt-3 grid grid-cols-1 gap-3 lg:grid-cols-2">
                    @foreach($pendingChargeRequests as $request)
                        @php
                            $meta = $request->meta['charge_request'] ?? [];
                            $sourceLabel = match($request->charge_origin) {
                                'request_form' => __('فرم درخواست شارژ'),
                                'quick_entry' => __('ثبت سریع تراکنش'),
                                default => __('نامشخص'),
                            };
                        @endphp
                        <div class="rounded-md border border-amber-200 bg-white p-4 shadow-sm">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-xs text-slate-500">{{ __('مبلغ') }}</p>
                                    <p class="mt-1 text-base font-semibold text-slate-800">{{ number_format($request->amount) }} {{ __('ریال') }}</p>
                                </div>
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-medium text-amber-700">
                                    {{ $sourceLabel }}
                                </span>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-600">
                                <div>
                                    <p class="text-slate-500">{{ __('درخواست دهنده') }}</p>
                                    <p class="font-medium">{{ optional($request->requester)->full_name ?? $request->requested_by }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">{{ __('وضعیت') }}</p>
                                    <p class="font-medium">{{ $statusOptions[$request->status] ?? $request->status }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">{{ __('تاریخ ثبت') }}</p>
                                    <p class="font-medium">{{ $request->transaction_date ? verta($request->transaction_date)->format('Y/m/d H:i') : '—' }}</p>
                                </div>
                                @if(!empty($meta['quick_amount']))
                                    <div>
                                        <p class="text-slate-500">{{ __('مبلغ سریع') }}</p>
                                        <p class="font-medium text-emerald-600">{{ __('بله') }}</p>
                                    </div>
                                @endif
                            </div>
                            @if(!empty($meta['note']))
                                <p class="mt-2 rounded bg-slate-50 p-2 text-[11px] text-slate-600">{{ __('توضیح: :note', ['note' => $meta['note']]) }}</p>
                            @endif
                            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                                <button type="button"
                                        wire:click="requestEdit({{ $request->id }})"
                                        class="inline-flex items-center gap-1 rounded-md border border-slate-200 px-2 py-1 font-medium text-slate-600 hover:bg-slate-100">
                                    <i class="fas fa-pen"></i>
                                    {{ __('ویرایش / بررسی') }}
                                </button>
                                <button type="button"
                                        wire:click="requestApprove({{ $request->id }})"
                                        class="inline-flex items-center gap-1 rounded-md bg-emerald-500 px-2 py-1 font-medium text-white hover:bg-emerald-600">
                                    <i class="fas fa-check"></i>
                                    {{ __('تایید فوری') }}
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="rounded-lg border border-indigo-300 bg-white p-6 shadow-sm">
            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs text-slate-500">{{ __('صاحب حساب') }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $ledger->account_holder ?? __('تعریف نشده') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs text-slate-500">{{ __('شماره حساب') }}</p>
                    <p class="mt-1 text-sm font-mono text-slate-800">{{ $ledger->account_number ?? __('تعریف نشده') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs text-slate-500">{{ __('شماره شبا') }}</p>
                    <p class="mt-1 text-sm font-mono text-slate-800">{{ $ledger->iban ?? __('تعریف نشده') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs text-slate-500">{{ __('شماره کارت') }}</p>
                    <p class="mt-1 text-sm font-mono text-slate-800">{{ $ledger->card_number ?? __('تعریف نشده') }}</p>
                </div>
            </div>
            @livewire('petty-cash.charge-request-form', ['ledger' => $ledger], key('charge-request-page-'.$ledger->id))
        </div>
    </div>
</x-layouts.backend-layout>

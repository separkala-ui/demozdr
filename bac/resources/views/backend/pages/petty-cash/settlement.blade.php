<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="space-y-6">
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-emerald-900">
                        <i class="fas fa-scale-balanced ml-2 text-emerald-600"></i>
                        {{ __('تسویه تنخواه شعبه :branch', ['branch' => $ledger->branch_name]) }}
                    </h1>
                    <p class="text-sm text-emerald-700">
                        {{ __('پس از کنترل تراکنش‌ها، درخواست تسویه را ثبت کنید تا مدیر سیستم بررسی و فصل جدید آغاز شود.') }}
                    </p>
                </div>
                <a href="{{ route('admin.petty-cash.index', ['ledger' => $ledger->id]) }}"
                   class="inline-flex items-center gap-2 rounded-md border border-emerald-400 px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm hover:bg-emerald-100">
                    <i class="fas fa-arrow-right"></i>
                    {{ __('بازگشت به صفحه تنخواه') }}
                </a>
            </div>
        </div>

        <div class="rounded-lg border border-emerald-300 bg-white p-6 shadow-sm">
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
            @livewire('petty-cash.settlement-panel', ['ledger' => $ledger], key('settlement-page-'.$ledger->id))
        </div>
    </div>
</x-layouts.backend-layout>

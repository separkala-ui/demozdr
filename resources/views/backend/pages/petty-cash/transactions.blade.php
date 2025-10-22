<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="space-y-6">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-800">
                        <i class="fas fa-plus-circle ml-2 text-indigo-600"></i>
                        {{ __('ثبت تراکنش برای :branch', ['branch' => $ledger->branch_name]) }}
                    </h1>
                    <p class="text-sm text-slate-500">
                        {{ __('هزینه‌ها، شارژها یا تعدیلات را با فرم زیر ثبت و مدیریت کنید. پس از ذخیره، تراکنش در داشبورد تنخواه قابل مشاهده خواهد بود.') }}
                    </p>
                </div>
                <a href="{{ route('admin.petty-cash.index', ['ledger' => $ledger->id]) }}"
                   class="inline-flex items-center gap-2 rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    <i class="fas fa-arrow-right"></i>
                    {{ __('بازگشت به داشبورد تنخواه') }}
                </a>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @livewire('petty-cash.transaction-form', ['ledger' => $ledger], key('transaction-form-page-'.$ledger->id))
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-slate-700">{{ __('آخرین تراکنش‌های شعبه') }}</h2>
            <p class="mt-1 text-xs text-slate-500">{{ __('مروری سریع بر جدیدترین تراکنش‌های ثبت شده برای این شعبه.') }}</p>

            <div class="mt-4">
                @livewire('petty-cash.transactions-table', ['ledger' => $ledger], key('transactions-table-page-'.$ledger->id))
            </div>
        </div>
    </div>
</x-layouts.backend-layout>

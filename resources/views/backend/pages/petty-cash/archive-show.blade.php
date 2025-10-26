@php
    $statusStyles = [
        'draft' => ['label' => __('پیش‌نویس'), 'badge' => 'bg-slate-200 text-slate-700'],
        'submitted' => ['label' => __('ارسال‌شده'), 'badge' => 'bg-amber-100 text-amber-700'],
        'approved' => ['label' => __('تایید‌شده'), 'badge' => 'bg-emerald-100 text-emerald-700'],
        'rejected' => ['label' => __('رد‌شده'), 'badge' => 'bg-rose-100 text-rose-700'],
        'needs_changes' => ['label' => __('نیاز به اصلاح'), 'badge' => 'bg-orange-100 text-orange-700'],
        'under_review' => ['label' => __('در حال بررسی'), 'badge' => 'bg-purple-100 text-purple-700'],
    ];
@endphp

<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="space-y-6">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-800">
                        <i class="fas fa-clipboard-list ml-2 text-indigo-600"></i>
                        {{ __('جزئیات سند بایگانی شماره :id', ['id' => $archive->id]) }}
                    </h1>
                    <p class="text-sm text-slate-500">
                        {{ __('مشاهده کامل تراکنش‌های ثبت شده در این سند به همراه پیوست فاکتور و رسید، و همچنین وضعیت فعلی تنخواه شعبه.') }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.petty-cash.archives.index') }}"
                       class="inline-flex items-center gap-2 rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        <i class="fas fa-arrow-right"></i>
                        {{ __('بازگشت به بایگانی') }}
                    </a>
                    <a href="{{ route('admin.petty-cash.print', ['ledger' => $archive->ledger_id, 'cycle' => $archive->id]) }}"
                       target="_blank"
                       class="inline-flex items-center gap-2 rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        <i class="fas fa-print"></i>
                        {{ __('چاپ سند') }}
                    </a>
                    @if($archive->report_path)
                        <a href="{{ route('admin.petty-cash.archives.download', ['ledger' => $archive->ledger_id, 'cycle' => $archive->id]) }}"
                           class="inline-flex items-center gap-2 rounded-md bg-emerald-500 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-600">
                            <i class="fas fa-download"></i>
                            {{ __('دانلود گزارش') }}
                        </a>
                    @endif
                    @if($canManageArchives)
                        <a href="{{ route('admin.petty-cash.archives.edit', $archive->id) }}"
                           class="inline-flex items-center gap-2 rounded-md bg-indigo-500 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-600">
                            <i class="fas fa-edit"></i>
                            {{ __('ویرایش سند') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
                    <p class="text-xs text-slate-500">{{ __('شعبه') }}</p>
                    <p class="mt-1 text-base font-semibold text-slate-800">{{ $archive->ledger->branch_name ?? __('نامشخص') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
                    <p class="text-xs text-slate-500">{{ __('بازه زمانی سند') }}</p>
                    <p class="mt-1 text-xs text-slate-600">
                        {{ $archive->opened_at ? verta($archive->opened_at)->format('Y/m/d H:i') : '—' }}
                        <span class="mx-1 text-slate-400">→</span>
                        {{ $archive->closed_at ? verta($archive->closed_at)->format('Y/m/d H:i') : '—' }}
                    </p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
                    <p class="text-xs text-slate-500">{{ __('تعداد تراکنش بایگانی‌شده') }}</p>
                    <p class="mt-1 text-base font-semibold text-slate-800">{{ $archive->transactions_count ?? $archivedTransactions->count() }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
                    <p class="text-xs text-slate-500">{{ __('جمع هزینه‌های سند') }}</p>
                    <p class="mt-1 text-base font-semibold text-rose-600">{{ number_format($archive->total_expenses ?? $archivedTransactions->sum('amount')) }} {{ __('ریال') }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-700">{{ __('تراکنش‌های بایگانی‌شده') }}</h2>
                    <p class="text-xs text-slate-500">{{ __('لیست کامل تراکنش‌های ثبت شده در این سند به همراه وضعیت و پیوست فاکتور/رسید.') }}</p>
                </div>
                <span class="text-xs text-slate-400">{{ __('تعداد: :count', ['count' => $archivedTransactions->count()]) }}</span>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-3 py-2 text-right">{{ __('شماره مرجع') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('نوع') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('وضعیت') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('مبلغ (ریال)') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('دسته‌بندی') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('تاریخ') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('شرح') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('پیوست فاکتور') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('پیوست رسید') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600">
                        @forelse($archivedTransactions as $transaction)
                            @php
                                $invoiceMedia = $transaction->getMedia('invoice');
                                $receiptMedia = $transaction->getMedia('bank_receipt');
                                $status = $statusStyles[$transaction->status] ?? ['label' => $statusLabels[$transaction->status] ?? $transaction->status, 'badge' => 'bg-slate-200 text-slate-700'];
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 font-semibold text-slate-800">{{ $transaction->reference_number ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $transaction->type === \App\Models\PettyCashTransaction::TYPE_EXPENSE ? __('هزینه') : ($transaction->type === \App\Models\PettyCashTransaction::TYPE_CHARGE ? __('شارژ') : __('تعدیل')) }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 {{ $status['badge'] }} text-[11px] font-semibold">
                                        {{ $status['label'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 font-semibold text-slate-800">{{ number_format($transaction->amount ?? 0) }}</td>
                                <td class="px-3 py-2">{{ config('petty-cash.categories')[$transaction->category] ?? $transaction->category ?? __('نامشخص') }}</td>
                                <td class="px-3 py-2">{{ $transaction->transaction_date ? verta($transaction->transaction_date)->format('Y/m/d H:i') : '—' }}</td>
                                <td class="px-3 py-2">
                                    <div class="max-w-xs whitespace-normal text-[11px] leading-relaxed">
                                        {{ $transaction->description ?? __('—') }}
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    @if($invoiceMedia->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($invoiceMedia as $media)
                                                <a href="{{ $media->getUrl() }}" target="_blank" class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-200">
                                                    <i class="fas fa-file-image"></i> {{ __('مشاهده') }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    @if($receiptMedia->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($receiptMedia as $media)
                                                <a href="{{ $media->getUrl() }}" target="_blank" class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-200">
                                                    <i class="fas fa-file-invoice"></i> {{ __('مشاهده') }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-3 py-6 text-center text-sm text-slate-500">
                                    {{ __('تراکنشی در این سند ثبت نشده است.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-700">{{ __('تراکنش‌های جاری شعبه') }}</h2>
                    <p class="text-xs text-slate-500">{{ __('تراکنش‌هایی که هنوز در این سند بایگانی نشده‌اند (در انتظار یا تایید شده).') }}</p>
                </div>
                <span class="text-xs text-slate-400">{{ __('تعداد: :count', ['count' => $activeTransactions->count()]) }}</span>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-3 py-2 text-right">{{ __('شماره مرجع') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('وضعیت') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('مبلغ (ریال)') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('دسته‌بندی') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('تاریخ') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('شرح') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('پیوست‌ها') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600">
                        @forelse($activeTransactions as $transaction)
                            @php
                                $invoiceMedia = $transaction->getMedia('invoice');
                                $receiptMedia = $transaction->getMedia('bank_receipt');
                                $status = $statusStyles[$transaction->status] ?? ['label' => $statusLabels[$transaction->status] ?? $transaction->status, 'badge' => 'bg-slate-200 text-slate-700'];
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 font-semibold text-slate-800">{{ $transaction->reference_number ?? '—' }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 {{ $status['badge'] }} text-[11px] font-semibold">
                                        {{ $status['label'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 font-semibold text-slate-800">{{ number_format($transaction->amount ?? 0) }}</td>
                                <td class="px-3 py-2">{{ config('petty-cash.categories')[$transaction->category] ?? $transaction->category ?? __('نامشخص') }}</td>
                                <td class="px-3 py-2">{{ $transaction->transaction_date ? verta($transaction->transaction_date)->format('Y/m/d H:i') : '—' }}</td>
                                <td class="px-3 py-2">
                                    <div class="max-w-xs whitespace-normal text-[11px] leading-relaxed">
                                        {{ $transaction->description ?? __('—') }}
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($invoiceMedia as $media)
                                            <a href="{{ $media->getUrl() }}" target="_blank" class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-200">
                                                <i class="fas fa-file-image"></i> {{ __('فاکتور') }}
                                            </a>
                                        @endforeach
                                        @foreach($receiptMedia as $media)
                                            <a href="{{ $media->getUrl() }}" target="_blank" class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-200">
                                                <i class="fas fa-file-invoice"></i> {{ __('رسید') }}
                                            </a>
                                        @endforeach
                                        @if($invoiceMedia->isEmpty() && $receiptMedia->isEmpty())
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-sm text-slate-500">
                                    {{ __('تراکنش فعالی برای این شعبه ثبت نشده است.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.backend-layout>

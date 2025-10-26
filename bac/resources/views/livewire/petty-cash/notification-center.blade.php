<div x-data="{ open:false }" wire:poll.180s="refreshCounts" class="relative">
    <button type="button"
            @click="open = !open"
            class="relative inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200">
        <iconify-icon icon="lucide:bell" class="text-lg"></iconify-icon>
        @php
            $total = $pendingTransactionsCount + $pendingChargeRequestsCount + $pendingArchivesCount;
        @endphp
        @if($total > 0)
            <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white">
                {{ $total }}
            </span>
        @endif
    </button>

    <div x-cloak
         x-show="open"
         @click.outside="open = false"
         class="absolute right-0 mt-2 w-80 rounded-xl border border-slate-200 bg-white p-3 shadow-xl">
        <h3 class="text-sm font-semibold text-slate-700">{{ __('اعلان‌های تنخواه') }}</h3>
        <p class="mt-1 text-[11px] text-slate-500">{{ __('تراکنش‌ها و درخواست‌های در انتظار بررسی مدیریت.') }}</p>

        @php
            $transactionsList = ($items['transactions'] ?? collect());
            $chargeRequestsList = ($items['charge_requests'] ?? collect());
            $archivesList = ($items['archives'] ?? collect());
            $latestTransaction = $transactionsList->first();
            $latestChargeRequest = $chargeRequestsList->first();
            $latestArchiveRequest = $archivesList->first();
        @endphp

        <div class="mt-3 space-y-3 max-h-72 overflow-y-auto">
            <div>
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span>{{ __('تراکنش‌های هزینه در انتظار تایید') }}</span>
                    <span class="font-semibold text-slate-800">{{ $pendingTransactionsCount }}</span>
                </div>
                @if($latestTransaction)
                    <div class="mt-1 text-[10px] text-slate-400">
                        {{ __('آخرین ثبت: :time', ['time' => verta($latestTransaction->created_at)->format('Y/m/d H:i')]) }}
                    </div>
                @endif
                @forelse($items['transactions'] ?? [] as $transaction)
                    <div class="mt-2 rounded border border-slate-200 bg-slate-50 p-2 text-[12px]">
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="font-semibold">{{ number_format($transaction->amount) }} {{ __('ریال') }}</span>
                            <span>{{ verta($transaction->created_at)->format('Y/m/d H:i') }}</span>
                        </div>
                        <div class="mt-1 text-slate-500">{{ $transaction->description ?: '---' }}</div>
                        <a href="{{ route('admin.petty-cash.transactions', ['ledger' => $transaction->ledger_id]) }}"
                           class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 hover:text-indigo-700">
                            {{ __('مشاهده در لیست تراکنش‌ها') }}
                            <iconify-icon icon="lucide:arrow-up-right"></iconify-icon>
                        </a>
                    </div>
                @empty
                    <div class="mt-2 rounded border border-dashed border-slate-200 bg-slate-50 p-3 text-center text-[11px] text-slate-500">
                        {{ __('موردی ثبت نشده است.') }}
                    </div>
                @endforelse
            </div>

            <div>
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span>{{ __('درخواست‌های شارژ در انتظار تایید') }}</span>
                    <span class="font-semibold text-slate-800">{{ $pendingChargeRequestsCount }}</span>
                </div>
                @if($latestChargeRequest)
                    <div class="mt-1 text-[10px] text-slate-400">
                        {{ __('آخرین ثبت: :time', ['time' => verta($latestChargeRequest->created_at)->format('Y/m/d H:i')]) }}
                    </div>
                @endif
                @forelse($items['charge_requests'] ?? [] as $request)
                    <div class="mt-2 rounded border border-slate-200 bg-slate-50 p-2 text-[12px]">
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="font-semibold">{{ number_format($request->amount) }} {{ __('ریال') }}</span>
                            <span>{{ verta($request->created_at)->format('Y/m/d H:i') }}</span>
                        </div>
                        <div class="mt-1 text-slate-500">{{ $request->description ?: '---' }}</div>
                        <a href="{{ route('admin.petty-cash.transactions', ['ledger' => $request->ledger_id]) }}#charge-requests"
                           class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 hover:text-indigo-700">
                            {{ __('بررسی درخواست شارژ') }}
                            <iconify-icon icon="lucide:arrow-up-right"></iconify-icon>
                        </a>
                    </div>
                @empty
                    <div class="mt-2 rounded border border-dashed border-slate-200 bg-slate-50 p-3 text-center text-[11px] text-slate-500">
                        {{ __('موردی ثبت نشده است.') }}
                    </div>
                @endforelse
            </div>

            <div>
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span>{{ __('درخواست‌های تسویه در انتظار بررسی') }}</span>
                    <span class="font-semibold text-slate-800">{{ $pendingArchivesCount }}</span>
                </div>
                @if($latestArchiveRequest?->requested_close_at)
                    <div class="mt-1 text-[10px] text-slate-400">
                        {{ __('آخرین ثبت: :time', ['time' => verta($latestArchiveRequest->requested_close_at)->format('Y/m/d H:i')]) }}
                    </div>
                @endif
                @forelse($items['archives'] ?? [] as $archive)
                    <div class="mt-2 rounded border border-slate-200 bg-slate-50 p-2 text-[12px]">
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="font-semibold">{{ optional($archive->ledger)->branch_name ?? __('شعبه نامشخص') }}</span>
                            <span>{{ $archive->requested_close_at ? verta($archive->requested_close_at)->format('Y/m/d H:i') : '---' }}</span>
                        </div>
                        <div class="mt-1 text-slate-500">{{ $archive->request_note ?: __('توضیحی ثبت نشده است.') }}</div>
                        <a href="{{ route('admin.petty-cash.settlement', ['ledger' => $archive->ledger_id]) }}"
                           class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 hover:text-indigo-700">
                            {{ __('مدیریت تسویه شعبه') }}
                            <iconify-icon icon="lucide:arrow-up-right"></iconify-icon>
                        </a>
                    </div>
                @empty
                    <div class="mt-2 rounded border border-dashed border-slate-200 bg-slate-50 p-3 text-center text-[11px] text-slate-500">
                        {{ __('موردی ثبت نشده است.') }}
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-3 flex justify-end">
            <button type="button" class="text-xs font-semibold text-slate-400 hover:text-slate-600" @click="open = false">
                {{ __('بستن') }}
            </button>
        </div>
    </div>
</div>

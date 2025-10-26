<div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h3 class="text-base font-semibold text-slate-700">{{ __('تسویه تنخواه شعبه') }}</h3>
            <p class="mt-1 text-xs text-slate-500">
                {{ __('با تسویه، دوره فعلی بسته و دوره جدید با مانده موجود آغاز می‌شود.') }}
            </p>
        </div>
        @if($cycle && $cycle->status === 'pending_close')
            <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                {{ __('در انتظار تایید مدیریت') }}
            </span>
        @endif
    </div>

    @if(session()->has('success'))
        <div class="mt-4 rounded-md border border-green-200 bg-green-50 px-3 py-2 text-xs text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="mt-4 rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
            {{ session('error') }}
        </div>
    @endif

    @php $isAdmin = auth()->user()?->hasRole(['Superadmin', 'Admin']); @endphp

    @if(! $cycle)
        <p class="mt-4 rounded-md bg-slate-100 p-3 text-xs text-slate-500">
            {{ __('چرخه فعالی برای این شعبه ثبت نشده است.') }}
        </p>
    @elseif($cycle->status === 'open')
        <div class="mt-4 space-y-3 text-xs text-slate-600">
            <div class="flex flex-wrap gap-4">
                <span>{{ __('تاریخ شروع دوره جاری: :date', ['date' => verta($cycle->opened_at)->format('Y/m/d H:i')]) }}</span>
                <span>{{ __('موجودی اولیه دوره: :amount ریال', ['amount' => number_format($cycle->opening_balance)]) }}</span>
                <span>{{ __('تراکنش‌های در انتظار: :count', ['count' => $transactionsPendingCount]) }}</span>
            </div>

            @if(! $isAdmin)
                <form wire:submit.prevent="requestSettlement" class="space-y-2">
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('پیام برای مدیریت (اختیاری)') }}</label>
                        <textarea
                            wire:model.lazy="note"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="{{ __('دلایل درخواست تسویه را بنویسید...') }}"
                        ></textarea>
                        @error('note')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <i class="fas fa-check-double"></i>
                            {{ __('درخواست تسویه تنخواه') }}
                        </button>
                    </div>
                </form>
            @else
                <p class="rounded-md bg-slate-100 p-3 text-xs text-slate-500">
                    {{ __('کاربران شعبه می‌توانند پس از تعیین تکلیف تراکنش‌های در انتظار، درخواست تسویه ثبت کنند.') }}
                </p>
            @endif
        </div>
    @elseif($cycle->status === 'pending_close')
        <div class="mt-4 space-y-3 text-xs text-slate-600">
            <div class="flex flex-wrap gap-4">
                <span>{{ __('درخواست توسط :name در تاریخ :date ثبت شده است.', [
                    'name' => optional($cycle->requester)->full_name ?? __('نامشخص'),
                    'date' => $cycle->requested_close_at ? verta($cycle->requested_close_at)->format('Y/m/d H:i') : __('نامشخص'),
                ]) }}</span>
                <span>{{ __('موجودی فعلی: :amount ریال', ['amount' => number_format($ledger->current_balance)]) }}</span>
            </div>

            @if($cycle->request_note)
                <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-amber-700">
                    <div class="text-xs font-semibold">{{ __('توضیح کاربر') }}</div>
                    <p class="mt-1 text-xs">{{ $cycle->request_note }}</p>
                </div>
            @endif

            @if($isAdmin)
                <form wire:submit.prevent="approveSettlement" class="space-y-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('یادداشت پایانی (اختیاری)') }}</label>
                        <textarea
                            wire:model.lazy="adminNote"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="{{ __('نتیجه بررسی را ثبت کنید...') }}"
                        ></textarea>
                        @error('adminNote')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap justify-end gap-2">
                        <button type="button" wire:click="rejectSettlement" class="inline-flex items-center gap-2 rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 shadow hover:bg-slate-50">
                            <i class="fas fa-undo"></i>
                            {{ __('لغو درخواست') }}
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <i class="fas fa-check"></i>
                            {{ __('تایید و بستن فصل') }}
                        </button>
                    </div>
                </form>
            @else
                <p class="rounded-md bg-slate-100 p-3 text-xs text-slate-500">
                    {{ __('درخواست تسویه شما در انتظار بررسی مدیریت است. در صورت نیاز به اصلاح، با شما تماس گرفته خواهد شد.') }}
                </p>
            @endif
        </div>
    @endif
</div>

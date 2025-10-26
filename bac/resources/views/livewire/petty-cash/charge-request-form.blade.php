@php
    $disableForm = $userHasPendingRequest || $revisionRequest !== null;
@endphp

<div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h3 class="text-base font-semibold text-slate-700">{{ __('درخواست شارژ تنخواه') }}</h3>
            <p class="mt-1 text-xs text-slate-500">
                {{ __('مبلغ دلخواه را وارد کنید یا یکی از گزینه‌های سریع را انتخاب کنید. پس از ثبت، درخواست برای تایید مدیریت ارسال می‌شود.') }}
            </p>
        </div>
        @if($activeRequest)
            <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                {{ __('در انتظار تایید') }}
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

    @if($activeRequest)
        <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
            <div class="font-semibold">{{ __('درخواست ثبت‌شده') }}</div>
            <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1">
                <span>{{ __('مبلغ: :amount ریال', ['amount' => number_format($activeRequest->amount)]) }}</span>
                <span>{{ __('تاریخ: :date', ['date' => verta($activeRequest->transaction_date)->format('Y/m/d H:i')]) }}</span>
                <span>{{ __('وضعیت: در انتظار تایید مدیریت') }}</span>
            </div>
            <p class="mt-2 text-slate-600">
                {{ __('تا قبل از اعلام نظر مدیریت امکان ثبت درخواست جدید وجود ندارد.') }}
            </p>
        </div>
    @elseif($revisionRequest)
        <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
            <div class="font-semibold">{{ __('درخواست نیازمند اصلاح') }}</div>
            <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1">
                <span>{{ __('مبلغ: :amount ریال', ['amount' => number_format($revisionRequest->amount)]) }}</span>
                <span>{{ __('تاریخ: :date', ['date' => verta($revisionRequest->transaction_date)->format('Y/m/d H:i')]) }}</span>
            </div>
            <p class="mt-2 text-slate-600">
                {{ __('مدیر سیستم از شما خواسته است درخواست شارژ را اصلاح کنید. از جدول تراکنش‌ها گزینه «ویرایش» را انتخاب کنید و پس از اعمال تغییرات، دوباره ارسال نمایید.') }}
            </p>
        </div>
    @endif

    <form wire:submit.prevent="submit" class="mt-4 space-y-4">
        <div>
            <label class="text-xs font-semibold text-slate-500">{{ __('انتخاب سریع مبلغ (میلیون ریال)') }}</label>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($quickAmounts as $quickAmount)
                    <button
                        type="button"
                        wire:click="selectQuickAmount({{ $quickAmount }})"
                        @class([
                            'rounded-md border px-3 py-2 text-xs font-semibold transition-colors',
                            'border-indigo-400 bg-indigo-50 text-indigo-600' => (int) $amount === $quickAmount,
                            'border-slate-200 bg-slate-50 text-slate-600 hover:border-indigo-300 hover:text-indigo-600' => (int) $amount !== $quickAmount,
                        ])
                        @if($disableForm) disabled @endif
                    >
                        {{ $quickAmount }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="text-xs font-semibold text-slate-500">{{ __('مبلغ درخواست (ریال)') }}</label>
                <div class="relative">
                    <input
                        type="text"
                        inputmode="numeric"
                        value="{{ $amount ? number_format((int) $amount) : '' }}"
                        class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="{{ __('مثال: 2,000,000') }}"
                        @if($disableForm) disabled @endif
                        x-data="{
                            isFormatting: false,
                            format(raw) {
                                const cleaned = (raw || '').toString().replace(/[^0-9]/g, '').slice(0, 20);
                                if (!cleaned) {
                                    $el.value = '';
                                    return '';
                                }

                                const formatted = cleaned.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                                $el.value = formatted;

                                return cleaned;
                            }
                        }"
                        x-init="format({{ json_encode((string) ($amount ?? '') ) }});"
                        x-on:input="
                            if (isFormatting) return;
                            isFormatting = true;
                            const raw = format($event.target.value);
                            $wire.set('amount', raw);
                            setTimeout(() => { isFormatting = false; }, 10);
                        "
                    >
                    @if(!empty($amount) && is_numeric($amount) && (float) $amount > 0)
                        @php
                            $amountData = \App\Helpers\NumberToWords::formatWithSeparatorsAndWordsWithToman((int) $amount);
                        @endphp
                        <div class="mt-1 text-xs text-slate-600 bg-slate-50 rounded-md px-2 py-1 space-y-1">
                            <div>
                                <strong>مبلغ حروفی (ریال):</strong> {{ $amountData['words'] }} ریال
                            </div>
                            <div>
                                <strong>معادل تومان:</strong> {{ $amountData['toman_amount'] }} تومان
                            </div>
                            <div>
                                <strong>مبلغ حروفی (تومان):</strong> {{ $amountData['toman_words'] }} تومان
                            </div>
                        </div>
                    @endif
                </div>
                @error('amount')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">{{ __('مستندات (اختیاری)') }}</label>
                <input
                    type="file"
                    wire:model="attachment"
                    class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow focus:border-indigo-500 focus:ring-indigo-500"
                    @if($disableForm) disabled @endif
                >
                @error('attachment')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
                @if($attachment)
                    <p class="mt-1 text-xs text-slate-500">{{ __('فایل انتخاب شده: :name', ['name' => $attachment->getClientOriginalName()]) }}</p>
                @endif
            </div>
        </div>

        <div>
            <label class="text-xs font-semibold text-slate-500">{{ __('توضیح برای مدیر (اختیاری)') }}</label>
            <textarea
                rows="3"
                wire:model.lazy="note"
                class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="{{ __('نیاز این شارژ را توضیح دهید...') }}"
                @if($disableForm) disabled @endif
            ></textarea>
            @error('note')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end">
            <button
                type="submit"
                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-400"
                @if($disableForm) disabled @endif
                wire:loading.attr="disabled"
            >
                <i class="fas fa-paper-plane"></i>
                {{ __('ارسال درخواست شارژ') }}
            </button>
        </div>
    </form>
</div>

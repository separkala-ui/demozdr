<div class="space-y-5">
    <div class="rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">
                    {{ __('تنخواه شعبه :branch', ['branch' => $ledger->branch_name]) }}
                </h2>
                <p class="mt-1 text-sm text-slate-600 font-medium">
                    {{ __('مانده تایید شده: :amount ریال', ['amount' => number_format($ledger->current_balance)]) }}
                </p>
                @if(isset($ledger->pending_balance))
                    <p class="text-sm {{ ($ledger->pending_balance_delta ?? 0) < 0 ? 'text-red-600' : 'text-amber-600' }}">
                        {{ __('مانده در انتظار تایید: :amount ریال', ['amount' => number_format($ledger->pending_balance)]) }}
                        @if(isset($ledger->pending_balance_delta))
                            <span class="text-xs text-slate-500">
                                {{ __('تغییر در انتظار تایید: :amount ریال', ['amount' => number_format($ledger->pending_balance_delta)]) }}
                            </span>
                        @endif
                    </p>
                    <p class="text-xs text-slate-500">
                        {{ __('هزینه‌های در انتظار تایید: :amount ریال', ['amount' => number_format($ledger->pending_expenses_total ?? 0)]) }} •
                        {{ __('شارژهای در انتظار تایید: :amount ریال', ['amount' => number_format($ledger->pending_charges_total ?? 0)]) }} •
                        {{ __('تعداد تراکنش‌های در انتظار: :count', ['count' => $ledger->pending_transactions_count ?? 0]) }}
                    </p>
                @endif
                @if(!empty($ledger->last_transaction_at))
                    <p class="text-xs text-slate-400 mt-1">
                        {{ __('آخرین تراکنش: :date', ['date' => verta($ledger->last_transaction_at)->format('Y/m/d H:i')]) }}
                    </p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                    {{ __('سقف مجاز: :amount ریال', ['amount' => number_format($ledger->limit_amount)]) }}
                </span>
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    {{ $ledger->max_charge_request_amount > 0
                        ? __('سقف شارژ: :amount ریال', ['amount' => number_format($ledger->max_charge_request_amount)])
                        : __('سقف شارژ: نامحدود') }}
                </span>
                <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                    {{ $ledger->max_transaction_amount > 0
                        ? __('سقف هر تراکنش: :amount ریال', ['amount' => number_format($ledger->max_transaction_amount)])
                        : __('سقف هر تراکنش: نامحدود') }}
                </span>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                    {{ __('آخرین بروزرسانی: :date', ['date' => $ledger->updated_at ? verta($ledger->updated_at)->format('Y/m/d H:i') : __('نامشخص')]) }}
                </span>
            </div>
        </div>
    </div>

    <form wire:submit.prevent="submit" class="space-y-4">
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-800">
                            {{ __('ثبت سریع تراکنش‌ها') }}
                        </h3>
                        <p class="text-xs text-slate-500">
                            {{ __('ردیف‌ها را کامل کنید؛ به محض تکمیل هر ردیف، ردیف جدیدی به صورت خودکار افزوده می‌شود.') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-500">
                        <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-1">
                            <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                            {{ __('ردیف در حال ویرایش') }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-md bg-green-100 px-2 py-1 text-green-700">
                            <span class="h-2 w-2 rounded-full bg-green-500"></span>
                            {{ __('ردیف آماده ثبت') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                @foreach($entries as $index => $entry)
                    @php
                        $smartState = $smartEntriesState[$index] ?? ['status' => 'idle'];
                    @endphp
                    @php
                        $rowNumber = $loop->iteration;
                        $isReady = !empty($entry['transaction_date']) && !empty($entry['amount']) && is_numeric($entry['amount']) && (float) $entry['amount'] > 0;
                        $isEditingRow = $index === 0 && $editingTransactionId;
                        $canRemove = count($entries) > 1 && !($loop->last && !$isReady);
                    @endphp
                    <div
                        wire:key="entry-card-{{ $index }}"
                        @class([
                            'rounded-xl border px-4 py-4 transition shadow-sm',
                            'border-green-300 bg-green-50' => $isReady,
                            'border-indigo-300 bg-indigo-50/60' => $isEditingRow,
                            'border-slate-200 bg-white' => !$isReady && !$isEditingRow,
                        ])
                    >
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-dashed border-slate-200 pb-3">
                            <div class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                    #{{ str_pad((string) $rowNumber, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                @if($isEditingRow)
                                    <span class="rounded-md bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                                        {{ __('ویرایش تراکنش موجود') }}
                                    </span>
                                @elseif($isReady)
                                    <span class="rounded-md bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">
                                        {{ __('آماده ثبت') }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">
                                        {{ __('لطفاً مبلغ و تاریخ را وارد کنید') }}
                                    </span>
                                @endif
                            </div>
                            @if($canRemove)
                                <button
                                    type="button"
                                    wire:click="removeRow({{ $index }})"
                                    class="inline-flex items-center gap-1 rounded-md border border-slate-300 px-2 py-1 text-xs font-medium text-slate-600 hover:border-red-400 hover:text-red-600"
                                >
                                    <i class="fas fa-times"></i>
                                    {{ __('حذف ردیف') }}
                                </button>
                            @endif
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-4">
                            <div class="space-y-2">
                                <label class="text-xs font-semibold text-slate-500">{{ __('نوع تراکنش') }}</label>
                                <select
                                    wire:model.lazy="entries.{{ $index }}.type"
                                    class="w-full rounded-lg border-slate-300 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-12 px-3"
                                >
                                    <option value="{{ \App\Models\PettyCashTransaction::TYPE_EXPENSE }}">{{ __('هزینه') }}</option>
                                    <option value="{{ \App\Models\PettyCashTransaction::TYPE_CHARGE }}">{{ __('شارژ') }}</option>
                                    @can('petty_cash.transaction.adjustment')
                                        <option value="{{ \App\Models\PettyCashTransaction::TYPE_ADJUSTMENT }}">{{ __('تعدیل') }}</option>
                                    @endcan
                                </select>
                                @error('entries.' . $index . '.type')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-semibold text-slate-500">{{ __('وضعیت') }}</label>
                                <select
                                    wire:model.lazy="entries.{{ $index }}.status"
                                    class="w-full rounded-lg border-slate-300 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-12 px-3"
                                >
                                    <option value="{{ \App\Models\PettyCashTransaction::STATUS_DRAFT }}">{{ __('پیشنویس') }}</option>
                                    <option value="{{ \App\Models\PettyCashTransaction::STATUS_SUBMITTED }}">{{ __('ارسال برای تایید') }}</option>
                                    @can('petty_cash.transaction.manage')
                                        <option value="{{ \App\Models\PettyCashTransaction::STATUS_APPROVED }}">{{ __('تایید شده') }}</option>
                                        <option value="{{ \App\Models\PettyCashTransaction::STATUS_NEEDS_CHANGES }}" disabled>
                                            {{ __('نیاز به اصلاح (توسط مدیریت)') }}
                                        </option>
                                        <option value="{{ \App\Models\PettyCashTransaction::STATUS_UNDER_REVIEW }}" disabled>
                                            {{ __('در حال بررسی مدیریت') }}
                                        </option>
                                        <option value="{{ \App\Models\PettyCashTransaction::STATUS_REJECTED }}" disabled>
                                            {{ __('رد شده توسط مدیریت') }}
                                        </option>
                                    @endcan
                                </select>
                                @error('entries.' . $index . '.status')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                                    <iconify-icon icon="lucide:coins" class="text-base text-emerald-500"></iconify-icon>
                                    {{ __('مبلغ (ریال)') }}
                                </label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        wire:model.live.debounce.500ms="entries.{{ $index }}.amount"
                                        inputmode="numeric"
                                        class="w-full rounded-lg border-slate-300 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-12 px-3"
                                        placeholder="0"
                                        x-data="{
                                            init() {
                                                // فرمت اولیه
                                                if (this.$el.value) {
                                                    let raw = this.$el.value.replace(/[^\d]/g, '');
                                                    this.$el.value = this.formatWithCommas(raw);
                                                }
                                            },
                                            formatWithCommas(value) {
                                                if (!value || value === '0') return '';
                                                // حذف همه چیز به جز اعداد
                                                let nums = String(value).replace(/[^\d]/g, '');
                                                // جداسازی با ممیز از راست به چپ (هر 3 رقم)
                                                return nums.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                                            }
                                        }"
                                        @input.stop="
                                            let input = $event.target;
                                            let cursorPos = input.selectionStart;
                                            let oldValue = input.value;
                                            let oldLength = oldValue.length;
                                            
                                            // حذف ممیزها و گرفتن فقط اعداد
                                            let rawValue = oldValue.replace(/[^\d]/g, '');
                                            
                                            // محدودیت 20 رقم
                                            if (rawValue.length > 20) {
                                                rawValue = rawValue.substring(0, 20);
                                            }
                                            
                                            // فرمت کردن با ممیز
                                            let formatted = rawValue ? rawValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '';
                                            
                                            // محاسبه تعداد ممیزها قبل از cursor
                                            let commasBefore = (oldValue.substring(0, cursorPos).match(/,/g) || []).length;
                                            let commasAfter = (formatted.substring(0, cursorPos).match(/,/g) || []).length;
                                            
                                            // تنظیم مقدار جدید
                                            input.value = formatted;
                                            
                                            // تنظیم cursor position
                                            let newCursorPos = cursorPos + (commasAfter - commasBefore);
                                            if (formatted.length < oldLength) {
                                                newCursorPos = cursorPos - (oldLength - formatted.length);
                                            }
                                            
                                            input.setSelectionRange(newCursorPos, newCursorPos);
                                            
                                            // ارسال به Livewire
                                            $wire.set('entries.{{ $index }}.amount', rawValue);
                                        "
                                    />
                                    @if(!empty($entry['amount']) && is_numeric($entry['amount']) && (float) $entry['amount'] > 0)
                                        @php
                                            $amount = (int) $entry['amount'];
                                            $amountData = \App\Helpers\NumberToWords::formatWithSeparatorsAndWordsWithToman($amount);
                                            $formattedRial = number_format($amount);
                                            $formattedToman = number_format(floor($amount / 10));
                                        @endphp
                                        <div class="mt-2 space-y-1.5 rounded-lg border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-3 shadow-sm">
                                            {{-- معادل ریال با فرمت --}}
                                            <div class="flex items-center gap-2">
                                                <iconify-icon icon="lucide:coins" class="text-base text-emerald-500"></iconify-icon>
                                                <span class="text-xs font-semibold text-slate-500">معادل ریال:</span>
                                                <span class="font-mono text-sm font-bold text-emerald-700">{{ $formattedRial }}</span>
                                                <span class="text-xs text-slate-400">ریال</span>
                                            </div>
                                            
                                            {{-- معادل تومان با فرمت --}}
                                            <div class="flex items-center gap-2">
                                                <iconify-icon icon="lucide:banknote" class="text-base text-indigo-500"></iconify-icon>
                                                <span class="text-xs font-semibold text-slate-500">معادل تومان:</span>
                                                <span class="font-mono text-sm font-bold text-indigo-700">{{ $formattedToman }}</span>
                                                <span class="text-xs text-slate-400">تومان</span>
                                            </div>
                                            
                                            {{-- معادل حروفی ریال --}}
                                            <div class="flex items-start gap-2 border-t border-slate-200 pt-2">
                                                <iconify-icon icon="lucide:text" class="text-base text-amber-500 mt-0.5"></iconify-icon>
                                                <div class="flex-1">
                                                    <span class="text-xs font-semibold text-slate-500">به حروف (ریال):</span>
                                                    <p class="mt-0.5 text-xs leading-relaxed text-slate-700">{{ $amountData['words'] }} ریال</p>
                                                </div>
                                            </div>
                                            
                                            {{-- معادل حروفی تومان --}}
                                            <div class="flex items-start gap-2">
                                                <iconify-icon icon="lucide:text" class="text-base text-purple-500 mt-0.5"></iconify-icon>
                                                <div class="flex-1">
                                                    <span class="text-xs font-semibold text-slate-500">به حروف (تومان):</span>
                                                    <p class="mt-0.5 text-xs leading-relaxed text-slate-700">{{ $amountData['toman_words'] }} تومان</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                @error('entries.' . $index . '.amount')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                                    <iconify-icon icon="lucide:calendar-clock" class="text-base text-indigo-500"></iconify-icon>
                                    {{ __('تاریخ و ساعت') }}
                                </label>
                                <div class="relative">
                                <input
                                    type="text"
                                    wire:model.lazy="entries.{{ $index }}.transaction_date"
                                        class="w-full rounded-lg border-slate-300 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-12 px-3 pr-10"
                                        placeholder="{{ __('1404/08/04 14:30') }}"
                                    x-data
                                        x-init="window.initPersianDatepicker($el, { enableTime: true })"
                                        dir="rtl"
                                    />
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                        <iconify-icon icon="lucide:calendar" class="text-lg"></iconify-icon>
                                    </div>
                                </div>
                                <p class="text-[10px] text-slate-500 flex items-center gap-1">
                                    <iconify-icon icon="lucide:info" class="text-xs"></iconify-icon>
                                    {{ __('فرمت: سال/ماه/روز ساعت:دقیقه (تاریخ شمسی)') }}
                                </p>
                                @error('entries.' . $index . '.transaction_date')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
                            <div class="space-y-2">
                                <label class="text-xs font-semibold text-slate-500">{{ __('شرح کوتاه') }}</label>
                                <input
                                    type="text"
                                    wire:model.lazy="entries.{{ $index }}.description"
                                    class="w-full rounded-lg border-slate-300 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-12 px-3"
                                    placeholder="{{ __('توضیح کوتاه...') }}"
                                />
                                @error('entries.' . $index . '.description')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-semibold text-slate-500">
                                    {{ __('شماره مرجع') }}
                                    <span class="text-[10px] font-normal text-slate-400">(دستی/خودکار)</span>
                                </label>
                                <div class="relative">
                                <input
                                    type="text"
                                    wire:model.lazy="entries.{{ $index }}.reference_number"
                                        class="w-full rounded-lg border-slate-300 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-12 px-3 pr-10"
                                        placeholder="{{ __('خودکار یا دستی...') }}"
                                        title="{{ __('می‌توانید شماره مرجع را به صورت دستی وارد کنید یا خالی بگذارید تا خودکار تولید شود.') }}"
                                    />
                                    @if(!empty($entries[$index]['reference_number']))
                                        <div class="absolute left-3 top-1/2 -translate-y-1/2">
                                            <iconify-icon icon="lucide:check-circle" class="text-lg text-green-500"></iconify-icon>
                                        </div>
                                    @else
                                        <div class="absolute left-3 top-1/2 -translate-y-1/2">
                                            <iconify-icon icon="lucide:hash" class="text-lg text-slate-400"></iconify-icon>
                                        </div>
                                    @endif
                                </div>
                                @error('entries.' . $index . '.reference_number')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="text-[10px] text-slate-400">
                                    {{ __('در صورت خالی بودن، شماره به صورت خودکار و مسلسل تولید می‌شود') }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-semibold text-slate-500">{{ __('دسته‌بندی') }}</label>
                                <select
                                    wire:model.lazy="entries.{{ $index }}.category"
                                    @class([
                                        'w-full rounded-lg text-base shadow-sm h-12 px-3',
                                        'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500' => ($smartState['category_status'] ?? null) !== 'manual_required',
                                        'border-rose-300 focus:border-rose-500 focus:ring-rose-500' => ($smartState['category_status'] ?? null) === 'manual_required',
                                    ])
                                >
                                    <option value="">{{ __('انتخاب دسته‌بندی') }}</option>
                                    @foreach($this->getItemCategories() as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('entries.' . $index . '.category')
                                    <p class="text-xs text-red-600 flex items-center gap-1 mt-1">
                                        <iconify-icon icon="lucide:alert-circle" class="text-sm"></iconify-icon>
                                        <span>{{ __('انتخاب دسته‌بندی الزامی است') }}</span>
                                    </p>
                                @enderror
                                @if(($smartState['category_status'] ?? null) === 'manual_required')
                                    <div class="mt-1 text-xs text-rose-600">{{ __('دسته‌بندی به صورت خودکار تشخیص داده نشد. لطفاً از لیست بالا انتخاب کنید.') }}</div>
                                @endif
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-semibold text-slate-500">{{ __('واحد پول') }}</label>
                                <input
                                    type="text"
                                    class="w-full rounded-lg border-slate-300 bg-slate-100 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-12 px-3"
                                    value="{{ __('ریال') }}"
                                    readonly
                                />
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-xs font-semibold text-slate-500">{{ __('پیوست فاکتور') }}</label>
                                <label
                                    x-data="{dragging:false}"
                                    x-on:dragover.prevent="dragging=true"
                                    x-on:dragleave.prevent="dragging=false"
                                    x-on:drop.prevent="dragging=false; $refs.invoice.files = event.dataTransfer.files; $refs.invoice.dispatchEvent(new Event('change', { bubbles: true }));"
                                    :class="dragging ? 'border-indigo-400 bg-indigo-50 text-indigo-600' : ''"
                                    class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border border-dashed border-slate-300 px-4 py-4 text-sm font-medium text-slate-600 transition hover:border-indigo-400 hover:text-indigo-600"
                                >
                                    <input
                                        type="file"
                                        accept="image/*"
                                        capture="environment"
                                        class="hidden"
                                        wire:model="entries.{{ $index }}.invoice_attachment"
                                        x-ref="invoice"
                                    >
                                    <i class="fas fa-cloud-upload-alt text-lg"></i>
                                    <span>{{ __('بارگذاری یا رها کردن فایل') }}</span>
                                    <span class="text-xs text-slate-400">{{ __('پشتیبانی از دوربین موبایل و درگ/دراپ') }}</span>
                                </label>
                                @error('entries.' . $index . '.invoice_attachment')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <div wire:loading wire:target="entries.{{ $index }}.invoice_attachment" class="text-xs text-slate-400">
                                    {{ __('در حال بارگذاری...') }}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-semibold text-slate-500">{{ __('پیوست رسید') }}</label>
                                <label
                                    x-data="{dragging:false}"
                                    x-on:dragover.prevent="dragging=true"
                                    x-on:dragleave.prevent="dragging=false"
                                    x-on:drop.prevent="dragging=false; $refs.receipt.files = event.dataTransfer.files; $refs.receipt.dispatchEvent(new Event('change', { bubbles: true }));"
                                    :class="dragging ? 'border-indigo-400 bg-indigo-50 text-indigo-600' : ''"
                                    class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border border-dashed border-slate-300 px-4 py-4 text-sm font-medium text-slate-600 transition hover:border-indigo-400 hover:text-indigo-600"
                                >
                                    <input
                                        type="file"
                                        accept="image/*"
                                        capture="environment"
                                        class="hidden"
                                        wire:model="entries.{{ $index }}.receipt_attachment"
                                        x-ref="receipt"
                                    >
                                    <i class="fas fa-cloud-upload-alt text-lg"></i>
                                    <span>{{ __('بارگذاری یا رها کردن فایل') }}</span>
                                    <span class="text-xs text-slate-400">{{ __('امکان عکس‌برداری مستقیم از موبایل') }}</span>
                                </label>
                                @error('entries.' . $index . '.receipt_attachment')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <div wire:loading wire:target="entries.{{ $index }}.receipt_attachment" class="text-xs text-slate-400">
                                    {{ __('در حال بارگذاری...') }}
                                </div>
                            </div>

                            @can('petty_cash.transaction.manage')
                                <div class="md:col-span-2 space-y-2">
                                    <label class="text-xs font-semibold text-slate-500">{{ __('یادداشت مدیر (اختیاری)') }}</label>
                                    <textarea
                                        rows="3"
                                        wire:model.defer="entries.{{ $index }}.manager_note"
                                        class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                                        placeholder="{{ __('توضیح یا نکته‌ای که باید در گزارش دیده شود...') }}"
                                    ></textarea>
                                    @error('entries.' . $index . '.manager_note')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="text-[11px] text-slate-400">{{ __('در صورت تایید یا رد تراکنش، این یادداشت در گزارش نمایش داده می‌شود.') }}</p>
                                </div>
                            @endcan

                            <div class="md:col-span-2 rounded-xl border border-dashed border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-slate-600">

                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            wire:click="runSmartExtraction({{ $index }})"
                                            wire:loading.attr="disabled"
                                            wire:target="runSmartExtraction({{ $index }})"
                                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-70"
                                        >
                                            <i class="fas fa-magic ml-1"></i>
                                            {{ __('smart_invoice.auto_fill') }}
                                        </button>
                                        <div
                                            wire:loading.flex
                                            wire:target="runSmartExtraction({{ $index }})"
                                            class="items-center gap-2 text-xs text-indigo-700"
                                        >
                                            <i class="fas fa-spinner fa-spin ml-1"></i>
                                            {{ __('smart_invoice.processing') }}
                                        </div>
                                    </div>
                                    @if(($smartState['status'] ?? null) === 'success')
                                        <div class="space-y-2">
                                            <div class="flex flex-wrap items-center gap-2 text-xs text-green-700">
                                                <span><i class="fas fa-check-circle ml-1"></i>{{ $smartState['message'] ?? __('smart_invoice.extraction_success') }}</span>
                                                @if(isset($smartState['confidence']))
                                                    <span class="rounded-md bg-green-100 px-2 py-1 text-[11px] font-semibold text-green-800">
                                                        {{ __('smart_invoice.confidence', ['value' => number_format(($smartState['confidence'] ?? 0) * 100, 1)]) }}
                                                    </span>
                                                    @if(($smartState['confidence'] ?? 0) < config('smart-invoice.confidence_threshold', 0.6))
                                                        <span class="rounded-md bg-amber-100 px-2 py-1 text-[11px] font-semibold text-amber-800">
                                                            {{ __('smart_invoice.low_confidence_hint') }}
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                            @if(isset($smartState['extracted_data']))
                                                <div class="rounded-md bg-blue-50 p-3 text-xs">
                                                    <div class="font-semibold text-blue-800 mb-2">{{ __('اطلاعات استخراج شده:') }}</div>
                                                    <div class="space-y-1 text-blue-700">
                                                        @if(isset($smartState['extracted_data']['total_amount']))
                                                            <div><strong>مبلغ:</strong> {{ number_format($smartState['extracted_data']['total_amount']) }} ریال</div>
                                                        @endif
                                                        @if(isset($smartState['extracted_data']['vendor_name']) && $smartState['extracted_data']['vendor_name'])
                                                            <div><strong>فروشنده:</strong> {{ $smartState['extracted_data']['vendor_name'] }}</div>
                                                        @else
                                                            <div><strong>فروشنده:</strong> <span class="text-gray-500">یافت نشد</span></div>
                                                        @endif
                                                        @if(isset($smartState['extracted_data']['invoice_number']) && $smartState['extracted_data']['invoice_number'])
                                                            <div><strong>شماره فاکتور:</strong> {{ $smartState['extracted_data']['invoice_number'] }}</div>
                                                        @else
                                                            <div><strong>شماره فاکتور:</strong> <span class="text-gray-500">یافت نشد</span></div>
                                                        @endif
                                                        @if(isset($smartState['extracted_data']['reference_number']) && $smartState['extracted_data']['reference_number'])
                                                            <div><strong>شماره مرجع:</strong> {{ $smartState['extracted_data']['reference_number'] }}</div>
                                                        @else
                                                            <div><strong>شماره مرجع:</strong> <span class="text-gray-500">یافت نشد</span></div>
                                                        @endif
                                                        @if(isset($smartState['extracted_data']['issued_at']))
                                                            <div><strong>تاریخ:</strong> {{ $smartState['extracted_data']['issued_at'] }}</div>
                                                        @endif
                                                        @if(isset($smartState['extracted_data']['currency']))
                                                            <div><strong>واحد پول:</strong> {{ $smartState['extracted_data']['currency'] }}</div>
                                                        @endif
                                                        
                                                        @if(isset($smartState['amount_verification']) && $smartState['amount_verification']['has_discrepancy'])
                                                            <div class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded text-xs">
                                                                <div class="flex items-center text-yellow-800">
                                                                    <i class="fas fa-exclamation-triangle ml-1"></i>
                                                                    <strong>اخطار: اختلاف در مبالغ</strong>
                                                                </div>
                                                            <div class="mt-1 text-yellow-700">
                                                                <div>مجموع محاسبه شده: {{ number_format($smartState['amount_verification']['calculated_total']) }} ریال</div>
                                                                <div>مجموع استخراج شده: {{ number_format($smartState['amount_verification']['extracted_total']) }} ریال</div>
                                                                <div>اختلاف: {{ number_format($smartState['amount_verification']['discrepancy_amount']) }} ریال</div>
                                                                @if(isset($smartState['amount_verification']['tolerance']))
                                                                    <div>آستانه مجاز: {{ number_format($smartState['amount_verification']['tolerance']) }} ریال</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @elseif(isset($smartState['amount_verification']))
                                                            <div class="mt-2 p-2 bg-green-50 border border-green-200 rounded text-xs">
                                                                <div class="flex items-center text-green-800">
                                                                    <i class="fas fa-check-circle ml-1"></i>
                                                                    <strong>مبالغ تایید شد</strong>
                                                                </div>
                                                                <div class="mt-1 text-green-700">
                                                                    مجموع: {{ number_format($smartState['amount_verification']['calculated_total'] ?: $smartState['amount_verification']['extracted_total']) }} ریال
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @if(!empty($smartState['extracted_data']['analytics']['validation']['issues']))
                                                            <div class="mt-2 p-2 bg-amber-50 border border-amber-200 rounded text-xs text-amber-800">
                                                                <div class="font-semibold mb-1">موارد نیازمند بررسی:</div>
                                                                @foreach($smartState['extracted_data']['analytics']['validation']['issues'] as $issue)
                                                                    <div>- {{ $issue }}</div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        @if(($smartState['category_status'] ?? null) === 'manual_required')
                                                            <div class="mt-2 p-2 bg-rose-50 border border-rose-200 rounded text-xs text-rose-700">
                                                                <strong>دسته‌بندی انتخاب نشده است.</strong>
                                                                <div class="mt-1">لطفاً یکی از دسته‌بندی‌ها را از لیست انتخاب کنید تا گزارش‌ها و داشبورد دقیق محاسبه شوند.</div>
                                                            </div>
                                                        @elseif(($smartState['category_status'] ?? null) === 'ready' && !empty($smartState['category_message']))
                                                            <div class="mt-2 p-2 bg-emerald-50 border border-emerald-200 rounded text-xs text-emerald-700">
                                                                {{ $smartState['category_message'] }}
                                                            </div>
                                                        @endif
                                                        @php
                                                            $extractedData = $smartState['extracted_data'] ?? [];
                                                            $structuredItems = $extractedData['items_details']['item_structure'] ?? [];

                                                            if (empty($structuredItems) && !empty($extractedData['line_items'])) {
                                                                $structuredItems = array_map(function ($item, $index) {
                                                                    return [
                                                                        'row_number' => ($item['row_number'] ?? $index + 1),
                                                                        'product_or_service_description_fa' => $item['description'] ?? null,
                                                                        'quantity_numerical' => $item['quantity'] ?? null,
                                                                        'unit_fa' => $item['unit'] ?? null,
                                                                        'unit_price_in_rial_numerical' => $item['unit_price'] ?? null,
                                                                        'discount_per_item_in_rial_numerical' => $item['discount'] ?? null,
                                                                        'total_after_discount_in_rial_numerical' => $item['total'] ?? null,
                                                                    ];
                                                                }, $extractedData['line_items'], array_keys($extractedData['line_items']));
                                                            }
                                                        @endphp
                                                        
                                                        @if(!empty($structuredItems))
                                                            <div><strong>آیتم‌های خریداری شده:</strong></div>
                                                            <div class="mt-2 overflow-x-auto">
                                                                <table class="w-full text-xs border border-gray-200 rounded">
                                                                    <thead class="bg-gray-50">
                                                                        <tr>
                                                                            <th class="px-2 py-1 border-b text-right">ردیف</th>
                                                                            <th class="px-2 py-1 border-b text-right">شرح کالا/خدمات</th>
                                                                            <th class="px-2 py-1 border-b text-center">تعداد</th>
                                                                            <th class="px-2 py-1 border-b text-center">واحد</th>
                                                                            <th class="px-2 py-1 border-b text-left">قیمت واحد</th>
                                                                            <th class="px-2 py-1 border-b text-left">تخفیف</th>
                                                                                <th class="px-2 py-1 border-b text-left">مجموع</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                        @foreach($structuredItems as $item)
                                                                            <tr class="border-b hover:bg-gray-50">
                                                                                <td class="px-2 py-1 text-center">{{ $item['row_number'] ?? '-' }}</td>
                                                                                <td class="px-2 py-1 text-right">{{ $item['product_or_service_description_fa'] ?? 'نامشخص' }}</td>
                                                                                <td class="px-2 py-1 text-center">{{ $item['quantity_numerical'] ?? '-' }}</td>
                                                                                <td class="px-2 py-1 text-center">{{ $item['unit_fa'] ?? '-' }}</td>
                                                                                <td class="px-2 py-1 text-left">
                                                                                    @if(isset($item['unit_price_in_rial_numerical']))
                                                                                        {{ number_format($item['unit_price_in_rial_numerical']) }} ریال
                                                                                    @else
                                                                                        -
                                                                                    @endif
                                                                                </td>
                                                                                <td class="px-2 py-1 text-left">
                                                                                    @if(isset($item['discount_per_item_in_rial_numerical']) && $item['discount_per_item_in_rial_numerical'] > 0)
                                                                                        <span class="text-red-600">{{ number_format($item['discount_per_item_in_rial_numerical']) }} ریال</span>
                                                                                    @else
                                                                                        -
                                                                                    @endif
                                                                                </td>
                                                                                <td class="px-2 py-1 text-left">
                                                                                    @if(isset($item['total_after_discount_in_rial_numerical']) && $item['total_after_discount_in_rial_numerical'])
                                                                                        {{ number_format($item['total_after_discount_in_rial_numerical']) }} ریال
                                                                                    @elseif(isset($item['total_price_in_rial_numerical']))
                                                                                        {{ number_format($item['total_price_in_rial_numerical']) }} ریال
                                                                                    @else
                                                                                        -
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                        
                                                                        @if($extractedData && isset($extractedData['financial_summary']['vat_and_tolls_amount_in_rial_numerical']) && $extractedData['financial_summary']['vat_and_tolls_amount_in_rial_numerical'])
                                                                            <tr class="border-b hover:bg-gray-50 bg-yellow-50">
                                                                                <td class="px-2 py-1 text-center">مالیات</td>
                                                                                <td class="px-2 py-1 text-right">مالیات و عوارض ارزش افزوده</td>
                                                                                <td class="px-2 py-1 text-center">-</td>
                                                                                <td class="px-2 py-1 text-center">-</td>
                                                                                <td class="px-2 py-1 text-left">-</td>
                                                                                <td class="px-2 py-1 text-left">-</td>
                                                                                <td class="px-2 py-1 text-left font-semibold text-yellow-700">
                                                                                    {{ number_format($extractedData['financial_summary']['vat_and_tolls_amount_in_rial_numerical']) }} ریال
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                        
                                                                        @if($extractedData && isset($extractedData['financial_summary']['final_amount_in_rial_numerical']) && $extractedData['financial_summary']['final_amount_in_rial_numerical'])
                                                                            <tr class="border-b bg-green-50 font-bold">
                                                                                <td class="px-2 py-1 text-center">مجموع</td>
                                                                                <td class="px-2 py-1 text-right">مبلغ نهایی قابل پرداخت</td>
                                                                                <td class="px-2 py-1 text-center">-</td>
                                                                                <td class="px-2 py-1 text-center">-</td>
                                                                                <td class="px-2 py-1 text-left">-</td>
                                                                                <td class="px-2 py-1 text-left">-</td>
                                                                                <td class="px-2 py-1 text-left font-bold text-green-700">
                                                                                    {{ number_format($extractedData['financial_summary']['final_amount_in_rial_numerical']) }} ریال
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @elseif(isset($smartState['extracted_data']['line_items']) && count($smartState['extracted_data']['line_items']) > 0)
                                                            <div><strong>آیتم‌ها:</strong> {{ count($smartState['extracted_data']['line_items']) }} مورد</div>
                                                            <div class="mt-2 space-y-1">
                                                                @foreach($smartState['extracted_data']['line_items'] as $item)
                                                                    <div class="text-xs bg-gray-100 p-2 rounded">
                                                                        <div><strong>شرح:</strong> {{ $item['description'] ?? 'نامشخص' }}</div>
                                                                        @if(isset($item['quantity']))
                                                                            <div><strong>تعداد:</strong> {{ $item['quantity'] }}</div>
                                                                        @endif
                                                                        @if(isset($item['unit_price']))
                                                                            <div><strong>قیمت واحد:</strong> {{ $item['unit_price'] }}</div>
                                                                        @endif
                                                                        @if(isset($item['total']))
                                                                            <div><strong>مجموع:</strong> {{ $item['total'] }}</div>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- Apply Data Button -->
                                                    <div class="mt-3">
                                                        <button 
                                                            type="button" 
                                                            wire:click="applyExtractedData({{ $index }})"
                                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-medium"
                                                        >
                                                            {{ __('اعمال اطلاعات به فرم') }}
                                                        </button>
                                                    </div>

                                                    @php
                                                        $summaryPreview = [
                                                            'شماره فاکتور' => $smartState['extracted_data']['invoice_number'] ?? ($smartState['summary']['invoice_number'] ?? null),
                                                            'شماره مرجع' => $smartState['extracted_data']['reference_number'] ?? ($smartState['summary']['reference_number'] ?? null),
                                                            'فروشنده' => $smartState['extracted_data']['vendor_name'] ?? ($smartState['summary']['vendor_name'] ?? null),
                                                            'تاریخ' => $smartState['extracted_data']['issued_at'] ?? ($smartState['summary']['issued_at'] ?? null),
                                                            'مبلغ کل (ریال)' => $smartState['extracted_data']['total_amount'] ?? ($smartState['summary']['total_amount'] ?? null),
                                                            'مالیات (ریال)' => $smartState['extracted_data']['financial_summary']['vat_and_tolls_amount_in_rial_numerical'] ?? null,
                                                            'تخفیف (ریال)' => $smartState['extracted_data']['financial_summary']['total_discount_in_rial_numerical'] ?? null,
                                                        ];
                                                        $hasPreviewData = collect($summaryPreview)->contains(fn($value) => filled($value));
                                                    @endphp
                                                    @if($hasPreviewData)
                                                        <div class="mt-3">
                                                            <table class="w-full text-xs border border-dashed border-slate-300 rounded-md bg-slate-50">
                                                                <tbody>
                                                                    @foreach($summaryPreview as $label => $value)
                                                                        <tr class="border-b border-slate-200 last:border-b-0">
                                                                            <td class="px-3 py-2 text-slate-500">{{ $label }}</td>
                                                                            <td class="px-3 py-2 font-semibold text-slate-700">{{ $value !== null && $value !== '' ? $value : '—' }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif

                                                    @php
                                                        $rawPayload = $smartState['debug_payload'] ?? $smartState['raw_payload'] ?? ($smartState['extracted_data']['raw_payload'] ?? []);
                                                    @endphp
                                                    @if(!empty($rawPayload))
                                                        <details class="mt-3 bg-slate-50 border border-slate-200 rounded p-2 text-xs text-slate-700">
                                                            <summary class="cursor-pointer font-semibold text-slate-800">
                                                                نمایش JSON برگشتی سرویس
                                                            </summary>
                                                            <pre class="mt-2 overflow-auto max-h-60 whitespace-pre-wrap text-left">{{ json_encode($rawPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                                        </details>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @elseif(($smartState['status'] ?? null) === 'error')
                                        <p class="text-xs text-red-600">
                                            <i class="fas fa-exclamation-circle ml-1"></i>
                                            {{ $smartState['message'] ?? __('smart_invoice.processing_failed') }}
                                        </p>
                                    @elseif(($smartState['status'] ?? null) === 'loading')
                                        <p class="text-xs text-indigo-700">
                                            <i class="fas fa-spinner fa-spin ml-1"></i>
                                            {{ __('smart_invoice.processing') }}
                                        </p>
                                    @else
                                        <p class="text-xs text-slate-500">
                                            {{ __('smart_invoice.helper_text') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @error('entries')
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                {{ $message }}
            </div>
        @enderror

        @php
            $readyRows = collect($entries)->filter(function ($entry) {
                return !empty($entry['transaction_date']) && !empty($entry['amount']) && is_numeric($entry['amount']) && (float) $entry['amount'] > 0;
            });
            $readyCount = $readyRows->count();
            $readyTotal = $readyRows->sum(fn ($entry) => (float) $entry['amount']);
        @endphp

        <div class="flex flex-col gap-3 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-600 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <span class="font-semibold text-slate-700">
                    {{ __('ردیف‌های آماده ثبت: :count', ['count' => $readyCount]) }}
                </span>
                <span>
                    {{ __('جمع مبالغ آماده: :amount ریال', ['amount' => number_format($readyTotal)]) }}
                </span>
            </div>
            <div class="text-xs text-slate-500">
                {{ __('پس از ثبت، جدول تراکنش‌ها به صورت خودکار تازه‌سازی می‌شود.') }}
            </div>
        </div>

        <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:justify-end">
            <button
                type="button"
                wire:click="resetFormState"
                class="inline-flex items-center justify-center rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50"
            >
                <i class="fas fa-undo ml-2"></i>
                {{ __('پاک‌سازی فرم') }}
            </button>

            <button
                type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                wire:loading.attr="disabled"
            >
                <i class="fas fa-save ml-2"></i>
                {{ $editingTransactionId ? __('به‌روزرسانی تراکنش') : __('ثبت ردیف‌های کامل شده') }}
            </button>
        </div>

        <div wire:loading.flex class="rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
            <i class="fas fa-spinner fa-spin ml-2"></i>
            {{ __('در حال پردازش اطلاعات... لطفاً منتظر بمانید.') }}
        </div>
    </form>
</div>

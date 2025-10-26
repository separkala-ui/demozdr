<div class="space-y-4">
    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <!-- فیلترهای دوره زمانی -->
        <div class="mb-4 flex flex-wrap gap-2">
            @foreach($periodOptions as $value => $label)
                <button
                    wire:click="setPeriod('{{ $value }}')"
                    @class([
                        'rounded-md px-3 py-2 text-sm font-medium transition-colors',
                        'bg-indigo-100 text-indigo-700 hover:bg-indigo-200' => $period === $value,
                        'bg-slate-100 text-slate-700 hover:bg-slate-200' => $period !== $value,
                    ])
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-6">
            <div>
                <label class="block text-xs font-semibold text-slate-500">{{ __('وضعیت') }}</label>
                <select wire:model.live="status" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('همه') }}</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500">{{ __('نوع') }}</label>
                <select wire:model.live="type" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('همه') }}</option>
                    @foreach($typeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500">{{ __('از تاریخ') }}</label>
                <input
                    type="text"
                    wire:model.live="dateFrom"
                    class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow focus:border-indigo-500 focus:ring-indigo-500 jalali-date-input"
                    placeholder="{{ __('مثال: 1404-07-27') }}"
                    x-data
                    x-init="window.initJalaliDatepicker($el, { enableTime: false })"
                />
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500">{{ __('تا تاریخ') }}</label>
                <input
                    type="text"
                    wire:model.live="dateTo"
                    class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow focus:border-indigo-500 focus:ring-indigo-500 jalali-date-input"
                    placeholder="{{ __('مثال: 1404-08-01') }}"
                    x-data
                    x-init="window.initJalaliDatepicker($el, { enableTime: false })"
                />
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500">{{ __('جستجو') }}</label>
                <input type="search" wire:model.live="search" placeholder="{{ __('شرح یا شماره مرجع') }}" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow focus:border-indigo-500 focus:ring-indigo-500" />
            </div>
            <div class="flex items-end">
                <a href="{{ route('admin.petty-cash.print', $ledger->id) . '?' . http_build_query(['period' => $period, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <i class="fas fa-print text-white"></i>
                    {{ __('چاپ لیست') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Pending Charge Requests -->
    @if($pendingChargeRequests->count() > 0)
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">
                        {{ $pendingChargeRequests->count() }} {{ __('درخواست باز') }}
                    </span>
                    <h3 class="text-base font-semibold text-amber-800">{{ __('درخواست‌های شارژ در انتظار تایید') }}</h3>
                </div>
            </div>
            <p class="mt-1 text-xs text-amber-600">{{ __('درخواست‌های ثبت شده کاربران بدون تایید در این بخش نمایش داده می‌شود.') }}</p>
            
            <div class="mt-4 space-y-3">
                @foreach($pendingChargeRequests as $request)
                    <div class="rounded-lg border border-amber-200 bg-white p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <p class="text-amber-600">{{ __('مبلغ درخواست شده') }}</p>
                                        <p class="font-semibold text-slate-800">{{ number_format($request->amount) }} {{ __('ریال') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-amber-600">{{ __('وضعیت') }}</p>
                                        <p class="font-semibold text-slate-800">{{ $statusOptions[$request->status] ?? $request->status }}</p>
                                    </div>
                                    <div>
                                        <p class="text-amber-600">{{ __('درخواست دهنده') }}</p>
                                        <p class="font-semibold text-slate-800">
                                            {{ $request->requester->name ?? __('نامشخص') }}
                                            @if($request->requester)
                                                <br><span class="text-xs text-slate-500">{{ $request->requester->branch->name ?? __('بدون شعبه') }}</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-amber-600">{{ __('تاریخ ثبت') }}</p>
                                        <p class="font-semibold text-slate-800">{{ verta($request->created_at)->format('H:i Y/m/d') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-amber-600">{{ __('شماره کارت') }}</p>
                                        <p class="font-semibold text-slate-800">{{ $request->ledger->card_number ?? __('نامشخص') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-amber-600">{{ __('شعبه') }}</p>
                                        <p class="font-semibold text-slate-800">{{ $request->ledger->branch_name ?? __('نامشخص') }}</p>
                                    </div>
                                </div>
                                
                                @if($request->meta && isset($request->meta['charge_request']['note']))
                                    <p class="mt-2 rounded bg-slate-50 p-2 text-[11px] text-slate-600">{{ __('توضیح کاربر') }}: {{ $request->meta['charge_request']['note'] }}</p>
                                @endif
                                
                                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                                    <button type="button"
                                            wire:click="requestEdit({{ $request->id }})"
                                            class="inline-flex items-center gap-1 rounded-md border border-slate-200 px-2 py-1 font-medium text-slate-600 hover:bg-slate-100">
                                        <i class="fas fa-pen"></i>
                                        {{ __('بررسی در فرم') }}
                                    </button>
                                    <button type="button"
                                            wire:click="requestApprove({{ $request->id }})"
                                            class="inline-flex items-center gap-1 rounded-md bg-emerald-500 px-2 py-1 font-medium text-white hover:bg-emerald-600">
                                        <i class="fas fa-check"></i>
                                        {{ __('تایید فوری') }}
                                    </button>
                                    <button type="button"
                                            wire:click="requestDelete({{ $request->id }})"
                                            class="inline-flex items-center gap-1 rounded-md bg-rose-500 px-2 py-1 font-medium text-white hover:bg-rose-600">
                                        <i class="fas fa-times"></i>
                                        {{ __('حذف/لغو') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Mobile cards -->
    <div class="space-y-3 rounded-lg border border-slate-200 bg-white p-3 shadow-sm md:hidden">
        @forelse($transactions as $transaction)
            <div class="rounded-xl border border-slate-100 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold text-slate-800">
                            {{ verta($transaction->transaction_date)->format('Y/m/d') }}
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ verta($transaction->transaction_date)->format('H:i') }}
                        </div>
                    </div>
                    <span @class([
                        'inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold',
                        'bg-yellow-100 text-yellow-700' => $transaction->status === \App\Models\PettyCashTransaction::STATUS_SUBMITTED,
                        'bg-green-100 text-green-700' => $transaction->status === \App\Models\PettyCashTransaction::STATUS_APPROVED,
                        'bg-slate-100 text-slate-700' => $transaction->status === \App\Models\PettyCashTransaction::STATUS_DRAFT,
                        'bg-red-100 text-red-700' => $transaction->status === \App\Models\PettyCashTransaction::STATUS_REJECTED,
                        'bg-amber-100 text-amber-700' => $transaction->status === \App\Models\PettyCashTransaction::STATUS_NEEDS_CHANGES,
                        'bg-purple-100 text-purple-700' => $transaction->status === \App\Models\PettyCashTransaction::STATUS_UNDER_REVIEW,
                    ])>
                        {{ $statusOptions[$transaction->status] ?? $transaction->status }}
                    </span>
                </div>

                <div class="mt-3 grid grid-cols-1 gap-2 text-sm text-slate-600">
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('نوع') }}</span>
                        <span class="font-semibold text-slate-700">{{ $typeOptions[$transaction->type] ?? $transaction->type }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('مبلغ') }}</span>
                        <span class="font-semibold text-slate-700">
                            {{ number_format($transaction->amount) }} {{ __('ریال') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-500">{{ __('شرح') }}:</span>
                        <div class="mt-1 text-sm text-slate-700">
                            {{ $transaction->description ?: '---' }}
                        </div>
                    </div>
                    <div class="flex justify-between text-xs text-slate-500">
                        <span>{{ __('شماره مرجع') }}</span>
                        <span>{{ $transaction->reference_number ?: '---' }}</span>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                    <button type="button"
                            wire:click="showPreview({{ $transaction->id }})"
                            class="inline-flex items-center gap-1 rounded bg-indigo-100 px-2 py-1 text-indigo-600 hover:bg-indigo-200">
                        <iconify-icon icon="lucide:eye" class="text-base"></iconify-icon>
                        {{ __('نمایش') }}
                    </button>
                    @if($transaction->getFirstMediaUrl('invoice'))
                        <a href="{{ $transaction->getFirstMediaUrl('invoice') }}" target="_blank" class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-1 text-slate-600 hover:bg-slate-200">
                            <iconify-icon icon="lucide:file-text" class="text-base"></iconify-icon>
                            {{ __('فاکتور') }}
                        </a>
                    @endif
                    @if($transaction->getFirstMediaUrl('bank_receipt'))
                        <a href="{{ $transaction->getFirstMediaUrl('bank_receipt') }}" target="_blank" class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-1 text-slate-600 hover:bg-slate-200">
                            <iconify-icon icon="lucide:receipt" class="text-base"></iconify-icon>
                            {{ __('رسید بانکی') }}
                        </a>
                    @endif
                    @if($transaction->getFirstMediaUrl('charge_request'))
                        <a href="{{ $transaction->getFirstMediaUrl('charge_request') }}" target="_blank" class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-1 text-slate-600 hover:bg-slate-200">
                            <iconify-icon icon="lucide:folder" class="text-base"></iconify-icon>
                            {{ __('مستند درخواست شارژ') }}
                        </a>
                    @endif
                </div>

                @php
                    $isManager = auth()->user()?->hasRole(['Superadmin', 'Admin']);
                    $canEditTransaction = $this->canEditTransaction($transaction);
                @endphp
                <div class="mt-3 flex flex-wrap gap-3 text-xs font-semibold">
                    @if($canEditTransaction)
                        <button type="button" wire:click="requestEdit({{ $transaction->id }})" class="text-indigo-600 hover:text-indigo-800">
                            {{ __('ویرایش') }}
                        </button>
                    @endif

                    @if($isManager)
                        <button type="button" wire:click="requestApprove({{ $transaction->id }})" class="text-green-600 hover:text-green-800">
                            {{ __('تایید') }}
                        </button>
                        <button type="button" wire:click="requestRevision({{ $transaction->id }})" class="text-amber-600 hover:text-amber-800">
                            {{ __('ارسال برای بازبینی') }}
                        </button>
                        <button type="button" wire:click="markSuspicious({{ $transaction->id }})" class="text-purple-600 hover:text-purple-800">
                            {{ __('رفتار مشکوک') }}
                        </button>
                        <button type="button" wire:click="requestReject({{ $transaction->id }})" class="text-rose-600 hover:text-rose-800">
                            {{ __('رد کردن') }}
                        </button>
                        <button type="button" onclick="return confirm('{{ __('آیا از حذف این تراکنش مطمئن هستید؟') }}')" wire:click="requestDelete({{ $transaction->id }})" class="text-red-600 hover:text-red-800">
                            {{ __('حذف') }}
                        </button>
                    @endif
                </div>

                @if($transaction->status === \App\Models\PettyCashTransaction::STATUS_NEEDS_CHANGES)
                    <p class="mt-2 text-xs font-medium text-amber-600">
                        {{ __('این تراکنش برای بازبینی به شما بازگردانده شده است. لطفاً اصلاحات لازم را اعمال و دوباره ارسال کنید.') }}
                    </p>
                @endif

                @if(($transaction->meta['suspicious'] ?? false) === true)
                    <p class="mt-1 text-xs font-medium text-purple-600">
                        {{ __('این تراکنش به‌عنوان رفتار مشکوک علامت‌گذاری شده و در حال بررسی مدیریت است.') }}
                    </p>
                @endif
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">
                {{ __('تراکنشی یافت نشد.') }}
            </div>
        @endforelse

        <div>
            {{ $transactions->links() }}
        </div>
    </div>

    <!-- Desktop table -->
    <div class="hidden overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm md:block">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                <tr>
                    <th class="px-4 py-3 text-right">{{ __('تاریخ') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('نوع') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('وضعیت') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('مبلغ') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('شرح') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('مرجع') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('عملیات') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                @forelse($transactions as $transaction)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium">{{ verta($transaction->transaction_date)->format('Y/m/d') }}</div>
                            <div class="text-xs text-slate-500">{{ verta($transaction->transaction_date)->format('H:i') }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                                {{ $typeOptions[$transaction->type] ?? $transaction->type }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                        <span @class([
                            'inline-flex rounded-full px-2 py-1 text-xs font-medium',
                            'bg-yellow-100 text-yellow-700' => $transaction->status === \App\Models\PettyCashTransaction::STATUS_SUBMITTED,
                            'bg-green-100 text-green-700' => $transaction->status === \App\Models\PettyCashTransaction::STATUS_APPROVED,
                            'bg-slate-100 text-slate-700' => $transaction->status === \App\Models\PettyCashTransaction::STATUS_DRAFT,
                            'bg-red-100 text-red-700' => $transaction->status === \App\Models\PettyCashTransaction::STATUS_REJECTED,
                            'bg-amber-100 text-amber-700' => $transaction->status === \App\Models\PettyCashTransaction::STATUS_NEEDS_CHANGES,
                            'bg-purple-100 text-purple-700' => $transaction->status === \App\Models\PettyCashTransaction::STATUS_UNDER_REVIEW,
                        ])>
                                {{ $statusOptions[$transaction->status] ?? $transaction->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-left font-semibold">
                            {{ number_format($transaction->amount) }} {{ __('ریال') }}
                        </td>
                        <td class="px-4 py-3 text-left text-sm">
                            <div class="line-clamp-2">{{ $transaction->description }}</div>
                        </td>
                        <td class="px-4 py-3 text-left text-xs text-slate-500">
                            {{ $transaction->reference_number }}
                        </td>
                        <td class="px-4 py-3 text-left text-xs">
                            <div class="flex flex-wrap items-center gap-2">
                                @if($transaction->getFirstMediaUrl('invoice'))
                                    <a href="{{ $transaction->getFirstMediaUrl('invoice') }}" target="_blank" class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-1 text-slate-600 hover:bg-slate-200">
                                        <iconify-icon icon="lucide:file-text" class="text-base"></iconify-icon>
                                        {{ __('فاکتور') }}
                                    </a>
                                @endif
                                @if($transaction->getFirstMediaUrl('bank_receipt'))
                                    <a href="{{ $transaction->getFirstMediaUrl('bank_receipt') }}" target="_blank" class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-1 text-slate-600 hover:bg-slate-200">
                                        <iconify-icon icon="lucide:receipt" class="text-base"></iconify-icon>
                                        {{ __('رسید بانکی') }}
                                    </a>
                                @endif
                                @if($transaction->getFirstMediaUrl('charge_request'))
                                    <a href="{{ $transaction->getFirstMediaUrl('charge_request') }}" target="_blank" class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-1 text-slate-600 hover:bg-slate-200">
                                        <iconify-icon icon="lucide:folder" class="text-base"></iconify-icon>
                                        {{ __('مستند درخواست شارژ') }}
                                    </a>
                                @endif
                            </div>
                            @php
                                $isManager = auth()->user()?->hasRole(['Superadmin', 'Admin']);
                                $canEditTransaction = $this->canEditTransaction($transaction);
                            @endphp
                            <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                <button type="button"
                                        wire:click="showPreview({{ $transaction->id }})"
                                        class="inline-flex items-center gap-1 rounded bg-indigo-100 px-2 py-1 text-indigo-600 hover:bg-indigo-200">
                                    <iconify-icon icon="lucide:eye" class="text-base"></iconify-icon>
                                    {{ __('نمایش') }}
                                </button>
                                @if($canEditTransaction)
                                    <button type="button" wire:click="requestEdit({{ $transaction->id }})" class="text-indigo-600 hover:text-indigo-800">
                                        {{ __('ویرایش') }}
                                    </button>
                                @endif

                                @if($isManager)
                                    <button type="button" wire:click="requestApprove({{ $transaction->id }})" class="text-green-600 hover:text-green-800">
                                        {{ __('تایید') }}
                                    </button>
                                    <button type="button" wire:click="requestRevision({{ $transaction->id }})" class="text-amber-600 hover:text-amber-800">
                                        {{ __('ارسال برای بازبینی') }}
                                    </button>
                                    <button type="button" wire:click="markSuspicious({{ $transaction->id }})" class="text-purple-600 hover:text-purple-800">
                                        {{ __('رفتار مشکوک') }}
                                    </button>
                                    <button type="button" wire:click="requestReject({{ $transaction->id }})" class="text-rose-600 hover:text-rose-800">
                                        {{ __('رد کردن') }}
                                    </button>
                                    <button type="button" onclick="return confirm('{{ __('آیا از حذف این تراکنش مطمئن هستید؟') }}')" wire:click="requestDelete({{ $transaction->id }})" class="text-red-600 hover:text-red-800">
                                        {{ __('حذف') }}
                                    </button>
                                @endif
                            </div>

                            @if($transaction->status === \App\Models\PettyCashTransaction::STATUS_NEEDS_CHANGES)
                                <p class="mt-2 text-xs font-medium text-amber-600">
                                    {{ __('این تراکنش برای بازبینی به شما بازگردانده شده است. لطفاً اصلاحات لازم را اعمال و دوباره ارسال کنید.') }}
                                </p>
                            @endif

                            @if(($transaction->meta['suspicious'] ?? false) === true)
                                <p class="mt-1 text-xs font-medium text-purple-600">
                                    {{ __('این تراکنش به‌عنوان رفتار مشکوک علامت‌گذاری شده و در حال بررسی مدیریت است.') }}
                                </p>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">
                            {{ __('تراکنشی یافت نشد.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">
            {{ $transactions->links() }}
        </div>
    </div>
</div>

@if($showPreviewModal && !empty($previewTransaction))
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70" wire:keydown.escape.window="closePreview">
        <div class="absolute inset-0" wire:click="closePreview"></div>
        <div class="relative z-10 w-full max-w-5xl max-h-[90vh] overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">{{ __('جزئیات تراکنش') }}</h2>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ __('بررسی فاکتور، رسید و اطلاعات تکمیلی تراکنش برای تایید راحت‌تر.') }}
                    </p>
                </div>
                <button type="button" wire:click="closePreview" class="rounded-full bg-slate-100 p-2 text-slate-500 hover:bg-slate-200">
                    <iconify-icon icon="lucide:x" class="text-lg"></iconify-icon>
                </button>
            </div>

            @php
                $detail = $previewTransaction;
                $attachmentLabels = [
                    'invoice' => __('فاکتور'),
                    'bank_receipt' => __('رسید بانکی'),
                    'charge_request' => __('مستند درخواست شارژ'),
                ];
            @endphp

            <div class="mt-4 flex flex-col gap-6 md:flex-row">
                <div class="space-y-3 text-sm text-slate-700 md:order-2 md:w-5/12">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <dl class="grid grid-cols-1 gap-2 text-[13px]">
                            <div class="flex items-center justify-between">
                                <dt class="text-slate-500">{{ __('شماره مرجع') }}</dt>
                                <dd class="font-semibold text-slate-800">{{ $detail['reference_number'] ?? '---' }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-slate-500">{{ __('نوع تراکنش') }}</dt>
                                <dd class="font-semibold text-slate-800">{{ $typeOptions[$detail['type'] ?? ''] ?? ($detail['type'] ?? '---') }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-slate-500">{{ __('وضعیت') }}</dt>
                                <dd class="font-semibold text-slate-800">{{ $statusOptions[$detail['status'] ?? ''] ?? ($detail['status'] ?? '---') }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-slate-500">{{ __('مبلغ (ریال)') }}</dt>
                                <dd class="font-semibold text-emerald-600">{{ number_format($detail['amount'] ?? 0) }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-slate-500">{{ __('دسته‌بندی') }}</dt>
                                <dd class="font-semibold text-slate-800">{{ config('petty-cash.categories')[ $detail['category'] ?? '' ] ?? ($detail['category'] ?? '---') }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-slate-500">{{ __('تاریخ تراکنش') }}</dt>
                                <dd class="font-semibold text-slate-800">{{ $detail['transaction_date'] ?? '---' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <h3 class="text-xs font-semibold text-slate-600">{{ __('شرح تراکنش') }}</h3>
                        <p class="mt-2 text-[13px] leading-relaxed text-slate-700">{{ $detail['description'] ?: __('---') }}</p>
                    </div>

                    <div class="grid gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3 text-[13px] text-slate-700">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">{{ __('ثبت توسط') }}</span>
                            <span class="font-semibold">{{ $detail['requester'] ?? '---' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">{{ __('تایید توسط') }}</span>
                            <span class="font-semibold">{{ $detail['approver'] ?? '---' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">{{ __('مسئول تنخواه') }}</span>
                            <span class="font-semibold">{{ $detail['custodian'] ?? __('مسئول انتخاب نشده') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">{{ __('ثبت در سیستم') }}</span>
                            <span class="font-semibold">{{ $detail['created_at'] ?? '---' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">{{ __('آخرین بروزرسانی') }}</span>
                            <span class="font-semibold">{{ $detail['updated_at'] ?? '---' }}</span>
                        </div>
                        @if(!empty($detail['meta']['approval_note']))
                            <div class="rounded-md bg-emerald-50 p-2 text-slate-700">
                                <div class="text-xs font-semibold text-emerald-700">{{ __('یادداشت مدیر') }}</div>
                                <p class="mt-1 whitespace-pre-line">{{ $detail['meta']['approval_note'] }}</p>
                            </div>
                        @endif
                        @if(!empty($detail['meta']['revision_note']))
                            <div class="rounded-md bg-amber-50 p-2 text-slate-700">
                                <div class="text-xs font-semibold text-amber-700">{{ __('توضیح بازگشت برای اصلاح') }}</div>
                                <p class="mt-1 whitespace-pre-line">{{ $detail['meta']['revision_note'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                @php
                    $attachments = $detail['attachments'] ?? [];
                    $previewTabs = collect($attachments)
                        ->flatMap(function ($files, $collection) use ($attachmentLabels) {
                            return collect($files)->map(function ($file, $index) use ($files, $collection, $attachmentLabels) {
                                $labelBase = $attachmentLabels[$collection] ?? __('پیوست');
                                $label = $labelBase . (count($files) > 1 ? ' #' . ($index + 1) : '');

                                return [
                                    'id' => $collection . '-' . $index,
                                    'label' => $label,
                                    'file' => $file,
                                    'collection' => $collection,
                                    'is_image' => str_starts_with($file['mime_type'] ?? '', 'image/'),
                                ];
                            });
                        })
                        ->values();
                    $firstTab = $previewTabs->first();
                    $defaultTabId = is_array($firstTab) ? ($firstTab['id'] ?? null) : null;
                @endphp

                <div class="space-y-4 md:order-1 md:flex-1">
                    <div class="rounded-lg border border-slate-200 bg-white p-3">
                        <div class="flex items-center justify-between">
                            <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                <iconify-icon icon="lucide:paperclip" class="text-base"></iconify-icon>
                                {{ __('پیش‌نمایش پیوست‌ها') }}
                            </h3>
                            @if($previewTabs->isNotEmpty())
                                <span class="text-[11px] font-medium text-slate-500">
                                    {{ __('تعداد فایل‌ها: :count', ['count' => $previewTabs->count()]) }}
                                </span>
                            @endif
                        </div>

                        @if($previewTabs->isNotEmpty())
                            <div
                                class="mt-3 space-y-4"
                                x-data="{ activeTab: '{{ $defaultTabId }}' }"
                            >
                                <div class="flex flex-wrap gap-2">
                                    @foreach($previewTabs as $tab)
                                        <button
                                            type="button"
                                            @click="activeTab = '{{ $tab['id'] }}'"
                                            x-bind:class="activeTab === '{{ $tab['id'] }}'
                                                ? 'bg-indigo-100 text-indigo-700 border-indigo-200'
                                                : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200'"
                                            class="rounded-full border px-3 py-1 text-xs font-semibold transition-colors"
                                        >
                                            {{ $tab['label'] }}
                                        </button>
                                    @endforeach
                                </div>

                                @foreach($previewTabs as $tab)
                                    <div
                                        x-show="activeTab === '{{ $tab['id'] }}'"
                                        x-cloak
                                        class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600"
                                    >
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="truncate font-semibold text-slate-700">{{ $tab['file']['name'] ?? __('بدون نام') }}</div>
                                            <div class="flex items-center gap-2">
                                                <a
                                                    href="{{ $tab['file']['url'] }}"
                                                    target="_blank"
                                                    class="inline-flex items-center gap-1 rounded-md bg-indigo-100 px-2 py-1 text-[11px] font-semibold text-indigo-600 hover:bg-indigo-200"
                                                >
                                                    <iconify-icon icon="lucide:external-link" class="text-sm"></iconify-icon>
                                                    {{ __('نمایش') }}
                                                </a>
                                                <a
                                                    href="{{ $tab['file']['url'] }}"
                                                    download
                                                    class="inline-flex items-center gap-1 rounded-md border border-indigo-200 px-2 py-1 text-[11px] font-semibold text-indigo-600 hover:bg-indigo-50"
                                                >
                                                    <iconify-icon icon="lucide:download" class="text-sm"></iconify-icon>
                                                    {{ __('دانلود') }}
                                                </a>
                                            </div>
                                        </div>

                                        @if($tab['is_image'])
                                            <div class="mt-3 overflow-hidden rounded-lg bg-white p-2">
                                                <img
                                                    src="{{ $tab['file']['preview_url'] }}"
                                                    alt="{{ $tab['label'] }}"
                                                    class="max-h-[420px] w-full rounded-md object-contain"
                                                />
                                            </div>
                                        @else
                                            <div class="mt-3 rounded-md bg-white p-3 text-[11px] text-slate-500">
                                                <div class="flex items-center gap-2">
                                                    <iconify-icon icon="lucide:file-text" class="text-base text-slate-400"></iconify-icon>
                                                    <span>{{ __('پیش‌نمایش تصویری برای این فایل در دسترس نیست.') }}</span>
                                                </div>
                                                <p class="mt-2 leading-relaxed">
                                                    {{ __('برای بررسی محتوا، از دکمه‌های نمایش یا دانلود استفاده کنید.') }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-3 rounded-lg border border-dashed border-amber-300 bg-amber-50 p-4 text-center text-sm font-medium text-amber-700">
                                {{ __('پیوست ثبت نشده') }}
                            </div>
                        @endif
                    </div>

                    @if($previewTabs->isNotEmpty())
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-[12px] text-slate-600">
                            <h4 class="mb-2 text-xs font-semibold text-slate-500">{{ __('راهنما') }}</h4>
                            <ul class="space-y-1 leading-relaxed">
                                <li>{{ __('برای جابجایی بین فایل‌ها، روی برچسب‌های بالا کلیک کنید.') }}</li>
                                <li>{{ __('نسخه اصلی فایل را می‌توانید از طریق دکمه دانلود دریافت کنید.') }}</li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" wire:click="closePreview" class="inline-flex items-center gap-2 rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                    <iconify-icon icon="lucide:x" class="text-base"></iconify-icon>
                    {{ __('بستن') }}
                </button>
            </div>
        </div>
    </div>
@endif

@if($showRejectModal)
    @php
        $rejectTransaction = $transactions->firstWhere('id', $rejectTransactionId);
    @endphp
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60" wire:keydown.escape.window="closeRejectModal">
        <div class="absolute inset-0" wire:click="closeRejectModal"></div>
        <div class="relative z-10 w-full max-w-lg rounded-xl bg-white p-6 shadow-lg">
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">{{ __('رد تراکنش') }}</h2>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ __('لطفاً علت رد تراکنش را مشخص کنید تا در گزارش‌ها و تاریخچه ثبت شود.') }}
                    </p>
                </div>
                <button type="button" wire:click="closeRejectModal" class="rounded-full bg-slate-100 p-2 text-slate-500 hover:bg-slate-200">
                    <iconify-icon icon="lucide:x" class="text-lg"></iconify-icon>
                </button>
            </div>

            <div class="mt-4 space-y-3 text-sm text-slate-600">
                @if($rejectTransaction)
                    <div class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-[13px]">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">{{ __('شماره مرجع') }}</span>
                            <span class="font-semibold text-slate-800">{{ $rejectTransaction->reference_number ?: '---' }}</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-slate-500">{{ __('مبلغ درخواست') }}</span>
                            <span class="font-semibold text-rose-600">{{ number_format($rejectTransaction->amount) }} {{ __('ریال') }}</span>
                        </div>
                        <div class="mt-2">
                            <span class="text-slate-500">{{ __('شرح') }}:</span>
                            <p class="mt-1 text-slate-700">{{ $rejectTransaction->description ?: '---' }}</p>
                        </div>
                    </div>
                @endif

                <div>
                    <label class="text-xs font-semibold text-slate-500" for="reject-note">{{ __('یادداشت مدیر (الزامی)') }}</label>
                    <textarea
                        id="reject-note"
                        rows="4"
                        wire:model.defer="rejectNote"
                        class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow focus:border-rose-500 focus:ring-rose-500"
                        placeholder="{{ __('علت رد را با جزئیات بنویسید...') }}"
                    ></textarea>
                    @error('rejectNote')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-[11px] text-slate-400">{{ __('این توضیح در متادیتای تراکنش ذخیره می‌شود و در گزارش‌ها نمایش داده خواهد شد.') }}</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" wire:click="closeRejectModal" class="inline-flex items-center gap-2 rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                    {{ __('انصراف') }}
                </button>
                <button type="button" wire:click="rejectSelectedTransaction" class="inline-flex items-center gap-2 rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                    <iconify-icon icon="lucide:x-circle" class="text-base"></iconify-icon>
                    {{ __('تایید رد تراکنش') }}
                </button>
            </div>
        </div>
    </div>
@endif

@if($showApproveModal)
    @php
        $approvalTransaction = $transactions->firstWhere('id', $approvalTransactionId);
    @endphp
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60" wire:keydown.escape.window="closeApproveModal">
        <div class="absolute inset-0" wire:click="closeApproveModal"></div>
        <div class="relative z-10 w-full max-w-lg rounded-xl bg-white p-6 shadow-lg">
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">{{ __('تایید تراکنش') }}</h2>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ __('در صورت نیاز توضیحی برای ثبت در گزارش‌ها اضافه کنید و سپس تایید را انجام دهید.') }}
                    </p>
                </div>
                <button type="button" wire:click="closeApproveModal" class="rounded-full bg-slate-100 p-2 text-slate-500 hover:bg-slate-200">
                    <iconify-icon icon="lucide:x" class="text-lg"></iconify-icon>
                </button>
            </div>

            <div class="mt-4 space-y-3 text-sm text-slate-600">
                @if($approvalTransaction)
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-[13px]">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">{{ __('شماره مرجع') }}</span>
                            <span class="font-semibold text-slate-800">{{ $approvalTransaction->reference_number ?: '---' }}</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-slate-500">{{ __('مبلغ درخواست') }}</span>
                            <span class="font-semibold text-emerald-600">{{ number_format($approvalTransaction->amount) }} {{ __('ریال') }}</span>
                        </div>
                        <div class="mt-2">
                            <span class="text-slate-500">{{ __('شرح') }}:</span>
                            <p class="mt-1 text-slate-700">{{ $approvalTransaction->description ?: '---' }}</p>
                        </div>
                    </div>
                @endif

                <div>
                    <label class="text-xs font-semibold text-slate-500" for="approval-note">{{ __('یادداشت مدیر (اختیاری)') }}</label>
                    <textarea id="approval-note" rows="3" wire:model.defer="approvalNote" class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('در صورت نیاز توضیحی بنویسید...') }}"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" wire:click="closeApproveModal" class="inline-flex items-center gap-2 rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                    {{ __('انصراف') }}
                </button>
                <button type="button" wire:click="approveSelectedTransaction" class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    <iconify-icon icon="lucide:check" class="text-base"></iconify-icon>
                    {{ __('تایید نهایی') }}
                </button>
            </div>
        </div>
    </div>
@endif

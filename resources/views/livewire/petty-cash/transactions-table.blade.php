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
                        <button type="button" wire:click="requestReject({{ $transaction->id }})" onclick="return confirm('{{ __('آیا از رد کردن این تراکنش مطمئن هستید؟') }}')" class="text-rose-600 hover:text-rose-800">
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
                                    <button type="button" onclick="return confirm('{{ __('آیا از رد کردن این تراکنش مطمئن هستید؟') }}')" wire:click="requestReject({{ $transaction->id }})" class="text-rose-600 hover:text-rose-800">
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

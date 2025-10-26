<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="space-y-6">
        <!-- Header -->
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-800">
                        <i class="ml-2 text-red-600 fas fa-exclamation-triangle"></i>
                        {{ __('حذف شعبه: :name', ['name' => $ledger->branch_name]) }}
                    </h1>
                    <p class="text-sm text-slate-500">{{ __('این عملیات غیرقابل بازگشت است. لطفاً با دقت ادامه دهید.') }}</p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('admin.petty-cash.index', ['ledger' => $ledger->id]) }}"
                       class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <i class="fas fa-arrow-right"></i>
                        {{ __('بازگشت') }}
                    </a>
                </div>
            </div>
        </div>

        @if($ledger->transactions()->count() > 0)
            <div class="rounded-md border border-red-200 bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-400"></i>
                    </div>
                    <div class="mr-3">
                        <h3 class="text-sm font-medium text-red-800">{{ __('هشدار مهم') }}</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>{{ __('این شعبه دارای :count تراکنش است. حذف آن باعث از بین رفتن تمام سوابق مالی می‌شود.', ['count' => $ledger->transactions()->count()]) }}</p>
                            <p class="mt-1">{{ __('قبل از حذف، از اطلاعات بک‌آپ تهیه خواهد شد.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Delete Form -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Warning Info -->
            <div class="rounded-lg border border-red-200 bg-red-50 p-6">
                <h3 class="text-lg font-semibold text-red-800">{{ __('اطلاعات حذف') }}</h3>

                <div class="mt-4 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100">
                            <i class="fas fa-building text-red-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-red-800">{{ $ledger->branch_name }}</p>
                            <p class="text-sm text-red-600">{{ __('نام شعبه') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100">
                            <i class="fas fa-wallet text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-red-800">{{ number_format($ledger->current_balance) }} ریال</p>
                            <p class="text-sm text-red-600">{{ __('موجودی فعلی') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-100">
                            <i class="fas fa-exchange-alt text-purple-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-red-800">{{ $ledger->transactions()->count() }}</p>
                            <p class="text-sm text-red-600">{{ __('تعداد تراکنش‌ها') }}</p>
                        </div>
                    </div>
                </div>

                @if($ledger->transactions()->count() > 0)
                    <div class="mt-6 rounded-lg bg-white p-4">
                        <h4 class="font-medium text-red-800">{{ __('تراکنش‌های اخیر') }}</h4>
                        <div class="mt-2 max-h-32 overflow-y-auto">
                            @foreach($ledger->transactions()->latest()->limit(5)->get() as $transaction)
                                <div class="flex items-center justify-between py-2 text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium
                                            {{ $transaction->type === 'charge' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $transaction->type === 'charge' ? __('شارژ') : __('هزینه') }}
                                        </span>
                                        <span class="text-slate-600">{{ number_format($transaction->amount) }} ریال</span>
                                    </div>
                                    <span class="text-xs text-slate-500">{{ verta($transaction->transaction_date)->format('Y/m/d') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Delete Form -->
            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                @if($ledger->transactions()->count() > 0)
                    <div class="mb-6 rounded-lg bg-yellow-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-yellow-400"></i>
                            </div>
                            <div class="mr-3">
                                <h3 class="text-sm font-medium text-yellow-800">{{ __('تاییدیه دوبل مورد نیاز است') }}</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>{{ __('برای حذف شعبه‌ای که دارای تراکنش است، باید تاییدیه دوبل بدهید.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.petty-cash.destroy', $ledger->id) }}" class="space-y-6">
                    @csrf
                    @method('DELETE')

                    @if($ledger->transactions()->count() > 0)
                        <!-- Double Confirmation -->
                        <div>
                            <label for="confirm_delete" class="block text-sm font-medium text-slate-700">
                                {{ __('تاییدیه حذف') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <input type="text"
                                       id="confirm_delete"
                                       name="confirm_delete"
                                       placeholder="DELETE_BRANCH_WITH_TRANSACTIONS"
                                       class="block w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow-sm placeholder-slate-400 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500 @error('confirm_delete') border-red-300 @enderror"
                                       required>
                                @error('confirm_delete')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-slate-500">{{ __('برای تایید حذف، دقیقاً این متن را وارد کنید: DELETE_BRANCH_WITH_TRANSACTIONS') }}</p>
                            </div>
                        </div>

                        <!-- Backup Reason -->
                        <div>
                            <label for="backup_reason" class="block text-sm font-medium text-slate-700">
                                {{ __('دلیل حذف شعبه') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <textarea id="backup_reason"
                                          name="backup_reason"
                                          rows="3"
                                          class="block w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow-sm placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('backup_reason') border-red-300 @enderror"
                                          placeholder="{{ __('لطفاً دلیل حذف این شعبه را توضیح دهید...') }}"
                                          required>{{ old('backup_reason') }}</textarea>
                                @error('backup_reason')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <p class="mt-1 text-xs text-slate-500">{{ __('این توضیح در فایل بک‌آپ ذخیره خواهد شد.') }}</p>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-3 border-t border-slate-200 pt-6">
                        <a href="{{ route('admin.petty-cash.index', ['ledger' => $ledger->id]) }}"
                           class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{ __('انصراف') }}
                        </a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                onclick="return confirm('{{ __('آیا کاملاً مطمئن هستید؟ این عملیات غیرقابل بازگشت است.') }}')">
                            <i class="fas fa-trash"></i>
                            {{ __('حذف شعبه') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Backup Information -->
        @if($ledger->transactions()->count() > 0)
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-6">
                <h3 class="text-lg font-semibold text-blue-800">{{ __('اطلاعات بک‌آپ') }}</h3>
                <div class="mt-4 text-sm text-blue-700">
                    <p>{{ __('قبل از حذف، از اطلاعات زیر بک‌آپ تهیه خواهد شد:') }}</p>
                    <ul class="mt-2 list-disc list-inside space-y-1">
                        <li>{{ __('اطلاعات کامل شعبه (نام، سقف مجاز، موجودی و غیره)') }}</li>
                        <li>{{ __('تمام تراکنش‌های مرتبط با شعبه') }}</li>
                        <li>{{ __('اطلاعات کاربران مرتبط (درخواست‌کننده و تاییدکننده)') }}</li>
                        <li>{{ __('زمان و دلیل حذف') }}</li>
                    </ul>
                    <p class="mt-3">{{ __('فایل بک‌آپ در مسیر storage/app/backups ذخیره خواهد شد.') }}</p>
                </div>
            </div>
        @endif
    </div>
</x-layouts.backend-layout>

<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    @php $assignableUsers = $assignableUsers ?? collect(); @endphp
    <div class="space-y-6">
        <!-- Header -->
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-800">
                        <i class="ml-2 text-indigo-600 fas fa-edit"></i>
                        {{ __('ویرایش شعبه: :name', ['name' => $ledger->branch_name]) }}
                    </h1>
                    <p class="text-sm text-slate-500">{{ __('ویرایش اطلاعات شعبه و تنظیمات تنخواه') }}</p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('admin.petty-cash.index', ['ledger' => $ledger->id]) }}"
                       class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <i class="fas fa-arrow-right"></i>
                        {{ __('بازگشت به لیست') }}
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-md border border-green-200 bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="mr-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-md border border-red-200 bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="mr-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Edit Form -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Form -->
            <div class="lg:col-span-2">
                <form method="POST" action="{{ route('admin.petty-cash.update', $ledger->id) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-800">{{ __('اطلاعات شعبه') }}</h2>

                        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Branch Name -->
                            <div>
                                <label for="branch_name" class="block text-sm font-medium text-slate-700">
                                    {{ __('نام شعبه') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="branch_name"
                                       name="branch_name"
                                       value="{{ old('branch_name', $ledger->branch_name) }}"
                                       class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow-sm placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('branch_name') border-red-300 @enderror"
                                       required>
                                @error('branch_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Limit Amount -->
                            <div>
                                <label for="limit_amount" class="block text-sm font-medium text-slate-700">
                                    {{ __('سقف مجاز (ریال)') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="relative mt-1">
                                    <input type="number"
                                           id="limit_amount"
                                           name="limit_amount"
                                           value="{{ old('limit_amount', $ledger->limit_amount) }}"
                                           min="0"
                                           step="0.01"
                                           class="block w-full rounded-md border-slate-300 px-3 py-2 pl-8 text-sm shadow-sm placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('limit_amount') border-red-300 @enderror"
                                           required>
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-slate-500 sm:text-sm">ریال</span>
                                    </div>
                                </div>
                                @error('limit_amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="max_charge_request_amount" class="block text-sm font-medium text-slate-700">
                                        {{ __('سقف مجاز درخواست شارژ (ریال)') }}
                                    </label>
                                    <div class="relative mt-1">
                                        <input type="number"
                                               id="max_charge_request_amount"
                                               name="max_charge_request_amount"
                                               value="{{ old('max_charge_request_amount', $ledger->max_charge_request_amount) }}"
                                               min="0"
                                               step="0.01"
                                               class="block w-full rounded-md border-slate-300 px-3 py-2 pl-8 text-sm shadow-sm placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('max_charge_request_amount') border-red-300 @enderror">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                            <span class="text-slate-500 sm:text-sm">ریال</span>
                                        </div>
                                    </div>
                                    @error('max_charge_request_amount')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-slate-500">{{ __('در صورت ورود 0، محدودیتی وجود نخواهد داشت.') }}</p>
                                </div>

                                <div>
                                    <label for="max_transaction_amount" class="block text-sm font-medium text-slate-700">
                                        {{ __('سقف مجاز هر تراکنش (ریال)') }}
                                    </label>
                                    <div class="relative mt-1">
                                        <input type="number"
                                               id="max_transaction_amount"
                                               name="max_transaction_amount"
                                               value="{{ old('max_transaction_amount', $ledger->max_transaction_amount) }}"
                                               min="0"
                                               step="0.01"
                                               class="block w-full rounded-md border-slate-300 px-3 py-2 pl-8 text-sm shadow-sm placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('max_transaction_amount') border-red-300 @enderror">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                            <span class="text-slate-500 sm:text-sm">ریال</span>
                                        </div>
                                    </div>
                                    @error('max_transaction_amount')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-slate-500">{{ __('در صورت ورود 0، محدودیتی وجود نخواهد داشت.') }}</p>
                                </div>
                            </div>

                            <!-- Opening Balance -->
                            <div>
                                <label for="opening_balance" class="block text-sm font-medium text-slate-700">
                                    {{ __('موجودی اولیه (ریال)') }}
                                </label>
                                <div class="relative mt-1">
                                    <input type="number"
                                           id="opening_balance"
                                           name="opening_balance"
                                           value="{{ old('opening_balance', $ledger->opening_balance) }}"
                                           min="0"
                                           step="0.01"
                                           class="block w-full rounded-md border-slate-300 px-3 py-2 pl-8 text-sm shadow-sm placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('opening_balance') border-red-300 @enderror">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-slate-500 sm:text-sm">ریال</span>
                                    </div>
                                </div>
                                @error('opening_balance')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-slate-500">{{ __('اگر موجودی اولیه مشخص نشود، از سقف مجاز استفاده می‌شود.') }}</p>
                            </div>

                            <!-- Active Status -->
                            <div>
                                <div class="flex items-center">
                                    <input type="checkbox"
                                           id="is_active"
                                           name="is_active"
                                           value="1"
                                           {{ old('is_active', $ledger->is_active) ? 'checked' : '' }}
                                           class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="is_active" class="mr-2 block text-sm font-medium text-slate-700">
                                        {{ __('شعبه فعال') }}
                                    </label>
                                </div>
                                @error('is_active')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Assigned User -->
                            <div class="md:col-span-2">
                                <label for="assigned_user_id" class="block text-sm font-medium text-slate-700">
                                    {{ __('کاربر مسئول شعبه') }} <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="assigned_user_id"
                                    name="assigned_user_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('assigned_user_id') border-red-300 @enderror"
                                >
                                    <option value="" @selected(! old('assigned_user_id', $ledger->assigned_user_id))>{{ __('یک کاربر را انتخاب کنید') }}</option>
                                    @foreach($assignableUsers as $userOption)
                                        <option value="{{ $userOption->id }}" @selected(old('assigned_user_id', $ledger->assigned_user_id) == $userOption->id)>
                                            {{ $userOption->full_name ?? $userOption->email }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_user_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            @if($assignableUsers->isEmpty())
                                <p class="mt-1 text-xs text-amber-600">{{ __('کاربری بدون شعبه یافت نشد. در صورت نیاز ابتدا شعبه کاربر دیگری را آزاد کنید.') }}</p>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="account_holder" class="block text-sm font-medium text-slate-700">
                                    {{ __('نام صاحب حساب') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="account_holder"
                                       name="account_holder"
                                       value="{{ old('account_holder', $ledger->account_holder) }}"
                                       class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('account_holder') border-red-300 @enderror"
                                       required>
                                @error('account_holder')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="account_number" class="block text-sm font-medium text-slate-700">
                                    {{ __('شماره حساب بانکی') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="account_number"
                                       name="account_number"
                                       value="{{ old('account_number', $ledger->account_number) }}"
                                       class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('account_number') border-red-300 @enderror"
                                       required>
                                @error('account_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="iban" class="block text-sm font-medium text-slate-700">
                                    {{ __('شماره شبا') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="iban"
                                       name="iban"
                                       value="{{ old('iban', $ledger->iban) }}"
                                       class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm uppercase shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('iban') border-red-300 @enderror"
                                       required>
                                @error('iban')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="card_number" class="block.text-sm font-medium text-slate-700">
                                    {{ __('شماره کارت بانکی') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="card_number"
                                       name="card_number"
                                       value="{{ old('card_number', $ledger->card_number) }}"
                                       class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2.text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('card_number') border-red-300 @enderror"
                                       required>
                                @error('card_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                        <div class="mt-6 flex justify-end gap-3 border-t border-slate-200 pt-6">
                            <a href="{{ route('admin.petty-cash.index', ['ledger' => $ledger->id]) }}"
                               class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                {{ __('انصراف') }}
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <i class="fas fa-save"></i>
                                {{ __('ذخیره تغییرات') }}
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Branch Users Management Section -->
                <div class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    @livewire('admin.branch-users-management', ['ledger' => $ledger], key('branch-users-'.$ledger->id))
                </div>
            </div>

            <!-- Current Info Sidebar -->
            <div class="lg:col-span-1">
                <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-800">{{ __('اطلاعات فعلی شعبه') }}</h3>

                    <div class="mt-6 space-y-4">
                        <!-- Current Balance -->
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                                    <i class="fas fa-wallet text-green-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-600">{{ __('موجودی فعلی') }}</p>
                                    <p class="text-xs text-slate-500">{{ __('مانده حساب') }}</p>
                                </div>
                            </div>
                            <p class="text-lg font-bold text-green-600">{{ number_format($ledger->current_balance) }}</p>
                        </div>

                        <!-- Limit Amount -->
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100">
                                    <i class="fas fa-chart-line text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-600">{{ __('سقف مجاز') }}</p>
                                    <p class="text-xs text-slate-500">{{ __('حداکثر مجاز') }}</p>
                                </div>
                            </div>
                            <p class="text-lg font-bold text-blue-600">{{ number_format($ledger->limit_amount) }}</p>
                        </div>

                        <!-- Transactions Count -->
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-100">
                                    <i class="fas fa-exchange-alt text-purple-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-600">{{ __('تعداد تراکنش‌ها') }}</p>
                                    <p class="text-xs text-slate-500">{{ __('کل تراکنش‌ها') }}</p>
                                </div>
                            </div>
                            <p class="text-lg font-bold text-purple-600">{{ $ledger->transactions()->count() }}</p>
                        </div>

                        <!-- Status -->
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $ledger->is_active ? 'bg-green-100' : 'bg-red-100' }}">
                                    <i class="fas {{ $ledger->is_active ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600' }}"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-600">{{ __('وضعیت شعبه') }}</p>
                                    <p class="text-xs text-slate-500">{{ __('فعال/غیرفعال') }}</p>
                                </div>
                            </div>
                            <div>
                                @if($ledger->is_active)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">
                                        <i class="fas fa-circle"></i>
                                        {{ __('فعال') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">
                                        <i class="fas fa-circle"></i>
                                        {{ __('غیرفعال') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Usage Progress -->
                    @php
                        $limit = (float) $ledger->limit_amount;
                        $balance = (float) $ledger->current_balance;
                        $usagePercent = $limit > 0 ? min(100, round((($limit - $balance) / $limit) * 100, 1)) : 0;
                    @endphp

                    @if($limit > 0)
                        <div class="mt-6">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-slate-600">{{ __('میزان مصرف') }}</span>
                                <span class="text-sm font-bold text-slate-800">{{ $usagePercent }}%</span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-slate-200">
                                <div class="h-2 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500" style="width: {{ $usagePercent }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.backend-layout>

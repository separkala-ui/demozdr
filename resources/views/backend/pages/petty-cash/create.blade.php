<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    @php
        $assignableUsers = $assignableUsers ?? collect();
        $createDisabled = $assignableUsers->isEmpty();
    @endphp
    <div class="space-y-6">
        <!-- Header -->
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-800">
                        <i class="ml-2 text-indigo-600 fas fa-plus-circle"></i>
                        {{ __('ایجاد دفتر تنخواه جدید') }}
                    </h1>
                    <p class="text-sm text-slate-500">{{ __('ایجاد شعبه جدید برای مدیریت تنخواه') }}</p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('admin.petty-cash.index') }}"
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

        <!-- Create Form -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Main Form -->
            <div class="lg:col-span-1">
                <form method="POST" action="{{ route('admin.petty-cash.store') }}" class="space-y-6">
                    @csrf

                    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-800">{{ __('اطلاعات شعبه') }}</h2>

                        <div class="mt-6 space-y-6">
                            <!-- Branch Name -->
                            <div>
                                <label for="branch_name" class="block text-sm font-medium text-slate-700">
                                    {{ __('نام شعبه') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="branch_name"
                                       name="branch_name"
                                       value="{{ old('branch_name') }}"
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
                                           value="{{ old('limit_amount') }}"
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

                            <!-- Opening Balance -->
                            <div>
                                <label for="opening_balance" class="block text-sm font-medium text-slate-700">
                                    {{ __('موجودی اولیه (ریال)') }}
                                </label>
                                <div class="relative mt-1">
                                    <input type="number"
                                           id="opening_balance"
                                           name="opening_balance"
                                           value="{{ old('opening_balance') }}"
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

                            <!-- Assigned User -->
                            <div>
                                <label for="assigned_user_id" class="block text-sm font-medium text-slate-700">
                                    {{ __('کاربر مسئول شعبه') }} <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="assigned_user_id"
                                    name="assigned_user_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('assigned_user_id') border-red-300 @enderror"
                                >
                                    <option value="" @selected(! old('assigned_user_id'))>{{ __('یک کاربر را انتخاب کنید') }}</option>
                                    @foreach($assignableUsers as $userOption)
                                        <option value="{{ $userOption->id }}" @selected(old('assigned_user_id') == $userOption->id)>
                                            {{ $userOption->full_name ?? $userOption->email }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_user_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @if($assignableUsers->isEmpty())
                                    <p class="mt-1 text-xs text-amber-600">{{ __('کاربری بدون شعبه یافت نشد. برای ایجاد شعبه جدید ابتدا کاربر آزاد یا کاربر تازه‌ای ایجاد کنید.') }}</p>
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
                                           value="{{ old('account_holder') }}"
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
                                           value="{{ old('account_number') }}"
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
                                           value="{{ old('iban') }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm uppercase shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('iban') border-red-300 @enderror"
                                           required>
                                    @error('iban')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="card_number" class="block text-sm font-medium text-slate-700">
                                        {{ __('شماره کارت بانکی') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="card_number"
                                           name="card_number"
                                           value="{{ old('card_number') }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('card_number') border-red-300 @enderror"
                                           required>
                                    @error('card_number')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-6 flex justify-end gap-3 border-t border-slate-200 pt-6">
                            <a href="{{ route('admin.petty-cash.index') }}"
                               class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                {{ __('انصراف') }}
                            </a>
                            <button type="submit"
                                    @if($createDisabled) disabled @endif
                                    class="inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $createDisabled ? 'cursor-not-allowed bg-indigo-400' : 'bg-indigo-600 hover:bg-indigo-500' }}">
                                <i class="fas fa-save"></i>
                                {{ __('ایجاد شعبه') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Info Sidebar -->
            <div class="lg:col-span-1">
                <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-800">{{ __('راهنما') }}</h3>

                    <div class="mt-6 space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100">
                                <i class="fas fa-info-circle text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-700">{{ __('نام شعبه') }}</p>
                                <p class="text-xs text-slate-500">{{ __('نام شعبه باید منحصر به فرد باشد') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100">
                                <i class="fas fa-wallet text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-700">{{ __('سقف مجاز') }}</p>
                                <p class="text-xs text-slate-500">{{ __('حداکثر مبلغ مجاز برای این شعبه') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-100">
                                <i class="fas fa-chart-line text-purple-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-700">{{ __('موجودی اولیه') }}</p>
                                <p class="text-xs text-slate-500">{{ __('موجودی شروع دوره (اختیاری)') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.backend-layout>

@php
    $formData = $formData ?? [];
    $serviceStatus = $serviceStatus ?? [];
@endphp

<x-layouts.backend-layout :breadcrumbs="[ 'title' => __('تنظیمات جمینای برای فاکتور هوشمند') ]">
    <div class="space-y-6">
        <div class="rounded-md border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-6 space-y-2">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-200">
                    {{ __('اتصال به Google Gemini') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ __('تمام فرآیند استخراج فاکتور اکنون فقط با Gemini انجام می‌شود و سرویس‌های پایتونی حذف شده‌اند.') }}
                </p>
                <div class="rounded-md bg-slate-50 p-3 text-xs text-slate-600 dark:bg-slate-800/60 dark:text-slate-300">
                    <div>{{ __('مدل استفاده‌شده:') }} <span class="font-semibold text-slate-700 dark:text-slate-100">{{ $serviceStatus['model'] ?? 'gemini-2.5-flash' }}</span></div>
                    <div>{{ __('مهلت پردازش (ثانیه):') }} <span class="font-semibold text-slate-700 dark:text-slate-100">{{ $serviceStatus['timeout'] ?? 45 }}</span></div>
                    <div>
                        {{ __('وضعیت فعلی:') }}
                        @if(($serviceStatus['gemini_enabled'] ?? false) && ($serviceStatus['has_api_key'] ?? false))
                            <span class="text-green-600 dark:text-green-400">{{ __('فعال') }}</span>
                        @elseif($serviceStatus['gemini_enabled'] ?? false)
                            <span class="text-amber-600 dark:text-amber-400">{{ __('کلید ثبت نشده') }}</span>
                        @else
                            <span class="text-red-600 dark:text-red-400">{{ __('غیرفعال') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.settings.smart-invoice.update') }}" class="space-y-6" data-prevent-unsaved-changes>
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label for="gemini_api_key" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                            {{ __('کلید API جمینای') }}
                        </label>
                        <input
                            id="gemini_api_key"
                            name="gemini_api_key"
                            type="text"
                            class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                            value="{{ old('gemini_api_key', $formData['gemini_api_key'] ?? '') }}"
                            required
                            autocomplete="off"
                        />
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            {{ __('کلید ارائه‌شده توسط Google AI Studio یا کنسول جمینای را وارد کنید.') }}
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="gemini_model" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                                {{ __('مدل Gemini') }}
                            </label>
                            <input
                                id="gemini_model"
                                name="gemini_model"
                                type="text"
                                class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                value="{{ old('gemini_model', $formData['gemini_model'] ?? 'gemini-2.5-flash') }}"
                                autocomplete="off"
                            />
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                {{ __('در صورت نیاز مدل دیگر (مثلاً gemini-1.5-pro) را وارد کنید.') }}
                            </p>
                        </div>

                        <div>
                            <label for="gemini_timeout" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                                {{ __('مهلت پاسخ (ثانیه)') }}
                            </label>
                            <input
                                id="gemini_timeout"
                                name="gemini_timeout"
                                type="number"
                                min="5"
                                max="180"
                                class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                value="{{ old('gemini_timeout', $formData['gemini_timeout'] ?? 45) }}"
                            />
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                {{ __('در صورت تصاویر سنگین یا اینترنت ضعیف، مقدار را افزایش دهید.') }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="confidence_threshold" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                                {{ __('آستانه اطمینان برای هشدار') }}
                            </label>
                            <input
                                id="confidence_threshold"
                                name="confidence_threshold"
                                type="number"
                                min="0"
                                max="1"
                                step="0.05"
                                class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                value="{{ old('confidence_threshold', $formData['confidence_threshold'] ?? 0.6) }}"
                            />
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                {{ __('اگر مقدار اطمینان خروجی کمتر از این مقدار باشد پیغام هشدار نمایش داده می‌شود.') }}
                            </p>
                        </div>

                        <div>
                            <label for="validation_tolerance" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                                {{ __('آستانه اختلاف مجاز (ریال)') }}
                            </label>
                            <input
                                id="validation_tolerance"
                                name="validation_tolerance"
                                type="number"
                                min="0"
                                step="500"
                                class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                value="{{ old('validation_tolerance', $formData['validation_tolerance'] ?? 1000) }}"
                            />
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                {{ __('اگر اختلاف جمع اقلام و جمع کل از این مقدار عبور کند، هشدار اختلاف نمایش داده می‌شود.') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <input
                            id="gemini_enabled"
                            name="gemini_enabled"
                            type="checkbox"
                            value="1"
                            class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600"
                            @checked(old('gemini_enabled', $formData['gemini_enabled'] ?? true))
                        />
                        <label for="gemini_enabled" class="text-sm text-slate-700 dark:text-slate-200">
                            {{ __('فعال‌سازی جمینای برای تکمیل هوشمند فاکتور') }}
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <x-buttons.button type="submit" variant="primary">
                        {{ __('ذخیره تنظیمات') }}
                    </x-buttons.button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.backend-layout>

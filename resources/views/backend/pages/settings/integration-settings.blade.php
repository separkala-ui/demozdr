@php
    $mailMailer = old('mail_mailer', config('settings.mail_mailer', env('MAIL_MAILER', 'smtp')));
    $mailHost = old('mail_host', config('settings.mail_host', env('MAIL_HOST')));
    $mailPort = old('mail_port', config('settings.mail_port', env('MAIL_PORT')));
    $mailUsername = old('mail_username', config('settings.mail_username', env('MAIL_USERNAME')));
    $mailPassword = old('mail_password', config('settings.mail_password', env('MAIL_PASSWORD')));
    $mailEncryption = old('mail_encryption', config('settings.mail_encryption', env('MAIL_ENCRYPTION', 'tls')));
    $mailFromAddress = old('mail_from_address', config('settings.mail_from_address', env('MAIL_FROM_ADDRESS')));
    $mailFromName = old('mail_from_name', config('settings.mail_from_name', env('MAIL_FROM_NAME', config('app.name'))));
    $mailReplyToAddress = old('mail_reply_to_address', config('settings.mail_reply_to_address', env('MAIL_REPLY_TO_ADDRESS')));
    $mailReplyToName = old('mail_reply_to_name', config('settings.mail_reply_to_name', env('MAIL_REPLY_TO_NAME')));
@endphp

{!! Hook::applyFilters(SettingFilterHook::SETTINGS_INTEGRATIONS_TAB_BEFORE_SECTION_START, '') !!}

<div class="rounded-md border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="px-5 py-4 sm:px-6 sm:py-5">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-base font-medium text-gray-700 dark:text-white/90">
                    {{ __('تنظیمات SMTP ایمیل') }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                    {{ __('برای ارسال ایمیل از طریق سرویس‌های خارجی (مانند Gmail، Mailtrap یا سرور اختصاصی) مقادیر زیر را تکمیل کنید. اطلاعات در فایل .env نیز به‌روزرسانی می‌شوند.') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <label for="smtp-provider" class="text-sm font-medium text-gray-600 dark:text-gray-300">
                    {{ __('پیکربندی سریع') }}
                </label>
                <select id="smtp-provider"
                        class="form-select min-w-[10rem]"
                        @if (config('app.demo_mode', false)) disabled @endif>
                    <option value="">{{ __('انتخاب کنید') }}</option>
                    <option value="gmail">{{ __('Gmail (App Password)') }}</option>
                    <option value="mailtrap">{{ __('Mailtrap') }}</option>
                    <option value="reset">{{ __('بازنشانی فیلدها') }}</option>
                </select>
            </div>
        </div>
    </div>

    <div class="space-y-6 border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Mail Driver (MAIL_MAILER)') }}
                </label>
                <select name="mail_mailer"
                        class="form-select"
                        @if (config('app.demo_mode', false)) disabled @endif>
                    @foreach (['smtp', 'sendmail', 'log', 'array'] as $driver)
                        <option value="{{ $driver }}" @selected($mailMailer === $driver)>
                            {{ strtoupper($driver) }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">
                    {{ __('برای اغلب سرویس‌ها گزینه SMTP را انتخاب کنید. حالت LOG خروجی را داخل فایل‌های لاگ ثبت می‌کند.') }}
                </p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('آدرس سرور (MAIL_HOST)') }}
                </label>
                <input type="text"
                       name="mail_host"
                       value="{{ $mailHost }}"
                       placeholder="smtp.example.com"
                       class="form-input"
                       @if (config('app.demo_mode', false)) disabled @endif>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('پورت (MAIL_PORT)') }}
                </label>
                <input type="number"
                       name="mail_port"
                       value="{{ $mailPort }}"
                       placeholder="587"
                       class="form-input"
                       @if (config('app.demo_mode', false)) disabled @endif>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('نام کاربری (MAIL_USERNAME)') }}
                </label>
                <input type="text"
                       name="mail_username"
                       value="{{ $mailUsername }}"
                       placeholder="user@example.com"
                       class="form-input"
                       autocomplete="username"
                       @if (config('app.demo_mode', false)) disabled @endif>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('رمز عبور / App Password (MAIL_PASSWORD)') }}
                </label>
                <input type="password"
                       name="mail_password"
                       value="{{ $mailPassword }}"
                       placeholder="••••••••"
                       class="form-input"
                       autocomplete="current-password"
                       @if (config('app.demo_mode', false)) disabled @endif>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">
                    {{ __('برای Gmail حتماً از App Password استفاده کنید (Google Account → Security → App passwords).') }}
                </p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('نوع رمزنگاری (MAIL_ENCRYPTION)') }}
                </label>
                <select name="mail_encryption"
                        class="form-select"
                        @if (config('app.demo_mode', false)) disabled @endif>
                    @foreach ([null => __('بدون رمزنگاری'), 'tls' => 'TLS', 'ssl' => 'SSL'] as $key => $label)
                        <option value="{{ $key }}" @selected((string) $mailEncryption === (string) $key)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">
                    {{ __('Gmail از TLS روی پورت 587 و SSL روی پورت 465 پشتیبانی می‌کند.') }}
                </p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('نشانی فرستنده (MAIL_FROM_ADDRESS)') }}
                </label>
                <input type="email"
                       name="mail_from_address"
                       value="{{ $mailFromAddress }}"
                       placeholder="no-reply@example.com"
                       class="form-input"
                       @if (config('app.demo_mode', false)) disabled @endif>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('نام نمایشی فرستنده (MAIL_FROM_NAME)') }}
                </label>
                <input type="text"
                       name="mail_from_name"
                       value="{{ $mailFromName }}"
                       placeholder="{{ config('app.name') }}"
                       class="form-input"
                       @if (config('app.demo_mode', false)) disabled @endif>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Reply-To (MAIL_REPLY_TO_ADDRESS)') }}
                </label>
                <input type="email"
                       name="mail_reply_to_address"
                       value="{{ $mailReplyToAddress }}"
                       placeholder="{{ __('در صورت نیاز وارد کنید') }}"
                       class="form-input"
                       @if (config('app.demo_mode', false)) disabled @endif>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('نام Reply-To (MAIL_REPLY_TO_NAME)') }}
                </label>
                <input type="text"
                       name="mail_reply_to_name"
                       value="{{ $mailReplyToName }}"
                       placeholder="{{ __('در صورت نیاز وارد کنید') }}"
                       class="form-input"
                       @if (config('app.demo_mode', false)) disabled @endif>
            </div>
        </div>

        <div class="rounded-md border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-500/40 dark:bg-blue-500/10 dark:text-blue-100">
            <ul class="list-disc space-y-1 ps-5">
                <li>{{ __('پس از ذخیره‌سازی، فایل .env به‌روزرسانی و مقادیر در تنظیمات سیستم ثبت می‌شود.') }}</li>
                <li>{{ __('برای اعمال کامل تغییرات، اجرای php artisan config:clear و سپس php artisan cache:clear توصیه می‌شود.') }}</li>
                <li>{{ __('در Gmail ابتدا احراز هویت دو مرحله‌ای را فعال کرده و یک App Password بسازید؛ مقدار تولید شده را در فیلد رمز عبور قرار دهید.') }}</li>
            </ul>
        </div>
    </div>
</div>

<div class="mt-6">
    <div class="rounded-md border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            <h3 class="text-base font-medium text-gray-700 dark:text-white/90">
                {{ __('Integration Setting') }}
            </h3>
        </div>
        <div class="space-y-6 border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
            <div class="relative">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Google Analytics') }}
                </label>
                <textarea name="google_analytics_script" rows="6" placeholder="{{ __('Paste your Google Analytics script here') }}"
                    @if (config('app.demo_mode', false)) disabled @endif
                    class="form-control h-20"
                    data-tooltip-target="tooltip-google-analytics">{{ config('settings.google_analytics_script') ?? '' }}</textarea>

                @if (config('app.demo_mode', false))
                <div id="tooltip-google-analytics" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700">
                    {{ __('Editing this script is disabled in demo mode.') }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                @endif

                <p class="mt-2 text-sm text-gray-500 dark:text-gray-300">
                    {{ __('Learn more about Google Analytics and how to set it up:') }}
                    <a href="https://analytics.google.com/" target="_blank" class="text-primary hover:underline">
                        {{ __('Google Analytics') }}
                    </a>
                </p>
            </div>
        </div>

        {!! Hook::applyFilters(SettingFilterHook::SETTINGS_INTEGRATIONS_TAB_BEFORE_SECTION_END, '') !!}
    </div>
</div>
{!! Hook::applyFilters(SettingFilterHook::SETTINGS_INTEGRATIONS_TAB_AFTER_SECTION_END, '') !!}

<div class="mt-6">
    @include('backend.pages.settings.ai-settings')
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const providerSelect = document.getElementById('smtp-provider');
        if (!providerSelect) {
            return;
        }

        const fieldMap = {
            mailer: document.querySelector('[name="mail_mailer"]'),
            host: document.querySelector('[name="mail_host"]'),
            port: document.querySelector('[name="mail_port"]'),
            username: document.querySelector('[name="mail_username"]'),
            password: document.querySelector('[name="mail_password"]'),
            encryption: document.querySelector('[name="mail_encryption"]'),
        };

        providerSelect.addEventListener('change', function () {
            switch (this.value) {
                case 'gmail':
                    if (fieldMap.mailer) fieldMap.mailer.value = 'smtp';
                    if (fieldMap.host) fieldMap.host.value = 'smtp.gmail.com';
                    if (fieldMap.port) fieldMap.port.value = '587';
                    if (fieldMap.encryption) fieldMap.encryption.value = 'tls';
                    break;
                case 'mailtrap':
                    if (fieldMap.mailer) fieldMap.mailer.value = 'smtp';
                    if (fieldMap.host) fieldMap.host.value = 'smtp.mailtrap.io';
                    if (fieldMap.port) fieldMap.port.value = '2525';
                    if (fieldMap.encryption) fieldMap.encryption.value = '';
                    break;
                case 'reset':
                    Object.values(fieldMap).forEach(function (input) {
                        if (!input) {
                            return;
                        }
                        if (input === fieldMap.password) {
                            input.value = '';
                            return;
                        }
                        input.value = '';
                    });
                    break;
            }

            if (this.value !== '') {
                setTimeout(() => this.value = '', 200);
            }
        });
    });
</script>
@endpush

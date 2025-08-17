@php echo ld_apply_filters('settings_recaptcha_integrations_tab_before_section_start', ''); @endphp
<div class="rounded-md border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="px-5 py-4 sm:px-6 sm:py-5">
        <h3 class="text-base font-medium text-gray-700 dark:text-white/90">
            {{ __('reCAPTCHA Integrations') }}
        </h3>
    </div>

    <div class="space-y-6 border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
        <div class="relative">
            <label class="form-label">
                {{ __('reCAPTCHA Site Key') }}
            </label>
            <input type="text" name="recaptcha_site_key" placeholder="{{ __('Enter your reCAPTCHA site key') }}"
                @if (config('app.demo_mode', false)) disabled @endif
                class="form-control"
                value="{{ config('settings.recaptcha_site_key') ?? '' }}">

            @if (config('app.demo_mode', false))
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ __('Editing this field is disabled in demo mode.') }}
            </div>
            @endif
        </div>

        <div class="relative">
            <label class="form-label">
                {{ __('reCAPTCHA Secret Key') }}
            </label>
            <input type="password" name="recaptcha_secret_key" placeholder="{{ __('Enter your reCAPTCHA secret key') }}"
                @if (config('app.demo_mode', false)) disabled @endif
                class="form-control"
                value="{{ config('settings.recaptcha_secret_key') ?? '' }}">

            @if (config('app.demo_mode', false))
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ __('Editing this field is disabled in demo mode.') }}
            </div>
            @endif
        </div>

        <div class="relative">
            <label class="form-label">
                {{ __('Enable reCAPTCHA on Pages') }}
            </label>
            @php
                $availablePages = \App\Services\RecaptchaService::getAvailablePages();
                $enabledPages = json_decode(config('settings.recaptcha_enabled_pages', '[]'), true) ?: [];
            @endphp

            <div class="space-y-2">
                @foreach($availablePages as $page => $label)
                <label class="flex items-center">
                    <input type="checkbox" name="recaptcha_enabled_pages[]" value="{{ $page }}"
                        @if(in_array($page, $enabledPages)) checked @endif
                        @if (config('app.demo_mode', false)) disabled @endif
                        class="form-checkbox rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                </label>
                @endforeach
            </div>

            @if (config('app.demo_mode', false))
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ __('Editing these options is disabled in demo mode.') }}
            </div>
            @endif

            <p class="mt-2 text-sm text-gray-500 dark:text-gray-300">
                {{ __('Learn more about Google reCAPTCHA and how to set it up:') }}
                <a href="https://www.google.com/recaptcha/" target="_blank" class="text-primary hover:underline">
                    {{ __('Google reCAPTCHA') }}
                </a>
            </p>
        </div>
    </div>
</div>
@php echo ld_apply_filters('settings_recaptcha_integrations_tab_before_section_end', ''); @endphp

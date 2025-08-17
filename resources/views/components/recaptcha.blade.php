@props(['page'])

@php
    $recaptchaService = app(\App\Services\RecaptchaService::class);
    $isEnabled = $recaptchaService->isEnabledForPage($page);
    $siteKey = $recaptchaService->getSiteKey();
@endphp

@if($isEnabled && $siteKey)
    <div class="mt-4">
        <div class="g-recaptcha" data-sitekey="{{ $siteKey }}"></div>
        @error('recaptcha')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    @push('scripts')
        {!! $recaptchaService->getScriptTag() !!}
    @endpush
@endif
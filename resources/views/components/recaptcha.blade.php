@props(['page'])

@php
    $recaptchaService = app(\App\Services\RecaptchaService::class);
    $isEnabled = $recaptchaService->isEnabledForPage($page);
    $siteKey = $recaptchaService->getSiteKey();
@endphp

@if($isEnabled && $siteKey)
    {{-- Hidden input field for reCAPTCHA v3 token --}}
    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response-{{ $page }}">
    
    @error('recaptcha')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror

    @push('scripts')
        {!! $recaptchaService->getScriptTag() !!}

        <style>
            .grecaptcha-badge {
                left: 20px !important;
                right: auto !important;
                bottom: 20px !important;
                z-index: 1000;
            }
        </style>
        
        <script>
            grecaptcha.ready(function() {
                // Find the form containing this reCAPTCHA
                const recaptchaInput = document.getElementById('g-recaptcha-response-{{ $page }}');
                const form = recaptchaInput.closest('form');
                
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        grecaptcha.execute('{{ $siteKey }}', {action: '{{ $page }}'}).then(function(token) {
                            recaptchaInput.value = token;
                            form.submit();
                        });
                    });
                }
            });
        </script>
    @endpush
@endif
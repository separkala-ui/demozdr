<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    private string $siteKey;
    private string $secretKey;
    private array $enabledPages;

    public function __construct()
    {
        $this->siteKey = config('settings.recaptcha_site_key', '');
        $this->secretKey = config('settings.recaptcha_secret_key', '');
        $this->enabledPages = json_decode(config('settings.recaptcha_enabled_pages', '[]'), true) ?: [];
    }

    /**
     * Check if reCAPTCHA is enabled for a specific page
     */
    public function isEnabledForPage(string $page): bool
    {
        if (empty($this->siteKey) || empty($this->secretKey)) {
            return false;
        }

        $isEnabled = in_array($page, $this->enabledPages);

        // Apply filter hook to allow modifications
        return ld_apply_filters('recaptcha_is_enabled_for_page', $isEnabled, $page, $this->enabledPages);
    }

    /**
     * Get the reCAPTCHA site key
     */
    public function getSiteKey(): string
    {
        return $this->siteKey;
    }

    /**
     * Verify reCAPTCHA response
     */
    public function verify(Request $request): bool
    {
        $recaptchaResponse = $request->input('g-recaptcha-response');

        if (empty($recaptchaResponse)) {
            return false;
        }

        // Apply filter hook to allow custom verification logic
        $preVerificationResult = ld_apply_filters('recaptcha_pre_verification', null, $request, $recaptchaResponse);
        if ($preVerificationResult !== null) {
            return $preVerificationResult;
        }

        try {
            $verifyUrl = ld_apply_filters('recaptcha_verify_url', 'https://www.google.com/recaptcha/api/siteverify');

            $response = Http::asForm()->post($verifyUrl, [
                'secret' => $this->secretKey,
                'response' => $recaptchaResponse,
                'remoteip' => $request->ip(),
            ]);

            $result = $response->json();
            $isValid = $result['success'] ?? false;

            // Apply filter hook to allow custom post-verification logic
            return ld_apply_filters('recaptcha_post_verification', $isValid, $result, $request);
        } catch (\Exception $e) {
            return ld_apply_filters('recaptcha_verification_exception', false, $e, $request);
        }
    }

    /**
     * Get reCAPTCHA HTML for frontend
     */
    public function getHtml(): string
    {
        if (empty($this->siteKey)) {
            return '';
        }

        return sprintf(
            '<div class="g-recaptcha" data-sitekey="%s"></div>',
            htmlspecialchars($this->siteKey)
        );
    }

    /**
     * Get reCAPTCHA script tag
     */
    public function getScriptTag(): string
    {
        return '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
    }

    /**
     * Get available pages for reCAPTCHA
     */
    public static function getAvailablePages(): array
    {
        return ld_apply_filters('recaptcha_available_pages', [
            'login' => __('Login'),
            'registration' => __('Registration'),
            'forgot_password' => __('Forgot Password'),
        ]);
    }
}

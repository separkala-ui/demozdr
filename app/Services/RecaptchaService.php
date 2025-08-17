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
    private float $scoreThreshold;

    public function __construct()
    {
        $this->siteKey = config('settings.recaptcha_site_key', '');
        $this->secretKey = config('settings.recaptcha_secret_key', '');
        $this->enabledPages = json_decode(config('settings.recaptcha_enabled_pages', '[]'), true) ?: [];
        $this->scoreThreshold = (float) config('settings.recaptcha_score_threshold', 0.5);
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
        return ld_apply_filters('recaptcha_is_enabled_for_page', $isEnabled, $page);
    }

    /**
     * Get the reCAPTCHA site key
     */
    public function getSiteKey(): string
    {
        return $this->siteKey;
    }

    /**
     * Verify reCAPTCHA v3 response.
     */
    public function verify(Request $request, string $action = 'general'): bool
    {
        $recaptchaResponse = $request->input('g-recaptcha-response');

        if (empty($recaptchaResponse)) {
            return false;
        }

        // Apply filter hook to allow custom verification logic
        $preVerificationResult = ld_apply_filters('recaptcha_pre_verification', null, $request);
        if ($preVerificationResult !== null && $preVerificationResult !== '') {
            return (bool) $preVerificationResult;
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

            // For v3, check the score and action.
            if ($isValid) {
                $score = $result['score'] ?? 0;
                $resultAction = $result['action'] ?? '';

                // Verify the action matches.
                if ($resultAction !== $action) {
                    $isValid = false;
                }

                // Verify the score meets threshold.
                if ($score < $this->scoreThreshold) {
                    $isValid = false;
                }
            }

            // Apply filter hook to allow custom post-verification logic
            $filteredResult = ld_apply_filters('recaptcha_post_verification', $isValid, $result);
            return (bool) $filteredResult;
        } catch (\Exception $e) {
            $exceptionResult = ld_apply_filters('recaptcha_verification_exception', false, $e);
            return (bool) $exceptionResult;
        }
    }

    /**
     * Get reCAPTCHA HTML for frontend (v3 doesn't need visible widget)
     */
    public function getHtml(): string
    {
        // v3 doesn't require a visible widget
        return '';
    }

    /**
     * Get reCAPTCHA v3 script tag
     */
    public function getScriptTag(): string
    {
        if (empty($this->siteKey)) {
            return '';
        }

        return sprintf(
            '<script src="https://www.google.com/recaptcha/api.js?render=%s"></script>',
            htmlspecialchars($this->siteKey)
        );
    }

    /**
     * Get the score threshold
     */
    public function getScoreThreshold(): float
    {
        return $this->scoreThreshold;
    }

    /**
     * Get available pages for reCAPTCHA
     */
    public static function getAvailablePages(): array
    {
        return ld_apply_filters('recaptcha_available_pages', [
            'login' => __('Login'),
            'forgot_password' => __('Forgot Password'),
        ]);
    }
}

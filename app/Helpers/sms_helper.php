<?php

use App\Services\SMS\IPPanelService;
use Illuminate\Support\Facades\Log;

if (!function_exists('sms')) {
    /**
     * Get IPPanel SMS Service instance
     */
    function sms(): IPPanelService
    {
        return app(IPPanelService::class);
    }
}

if (!function_exists('send_sms')) {
    /**
     * ارسال پیامک ساده
     * 
     * @param string|array $recipients شماره موبایل یا آرایه‌ای از شماره‌ها
     * @param string $message متن پیام
     * @return array
     */
    function send_sms($recipients, string $message): array
    {
        // اگر SMS غیرفعال باشد
        if (!config('services.ippanel.enabled', false)) {
            Log::info('[SMS DISABLED] SMS would have been sent', [
                'recipients' => $recipients,
                'message' => $message,
            ]);

            return [
                'success' => true,
                'message' => 'SMS service is disabled',
            ];
        }

        // اگر log_only فعال باشد
        if (config('services.ippanel.log_only', true)) {
            Log::info('[SMS LOG-ONLY] SMS would have been sent', [
                'recipients' => $recipients,
                'message' => $message,
            ]);

            return [
                'success' => true,
                'message' => 'SMS logged only (not sent)',
            ];
        }

        return sms()->send($recipients, $message);
    }
}

if (!function_exists('send_pattern_sms')) {
    /**
     * ارسال پیامک پترن
     * 
     * @param string $mobile شماره موبایل
     * @param string $patternCode کد پترن
     * @param array $variables متغیرهای پترن
     * @return array
     */
    function send_pattern_sms(string $mobile, string $patternCode, array $variables): array
    {
        // اگر SMS غیرفعال باشد
        if (!config('services.ippanel.enabled', false)) {
            Log::info('[SMS DISABLED] Pattern SMS would have been sent', [
                'mobile' => $mobile,
                'pattern' => $patternCode,
                'variables' => $variables,
            ]);

            return [
                'success' => true,
                'message' => 'SMS service is disabled',
            ];
        }

        // اگر log_only فعال باشد
        if (config('services.ippanel.log_only', true)) {
            Log::info('[SMS LOG-ONLY] Pattern SMS would have been sent', [
                'mobile' => $mobile,
                'pattern' => $patternCode,
                'variables' => $variables,
            ]);

            return [
                'success' => true,
                'message' => 'SMS logged only (not sent)',
            ];
        }

        return sms()->sendPattern($mobile, $patternCode, $variables);
    }
}


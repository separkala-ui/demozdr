<?php

namespace App\Services\SMS;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * IPPanel SMS Service
 * 
 * سرویس ارسال پیامک از طریق IPPanel
 * API Documentation: https://docs.ippanel.com/
 */
class IPPanelService
{
    /**
     * API Base URL
     */
    private const BASE_URL = 'https://api2.ippanel.com/api/v1';

    /**
     * API Key
     */
    private string $apiKey;

    /**
     * Originator (شماره فرستنده)
     */
    private string $originator;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apiKey = config('services.ippanel.api_key');
        $this->originator = config('services.ippanel.originator');

        if (empty($this->apiKey)) {
            throw new Exception('IPPanel API Key is not configured');
        }
    }

    /**
     * ارسال پیامک ساده
     * 
     * @param string|array $recipients شماره موبایل یا آرایه‌ای از شماره‌ها
     * @param string $message متن پیام
     * @return array
     */
    public function send($recipients, string $message): array
    {
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post(self::BASE_URL . '/sms/send/webservice/single', [
                'recipient' => $recipients,
                'sender' => $this->originator,
                'message' => $message,
            ]);

            $result = $response->json();

            if ($response->successful()) {
                Log::info('SMS sent successfully', [
                    'recipients' => $recipients,
                    'response' => $result,
                ]);

                return [
                    'success' => true,
                    'message_id' => $result['message_id'] ?? null,
                    'data' => $result,
                ];
            } else {
                Log::error('SMS sending failed', [
                    'recipients' => $recipients,
                    'status' => $response->status(),
                    'response' => $result,
                ]);

                return [
                    'success' => false,
                    'error' => $result['message'] ?? 'Unknown error',
                    'data' => $result,
                ];
            }
        } catch (Exception $e) {
            Log::error('SMS sending exception', [
                'recipients' => $recipients,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * ارسال پیامک پترن
     * 
     * @param string $mobile شماره موبایل
     * @param string $patternCode کد پترن
     * @param array $variables متغیرهای پترن
     * @return array
     */
    public function sendPattern(string $mobile, string $patternCode, array $variables): array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post(self::BASE_URL . '/sms/pattern/normal/send', [
                'code' => $patternCode,
                'sender' => $this->originator,
                'recipient' => $mobile,
                'variable' => $variables,
            ]);

            $result = $response->json();

            if ($response->successful()) {
                Log::info('Pattern SMS sent successfully', [
                    'mobile' => $mobile,
                    'pattern' => $patternCode,
                    'response' => $result,
                ]);

                return [
                    'success' => true,
                    'message_id' => $result['message_id'] ?? null,
                    'bulk_id' => $result['bulk_id'] ?? null,
                    'data' => $result,
                ];
            } else {
                Log::error('Pattern SMS sending failed', [
                    'mobile' => $mobile,
                    'pattern' => $patternCode,
                    'status' => $response->status(),
                    'response' => $result,
                ]);

                return [
                    'success' => false,
                    'error' => $result['message'] ?? 'Unknown error',
                    'data' => $result,
                ];
            }
        } catch (Exception $e) {
            Log::error('Pattern SMS sending exception', [
                'mobile' => $mobile,
                'pattern' => $patternCode,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * دریافت اعتبار
     * 
     * @return array
     */
    public function getCredit(): array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->post(self::BASE_URL . '/sms/credit');

            $result = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'credit' => $result['credit'] ?? 0,
                    'data' => $result,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['message'] ?? 'Unknown error',
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * دریافت وضعیت پیام
     * 
     * @param string $messageId شناسه پیام
     * @return array
     */
    public function getMessageStatus(string $messageId): array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->post(self::BASE_URL . '/sms/status', [
                'message_id' => $messageId,
            ]);

            $result = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $result['status'] ?? null,
                    'data' => $result,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['message'] ?? 'Unknown error',
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * پترن‌های پیش‌فرض سیستم
     */

    /**
     * ارسال پیامک خوش‌آمدگویی هنگام ثبت‌نام
     */
    public function sendWelcomeSMS(string $mobile, string $name): array
    {
        $patternCode = config('services.ippanel.patterns.welcome');

        if (empty($patternCode)) {
            // اگر پترن تعریف نشده، پیامک ساده ارسال کن
            return $this->send($mobile, "سلام {$name} عزیز،\n\nبه سیستم ZDR خوش آمدید. اطلاعات ورود به حساب شما ایجاد شده است.");
        }

        return $this->sendPattern($mobile, $patternCode, [
            'name' => $name,
        ]);
    }

    /**
     * ارسال پیامک هنگام ساخت شعبه جدید
     */
    public function sendBranchCreatedSMS(string $mobile, string $branchName, string $managerName): array
    {
        $patternCode = config('services.ippanel.patterns.branch_created');

        if (empty($patternCode)) {
            return $this->send($mobile, "سلام {$managerName} عزیز،\n\nشعبه {$branchName} با موفقیت ایجاد شد و شما به عنوان مسئول آن منصوب شدید.");
        }

        return $this->sendPattern($mobile, $patternCode, [
            'manager_name' => $managerName,
            'branch_name' => $branchName,
        ]);
    }

    /**
     * ارسال پیامک اطلاع‌رسانی عمومی
     */
    public function sendAnnouncementSMS(string $mobile, string $title, string $message): array
    {
        $patternCode = config('services.ippanel.patterns.announcement');

        if (empty($patternCode)) {
            return $this->send($mobile, "{$title}\n\n{$message}");
        }

        return $this->sendPattern($mobile, $patternCode, [
            'title' => $title,
            'message' => $message,
        ]);
    }

    /**
     * ارسال پیامک تایید تراکنش
     */
    public function sendTransactionApprovedSMS(string $mobile, string $amount, string $reference): array
    {
        $patternCode = config('services.ippanel.patterns.transaction_approved');

        if (empty($patternCode)) {
            return $this->send($mobile, "تراکنش شماره {$reference} به مبلغ {$amount} ریال تایید شد.");
        }

        return $this->sendPattern($mobile, $patternCode, [
            'reference' => $reference,
            'amount' => $amount,
        ]);
    }

    /**
     * ارسال پیامک رد تراکنش
     */
    public function sendTransactionRejectedSMS(string $mobile, string $amount, string $reference, string $reason = ''): array
    {
        $patternCode = config('services.ippanel.patterns.transaction_rejected');

        if (empty($patternCode)) {
            $message = "تراکنش شماره {$reference} به مبلغ {$amount} ریال رد شد.";
            if ($reason) {
                $message .= "\nدلیل: {$reason}";
            }
            return $this->send($mobile, $message);
        }

        return $this->sendPattern($mobile, $patternCode, [
            'reference' => $reference,
            'amount' => $amount,
            'reason' => $reason ?: 'نامشخص',
        ]);
    }

    /**
     * ارسال پیامک درخواست بازبینی تراکنش
     */
    public function sendTransactionRevisionSMS(string $mobile, string $amount, string $reference, string $reason = ''): array
    {
        $patternCode = config('services.ippanel.patterns.transaction_revision');

        if (empty($patternCode)) {
            $message = "تراکنش شماره {$reference} به مبلغ {$amount} ریال برای بازبینی ارسال شد.";
            if ($reason) {
                $message .= "\nدلیل: {$reason}";
            }
            return $this->send($mobile, $message);
        }

        return $this->sendPattern($mobile, $patternCode, [
            'reference' => $reference,
            'amount' => $amount,
            'reason' => $reason ?: 'نامشخص',
        ]);
    }
}


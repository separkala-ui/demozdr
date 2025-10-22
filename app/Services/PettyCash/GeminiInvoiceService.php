<?php

namespace App\Services\PettyCash;

use App\Exceptions\SmartInvoiceException;
use App\Services\PettyCash\Data\SmartInvoiceExtraction;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class GeminiInvoiceService
{
    public function __construct(
        private readonly HttpFactory $http
    ) {
    }

    public function isEnabled(): bool
    {
        $enabled = config('settings.smart-invoice.gemini.enabled', config('smart-invoice.gemini.enabled'));
        $apiKey = config('settings.smart-invoice.gemini.api_key', config('smart-invoice.gemini.api_key'));

        return (bool) $enabled && filled($apiKey);
    }

    public function analyticsEnabled(): bool
    {
        return (bool) config('settings.smart-invoice.analytics', config('smart-invoice.analytics', false));
    }

    /**
     * @param  TemporaryUploadedFile|UploadedFile|null  $invoice
     * @param  TemporaryUploadedFile|UploadedFile|null  $receipt
     * @param  array<string, mixed>  $context
     */
    public function extractFromUploads($invoice, $receipt, array $context = []): SmartInvoiceExtraction
    {
        if (! $this->isEnabled()) {
            throw SmartInvoiceException::serviceDisabled();
        }

        if (! $invoice && ! $receipt) {
            throw SmartInvoiceException::attachmentsMissing();
        }

        try {
            $response = $this->requestGeminiExtraction($invoice, $receipt, $context);
        } catch (\Throwable $exception) {
            throw SmartInvoiceException::requestFailed(
                __('smart_invoice.gemini_error', ['message' => $this->stringifyMessage($exception->getMessage())])
            );
        }

        if ($response->failed()) {
            $message = $response->json('error.message') ?? $response->body();
            Log::warning('Gemini invoice extraction failed', [
                'status' => $response->status(),
                'body' => $message,
            ]);

            throw SmartInvoiceException::requestFailed(
                __('دریافت اطلاعات هوشمند فاکتور از Gemini با خطا مواجه شد: :message', [
                    'message' => $this->stringifyMessage($message) ?: __('پاسخ نامعتبر'),
                ])
            );
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw SmartInvoiceException::requestFailed(__('پاسخ نامعتبر از Gemini دریافت شد.'));
        }

        return $this->parseGeminiResponse($payload);
    }

    /**
     * @param  TemporaryUploadedFile|UploadedFile|null  $invoice
     * @param  TemporaryUploadedFile|UploadedFile|null  $receipt
     * @param  array<string, mixed>  $context
     */
    protected function requestGeminiExtraction($invoice, $receipt, array $context): Response
    {
        $apiKey = config('settings.smart-invoice.gemini.api_key', config('smart-invoice.gemini.api_key'));
        $model = config('settings.smart-invoice.gemini.model', config('smart-invoice.gemini.model'));
        $timeout = config('settings.smart-invoice.gemini.timeout', config('smart-invoice.gemini.timeout'));

        // Convert image to base64
        $imageData = null;
        if ($invoice) {
            $imageData = base64_encode($this->readStream($invoice));
        } elseif ($receipt) {
            $imageData = base64_encode($this->readStream($receipt));
        }

        if (! $imageData) {
            throw SmartInvoiceException::requestFailed(__('فایل تصویر قابل خواندن نیست.'));
        }

        // Prepare Gemini request
        $requestData = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $this->getPromptText()
                        ],
                        [
                            'inline_data' => [
                                'mime_type' => $this->getMimeType($invoice ?: $receipt),
                                'data' => $imageData
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'topK' => 32,
                'topP' => 1,
                'maxOutputTokens' => 2048,
            ]
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        return $this->http
            ->timeout($timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($url, $requestData);
    }

    protected function parseGeminiResponse(array $payload): SmartInvoiceExtraction
    {
        $candidates = $payload['candidates'] ?? [];
        if (empty($candidates)) {
            throw SmartInvoiceException::requestFailed(__('پاسخ نامعتبر از Gemini دریافت شد.'));
        }

        $content = $candidates[0]['content']['parts'][0]['text'] ?? '';
        
        // Parse JSON response from Gemini
        $extractedData = $this->parseJsonResponse($content);
        
        return SmartInvoiceExtraction::fromArray($extractedData);
    }

    protected function parseJsonResponse(string $content): array
    {
        // Try to extract JSON from the response
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $jsonData = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $jsonData;
            }
        }

        // Fallback: try to parse the entire content as JSON
        $jsonData = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $jsonData;
        }

        // If no valid JSON found, return empty data
        return [
            'total_amount' => null,
            'invoice_number' => null,
            'reference_number' => null,
            'currency' => 'IRR',
            'confidence' => 0.0,
            'ocr_score' => 0.0,
            'line_items' => [],
            'analytics' => [],
            'raw_payload' => ['content' => $content],
        ];
    }

    protected function getPromptText(): string
    {
        return 'You are an expert Iranian invoice and receipt data extraction system. Extract ALL information from the provided image with maximum accuracy.

CRITICAL REQUIREMENTS:
1. Extract data in the EXACT structure provided below
2. Use Persian (Farsi) for all text fields with _fa suffix
3. All numerical amounts must be in Rials (not Tomans) - multiply by 10 if needed
4. Handle incomplete or damaged invoices gracefully
5. Validate that totals match line item sums
6. Return ONLY valid JSON, no markdown formatting

{
  "document_type_fa": "نوع دقیق سند (مثلاً: صورتحساب فروش کالا و خدمات، رسید پرداخت کارت‌خوان، فاکتور طلا، قبض)",
  "invoice_serial_number": "شماره سریال/شماره فاکتور/شماره سند (فقط اعداد و حروف انگلیسی، بدون خط خوردگی)",
  "transaction_id_or_request_number": "شناسه تراکنش/شماره درخواست/شماره قرارداد (در صورت وجود)",
  "date_jalali": "تاریخ صدور/تراکنش (فقط شمسی، با فرمت YYYY/MM/DD)",
  "time": "ساعت/زمان دقیق تراکنش (با فرمت HH:MM:SS)",
  "origin_of_document_fa": "نام فروشگاه/شرکت/سازمان صادرکننده (مثلاً: اسپرت کار، طلاسازی محمد شهاب، ای استخدام، بانک ملی)",
  
  "seller_info": {
    "name_fa": "نام کامل شخص حقیقی/حقوقی فروشنده",
    "economic_code_or_national_id": "کد اقتصادی/شناسه ملی/شماره ثبت (فروشنده)",
    "address_fa": "نشانی کامل فروشنده",
    "phone_number": "تلفن ثابت/همراه فروشنده",
    "seller_stamp_and_signature_status": "وضعیت مهر و امضا فروشنده (Yes/No)"
  },
  
  "buyer_info": {
    "name_fa": "نام کامل شخص حقیقی/حقوقی خریدار",
    "economic_code_or_national_id": "کد اقتصادی/شناسه ملی (خریدار)",
    "national_code": "کد ملی (خریدار)",
    "address_fa": "نشانی کامل خریدار",
    "phone_number": "تلفن تماس خریدار",
    "buyer_signature_status": "وضعیت امضا خریدار (Yes/No)"
  },
  
        "items_details": {
          "table_present": "آیا جدول اقلام کالا/خدمات وجود دارد؟ (Yes/No)",
          "item_structure": [
            {
              "row_number": "شماره ردیف (فقط عدد، مثلاً: ۱، ۲، ۳)",
              "product_or_service_description_fa": "شرح دقیق کالا یا خدمات/متن آگهی",
              "unit_fa": "واحد اندازه‌گیری (مثلاً: عدد، گرم، کیلوگرم، ریال)",
              "quantity_numerical": "تعداد/مقدار (فقط عدد)",
              "unit_price_raw_fa": "مبلغ واحد (به صورت خام از سند، شامل تومان/ریال)",
              "unit_price_in_rial_numerical": "مبلغ واحد (به ریال، فقط عدد انگلیسی)",
              "total_price_raw_fa": "مبلغ کل ردیف (به صورت خام از سند، شامل تومان/ریال)",
              "total_price_in_rial_numerical": "مبلغ کل ردیف (به ریال، فقط عدد انگلیسی)",
              "discount_per_item_in_rial_numerical": "تخفیف برای این ردیف (به ریال، فقط عدد)",
              "total_after_discount_in_rial_numerical": "مبلغ کل پس از تخفیف (به ریال، فقط عدد انگلیسی)",
              "transport_cost_in_rial_numerical": "هزینه حمل و نقل (به ریال، فقط عدد، در صورت وجود)",
              "service_cost_in_rial_numerical": "هزینه خدمات (به ریال، فقط عدد، در صورت وجود)"
            }
          ]
        },
  
  "financial_summary": {
    "total_before_discount_in_rial_numerical": "جمع کل مبلغ کالا/خدمات قبل از تخفیف و مالیات (به ریال، فقط عدد)",
    "total_discount_in_rial_numerical": "جمع کل تخفیفات (به ریال، فقط عدد)",
    "total_after_discount_in_rial_numerical": "جمع کل پس از تخفیف (به ریال، فقط عدد)",
    "transport_total_in_rial_numerical": "جمع کل هزینه حمل و نقل (به ریال، فقط عدد، در صورت وجود)",
    "service_total_in_rial_numerical": "جمع کل هزینه خدمات (به ریال، فقط عدد، در صورت وجود)",
    "net_taxable_amount_in_rial_numerical": "مبلغ کل مشمول مالیات (پس از تخفیف، به ریال، فقط عدد)",
    "vat_and_tolls_percentage": "درصد مالیات بر ارزش افزوده (VAT) (فقط عدد، در صورت قابل تشخیص بودن)",
    "vat_and_tolls_amount_in_rial_numerical": "مبلغ مالیات و عوارض ارزش افزوده (به ریال، فقط عدد)",
    "subtotal_in_rial_numerical": "جمع جزء (جمع مبالغ ردیف‌ها قبل از مالیات، به ریال، فقط عدد)",
    "final_amount_raw_fa": "مبلغ نهایی قابل پرداخت (به صورت خام از سند، شامل تومان/ریال)",
    "final_amount_in_rial_numerical": "مبلغ نهایی قابل پرداخت (به ریال، فقط عدد انگلیسی)",
    "currency_used": "واحد پول غالب در فاکتور (ریال/تومان)"
  },
  
  "payment_and_banking_details": {
    "payment_status_fa": "وضعیت پرداخت (مثلاً: پرداخت شده، عملیات موفق، وارد نشده)",
    "payment_method_fa": "نحوه پرداخت (مثلاً: نقدی، غیرنقدی، درگاه بانکی، کارت)",
    "terminal_or_acquirer_number": "شماره پایانه/شماره پذیرنده/شماره ترمینال (فقط عدد)",
    "reference_number_or_sheba": "شماره مرجع/شماره ارجاع/شماره شبای حساب (فقط عدد/حروف انگلیسی)",
    "tracking_code": "شماره پیگیری/کد پیگیری (فقط عدد/حروف انگلیسی)",
    "card_number_masked": "شماره کارت (ماسک شده - فقط ۴ رقم اول و ۴ رقم آخر - یا کامل در صورت عدم ماسک‌شدگی در سند)",
    "bank_name_fa": "نام بانک/درگاه پرداخت"
  },
  
  "gold_and_jewelry_details": {
    "applies_to_gold": "آیا این فاکتور طلا/جواهر است؟ (Yes/No)",
    "karat": "عیار (در صورت وجود، مثلاً: ۱۸، ۷۵۰)",
    "weight_grams_numerical": "وزن خالص طلا (گرم، فقط عدد)",
    "labor_cost_in_rial_numerical": "اجرت ساخت (به ریال، فقط عدد)",
    "profit_percentage_numerical": "درصد سود تک‌فروشی (فقط عدد)"
  },
  
  "notes_and_conditions": {
    "notes_fa": "توضیحات/شرایط و نحوه فروش/اطلاعات اضافی"
  },
  
  "confidence": "اعتماد (0.0 تا 1.0)"
}

نکات مهم:
- تمام مبالغ را به ریال تبدیل کنید (تومان × 10 = ریال)
- اطلاعات حساس (شماره کارت، کد ملی) را ماسک کنید (XXXX)
- فقط JSON خالص برگردانید، بدون توضیحات اضافی
- در صورت عدم وجود اطلاعات، از null استفاده کنید

نکات فنی:
- اعداد فارسی را به انگلیسی تبدیل کنید (۰→0, ۱→1, ...)
- واحد پول اصلی فاکتور را نگه دارید، فقط مبالغ را تبدیل کنید
- شماره ردیف‌ها را به ترتیب درست مرتب کنید
- قیمت واحد را با دقت بخوانید - اگر قیمت واحد ۷ تومان است، مبلغ واحد ۷۰ ریال است (تومان × ۱۰)
- اگر قیمت واحد ۷ تومان است و تعداد ۲ است، مجموع باید ۱۴۰ ریال باشد نه ۱۴۰ تومان
- اگر مبلغ نهایی ۱۹۰ تومان است، باید ۱۹۰۰ ریال باشد

سناریوهای مختلف فاکتور:
1. فاکتور بدون تخفیف و مالیات: فقط مبلغ کالاها
2. فاکتور با تخفیف: مبلغ کالا - تخفیف = مبلغ نهایی
3. فاکتور با مالیات: (مبلغ کالا - تخفیف) + مالیات = مبلغ نهایی
4. فاکتور با حمل و نقل: مبلغ کالا + حمل و نقل = مبلغ نهایی
5. فاکتور با خدمات: مبلغ کالا + خدمات = مبلغ نهایی
6. فاکتور کامل: مبلغ کالا - تخفیف + حمل و نقل + خدمات + مالیات = مبلغ نهایی

محاسبه صحیح مبلغ نهایی:
- اگر تخفیف وجود دارد، از مبلغ کل کم کنید
- اگر حمل و نقل وجود دارد، به مبلغ اضافه کنید
- اگر خدمات وجود دارد، به مبلغ اضافه کنید
- اگر مالیات وجود دارد، به مبلغ اضافه کنید
- مبلغ نهایی = (مبلغ کالا - تخفیف) + حمل و نقل + خدمات + مالیات

نکات مهم برای فاکتورهای فارسی:
- ردیف‌های خالی را نادیده بگیرید (ردیف‌هایی که فقط تعداد 1 دارند اما سایر فیلدها خالی هستند)
- اگر ستون مالیات و عوارض خالی است یا نقطه دارد، آن را 0 در نظر بگیرید
- اگر فیلدها خالی هستند، از null استفاده کنید نه رشته خالی
- شماره سریال و تاریخ را با دقت بخوانید
- نام فروشنده و خریدار را حتی اگر کوتاه باشد، استخراج کنید
- مبالغ را دقیقاً همان‌طور که در فاکتور نوشته شده، بخوانید

مثال فاکتور چالاک حساب:
- شماره سریال: 40211
- تاریخ: 1404/01/01
- فروشنده: چالاک حساب
- خریدار: علیرضا محمدی
- ردیف 1: نرم افزار چالاک حساب - نسخه پایه، تعداد 2، قیمت واحد 900,000 تومان، مجموع 1,800,000 تومان، تخفیف 200,000 تومان، پس از تخفیف 1,600,000 تومان
- ردیف 2: نرم افزار چالاک حساب - نسخه پیشرفته، تعداد 4، قیمت واحد 3,400,000 تومان، مجموع 13,600,000 تومان، تخفیف 400,000 تومان، پس از تخفیف 13,200,000 تومان
- جمع کل: 15,400,000 تومان
- مجموع تخفیف: 600,000 تومان
- مجموع پس از تخفیف: 14,800,000 تومان
- مالیات و عوارض: 0 تومان
- مبلغ نهایی: 14,800,000 تومان

مثال فاکتور جدید (فاکتور اکسل):
- شماره سریال: 1001
- تاریخ: 1403 (ناقص - فقط سال)
- فروشنده: null (خالی)
- خریدار: null (خالی)
- ردیف 1: موس بلوتوثی، تعداد 5، قیمت واحد 1,500,000 ریال، مجموع 7,500,000 ریال، تخفیف 0 ریال، پس از تخفیف 7,500,000 ریال، مالیات 750,000 ریال، مجموع نهایی 8,250,000 ریال
- ردیف 2: مانیتور، تعداد 1، قیمت واحد 135,000,000 ریال، مجموع 135,000,000 ریال، تخفیف 0 ریال، پس از تخفیف 135,000,000 ریال، مالیات 13,500,000 ریال، مجموع نهایی 148,500,000 ریال
- ردیف 3: هاب USB، تعداد 20، قیمت واحد 500,000 ریال، مجموع 10,000,000 ریال، تخفیف 0 ریال، پس از تخفیف 10,000,000 ریال، مالیات 1,000,000 ریال، مجموع نهایی 11,000,000 ریال
- ردیف 4: اسپیکر، تعداد 9، قیمت واحد 25,000,000 ریال، مجموع 225,000,000 ریال، تخفیف 0 ریال، پس از تخفیف 225,000,000 ریال، مالیات 22,500,000 ریال، مجموع نهایی 247,500,000 ریال
- ردیف 5: لپ تاپ لنوو، تعداد 2، قیمت واحد 750,000,000 ریال، مجموع 1,500,000,000 ریال، تخفیف 0 ریال، پس از تخفیف 1,500,000,000 ریال، مالیات 150,000,000 ریال، مجموع نهایی 1,650,000,000 ریال
- ردیف 6: کیبورد، تعداد 5، قیمت واحد 2,500,000 ریال، مجموع 12,500,000 ریال، تخفیف 0 ریال، پس از تخفیف 12,500,000 ریال، مالیات 1,250,000 ریال، مجموع نهایی 13,750,000 ریال
- ردیف 7: کابل HDMI، تعداد 12، قیمت واحد 550,000 ریال، مجموع 6,600,000 ریال، تخفیف 0 ریال، پس از تخفیف 6,600,000 ریال، مالیات 660,000 ریال، مجموع نهایی 7,260,000 ریال
- جمع کل: 1,896,600,000 ریال
- مجموع تخفیف: 0 ریال
- مجموع پس از تخفیف: 1,896,600,000 ریال
- مالیات و عوارض: 189,660,000 ریال
- مبلغ نهایی: 2,086,260,000 ریال

دستورالعمل‌های خاص برای تشخیص فیلدهای خالی:
- اگر ردیفی فقط تعداد 1 دارد اما سایر فیلدها خالی هستند، آن ردیف را نادیده بگیرید
- اگر ستون مالیات و عوارض نقطه (.) دارد، آن را 0 در نظر بگیرید
- اگر فیلدی کاملاً خالی است، null برگردانید
- اگر فیلدی فقط فاصله یا خط تیره دارد، null برگردانید
- اگر فیلدی "نامشخص" یا "ندارد" نوشته شده، null برگردانید
- فقط فیلدهایی که واقعاً دارای محتوا هستند را پر کنید

مدیریت فیلدهای ناقص و مشکل‌دار:
- اگر تاریخ ناقص است (مثل 1403//)، آن را null برگردانید
- اگر شماره فاکتور فقط عدد است (مثل 1001)، آن را به عنوان شماره سریال در نظر بگیرید
- اگر نام فروشنده/خریدار خالی است، null برگردانید
- اگر آدرس خالی است، null برگردانید
- اگر تلفن خالی است، null برگردانید
- اگر کد اقتصادی/ملی خالی است، null برگردانید

مدیریت ستون‌های خالی:
- اگر ستون تخفیف خالی است یا فقط خط تیره (-) دارد، آن را 0 در نظر بگیرید
- اگر ستون مالیات و عوارض خالی است، آن را 0 در نظر بگیرید
- اگر ستون حمل و نقل خالی است، آن را 0 در نظر بگیرید
- اگر ستون خدمات خالی است، آن را 0 در نظر بگیرید

مدیریت متن‌های تداخلی:
- اگر متن قرمز یا واترمارک روی فاکتور است، آن را نادیده بگیرید
- اگر متن "فاکتور رسمی اکسل اتوماتیک" روی فاکتور است، آن را نادیده بگیرید
- اگر مهر و امضا خالی است، null برگردانید
- اگر توضیحات فقط شامل متن‌های سیستم است، آن را نادیده بگیرید

مدیریت اعداد فارسی:
- اعداد فارسی را به انگلیسی تبدیل کنید (۰→0, ۱→1, ۲→2, ۳→3, ۴→4, ۵→5, ۶→6, ۷→7, ۸→8, ۹→9)
- اگر عددی مخلوط فارسی-انگلیسی است، آن را به انگلیسی تبدیل کنید
- اگر عددی با کاما جدا شده (مثل ۱,۵۰۰,۰۰۰)، آن را به صورت 1500000 تبدیل کنید

مدیریت جدول پیچیده:
- اگر جدول دارای ستون‌های زیادی است، فقط ستون‌های مهم را پر کنید
- اگر ستونی خالی است، آن را نادیده بگیرید
- اگر ردیفی خالی است، آن را نادیده بگیرید
- اگر ردیفی فقط تعداد دارد اما سایر فیلدها خالی است، آن را نادیده بگیرید

مدیریت سناریوهای مختلف:
- فاکتور بدون تخفیف: ستون تخفیف را 0 در نظر بگیرید
- فاکتور بدون مالیات: ستون مالیات را 0 در نظر بگیرید
- فاکتور بدون حمل و نقل: ستون حمل و نقل را 0 در نظر بگیرید
- فاکتور بدون خدمات: ستون خدمات را 0 در نظر بگیرید
- فاکتور با فیلدهای خالی: فیلدهای خالی را null برگردانید
- فاکتور با تاریخ ناقص: تاریخ ناقص را null برگردانید';
    }

    protected function getMimeType($file): string
    {
        $mimeType = $file->getMimeType();
        
        // Map common MIME types to Gemini supported types
        return match ($mimeType) {
            'image/jpeg' => 'image/jpeg',
            'image/png' => 'image/png',
            'image/webp' => 'image/webp',
            default => 'image/jpeg'
        };
    }

    protected function readStream($file): string
    {
        $path = $file->getRealPath();

        if (! $path || ! is_readable($path)) {
            throw SmartInvoiceException::requestFailed(__('فایل بارگذاری شده قابل خواندن نیست.'));
        }

        return file_get_contents($path);
    }

    protected function stringifyMessage($message): string
    {
        if (is_string($message)) {
            return $message;
        }

        if (is_array($message)) {
            return json_encode($message, JSON_UNESCAPED_UNICODE);
        }

        return (string) $message;
    }
}

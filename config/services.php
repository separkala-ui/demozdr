<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IPPanel SMS Service
    |--------------------------------------------------------------------------
    |
    | تنظیمات سرویس پیامک IPPanel
    | https://ippanel.com/
    |
    */

    'ippanel' => [
        'api_key' => env('IPPANEL_API_KEY', 'OWZjZTMwYTktYTc1Ni00YTg3LTg2NTYtYWM5NjliZTdiZGE0NDhkNzJjZGMxNmM5NGRmYmZhMWU3ZDkwNmE0MTJlOGE='),
        'originator' => env('IPPANEL_ORIGINATOR', '+985000...'), // شماره فرستنده
        'finance_manager_mobile' => env('FINANCE_MANAGER_MOBILE', ''), // موبایل مدیر مالی برای دریافت اطلاعیه‌ها

        // کدهای پترن (Pattern Codes)
        'patterns' => [
            'welcome' => [
                'code' => env('IPPANEL_PATTERN_WELCOME', ''),
                'variables' => ['name'],
                'description' => 'ارسال پیام خوشامدگویی هنگام ثبت نام کاربر جدید',
            ],
            'branch_created' => [
                'code' => env('IPPANEL_PATTERN_BRANCH_CREATED', ''),
                'variables' => ['manager_name', 'branch_name'],
                'description' => 'اطلاع رسانی به مدیر شعبه هنگام ساخت شعبه جدید',
            ],
            'charge_request' => [
                'code' => env('IPPANEL_PATTERN_CHARGE_REQUEST', ''),
                'variables' => ['manager_name', 'branch_name', 'amount', 'date'],
                'description' => 'ارسال درخواست شارژ به مدیر مالی',
            ],
            'transaction_approved' => [
                'code' => env('IPPANEL_PATTERN_TRANSACTION_APPROVED', ''),
                'variables' => ['reference', 'amount'],
                'description' => 'اطلاع رسانی تایید تراکنش به کاربر',
            ],
            'transaction_rejected' => [
                'code' => env('IPPANEL_PATTERN_TRANSACTION_REJECTED', ''),
                'variables' => ['reference', 'amount', 'reason'],
                'description' => 'اطلاع رسانی رد تراکنش به کاربر',
            ],
            'transaction_revision' => [
                'code' => env('IPPANEL_PATTERN_TRANSACTION_REVISION', ''),
                'variables' => ['reference', 'amount', 'reason'],
                'description' => 'اطلاع رسانی درخواست بازبینی به کاربر',
            ],
        ],

        // تنظیمات عمومی
        'enabled' => env('IPPANEL_ENABLED', false), // فعال/غیرفعال بودن ارسال SMS
        'log_only' => env('IPPANEL_LOG_ONLY', true), // فقط لاگ کن، ارسال نکن (برای تست)
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Gemini AI Service
    |--------------------------------------------------------------------------
    */

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-flash-002'),
    ],

];

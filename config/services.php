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

        // کدهای پترن (Pattern Codes)
        'patterns' => [
            'welcome' => env('IPPANEL_PATTERN_WELCOME', ''),
            'branch_created' => env('IPPANEL_PATTERN_BRANCH_CREATED', ''),
            'announcement' => env('IPPANEL_PATTERN_ANNOUNCEMENT', ''),
            'transaction_approved' => env('IPPANEL_PATTERN_TRANSACTION_APPROVED', ''),
            'transaction_rejected' => env('IPPANEL_PATTERN_TRANSACTION_REJECTED', ''),
            'transaction_revision' => env('IPPANEL_PATTERN_TRANSACTION_REVISION', ''),
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

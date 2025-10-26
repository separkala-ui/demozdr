<?php

return [
    'provider' => env('SMART_INVOICE_PROVIDER', 'gemini'),
    'analytics' => (bool) env('SMART_INVOICE_ANALYTICS', true),
    'confidence_threshold' => (float) env('SMART_INVOICE_CONFIDENCE_THRESHOLD', 0.6),

    'gemini' => [
        'enabled' => (bool) env('SMART_INVOICE_GEMINI_ENABLED', true),
        'api_key' => env('SMART_INVOICE_GEMINI_API_KEY'),
        'model' => env('SMART_INVOICE_GEMINI_MODEL', 'gemini-2.5-flash'),
        'timeout' => (int) env('SMART_INVOICE_GEMINI_TIMEOUT', 45),
        'max_output_tokens' => (int) env('SMART_INVOICE_GEMINI_MAX_TOKENS', 8192),
        'locale' => env('SMART_INVOICE_GEMINI_LOCALE', 'fa-IR'),
    ],

    'openai' => [
        'enabled' => (bool) env('SMART_INVOICE_OPENAI_ENABLED', false),
        'api_key' => env('SMART_INVOICE_OPENAI_API_KEY'),
        'model' => env('SMART_INVOICE_OPENAI_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('SMART_INVOICE_OPENAI_TIMEOUT', 60),
        'max_output_tokens' => (int) env('SMART_INVOICE_OPENAI_MAX_TOKENS', 4096),
        'fallback_to_gemini' => (bool) env('SMART_INVOICE_OPENAI_FALLBACK', true),
    ],

    'validation' => [
        'tolerance' => (float) env('SMART_INVOICE_VALIDATION_TOLERANCE', 1000),
        'enforce_currency_normalisation' => (bool) env('SMART_INVOICE_ENFORCE_CURRENCY_NORMALISATION', true),
    ],
];

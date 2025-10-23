<?php

return [
    'analytics' => (bool) env('SMART_INVOICE_ANALYTICS', true),
    'confidence_threshold' => (float) env('SMART_INVOICE_CONFIDENCE_THRESHOLD', 0.6),

    'gemini' => [
        'enabled' => (bool) env('SMART_INVOICE_GEMINI_ENABLED', true),
        'api_key' => env('SMART_INVOICE_GEMINI_API_KEY'),
        'model' => env('SMART_INVOICE_GEMINI_MODEL', 'gemini-2.5-flash'),
        'timeout' => (int) env('SMART_INVOICE_GEMINI_TIMEOUT', 45),
        'locale' => env('SMART_INVOICE_GEMINI_LOCALE', 'fa-IR'),
    ],

    'validation' => [
        'tolerance' => (float) env('SMART_INVOICE_VALIDATION_TOLERANCE', 1000),
        'enforce_currency_normalisation' => (bool) env('SMART_INVOICE_ENFORCE_CURRENCY_NORMALISATION', true),
    ],
];

<?php

return [
    'endpoint' => env('SMART_INVOICE_SERVICE_URL'),
    'api_key' => env('SMART_INVOICE_API_KEY'),
    'timeout' => env('SMART_INVOICE_TIMEOUT', 45),
    'analytics' => (bool) env('SMART_INVOICE_ANALYTICS', true),
    'confidence_threshold' => (float) env('SMART_INVOICE_CONFIDENCE_THRESHOLD', 0.5),
    
    // Gemini AI Settings
    'gemini' => [
        'enabled' => (bool) env('SMART_INVOICE_GEMINI_ENABLED', false),
        'api_key' => env('SMART_INVOICE_GEMINI_API_KEY'),
        'model' => env('SMART_INVOICE_GEMINI_MODEL', 'gemini-2.5-flash'),
        'timeout' => env('SMART_INVOICE_GEMINI_TIMEOUT', 30),
    ],
    
    // Service Selection
    'primary_service' => env('SMART_INVOICE_PRIMARY_SERVICE', 'python'), // 'python' or 'gemini'
    'fallback_service' => env('SMART_INVOICE_FALLBACK_SERVICE', 'gemini'), // 'python' or 'gemini'
];

<?php

declare(strict_types=1);

return [
    'enabled' => env('ZDR_UPDATE_ENABLED', true),

    'repository' => [
        'name' => env('ZDR_UPDATE_REPO', 'separkala-ui/demozdr'),
        'branch' => env('ZDR_UPDATE_BRANCH', 'main'),
    ],

    'source' => [
        'type' => env('ZDR_UPDATE_SOURCE', 'github'),
        'manifest_url' => env('ZDR_UPDATE_MANIFEST_URL'),
    ],

    'token' => env('ZDR_UPDATE_TOKEN'),

    'cache_ttl' => (int) env('ZDR_UPDATE_CACHE_TTL', 10),

    'lock_ttl' => (int) env('ZDR_UPDATE_LOCK_TTL', 900),
];

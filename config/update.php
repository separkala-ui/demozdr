<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Update Repository Information
    |--------------------------------------------------------------------------
    |
    | Configure how the application checks for new releases. By default the
    | GitHub API is used, however you may switch to a custom manifest by
    | providing the `ZDR_UPDATE_SOURCE=manifest` and a manifest URL.
    |
    */
    'enabled' => env('ZDR_UPDATE_ENABLED', true),

    'repository' => [
        'name' => env('ZDR_UPDATE_REPO', 'separkala-ui/demozdr'),
        'branch' => env('ZDR_UPDATE_BRANCH', 'main'),
    ],

    'source' => [
        'type' => env('ZDR_UPDATE_SOURCE', 'github'),
        'manifest_url' => env('ZDR_UPDATE_MANIFEST_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Version Strategy
    |--------------------------------------------------------------------------
    |
    | When set to `tag`, the updater compares semantic versions (Git tags /
    | GitHub releases). Fallback is commit hash comparison.
    |
    */
    'version_source' => env('ZDR_UPDATE_VERSION_SOURCE', 'commit'), // commit|tag
    'version_file' => env('ZDR_UPDATE_VERSION_FILE', 'package-info.json'),

    'token' => env('ZDR_UPDATE_TOKEN'),

    'cache_ttl' => (int) env('ZDR_UPDATE_CACHE_TTL', 10),

    'lock_ttl' => (int) env('ZDR_UPDATE_LOCK_TTL', 900),
];

<?php

declare(strict_types=1);

return [
    'database' => [
        'directory' => 'db-backups',
        'prefix' => env('DB_BACKUP_PREFIX', 'db-backup_'),
        'compress' => env('DB_BACKUP_COMPRESS', true),
        'default_recipient' => env('DB_BACKUP_RECIPIENT'),
        'notify' => env('DB_BACKUP_NOTIFY', true),
    ],
];

<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Backup\DatabaseBackupService;
use Illuminate\Console\Command;

class RunDailyBackup extends Command
{
    protected $signature = 'app:daily-backup';

    protected $description = 'Create a database backup and email it to the configured recipient';

    public function handle(DatabaseBackupService $backupService): int
    {
        try {
            $backupService->create();
            $this->info('Database backup created successfully.');
        } catch (\Throwable $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            report($e);

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}

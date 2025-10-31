<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Update\UpdateManager;
use Illuminate\Console\Command;

class SyncCurrentVersion extends Command
{
    protected $signature = 'zdr:update:sync';

    protected $description = 'همگام‌سازی نسخه فعلی برنامه با مقدار ذخیره شده در تنظیمات';

    public function handle(UpdateManager $manager): int
    {
        $version = $manager->syncCurrentVersion();
        $this->info("Current version recorded: {$version}");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--name= : Custom backup name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup of the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('🔄 Starting database backup...');

            // تنظیمات دیتابیس
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);

            // نام فایل بک‌آپ
            $filename = $this->option('name') 
                ? $this->option('name') . '.sql'
                : 'backup_' . Carbon::now()->format('Y-m-d_H-i-s') . '.sql';

            // مسیر ذخیره بک‌آپ
            $backupPath = storage_path('app/backups/database');
            
            // ایجاد پوشه اگر وجود ندارد
            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $fullPath = $backupPath . '/' . $filename;

            // ساخت دستور mysqldump
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s %s > %s',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($database),
                escapeshellarg($fullPath)
            );

            // اجرای دستور
            $returnVar = null;
            $output = null;
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                $this->error('❌ Backup failed!');
                return 1;
            }

            // بررسی اندازه فایل
            $fileSize = filesize($fullPath);
            $fileSizeFormatted = $this->formatBytes($fileSize);

            $this->info('✅ Backup created successfully!');
            $this->table(
                ['Property', 'Value'],
                [
                    ['File', $filename],
                    ['Size', $fileSizeFormatted],
                    ['Path', $fullPath],
                    ['Database', $database],
                    ['Time', Carbon::now()->format('Y-m-d H:i:s')],
                ]
            );

            // پاک کردن بک‌آپ‌های قدیمی (بیشتر از 30 روز)
            $this->cleanOldBackups($backupPath);

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * پاک کردن بک‌آپ‌های قدیمی
     */
    protected function cleanOldBackups($path)
    {
        $files = glob($path . '/*.sql');
        $now = time();
        $deleted = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 30 * 24 * 60 * 60) { // 30 روز
                    unlink($file);
                    $deleted++;
                }
            }
        }

        if ($deleted > 0) {
            $this->info("🗑️  Cleaned $deleted old backup(s) (older than 30 days)");
        }
    }

    /**
     * تبدیل بایت به فرمت خوانا
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}


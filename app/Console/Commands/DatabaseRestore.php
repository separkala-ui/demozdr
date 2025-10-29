<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DatabaseRestore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:restore {file? : Backup file name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore database from a backup file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $backupPath = storage_path('app/backups/database');

            // بررسی وجود پوشه بک‌آپ
            if (!is_dir($backupPath)) {
                $this->error('❌ No backup directory found!');
                return 1;
            }

            // لیست بک‌آپ‌های موجود
            $backups = glob($backupPath . '/*.sql');

            if (empty($backups)) {
                $this->error('❌ No backup files found!');
                return 1;
            }

            // اگر نام فایل مشخص نشده، نمایش لیست
            $file = $this->argument('file');
            
            if (!$file) {
                $this->info('📋 Available backups:');
                $backupOptions = [];
                
                foreach ($backups as $index => $backup) {
                    $filename = basename($backup);
                    $fileSize = $this->formatBytes(filesize($backup));
                    $fileTime = date('Y-m-d H:i:s', filemtime($backup));
                    
                    $backupOptions[] = [
                        'Index' => $index + 1,
                        'File' => $filename,
                        'Size' => $fileSize,
                        'Date' => $fileTime,
                    ];
                }

                $this->table(['#', 'File', 'Size', 'Date'], $backupOptions);

                $choice = $this->ask('Enter backup number to restore (or filename)');
                
                if (is_numeric($choice) && isset($backups[$choice - 1])) {
                    $file = basename($backups[$choice - 1]);
                } else {
                    $file = $choice;
                }
            }

            // مسیر کامل فایل
            $fullPath = $backupPath . '/' . $file;

            // بررسی وجود فایل
            if (!file_exists($fullPath)) {
                $this->error("❌ Backup file not found: $file");
                return 1;
            }

            // هشدار
            $this->warn('⚠️  WARNING: This will replace all current database data!');
            
            if (!$this->confirm('Are you sure you want to restore this backup?')) {
                $this->info('❌ Restore cancelled.');
                return 0;
            }

            $this->info('🔄 Restoring database...');

            // تنظیمات دیتابیس
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);

            // ساخت دستور mysql restore
            $command = sprintf(
                'mysql --user=%s --password=%s --host=%s --port=%s %s < %s',
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
            exec($command . ' 2>&1', $output, $returnVar);

            if ($returnVar !== 0) {
                $this->error('❌ Restore failed!');
                $this->error('Error: ' . implode("\n", $output));
                return 1;
            }

            $this->info('✅ Database restored successfully!');
            $this->table(
                ['Property', 'Value'],
                [
                    ['File', $file],
                    ['Database', $database],
                    ['Status', 'Restored'],
                ]
            );

            // پاک کردن cache بعد از restore
            $this->info('🔄 Clearing application cache...');
            $this->call('optimize:clear');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
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


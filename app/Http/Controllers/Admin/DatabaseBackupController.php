<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use ZipArchive;

class DatabaseBackupController extends Controller
{
    protected $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups/database');
        
        // ایجاد پوشه اگر وجود ندارد
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    /**
     * نمایش صفحه مدیریت بک‌آپ
     */
    public function index()
    {
        $backups = $this->getBackupList();
        
        return view('admin.database-backup.index', compact('backups'));
    }

    /**
     * ایجاد بک‌آپ جدید
     */
    public function create(Request $request)
    {
        try {
            $name = $request->input('name');
            
            if ($name) {
                Artisan::call('db:backup', ['--name' => $name]);
            } else {
                Artisan::call('db:backup');
            }

            return response()->json([
                'success' => true,
                'message' => 'بک‌آپ با موفقیت ایجاد شد',
                'backups' => $this->getBackupList()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد بک‌آپ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * دانلود فایل بک‌آپ
     */
    public function download($filename)
    {
        $filePath = $this->backupPath . '/' . $filename;

        if (!file_exists($filePath)) {
            abort(404, 'فایل بک‌آپ یافت نشد');
        }

        return response()->download($filePath);
    }

    /**
     * حذف فایل بک‌آپ
     */
    public function delete($filename)
    {
        try {
            $filePath = $this->backupPath . '/' . $filename;

            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'فایل بک‌آپ یافت نشد'
                ], 404);
            }

            unlink($filePath);

            return response()->json([
                'success' => true,
                'message' => 'بک‌آپ با موفقیت حذف شد',
                'backups' => $this->getBackupList()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف بک‌آپ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * بازگردانی بک‌آپ
     */
    public function restore(Request $request, $filename)
    {
        try {
            $filePath = $this->backupPath . '/' . $filename;

            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'فایل بک‌آپ یافت نشد'
                ], 404);
            }

            // تنظیمات دیتابیس
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);

            // ساخت دستور mysql restore
            $command = sprintf(
                'mysql --user=%s --password=%s --host=%s --port=%s %s < %s 2>&1',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($database),
                escapeshellarg($filePath)
            );

            // اجرای دستور
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطا در بازگردانی بک‌آپ: ' . implode("\n", $output)
                ], 500);
            }

            // پاک کردن cache
            Artisan::call('optimize:clear');

            return response()->json([
                'success' => true,
                'message' => 'بک‌آپ با موفقیت بازگردانی شد'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بازگردانی بک‌آپ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * دریافت لیست بک‌آپ‌ها
     */
    protected function getBackupList()
    {
        $files = glob($this->backupPath . '/*.sql');
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'size' => filesize($file),
                'size_formatted' => $this->formatBytes(filesize($file)),
                'created_at' => filemtime($file),
                'created_at_formatted' => Carbon::createFromTimestamp(filemtime($file))->format('Y-m-d H:i:s'),
                'created_at_jalali' => \Morilog\Jalali\Jalalian::fromCarbon(Carbon::createFromTimestamp(filemtime($file)))->format('Y/m/d H:i:s'),
            ];
        }

        // مرتب‌سازی بر اساس تاریخ (جدیدترین اول)
        usort($backups, function($a, $b) {
            return $b['created_at'] - $a['created_at'];
        });

        return $backups;
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

    /**
     * آپلود فایل بک‌آپ
     */
    public function upload(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql|max:512000' // حداکثر 500MB
        ]);

        try {
            $file = $request->file('backup_file');
            $filename = 'uploaded_' . time() . '_' . $file->getClientOriginalName();
            
            $file->move($this->backupPath, $filename);

            return response()->json([
                'success' => true,
                'message' => 'فایل بک‌آپ با موفقیت آپلود شد',
                'backups' => $this->getBackupList()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در آپلود فایل: ' . $e->getMessage()
            ], 500);
        }
    }
}


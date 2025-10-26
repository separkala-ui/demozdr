<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class SystemHealth extends Component
{
    public array $health = [];

    protected $listeners = [
        'refreshSystemHealth' => 'loadHealth',
    ];

    public function mount(): void
    {
        $this->loadHealth();
    }

    public function loadHealth(): void
    {
        $this->health = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
            'overall' => 'healthy',
        ];

        // Calculate overall health
        $issues = collect($this->health)->filter(fn($item) => 
            is_array($item) && isset($item['status']) && $item['status'] !== 'healthy'
        )->count();

        if ($issues > 2) {
            $this->health['overall'] = 'critical';
        } elseif ($issues > 0) {
            $this->health['overall'] = 'warning';
        }
    }

    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $dbSize = DB::select('SELECT pg_database_size(current_database()) as size')[0]->size ?? 0;
            $dbSizeMB = round($dbSize / 1024 / 1024, 2);

            return [
                'status' => 'healthy',
                'message' => __('متصل'),
                'details' => __(':size MB', ['size' => $dbSizeMB]),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'critical',
                'message' => __('خطا'),
                'details' => __('اتصال ناموفق'),
            ];
        }
    }

    protected function checkCache(): array
    {
        try {
            Cache::put('health_check', true, 60);
            $result = Cache::get('health_check');

            return [
                'status' => $result ? 'healthy' : 'warning',
                'message' => $result ? __('فعال') : __('غیرفعال'),
                'details' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'warning',
                'message' => __('خطا'),
                'details' => __('دسترسی ناموفق'),
            ];
        }
    }

    protected function checkStorage(): array
    {
        try {
            $storagePath = storage_path();
            $totalSpace = disk_total_space($storagePath);
            $freeSpace = disk_free_space($storagePath);
            $usedSpace = $totalSpace - $freeSpace;
            $usagePercent = round(($usedSpace / $totalSpace) * 100, 1);

            $status = 'healthy';
            if ($usagePercent > 90) {
                $status = 'critical';
            } elseif ($usagePercent > 80) {
                $status = 'warning';
            }

            return [
                'status' => $status,
                'message' => __(':percent% استفاده', ['percent' => $usagePercent]),
                'details' => __(':free GB آزاد', ['free' => round($freeSpace / 1024 / 1024 / 1024, 1)]),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'warning',
                'message' => __('نامشخص'),
                'details' => __('خطا در بررسی'),
            ];
        }
    }

    protected function checkQueue(): array
    {
        try {
            // Check if queue table exists and has jobs
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();

            $status = 'healthy';
            if ($failedJobs > 10) {
                $status = 'critical';
            } elseif ($failedJobs > 5 || $pendingJobs > 100) {
                $status = 'warning';
            }

            return [
                'status' => $status,
                'message' => $pendingJobs > 0 ? __(':count در صف', ['count' => $pendingJobs]) : __('خالی'),
                'details' => $failedJobs > 0 ? __(':count خطا', ['count' => $failedJobs]) : __('بدون خطا'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'warning',
                'message' => __('نامشخص'),
                'details' => __('بررسی ناموفق'),
            ];
        }
    }

    public function render()
    {
        return view('livewire.dashboard.system-health');
    }
}

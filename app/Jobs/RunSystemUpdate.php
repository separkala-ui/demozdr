<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\SystemUpdateLog;
use App\Services\Update\UpdateManager;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Throwable;

class RunSystemUpdate implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 0;
    public int $tries = 1;

    public function __construct(public int $logId)
    {
    }

    public function handle(UpdateManager $manager): void
    {
        $log = SystemUpdateLog::find($this->logId);

        if (! $log) {
            return;
        }

        $lock = $manager->acquireLock();

        if (! $lock) {
            $manager->appendLog($log, 'به‌روزرسانی ممکن نیست: فرآیند دیگری در حال اجرا است.');
            return;
        }

        $manager->markRunning($log);

        $targetVersion = $log->target_version;

        $dirtyState = Process::command(['git', 'status', '--porcelain'])
            ->path(base_path())
            ->timeout(15)
            ->run();

        if ($dirtyState->successful() && trim($dirtyState->output()) !== '') {
            $manager->appendLog($log, 'به‌روزرسانی لغو شد: تغییرات محلی کشف شد. ابتدا تغییرات را پاک یا ذخیره کنید.');
            $manager->markFinished($log, false, $targetVersion);
            Cache::forget('system.update.status');
            $lock->release();
            return;
        }

        $this->ensureSafeDirectory($log);

        $output = [];
        $success = true;

        try {
            foreach ($this->commands() as $description => $command) {
                $output[] = "> {$description}";
                $process = Process::command($command)
                    ->path(base_path())
                    ->timeout(0)
                    ->env(['COMPOSER_ALLOW_SUPERUSER' => '1'] + $_SERVER + $_ENV)
                    ->run();

                $stdout = trim($process->output());
                $stderr = trim($process->errorOutput());

                if ($stdout !== '') {
                    $output[] = $stdout;
                }

                if ($stderr !== '') {
                    $output[] = $stderr;
                }

                if ($process->failed()) {
                    $success = false;
                    $output[] = 'خطا در اجرای دستور. فرآیند متوقف شد.';
                    break;
                }
            }

            if ($success) {
                $targetVersion = $manager->syncCurrentVersion();
                $output[] = 'سیستم با موفقیت به‌روزرسانی شد.';
            }
        } catch (Throwable $e) {
            report($e);
            $success = false;
            $output[] = 'Exception: ' . $e->getMessage();
        } finally {
            $manager->appendLog($log, implode("\n", $output));
            $manager->markFinished($log, $success, $targetVersion);
            Cache::forget('system.update.status');
            $lock->release();
        }
    }

    protected function ensureSafeDirectory(SystemUpdateLog $log): void
    {
        $environment = array_merge(['HOME' => env('HOME', base_path())], $_SERVER, $_ENV);

        $process = Process::command(['git', 'config', '--global', '--add', 'safe.directory', base_path()])
            ->path(base_path())
            ->timeout(15)
            ->env($environment)
            ->run();

        if ($process->failed() && trim($process->errorOutput()) !== '') {
            $log->appendLog('هشدار: ثبت safe.directory ناموفق بود: ' . trim($process->errorOutput()));
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function commands(): array
    {
        $branch = config('update.repository.branch', 'main');

        return [
            'دریافت آخرین تغییرات' => ['git', 'fetch', '--all'],
            'همگام‌سازی با مخزن' => ['git', 'reset', '--hard', "origin/{$branch}"],
            'نصب وابستگی‌های Composer' => ['composer', 'install', '--no-dev', '--prefer-dist', '--optimize-autoloader'],
            'اجرای مایگریشن‌ها' => ['php', 'artisan', 'migrate', '--force'],
            'پاکسازی کش‌ها' => ['php', 'artisan', 'optimize:clear'],
            'نصب وابستگی‌های Node' => ['npm', 'install'],
            'ساخت دارایی‌های فرانت‌اند' => ['npm', 'run', 'build'],
        ];
    }
}

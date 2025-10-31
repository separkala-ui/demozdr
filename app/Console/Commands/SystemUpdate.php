<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class SystemUpdate extends Command
{
    protected $signature = 'system:update
                            {--force : Force update even if there are local changes}
                            {--check-only : Only check for updates without applying}';

    protected $description = 'Check for and apply system updates from GitHub repository';

    public function handle(): int
    {
        $this->info('🔍 Checking for system updates...');
        $this->newLine();

        if (! $this->isGitAvailable()) {
            $this->error('❌ Git is not installed or not available in PATH');
            Log::error('System update failed: Git not available');
            return Command::FAILURE;
        }

        if (! $this->isGitRepository()) {
            $this->error('❌ Not a git repository');
            Log::error('System update failed: Not a git repository');
            return Command::FAILURE;
        }

        $currentBranch = $this->getCurrentBranch();
        $this->info("📍 Current branch: {$currentBranch}");

        if ($this->hasUncommittedChanges()) {
            if ($this->option('force')) {
                $this->warn('⚠️  Uncommitted changes detected. Forcing update...');
            } else {
                $this->error('❌ Uncommitted changes detected. Use --force to proceed anyway.');
                Log::error('System update failed: Uncommitted changes');
                return Command::FAILURE;
            }
        }

        $this->info('📥 Fetching latest changes from remote...');
        $fetchSuccess = $this->fetchLatestChanges();
        if (! $fetchSuccess) {
            $this->error('❌ Failed to fetch latest changes');
            Log::error('System update failed: Fetch failed');
            return Command::FAILURE;
        }

        $updatesAvailable = $this->checkUpdatesAvailable();
        if (! $updatesAvailable) {
            $this->info('✅ System is up to date');
            Log::info('System update check: Up to date');
            return Command::SUCCESS;
        }

        if ($this->option('check-only')) {
            $this->info('📦 Updates available! Run without --check-only to apply.');
            Log::info('System update check: Updates available');
            return Command::SUCCESS;
        }

        $this->info('🔄 Applying updates...');
        $updateSuccess = $this->applyUpdates();

        if (! $updateSuccess) {
            $this->error('❌ Failed to apply updates');
            Log::error('System update failed: Update application failed');
            return Command::FAILURE;
        }

        $this->info('🧹 Clearing caches...');
        $this->clearCaches();

        $this->info('🗄️  Running database migrations...');
        $migrationSuccess = $this->runMigrations();

        if (! $migrationSuccess) {
            $this->warn('⚠️  Migrations may have failed. Please check manually.');
        }

        $this->newLine();
        $this->info('✅ System update completed successfully!');
        Log::info('System update completed successfully');

        return Command::SUCCESS;
    }

    protected function isGitAvailable(): bool
    {
        $process = Process::fromShellCommandline('git --version');
        $process->run();
        return $process->isSuccessful();
    }

    protected function isGitRepository(): bool
    {
        $process = Process::fromShellCommandline('git rev-parse --git-dir');
        $process->run();
        return $process->isSuccessful();
    }

    protected function getCurrentBranch(): string
    {
        $process = Process::fromShellCommandline('git rev-parse --abbrev-ref HEAD');
        $process->run();

        if ($process->isSuccessful()) {
            return trim($process->getOutput());
        }

        return 'unknown';
    }

    protected function hasUncommittedChanges(): bool
    {
        $process = Process::fromShellCommandline('git diff-index --quiet HEAD --');
        $process->run();
        return ! $process->isSuccessful();
    }

    protected function fetchLatestChanges(): bool
    {
        $process = Process::fromShellCommandline('git fetch origin');
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Fetch error: ' . $process->getErrorOutput());
            return false;
        }

        return true;
    }

    protected function checkUpdatesAvailable(): bool
    {
        $currentBranch = $this->getCurrentBranch();
        $process = Process::fromShellCommandline("git rev-list HEAD..origin/{$currentBranch} --count");
        $process->run();

        if ($process->isSuccessful()) {
            $commitCount = (int) trim($process->getOutput());
            return $commitCount > 0;
        }

        return false;
    }

    protected function applyUpdates(): bool
    {
        $currentBranch = $this->getCurrentBranch();
        $process = Process::fromShellCommandline("git pull origin {$currentBranch}");
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Pull error: ' . $process->getErrorOutput());
            return false;
        }

        $output = trim($process->getOutput());
        if (! empty($output)) {
            $this->line($output);
        }

        return true;
    }

    protected function clearCaches(): void
    {
        try {
            Artisan::call('optimize:clear', [], $this->output);
        } catch (\Exception $e) {
            $this->warn('Cache clear warning: ' . $e->getMessage());
        }
    }

    protected function runMigrations(): bool
    {
        try {
            $process = Process::fromShellCommandline('php artisan migrate --force');
            $process->setTimeout(300);
            $process->run();

            if (! $process->isSuccessful()) {
                $this->error('Migration error: ' . $process->getErrorOutput());
                return false;
            }

            $output = trim($process->getOutput());
            if (! empty($output)) {
                $this->line($output);
            }

            return true;
        } catch (\Exception $e) {
            $this->warn('Migration warning: ' . $e->getMessage());
            return false;
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Update;

use App\Models\SystemUpdateLog;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class UpdateManager
{
    public const CURRENT_VERSION_KEY = 'system.current_version';
    public const LAST_CHECK_AT_KEY = 'system.update_last_check';
    public const LAST_REMOTE_VERSION_KEY = 'system.update_last_remote_version';

    public function getCurrentVersion(): string
    {
        $current = get_setting(self::CURRENT_VERSION_KEY);

        if (! $current) {
            $current = $this->detectGitVersion() ?? 'unknown';
            add_setting(self::CURRENT_VERSION_KEY, $current, true);
        }

        return (string) $current;
    }

    public function syncCurrentVersion(?string $version = null): string
    {
        if (! $version) {
            $version = $this->detectGitVersion() ?? 'unknown';
        }

        add_setting(self::CURRENT_VERSION_KEY, $version, true);

        return $version;
    }

    public function getUpdateStatus(bool $forceRefresh = false): array
    {
        if (! config('update.enabled')) {
            return [
                'enabled' => false,
                'has_update' => false,
                'current_version' => $this->getCurrentVersion(),
                'latest_version' => null,
                'latest_details' => null,
            ];
        }

        $cacheKey = 'system.update.status';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, now()->addMinutes((int) config('update.cache_ttl', 10)), function () {
            $latest = $this->fetchLatestRelease();
            $current = $this->getCurrentVersion();

            $hasUpdate = $latest && ! empty($latest['version'])
                ? ! hash_equals($this->normalizeHash($latest['version']), $this->normalizeHash($current))
                : false;

            if ($latest) {
                add_setting(self::LAST_CHECK_AT_KEY, now()->toDateTimeString());
                add_setting(self::LAST_REMOTE_VERSION_KEY, $latest['version']);
            }

            return [
                'enabled' => true,
                'has_update' => $hasUpdate,
                'current_version' => $current,
                'latest_version' => $latest['version'] ?? null,
                'latest_details' => $latest,
            ];
        });
    }

    public function fetchLatestRelease(): ?array
    {
        try {
            return match (config('update.source.type', 'github')) {
                'manifest' => $this->fetchFromManifest(),
                default => $this->fetchFromGitHub(),
            };
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    public function createLog(string $targetVersion, ?int $userId = null, ?string $currentVersion = null): SystemUpdateLog
    {
        return SystemUpdateLog::create([
            'status' => 'queued',
            'target_version' => $targetVersion,
            'current_version' => $currentVersion ?? $this->getCurrentVersion(),
            'triggered_by' => $userId,
        ]);
    }

    public function markRunning(SystemUpdateLog $log): void
    {
        $log->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function markFinished(SystemUpdateLog $log, bool $success, ?string $targetVersion = null): void
    {
        $log->update([
            'status' => $success ? 'success' : 'failed',
            'finished_at' => now(),
            'target_version' => $targetVersion ?? $log->target_version,
        ]);

        if ($success && $targetVersion) {
            $this->syncCurrentVersion($targetVersion);
        }
    }

    public function appendLog(SystemUpdateLog $log, string $message): void
    {
        $log->appendLog($message);
    }

    public function acquireLock(): ?Lock
    {
        $lock = Cache::lock('system.update.lock', (int) config('update.lock_ttl', 900));

        if ($lock->get()) {
            return $lock;
        }

        return null;
    }

    protected function fetchFromGitHub(): ?array
    {
        $repo = config('update.repository.name');
        $branch = config('update.repository.branch', 'main');

        if (! $repo) {
            return null;
        }

        $request = Http::acceptJson()->timeout(15);

        if ($token = config('update.token')) {
            $request = $request->withToken($token);
        }

        $response = $request->get("https://api.github.com/repos/{$repo}/commits/{$branch}");

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        if (! $data) {
            return null;
        }

        return [
            'version' => Arr::get($data, 'sha'),
            'short_hash' => substr((string) Arr::get($data, 'sha'), 0, 7),
            'published_at' => Arr::get($data, 'commit.author.date'),
            'message' => (string) Arr::get($data, 'commit.message'),
            'author' => Arr::get($data, 'commit.author.name'),
            'url' => Arr::get($data, 'html_url'),
        ];
    }

    protected function fetchFromManifest(): ?array
    {
        $url = config('update.source.manifest_url');

        if (! $url) {
            return null;
        }

        $request = Http::acceptJson()->timeout(15);

        if ($token = config('update.token')) {
            $request = $request->withToken($token);
        }

        $response = $request->get($url);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        if (! is_array($data)) {
            return null;
        }

        return [
            'version' => Arr::get($data, 'version'),
            'short_hash' => Arr::get($data, 'short_hash'),
            'published_at' => Arr::get($data, 'released_at'),
            'message' => Arr::get($data, 'message'),
            'author' => Arr::get($data, 'author'),
            'url' => Arr::get($data, 'url'),
        ];
    }

    protected function detectGitVersion(): ?string
    {
        try {
            $process = Process::command(['git', 'rev-parse', 'HEAD'])
                ->path(base_path())
                ->timeout(15)
                ->run();

            if ($process->successful()) {
                return trim($process->output());
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return null;
    }

    protected function normalizeHash(?string $value): string
    {
        return Str::substr((string) $value, 0, 40);
    }
}

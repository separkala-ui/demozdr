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
        $prepared = $this->prepareVersionForStorage($version ?? $this->detectGitVersion() ?? 'unknown');
        add_setting(self::CURRENT_VERSION_KEY, $prepared, true);

        return $prepared;
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
                ? $this->versionsDiffer($latest['version'], $current)
                : false;

            if ($latest) {
                add_setting(self::LAST_CHECK_AT_KEY, now()->toDateTimeString());
                add_setting(self::LAST_REMOTE_VERSION_KEY, $this->prepareVersionForStorage($latest['version'] ?? ''));
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

        if (! $repo) {
            return null;
        }

        $request = Http::acceptJson()->timeout(15);

        if ($token = config('update.token')) {
            $request = $request->withToken($token);
        }

        if ($this->versionSource() === 'tag') {
            $release = $this->fetchGithubRelease($request, $repo);
            if ($release && ! empty($release['version'])) {
                return $release;
            }

            $commit = $this->fetchGithubCommit($request, $repo) ?? [];
            $versionFromFile = $this->fetchVersionFromFile($request, $repo, Arr::get($commit, 'commit'));
            if ($versionFromFile) {
                $commit['version'] = $versionFromFile;
                $commit['tag'] = $versionFromFile;
            }

            return $commit ?: null;
        }

        return $this->fetchGithubCommit($request, $repo);
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

        $rawVersion = Arr::get($data, 'version');

        return [
            'version' => $this->sanitizeVersion($rawVersion) ?? $rawVersion,
            'tag' => Arr::get($data, 'tag', $rawVersion),
            'short_hash' => Arr::get($data, 'short_hash'),
            'published_at' => Arr::get($data, 'released_at'),
            'message' => Arr::get($data, 'message'),
            'author' => Arr::get($data, 'author'),
            'url' => Arr::get($data, 'url'),
        ];
    }

    protected function detectGitVersion(): ?string
    {
        if ($this->versionSource() === 'tag') {
            $tag = $this->detectGitTag();
            if ($tag) {
                return $this->sanitizeVersion($tag);
            }

             $manifestVersion = $this->detectVersionFromFile();
             if ($manifestVersion) {
                 return $manifestVersion;
             }
        }

        return $this->detectGitCommit();
    }

    protected function fetchGithubRelease($request, string $repo): ?array
    {
        $response = $request->get("https://api.github.com/repos/{$repo}/releases/latest");

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        $tag = Arr::get($data, 'tag_name');

        if (! $tag) {
            return null;
        }

        $commitData = $this->fetchGithubCommit($request, $repo, $tag) ?? [];
        $commitSha = Arr::get($commitData, 'commit') ?? Arr::get($commitData, 'version');

        $version = $this->sanitizeVersion($tag);

        if (! $version) {
            $version = $this->fetchVersionFromFile($request, $repo, $tag);
        }

        return [
            'version' => $version ?? $this->sanitizeVersion($tag),
            'tag' => $tag,
            'short_hash' => substr((string) $commitSha, 0, 7),
            'commit' => $commitSha,
            'published_at' => Arr::get($data, 'published_at') ?? Arr::get($commitData, 'published_at'),
            'message' => Arr::get($data, 'name') ?: Arr::get($data, 'body') ?: Arr::get($commitData, 'message'),
            'author' => Arr::get($data, 'author.login') ?? Arr::get($commitData, 'author'),
            'url' => Arr::get($data, 'html_url'),
        ];
    }

    protected function fetchGithubCommit($request, string $repo, ?string $ref = null): ?array
    {
        $ref = $ref ?? config('update.repository.branch', 'main');

        $response = $request->get("https://api.github.com/repos/{$repo}/commits/{$ref}");

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
            'commit' => Arr::get($data, 'sha'),
            'published_at' => Arr::get($data, 'commit.author.date'),
            'message' => (string) Arr::get($data, 'commit.message'),
            'author' => Arr::get($data, 'commit.author.name'),
            'url' => Arr::get($data, 'html_url'),
        ];
    }

    protected function detectVersionFromFile(): ?string
    {
        $file = $this->versionFile();

        if (! $file) {
            return null;
        }

        $path = base_path($file);

        if (! file_exists($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        return $this->extractVersionFromContents($contents, $file);
    }

    protected function fetchVersionFromFile($request, string $repo, ?string $ref = null): ?string
    {
        $file = $this->versionFile();

        if (! $file) {
            return null;
        }

        $ref = $ref ?? config('update.repository.branch', 'main');
        $url = "https://raw.githubusercontent.com/{$repo}/{$ref}/{$file}";

        $response = $request->get($url);

        if (! $response->successful()) {
            return null;
        }

        return $this->extractVersionFromContents($response->body(), $file);
    }

    protected function extractVersionFromContents(string $contents, string $file): ?string
    {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if ($extension === 'json') {
            $data = json_decode($contents, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }

            return $this->sanitizeVersion(Arr::get($data, 'version'));
        }

        return $this->sanitizeVersion($contents);
    }
    protected function detectGitCommit(): ?string
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

    protected function detectGitTag(): ?string
    {
        try {
            $process = Process::command(['git', 'describe', '--tags', '--abbrev=0'])
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

    protected function versionFile(): ?string
    {
        return config('update.version_file');
    }

    protected function versionSource(): string
    {
        return config('update.version_source', 'commit');
    }

    protected function sanitizeVersion(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($this->versionSource() === 'tag') {
            return ltrim($value, 'vV');
        }

        return $value;
    }

    protected function prepareVersionForStorage(?string $value): string
    {
        $value = $value ?? '';

        if ($this->versionSource() === 'tag') {
            return $this->sanitizeVersion($value) ?: 'unknown';
        }

        return $this->normalizeHash($value);
    }

    protected function versionsDiffer(?string $latest, ?string $current): bool
    {
        if ($this->versionSource() === 'tag') {
            $latestVersion = $this->sanitizeVersion($latest);
            $currentVersion = $this->sanitizeVersion($current);

            if (! $latestVersion || ! $currentVersion) {
                return $latestVersion !== $currentVersion;
            }

            return version_compare($latestVersion, $currentVersion, '>');
        }

        return ! hash_equals($this->normalizeHash($latest), $this->normalizeHash($current));
    }

    protected function normalizeHash(?string $value): string
    {
        return Str::substr((string) $value, 0, 40);
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Backup;

use App\Mail\DatabaseBackupCreated;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    public function create(?string $recipient = null, bool $compress = true): array
    {
        $config = config('database.connections.mysql');
        if (! $config) {
            throw new RuntimeException('mysql connection is not configured.');
        }

        $backupConfig = config('backups.database');
        $compress = $compress && (bool) ($backupConfig['compress'] ?? true);

        $directory = storage_path('app/' . ($backupConfig['directory'] ?? 'db-backups'));
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $timestamp = now()->format('Y_m_d_H_i_s');
        $baseName = ($backupConfig['prefix'] ?? 'db-backup_') . $timestamp;
        $tempPath = $directory . DIRECTORY_SEPARATOR . $baseName . '.sql';

        $command = [
            'mysqldump',
            '--host=' . ($config['host'] ?? '127.0.0.1'),
            '--port=' . ($config['port'] ?? '3306'),
            '--user=' . ($config['username'] ?? 'root'),
            '--single-transaction',
            '--quick',
            '--default-character-set=utf8mb4',
        ];

        if (! empty($config['unix_socket'])) {
            $command[] = '--socket=' . $config['unix_socket'];
        }

        $command[] = $config['database'];

        $process = new Process($command);
        $process->setTimeout(300);
        $process->setEnv(['MYSQL_PWD' => $config['password'] ?? '']);
        $process->run(function ($type, $buffer) use ($tempPath) {
            if ($type === Process::OUT) {
                file_put_contents($tempPath, $buffer, FILE_APPEND);
            }
        });

        if (! $process->isSuccessful()) {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
            throw new RuntimeException($process->getErrorOutput() ?: 'Failed to execute mysqldump');
        }

        $finalPath = $tempPath;
        $finalName = basename($tempPath);
        $mime = 'application/sql';

        if ($compress) {
            $compressedPath = $tempPath . '.gz';
            $handle = fopen($tempPath, 'rb');
            if (! $handle) {
                throw new RuntimeException('Failed to open temporary backup for compression');
            }

            $gz = gzopen($compressedPath, 'wb9');
            if (! $gz) {
                fclose($handle);
                throw new RuntimeException('Failed to open gzip stream');
            }

            while (! feof($handle)) {
                gzwrite($gz, fread($handle, 1024 * 512));
            }

            fclose($handle);
            gzclose($gz);
            @unlink($tempPath);

            $finalPath = $compressedPath;
            $finalName = basename($compressedPath);
            $mime = 'application/gzip';
        }

        $this->adjustPermissions($finalPath);

        $metadata = [
            'name' => $finalName,
            'path' => $finalPath,
            'size' => filesize($finalPath),
            'mime' => $mime,
            'created_at' => now(),
        ];

        $recipient = $recipient ?: ($backupConfig['default_recipient'] ?? null);
        if ($recipient && ($backupConfig['notify'] ?? true)) {
            Mail::to($recipient)->send(new DatabaseBackupCreated($metadata));
        }

        return $metadata;
    }

    public function list(): array
    {
        $backupConfig = config('backups.database');
        $directory = storage_path('app/' . ($backupConfig['directory'] ?? 'db-backups'));
        $files = [];

        if (! File::exists($directory)) {
            return [];
        }

        foreach (File::files($directory) as $file) {
            $files[] = [
                'name' => $file->getFilename(),
                'path' => $file->getRealPath(),
                'size' => $file->getSize(),
                'created_at' => Carbon::createFromTimestamp($file->getMTime()),
            ];
        }

        usort($files, fn ($a, $b) => $b['created_at']->timestamp <=> $a['created_at']->timestamp);

        return $files;
    }

    public function delete(string $filename): void
    {
        $path = $this->getFilePath($filename);
        if (! File::exists($path)) {
            throw new RuntimeException('Backup file not found');
        }

        File::delete($path);
    }

    public function send(string $filename, ?string $recipient = null): void
    {
        $path = $this->getFilePath($filename);
        if (! File::exists($path)) {
            throw new RuntimeException('Backup file not found');
        }

        $backupConfig = config('backups.database');
        $recipient = $recipient ?: ($backupConfig['default_recipient'] ?? null);
        if (! $recipient) {
            throw new RuntimeException('No recipient defined for database backups');
        }

        $metadata = [
            'name' => basename($path),
            'path' => $path,
            'size' => filesize($path),
            'mime' => $this->guessMime($path),
                'created_at' => Carbon::createFromTimestamp(filemtime($path)),
        ];

        Mail::to($recipient)->send(new DatabaseBackupCreated($metadata));
    }

    public function restore(string $uploadedPath): void
    {
        $config = config('database.connections.mysql');
        if (! $config) {
            throw new RuntimeException('mysql connection is not configured.');
        }

        $path = $uploadedPath;
        $isCompressed = Str::endsWith($path, '.gz');

        $temp = null;
        if ($isCompressed) {
            $temp = tempnam(sys_get_temp_dir(), 'dbrestore');
            $gz = gzopen($path, 'rb');
            if (! $gz) {
                throw new RuntimeException('Failed to open gzip file');
            }

            while (! gzeof($gz)) {
                file_put_contents($temp, gzread($gz, 1024 * 512), FILE_APPEND);
            }
            gzclose($gz);
            $path = $temp;
        }

        $command = [
            'mysql',
            '--host=' . ($config['host'] ?? '127.0.0.1'),
            '--port=' . ($config['port'] ?? '3306'),
            '--user=' . ($config['username'] ?? 'root'),
        ];

        if (! empty($config['unix_socket'])) {
            $command[] = '--socket=' . $config['unix_socket'];
        }

        $command[] = $config['database'];

        $process = new Process($command, null, ['MYSQL_PWD' => $config['password'] ?? ''], null);
        $process->setTimeout(300);
        $process->setInput(file_get_contents($path));
        $process->run();

        if ($temp && file_exists($temp)) {
            @unlink($temp);
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput() ?: 'Restore failed');
        }
    }

    public function getFilePath(string $filename): string
    {
        $backupConfig = config('backups.database');
        $directory = storage_path('app/' . ($backupConfig['directory'] ?? 'db-backups'));

        return $directory . DIRECTORY_SEPARATOR . $filename;
    }

    protected function guessMime(string $path): string
    {
        if (Str::endsWith($path, '.gz')) {
            return 'application/gzip';
        }

        return 'application/sql';
    }

    protected function adjustPermissions(string $path): void
    {
        if (! config('petty-cash.backups.adjust_permissions', false)) {
            return;
        }

        $owner = config('petty-cash.backups.owner');
        $group = config('petty-cash.backups.group');

        if ($owner || $group) {
            @chown($path, $owner ?: null);
            @chgrp($path, $group ?: null);
        }
    }
}

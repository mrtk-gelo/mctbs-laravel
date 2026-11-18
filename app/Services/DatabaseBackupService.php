<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Exception\ExceptionInterface;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    /**
     * @return array<int, array{filename: string, created_at: Carbon, size: string}>
     */
    public function recent(): array
    {
        $disk = Storage::disk('backups');

        return collect($disk->files())
            ->filter(fn (string $path): bool => $this->isBackupFilename(basename($path)))
            ->sortByDesc(fn (string $path): int => $disk->lastModified($path))
            ->take(10)
            ->map(function (string $path) use ($disk): array {
                return [
                    'filename' => basename($path),
                    'created_at' => Carbon::createFromTimestamp($disk->lastModified($path), config('app.timezone')),
                    'size' => $this->formatSize($disk->size($path)),
                ];
            })
            ->values()
            ->all();
    }

    public function create(): string
    {
        $connection = config('database.connections.'.config('database.default'));
        if (! is_array($connection) || ! in_array($connection['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('Backups require a MySQL or MariaDB connection.');
        }

        $dumpPath = config('database.mysql_dump_path');
        if (! is_string($dumpPath) || ! is_file($dumpPath)) {
            throw new RuntimeException('Set MYSQLDUMP_PATH to the mysqldump.exe file in .env.');
        }

        $database = $connection['database'] ?? null;
        if (! is_string($database) || $database === '') {
            throw new RuntimeException('Set the database name in .env before creating a backup.');
        }

        $filename = 'database_'.now()->format('Ymd_His').'_'.bin2hex(random_bytes(4)).'.sql';
        $disk = Storage::disk('backups');
        $destination = $disk->path($filename);
        File::ensureDirectoryExists(dirname($destination));

        $process = new Process([
            $dumpPath,
            '--protocol=tcp',
            '--host='.($connection['host'] ?? '127.0.0.1'),
            '--port='.($connection['port'] ?? '3306'),
            '--user='.($connection['username'] ?? ''),
            '--default-character-set='.($connection['charset'] ?? 'utf8mb4'),
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--routines',
            '--triggers',
            '--events',
            '--result-file='.$destination,
            '--',
            $database,
        ], base_path(), [
            'MYSQL_PWD' => (string) ($connection['password'] ?? ''),
        ], timeout: 300);

        try {
            $process->run();
        } catch (ExceptionInterface $exception) {
            $disk->delete($filename);
            Log::error('The database backup process could not be started or completed.', [
                'exception' => $exception,
            ]);

            throw new RuntimeException('The database backup could not be completed. Check the application log.', previous: $exception);
        }

        if (! $process->isSuccessful()) {
            $disk->delete($filename);
            Log::error('The database backup process failed.', [
                'exit_code' => $process->getExitCode(),
                'error' => $process->getErrorOutput(),
            ]);

            throw new RuntimeException('The database backup failed. Check the application log.');
        }

        if (! $disk->exists($filename) || $disk->size($filename) === 0) {
            $disk->delete($filename);

            throw new RuntimeException('The database backup file was not created.');
        }

        return $filename;
    }

    public function pathForDownload(string $filename): string
    {
        $disk = Storage::disk('backups');
        abort_unless($this->isBackupFilename($filename) && $disk->exists($filename), 404);

        return $disk->path($filename);
    }

    private function isBackupFilename(string $filename): bool
    {
        return preg_match('/\Adatabase_\d{8}_\d{6}_[a-f0-9]{8}\.sql\z/', $filename) === 1;
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }
}

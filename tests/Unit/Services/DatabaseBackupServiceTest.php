<?php

use App\Services\DatabaseBackupService;
use Tests\TestCase;

uses(TestCase::class);

it('uses an existing configured dump path before searching PATH', function (): void {
    $originalPath = getenv('PATH');
    $temporaryDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'backup-tools-'.bin2hex(random_bytes(4));
    mkdir($temporaryDirectory);

    $configuredDumpPath = $temporaryDirectory.DIRECTORY_SEPARATOR.'configured-dump.exe';
    file_put_contents($configuredDumpPath, '');
    putenv('PATH='.$temporaryDirectory);
    config([
        'database.default' => 'mysql',
        'database.connections.mysql' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => '',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
        ],
        'database.mysql_dump_path' => $configuredDumpPath,
    ]);

    try {
        expect(fn () => app(DatabaseBackupService::class)->create())
            ->toThrow(RuntimeException::class, 'Set the database name in .env before creating a backup.');
    } finally {
        if ($originalPath === false) {
            putenv('PATH');
        } else {
            putenv('PATH='.$originalPath);
        }

        unlink($configuredDumpPath);
        rmdir($temporaryDirectory);
    }
});

it('uses either supported dump program from PATH when the configured path is missing', function (string $program): void {
    $originalPath = getenv('PATH');
    $temporaryDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'backup-tools-'.bin2hex(random_bytes(4));
    mkdir($temporaryDirectory);

    $programPath = $temporaryDirectory.DIRECTORY_SEPARATOR.$program.(DIRECTORY_SEPARATOR === '\\' ? '.exe' : '');
    if (DIRECTORY_SEPARATOR === '\\') {
        file_put_contents($programPath, '');
    } else {
        file_put_contents($programPath, "#!/bin/sh\nexit 1\n");
        chmod($programPath, 0755);
    }

    putenv('PATH='.$temporaryDirectory);
    config([
        'database.default' => 'mysql',
        'database.connections.mysql' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => '',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
        ],
        'database.mysql_dump_path' => $temporaryDirectory.DIRECTORY_SEPARATOR.'missing.exe',
    ]);

    try {
        expect(fn () => app(DatabaseBackupService::class)->create())
            ->toThrow(RuntimeException::class, 'Set the database name in .env before creating a backup.');
    } finally {
        if ($originalPath === false) {
            putenv('PATH');
        } else {
            putenv('PATH='.$originalPath);
        }

        unlink($programPath);
        rmdir($temporaryDirectory);
    }
})->with(['mysqldump', 'mariadb-dump']);

it('reports when no dump program is configured or available on PATH', function (): void {
    $originalPath = getenv('PATH');
    $temporaryDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'backup-tools-'.bin2hex(random_bytes(4));
    mkdir($temporaryDirectory);

    putenv('PATH='.$temporaryDirectory);
    config([
        'database.default' => 'mysql',
        'database.connections.mysql' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'backups_test',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
        ],
        'database.mysql_dump_path' => $temporaryDirectory.DIRECTORY_SEPARATOR.'missing.exe',
    ]);

    try {
        expect(fn () => app(DatabaseBackupService::class)->create())
            ->toThrow(RuntimeException::class, 'Set MYSQLDUMP_PATH to an existing mysqldump or mariadb-dump executable, or ensure one is available on PATH.');
    } finally {
        if ($originalPath === false) {
            putenv('PATH');
        } else {
            putenv('PATH='.$originalPath);
        }

        rmdir($temporaryDirectory);
    }
});

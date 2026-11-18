<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use RuntimeException;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'database:backup';

    protected $description = 'Create a private database backup.';

    public function handle(DatabaseBackupService $backups): int
    {
        try {
            $filename = $backups->create();
        } catch (RuntimeException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Database backup created: {$filename}");

        return self::SUCCESS;
    }
}

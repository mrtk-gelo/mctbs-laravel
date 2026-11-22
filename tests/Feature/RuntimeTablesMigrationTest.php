<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates the session cache and queue tables and can be rerun and rolled back', function (): void {
    $tableNames = [
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
    ];

    foreach ($tableNames as $tableName) {
        Schema::dropIfExists($tableName);
    }

    $migration = require database_path('migrations/2026_11_22_122731_create_session_cache_and_queue_tables.php');
    $migration->up();

    foreach ($tableNames as $tableName) {
        expect(Schema::hasTable($tableName))->toBeTrue();
    }

    $migration->up();
    $migration->down();

    foreach ($tableNames as $tableName) {
        expect(Schema::hasTable($tableName))->toBeFalse();
    }
});

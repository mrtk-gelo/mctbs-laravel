<?php

use App\Models\Role;
use App\Models\User;
use App\Services\DatabaseBackupService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->sysadmin = User::create([
        'role_id' => Role::query()->where('name', 'Account Admin')->value('role_id'),
        'first_name' => 'System',
        'last_name' => 'Admin',
        'email' => 'sysadmin@example.com',
        'password' => 'SysAdmin123!',
        'is_active' => true,
    ]);
});

it('shows registered owners and recent private backups on the dashboard', function (): void {
    $this->freezeTime();
    $owner = User::create([
        'role_id' => Role::query()->where('name', 'Owner')->value('role_id'),
        'first_name' => 'Harbor',
        'last_name' => 'Owner',
        'email' => 'harbor@example.com',
        'password' => 'OwnerPass123!',
        'email_verified_at' => now(),
        'is_active' => true,
    ]);
    $owner->transientHouses()->create([
        'name' => 'Harbor House',
        'address' => '1 Bay Road',
        'city' => 'Manila',
        'contact_number' => '09170000000',
        'is_active' => true,
    ]);

    Storage::fake('backups');
    $filename = 'database_20261025_110000_1234abcd.sql';
    Storage::disk('backups')->put($filename, 'sql-data');
    touch(
        Storage::disk('backups')->path($filename),
        Carbon::parse('2026-10-25 11:00:00', config('app.timezone'))->timestamp
    );

    $this->actingAs($this->sysadmin)
        ->get(route('accadmin.dashboard'))
        ->assertSee('Registered Owners')
        ->assertSee('Harbor Owner')
        ->assertSee('harbor@example.com')
        ->assertSee('<td>1</td>', false)
        ->assertSee('Active')
        ->assertSee('Oct 25, 2026')
        ->assertSee('Database backups')
        ->assertSee('8 B')
        ->assertSee(route('accadmin.backups.download', $filename));
});

it('creates a backup immediately from the admin dashboard action', function (): void {
    $filename = 'database_20261025_110000_1234abcd.sql';
    Storage::fake('backups');
    $backupService = Mockery::mock(DatabaseBackupService::class);
    $backupService->shouldReceive('create')->once()->andReturnUsing(function () use ($filename): string {
        Storage::disk('backups')->put($filename, 'sql-data');

        return $filename;
    });
    $backupService->shouldReceive('recent')->once()->andReturn([
        [
            'filename' => $filename,
            'created_at' => now(),
            'size' => '8 B',
        ],
    ]);
    $this->app->instance(DatabaseBackupService::class, $backupService);

    $this->actingAs($this->sysadmin)
        ->post(route('accadmin.backups.store'))
        ->assertRedirect(route('accadmin.dashboard'))
        ->assertSessionHas('success', 'Database backup created.');

    Storage::disk('backups')->assertExists($filename);
    $this->get(route('accadmin.dashboard'))
        ->assertSee($filename)
        ->assertSee(route('accadmin.backups.download', $filename));
});

it('shows a useful error when the dashboard backup action fails', function (): void {
    $backupService = Mockery::mock(DatabaseBackupService::class);
    $backupService->shouldReceive('create')
        ->once()
        ->andThrow(new RuntimeException('The database backup failed. Check the application log.'));
    $this->app->instance(DatabaseBackupService::class, $backupService);

    $this->actingAs($this->sysadmin)
        ->post(route('accadmin.backups.store'))
        ->assertRedirect(route('accadmin.dashboard'))
        ->assertSessionHas('error', 'The database backup failed. Check the application log.');
});

it('downloads a private backup only for account admins', function (): void {
    Storage::fake('backups');
    $filename = 'database_20261025_110000_1234abcd.sql';
    Storage::disk('backups')->put($filename, 'sql-data');

    $this->actingAs($this->sysadmin)
        ->get(route('accadmin.backups.download', $filename))
        ->assertDownload($filename);

    $owner = User::create([
        'role_id' => Role::query()->where('name', 'Owner')->value('role_id'),
        'first_name' => 'Owner',
        'last_name' => 'Account',
        'email' => 'owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);

    $this->actingAs($owner)
        ->get(route('accadmin.backups.download', $filename))
        ->assertForbidden();
});

it('rejects unsafe backup filenames', function (): void {
    $this->actingAs($this->sysadmin)
        ->get(route('accadmin.backups.download', 'outside.sql'))
        ->assertNotFound();
});

it('reports when the configured database is not supported for backup', function (): void {
    Storage::fake('backups');
    config(['database.default' => 'sqlite']);

    $this->artisan('database:backup')
        ->expectsOutputToContain('Backups require a MySQL or MariaDB connection.')
        ->assertExitCode(1);

    expect(Storage::disk('backups')->files())->toBeEmpty();
});

it('does not allow non-admins to create backups', function (): void {
    $owner = User::create([
        'role_id' => Role::query()->where('name', 'Owner')->value('role_id'),
        'first_name' => 'Owner',
        'last_name' => 'Account',
        'email' => 'owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);

    $this->actingAs($owner)
        ->post(route('accadmin.backups.store'))
        ->assertForbidden();
});

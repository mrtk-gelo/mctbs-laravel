<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeSysadmin(string $email): User
{
    return User::create([
        'role_id' => Role::where('name', 'Account Admin')->firstOrFail()->role_id,
        'first_name' => 'System',
        'last_name' => 'Admin',
        'email' => $email,
        'password' => 'SysAdmin123!',
        'is_active' => true,
    ]);
}

test('the sysadmin email can be set to the official MCTBS email', function () {
    $sysadmin = makeSysadmin('old-sysadmin@example.com');

    $this->artisan('mctbs:set-sysadmin-email', ['email' => 'official@mctbs.test'])
        ->expectsOutput('System Admin email set to official@mctbs.test.')
        ->assertSuccessful();

    expect($sysadmin->fresh()->email)->toBe('official@mctbs.test');
});

test('the command defaults to MCTBS_SYSAD_EMAIL when no email is passed', function () {
    $sysadmin = makeSysadmin('old-sysadmin@example.com');
    config(['services.mctbs.sysad_email' => 'from-config@mctbs.test']);

    $this->artisan('mctbs:set-sysadmin-email')->assertSuccessful();

    expect($sysadmin->fresh()->email)->toBe('from-config@mctbs.test');
});

test('the command refuses an email that belongs to another account', function () {
    $sysadmin = makeSysadmin('old-sysadmin@example.com');
    User::create([
        'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Taken',
        'last_name' => 'Owner',
        'email' => 'taken@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);

    $this->artisan('mctbs:set-sysadmin-email', ['email' => 'taken@example.com'])->assertFailed();

    expect($sysadmin->fresh()->email)->toBe('old-sysadmin@example.com');
});

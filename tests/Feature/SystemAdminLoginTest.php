<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createSystemAdminLoginTestUser(string $role, string $email, bool $isActive = true): User
{
    return User::create([
        'role_id' => Role::query()->where('name', $role)->firstOrFail()->role_id,
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => $email,
        'password' => Hash::make('ValidPassword123!'),
        'email_verified_at' => now(),
        'is_active' => $isActive,
    ]);
}

it('allows account admins to sign in through the dedicated system admin page', function (): void {
    $accountAdmin = createSystemAdminLoginTestUser('Account Admin', 'sysadmin-login@example.com');

    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee(route('sysadmin.login'), false);

    $this->get(route('sysadmin.login'))
        ->assertOk()
        ->assertSee('System Admin sign in')
        ->assertSee(route('sysadmin.login.store'), false);

    $this->post(route('sysadmin.login.store'), [
        'email' => $accountAdmin->email,
        'password' => 'ValidPassword123!',
    ])->assertRedirect(route('accadmin.dashboard'));

    $this->assertAuthenticatedAs($accountAdmin);

    $this->get(route('accadmin.dashboard'))
        ->assertOk()
        ->assertSee('Account Admin Dashboard')
        ->assertSee('Manage Owners')
        ->assertSee('Database backups');
});

it('rejects non-admin accounts from the dedicated system admin login', function (): void {
    $owner = createSystemAdminLoginTestUser('Owner', 'sysadmin-login-denied@example.com');

    $this->from(route('sysadmin.login'))
        ->post(route('sysadmin.login.store'), [
            'email' => $owner->email,
            'password' => 'ValidPassword123!',
        ])
        ->assertRedirect(route('sysadmin.login'))
        ->assertSessionHasErrors([
            'email' => 'This sign-in is for System Admin accounts only.',
        ]);

    $this->assertGuest();
});

it('keeps the existing account admin login destination unchanged', function (): void {
    $accountAdmin = createSystemAdminLoginTestUser('Account Admin', 'account-admin-login@example.com');

    $this->post(route('login'), [
        'email' => $accountAdmin->email,
        'password' => 'ValidPassword123!',
    ])->assertRedirect('/accadmin/dashboard');

    $this->assertAuthenticatedAs($accountAdmin);
});

<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createProfileManagementUser(string $role, string $email): User
{
    return User::create([
        'role_id' => Role::where('name', $role)->firstOrFail()->role_id,
        'first_name' => 'Profile',
        'last_name' => 'User',
        'email' => $email,
        'phone' => '5551000',
        'password' => 'OldPassword123!',
        'email_verified_at' => now(),
        'is_active' => true,
    ]);
}

test('owner staff and account admin dashboards show icon navigation and profile actions', function () {
    foreach ([
        ['Owner', 'owner.dashboard', 'fa-building'],
        ['Staff', 'staff.dashboard', 'fa-building'],
        ['Account Admin', 'accadmin.dashboard', 'fa-users'],
    ] as [$role, $route, $navigationIcon]) {
        $user = createProfileManagementUser($role, strtolower(str_replace(' ', '-', $role)).'@example.com');

        $response = $this->actingAs($user)
            ->get(route($route))
            ->assertSee('fa-gauge', false)
            ->assertSee($navigationIcon, false)
            ->assertSee('Notifications')
            ->assertSee('wire:poll.15s.visible', false)
            ->assertSee('class="profile-btn"', false)
            ->assertSee('Profile')
            ->assertSee('Logout')
            ->assertSee($user->first_name);
        if ($role === 'Account Admin') {
            $response->assertSee('Account Admin')
                ->assertSee('Account Admin Dashboard');
        }
    }
});

test('an authenticated user can update profile details without changing account credentials', function () {
    $user = createProfileManagementUser('Owner', 'profile-owner@example.com');

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertSee('<h1>Profile</h1>', false)
        ->assertSee('First Name')
        ->assertSee('field-requirement">Required', false)
        ->assertSee('Phone')
        ->assertSee('field-requirement">Optional', false)
        ->assertSee('PU')
        ->assertDontSee('type="file"', false)
        ->assertDontSee('current_password')
        ->assertSee('Session & account');

    $this->actingAs($user)
        ->get(route('profile.security'))
        ->assertSee('<h1>Session &amp; account</h1>', false)
        ->assertSee('current_password')
        ->assertDontSee('Profile information');

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertSee('Email changes are disabled');

    $this->put(route('profile.update'), [
        'first_name' => 'Updated',
        'last_name' => 'Owner',
        'phone' => '5559876',
        'email' => 'changed@example.com',
        'role_id' => Role::where('name', 'Account Admin')->firstOrFail()->role_id,
    ])->assertRedirect(route('profile.edit'))
        ->assertSessionHas('success');

    $user->refresh();
    expect($user->first_name)->toBe('Updated')
        ->and($user->last_name)->toBe('Owner')
        ->and($user->phone)->toBe('5559876')
        ->and($user->email)->toBe('profile-owner@example.com')
        ->and($user->role->name)->toBe('Owner')
        ->and(Hash::check('OldPassword123!', $user->password))->toBeTrue();
});

test('an authenticated user can change their password from the separate security form', function () {
    $user = createProfileManagementUser('Owner', 'password-owner@example.com');

    $this->actingAs($user)
        ->put(route('profile.password.update'), [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])
        ->assertRedirect(route('profile.security'))
        ->assertSessionHas('success');

    expect(Hash::check('NewPassword123!', $user->fresh()->password))->toBeTrue();
});

test('changing a password requires the correct current password', function () {
    $user = createProfileManagementUser('Staff', 'profile-staff@example.com');

    $this->actingAs($user)
        ->from(route('profile.security'))
        ->put(route('profile.password.update'), [
            'current_password' => 'IncorrectPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])
        ->assertRedirect(route('profile.security'))
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('OldPassword123!', $user->fresh()->password))->toBeTrue();
});

test('profile editing requires authentication', function () {
    $this->get(route('profile.edit'))->assertRedirect(route('login'));
    $this->get(route('profile.security'))->assertRedirect(route('login'));

    $this->put(route('profile.update'), [
        'first_name' => 'Guest',
        'last_name' => 'User',
    ])->assertRedirect(route('login'));
});

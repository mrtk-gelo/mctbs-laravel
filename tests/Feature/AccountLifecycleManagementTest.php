<?php

use App\Models\Invitation;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function lifecycleRole(string $name): Role
{
    return Role::firstOrCreate(['name' => $name]);
}

function lifecycleUser(string $role, string $email, array $attributes = []): User
{
    return User::create([
        'role_id' => lifecycleRole($role)->role_id,
        'first_name' => $attributes['first_name'] ?? ucfirst($role),
        'last_name' => $attributes['last_name'] ?? 'Lifecycle',
        'email' => $email,
        'phone' => $attributes['phone'] ?? null,
        'password' => Hash::make($attributes['password'] ?? 'Password123!'),
        'email_verified_at' => now(),
        'is_active' => $attributes['is_active'] ?? true,
    ]);
}

test('system admin can deactivate and reactivate an owner', function () {
    $sysadmin = lifecycleUser('System Admin', 'lifecycle-sysadmin@example.com');
    $owner = lifecycleUser('Owner', 'lifecycle-owner@example.com');

    $this->actingAs($sysadmin)
        ->post(route('sysadmin.owners.deactivate', $owner->user_id))
        ->assertRedirect(route('sysadmin.owners.index'));

    expect($owner->fresh()->is_active)->toBeFalse()
        ->and($owner->fresh()->deactivated_at)->not->toBeNull()
        ->and($owner->fresh()->deactivated_by)->toBe($sysadmin->user_id);

    $this->get(route('sysadmin.owners.deactivated'))
        ->assertOk()
        ->assertSee('lifecycle-owner@example.com');

    $this->post(route('sysadmin.owners.reactivate', $owner->user_id))
        ->assertRedirect(route('sysadmin.owners.deactivated'));

    expect($owner->fresh()->is_active)->toBeTrue()
        ->and($owner->fresh()->deactivated_at)->toBeNull();
});

test('deactivated owner login is denied with an account notice', function () {
    lifecycleUser('Owner', 'inactive-owner@example.com', [
        'is_active' => false,
        'password' => 'Password123!',
    ]);

    $this->post('/login', [
        'email' => 'inactive-owner@example.com',
        'password' => 'Password123!',
    ])
        ->assertSessionHasErrors('email');

    expect(session('errors')->first('email'))->toContain('Account Deactivated');
});

test('owner self deactivation requires password and invalidates the session', function () {
    $owner = lifecycleUser('Owner', 'self-deactivate-owner@example.com');

    $this->actingAs($owner)
        ->post(route('profile.deactivate'), ['current_password' => 'WrongPassword123!'])
        ->assertSessionHasErrors('current_password');

    expect($owner->fresh()->is_active)->toBeTrue();

    $this->actingAs($owner)
        ->post(route('profile.deactivate'), ['current_password' => 'Password123!'])
        ->assertRedirect(route('login'));

    expect($owner->fresh()->is_active)->toBeFalse()
        ->and(Auth::check())->toBeFalse();
});

test('deactivated owner properties cannot accept new bookings', function () {
    $owner = lifecycleUser('Owner', 'booking-owner@example.com');
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Lifecycle House',
        'address' => '1 Lifecycle Road',
        'city' => 'Baguio',
        'contact_number' => '5551000',
        'is_active' => true,
    ]);
    $unit = Unit::create([
        'house_id' => $house->house_id,
        'unit_type' => 'Lifecycle Room',
        'capacity' => 2,
        'price' => 1800,
        'status' => 'Available',
    ]);

    $owner->update(['is_active' => false]);

    $guest = lifecycleUser('Guest', 'lifecycle-guest@example.com');

    expect(fn () => app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(2)->toDateString(),
        'check_out' => now()->addDays(3)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Lifecycle Guest']],
    ]))->toThrow(ValidationException::class);
});

test('owner can deactivate and reactivate staff without changing parent restrictions', function () {
    $owner = lifecycleUser('Owner', 'staff-parent-owner@example.com');
    $staff = lifecycleUser('Staff', 'managed-staff@example.com');
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Staff House',
        'address' => '2 Staff Road',
        'city' => 'Baguio',
        'contact_number' => '5552000',
        'is_active' => true,
    ]);
    $invitation = Invitation::create([
        'invited_by' => $owner->user_id,
        'house_id' => $house->house_id,
        'role_id' => lifecycleRole('Staff')->role_id,
        'email' => $staff->email,
        'token' => 'lifecycle-staff-token',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($owner)
        ->post(route('owner.admins.deactivate', $invitation))
        ->assertRedirect(route('owner.admins.index'));

    expect($staff->fresh()->is_active)->toBeFalse();

    $this->post(route('owner.admins.reactivate', $invitation))
        ->assertRedirect(route('owner.admins.deactivated'));

    expect($staff->fresh()->is_active)->toBeTrue();

    $owner->update(['is_active' => false]);
    $this->actingAs($owner)
        ->post(route('owner.admins.reactivate', $invitation))
        ->assertRedirect(route('login'));
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

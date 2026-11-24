<?php

use App\Models\Role;
use App\Models\User;
use App\Support\BookingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('guest registration creates an account and provides the public browsing and bookings navigation', function () {
    $response = $this->post(route('register.store'), [
        'first_name' => 'New',
        'last_name' => 'Guest',
        'email' => 'new-guest@example.com',
        'phone' => '5551234567',
        'password' => 'GuestPass123!',
        'password_confirmation' => 'GuestPass123!',
        'terms_accepted' => '1',
    ])->assertRedirect(route('home'));

    $guest = User::where('email', 'new-guest@example.com')->firstOrFail();
    expect($guest->role->name)->toBe('Guest')
        ->and($guest->phone)->toBe('5551234567')
        ->and($guest->email_verified_at)->toBeNull()
        ->and($guest->is_active)->toBeTrue()
        ->and(Hash::check('GuestPass123!', $guest->password))->toBeTrue();

    $this->assertAuthenticatedAs($guest);
    $this->get(route('home'))
        ->assertSee('Bookings')
        ->assertSee('data-scroll-nav="transients"', false)
        ->assertSee('Home')
        ->assertSee('Transients')
        ->assertSee('class="profile-btn"', false)
        ->assertSee('wire:poll.15s.visible', false)
        ->assertSee('this.closest(\'.nav-dropdown\').classList.toggle(\'open\')', false)
        ->assertSee('Logout');
    $this->get(route('public.transients'))
        ->assertSee('Bookings')
        ->assertSee('New');
});

test('registration labels required fields and describes the enforced password requirements', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('First Name')
        ->assertSee('Last Name')
        ->assertSee('field-requirement">Required', false)
        ->assertSee('Phone Number')
        ->assertSee('field-requirement">Optional', false)
        ->assertSee('At least 8 characters, with uppercase (A-Z) and lowercase letters (a-z), a number (0-9), and a symbol (!@#$%^&*()-+).', false);
});

test('registration rejects passwords that do not include a symbol', function () {
    $this->from(route('register'))
        ->post(route('register.store'), [
            'first_name' => 'New',
            'last_name' => 'Guest',
            'email' => 'weak-password@example.com',
            'password' => 'GuestPassword123',
            'password_confirmation' => 'GuestPassword123',
            'terms_accepted' => '1',
        ])
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('password');

    $this->assertDatabaseMissing('users', ['email' => 'weak-password@example.com']);
});

test('an existing guest can log in and see their bookings from the finalized schema', function () {
    $guest = User::create([
        'role_id' => Role::where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Returning',
        'last_name' => 'Guest',
        'email' => 'returning-guest@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);

    $houseId = DB::table('transient_houses')->insertGetId([
        'owner_id' => User::create([
            'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
            'first_name' => 'House',
            'last_name' => 'Owner',
            'email' => 'booking-house-owner@example.com',
            'password' => 'OwnerPass123!',
            'is_active' => true,
        ])->user_id,
        'name' => 'Pine View House',
        'address' => '123 Pine Road',
        'city' => 'Baguio',
        'contact_number' => '5551000',
        'is_active' => true,
    ], 'house_id');

    $unitId = DB::table('units')->insertGetId([
        'house_id' => $houseId,
        'unit_type' => 'Studio',
        'capacity' => 4,
        'price' => 2500,
        'status' => 'Available',
    ], 'unit_id');

    DB::table('bookings')->insert([
        'user_id' => $guest->user_id,
        'unit_id' => $unitId,
        'check_in' => '2026-11-01 14:00:00',
        'check_out' => '2026-11-03 11:00:00',
        'adult_count' => 2,
        'child_count' => 1,
        'total_price' => 5000,
        'status' => BookingStatus::PENDING,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($guest)->post('/logout')->assertRedirect(route('home'));

    $this->get(route('login'))
        ->assertDontSee('Remember me')
        ->assertDontSee('name="remember"', false);

    $response = $this->post('/login', [
        'email' => 'returning-guest@example.com',
        'password' => 'GuestPass123!',
        'remember' => true,
    ]);

    $response->assertRedirect(route('home'))
        ->assertCookieMissing(Auth::guard()->getRecallerName());
    $this->assertAuthenticatedAs($guest);

    $this->get(route('home'))
        ->assertSee('Pine View House')
        ->assertSee('data-scroll-section="home"', false);
    $this->get('/guest/dashboard')
        ->assertSee('Pine View House')
        ->assertSee('Nov 1, 2026')
        ->assertSee('Nov 3, 2026')
        ->assertDontSee('3 guests')
        ->assertSee('₱5,000.00')
        ->assertSee('Booking #1');
});

test('guest signup rejects an email already registered to another account', function () {
    User::create([
        'role_id' => Role::where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Existing',
        'last_name' => 'Guest',
        'email' => 'already-registered@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);

    $this->from('/register')
        ->post(route('register.store'), [
            'first_name' => 'Duplicate',
            'last_name' => 'Guest',
            'email' => 'already-registered@example.com',
            'password' => 'GuestPass123!',
            'password_confirmation' => 'GuestPass123!',
            'terms_accepted' => '1',
        ])
        ->assertRedirect('/register')
        ->assertSessionHasErrors('email');

    $this->assertDatabaseCount('users', 1);
});

test('registration requires consent to the terms and shows the draft terms modal', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('name="terms_accepted"', false)
        ->assertSee('Terms and Conditions')
        ->assertSee('id="registration-terms"', false)
        ->assertSee('Transient listings and booking requests')
        ->assertSee('Draft terms for MCTBS');

    $this->from(route('register'))
        ->post(route('register.store'), [
            'first_name' => 'New',
            'last_name' => 'Guest',
            'email' => 'terms-guest@example.com',
            'password' => 'GuestPass123!',
            'password_confirmation' => 'GuestPass123!',
        ])
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('terms_accepted');

    $this->assertDatabaseMissing('users', ['email' => 'terms-guest@example.com']);
});

test('logging out ends the guest session and protected pages require authentication', function () {
    $guest = User::create([
        'role_id' => Role::where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Signed',
        'last_name' => 'Out',
        'email' => 'signed-out@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);

    $this->actingAs($guest)
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
    $this->get(route('home'))
        ->assertSee('Log in')
        ->assertSee('Register')
        ->assertDontSee('Signed');
    $this->get(route('guest.dashboard'))->assertRedirect(route('login'));
    $this->get('/owner/dashboard')->assertRedirect(route('login'));
    $this->get('/staff/dashboard')->assertRedirect(route('login'));
    $this->get('/accadmin/dashboard')->assertRedirect(route('login'));
});

test('visiting logout does not end the session while posting logout does', function () {
    $guest = User::create([
        'role_id' => Role::where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Signed',
        'last_name' => 'In',
        'email' => 'logout-visit@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);

    $this->actingAs($guest)
        ->get('/logout')
        ->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($guest);

    $this->post('/logout')->assertRedirect(route('home'));

    $this->assertGuest();
});

<?php

use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the public home contains home and transient sections and returns ajax listing results', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-scroll-section="home"', false)
        ->assertSee('data-scroll-section="transients"', false)
        ->assertSee('data-scroll-nav="transients"', false)
        ->assertSee('class="transients-hero"', false)
        ->assertSee('class="transients-search-card"', false);

    $this->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get(route('home', ['q' => 'Baguio']))
        ->assertOk()
        ->assertSee('id="transient-results"', false)
        ->assertDontSee('<x-shell', false);
});

test('guest navigation places transient browsing before bookings and keeps bookings outside the home sections', function () {
    $guest = User::create([
        'role_id' => Role::where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Public',
        'last_name' => 'Guest',
        'email' => 'public-guest@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);

    $response = $this->actingAs($guest)->get(route('home'))->assertOk();
    $html = $response->getContent();
    $navigationStart = strpos($html, '<div class="topnav-center">');
    $navigationEnd = strpos($html, '</div>', $navigationStart);
    $navigation = substr($html, $navigationStart, $navigationEnd - $navigationStart);

    expect(strpos($navigation, 'data-scroll-nav="transients"'))
        ->toBeLessThan(strpos($navigation, 'href="'.route('guest.dashboard').'"'))
        ->and(substr_count($html, 'data-scroll-section='))
        ->toBe(2)
        ->and($html)
        ->toContain('guest/dashboard');
});

test('the public home shows three transient houses and links to the complete listing', function () {
    $owner = User::create([
        'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Listing',
        'last_name' => 'Owner',
        'email' => 'listing-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);

    foreach (range(1, 4) as $number) {
        TransientHouse::create([
            'owner_id' => $owner->user_id,
            'name' => "Listing House {$number}",
            'address' => '10 Test Road',
            'city' => 'Baguio',
            'contact_number' => '5551234',
            'is_active' => true,
        ]);
    }

    $this->get(route('home'))
        ->assertSee('Listing House 4')
        ->assertSee('Listing House 3')
        ->assertSee('Listing House 2')
        ->assertDontSee('Listing House 1')
        ->assertSee('View More Transient')
        ->assertSee('href="'.route('public.transients').'"', false);

    $this->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get(route('home'))
        ->assertSee('Listing House 4')
        ->assertSee('Listing House 3')
        ->assertSee('Listing House 2')
        ->assertDontSee('Listing House 1')
        ->assertSee('View More Transient');

    $this->get(route('public.transients'))
        ->assertSee('Listing House 1')
        ->assertSee('Listing House 2')
        ->assertSee('Listing House 3')
        ->assertSee('Listing House 4');

    TransientHouse::where('name', 'Listing House 1')->delete();

    $this->get(route('home'))
        ->assertSee('Listing House 2')
        ->assertSee('Listing House 3')
        ->assertSee('Listing House 4')
        ->assertDontSee('View More Transient');
});

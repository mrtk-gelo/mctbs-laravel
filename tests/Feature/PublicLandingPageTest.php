<?php

use App\Models\Role;
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

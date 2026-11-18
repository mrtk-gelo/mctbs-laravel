<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('data-scroll-section="home"', false)
        ->assertSee('data-scroll-section="transients"', false);
});

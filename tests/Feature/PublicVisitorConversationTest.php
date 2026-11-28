<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the public landing page loads with a migrated database', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

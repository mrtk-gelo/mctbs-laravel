<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('email replies cannot be used to send messages into MCTBS Connect', function () {
    $this->postJson('/webhooks/postmark/inbound', [
        'From' => 'Guest <guest@example.com>',
        'To' => 'reply@example.com',
        'TextBody' => 'Please respond by email.',
        'MessageID' => '<message@example.com>',
    ])->assertNotFound();
});

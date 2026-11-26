<?php

use App\Mail\ConversationMessage;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('postmark inbound replies are authenticated, guest scoped, and idempotent', function () {
    config([
        'services.postmark.inbound_username' => 'postmark',
        'services.postmark.inbound_password' => 'webhook-secret',
        'services.postmark.inbound_domain' => 'inbound.example.test',
    ]);
    Mail::fake();
    $owner = inboundUser('Owner', 'inbound-owner@example.com');
    $guest = inboundUser('Guest', 'inbound-guest@example.com');
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Inbound House',
        'address' => '1 Inbound Road',
        'city' => 'Baguio',
        'contact_number' => '5553000',
        'is_active' => true,
    ]);
    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id);
    $payload = [
        'From' => 'Inbound Guest <'.$guest->email.'>',
        'To' => 'reply+'.$conversation->external_token.'@inbound.example.test',
        'TextBody' => 'I will arrive later.',
        'MessageID' => '<postmark-message-1@example.test>',
    ];

    $this->withBasicAuth('postmark', 'webhook-secret')
        ->postJson(route('webhooks.postmark.inbound'), $payload)
        ->assertOk()
        ->assertJson(['accepted' => true]);

    $this->withBasicAuth('postmark', 'webhook-secret')
        ->postJson(route('webhooks.postmark.inbound'), $payload)
        ->assertOk();

    expect($conversation->messages()->where('external_message_id', $payload['MessageID'])->count())->toBe(1);
    Mail::assertSent(
        ConversationMessage::class,
        fn (ConversationMessage $mail): bool => $mail->replyAddress === 'reply+'.$conversation->external_token.'@inbound.example.test'
    );

    $this->withBasicAuth('postmark', 'webhook-secret')
        ->postJson(route('webhooks.postmark.inbound'), array_merge($payload, [
            'From' => 'Imposter <imposter@example.com>',
            'MessageID' => '<postmark-message-2@example.test>',
        ]))
        ->assertForbidden();
});

function inboundUser(string $role, string $email): User
{
    return User::create([
        'role_id' => Role::query()->where('name', $role)->firstOrFail()->role_id,
        'first_name' => $role,
        'last_name' => 'Inbound',
        'email' => $email,
        'password' => 'Password123!',
        'is_active' => true,
    ]);
}

<?php

use App\Models\Conversation;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

test('the public landing page loads with a migrated database', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('anonymous conversations are scoped to their visitor session and can be reopened from a signed link', function () {
    Mail::fake();
    $owner = User::create([
        'role_id' => Role::query()->where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Property',
        'last_name' => 'Owner',
        'email' => 'visitor-chat-owner@example.com',
        'password' => 'Password123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Visitor Chat House',
        'address' => '1 Visitor Road',
        'city' => 'Baguio',
        'contact_number' => '5557000',
        'is_active' => true,
    ]);

    $this->post(route('public.visitor-conversations.store'), [
        'house_id' => $house->house_id,
        'name' => 'Public Guest',
        'email' => 'public-guest@example.com',
        'message' => 'Can someone help me?',
    ])->assertRedirect();
    $conversation = Conversation::query()->firstOrFail();

    $this->get(route('public.visitor-conversations.show', $conversation->conversation_id))
        ->assertOk()
        ->assertSee('Can someone help me?')
        ->assertDontSee('guest-chat-launcher');

    $this->flushSession();
    $this->get(route('public.visitor-conversations.show', $conversation->conversation_id))
        ->assertNotFound();

    $signedUrl = URL::temporarySignedRoute(
        'public.visitor-conversations.access',
        now()->addDay(),
        ['conversationId' => $conversation->conversation_id]
    );

    $this->get($signedUrl)
        ->assertRedirect(route('public.visitor-conversations.show', $conversation->conversation_id));
    $this->get(route('public.visitor-conversations.show', $conversation->conversation_id))
        ->assertOk()
        ->assertSee('Can someone help me?');
});

test('sensitive visitor questions receive a sign-in prompt instead of an answer', function () {
    $owner = User::create([
        'role_id' => Role::query()->where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Property',
        'last_name' => 'Owner',
        'email' => 'visitor-sensitive-owner@example.com',
        'password' => 'Password123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Private Booking House',
        'address' => '1 Private Road',
        'city' => 'Baguio',
        'contact_number' => '5557001',
        'is_active' => true,
    ]);

    $this->postJson(route('public.visitor-chat.answer'), [
        'house_id' => $house->house_id,
        'question' => 'How much have I paid for my booking?',
    ])
        ->assertOk()
        ->assertJson([
            'requiresSignIn' => true,
            'signInUrl' => route('login'),
        ])
        ->assertJsonMissingPath('answer');
});

test('login claims only visitor chats with the matching email from the same browser session', function () {
    Mail::fake();
    $owner = User::create([
        'role_id' => Role::query()->where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Property',
        'last_name' => 'Owner',
        'email' => 'visitor-claim-owner@example.com',
        'password' => 'Password123!',
        'is_active' => true,
    ]);
    $guest = User::create([
        'role_id' => Role::query()->where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Matching',
        'last_name' => 'Guest',
        'email' => 'matching-visitor@example.com',
        'password' => 'Password123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Visitor Claim House',
        'address' => '1 Claim Road',
        'city' => 'Baguio',
        'contact_number' => '5557002',
        'is_active' => true,
    ]);

    foreach (['matching-visitor@example.com', 'other-visitor@example.com'] as $email) {
        $this->post(route('public.visitor-conversations.store'), [
            'house_id' => $house->house_id,
            'name' => 'Public Guest',
            'email' => $email,
            'message' => 'Could someone help me?',
        ])->assertRedirect();
    }

    $matchingConversationId = Conversation::query()
        ->where('visitor_email', $guest->email)
        ->value('conversation_id');
    $otherConversationId = Conversation::query()
        ->where('visitor_email', 'other-visitor@example.com')
        ->value('conversation_id');

    $this->post(route('login'), [
        'email' => $guest->email,
        'password' => 'Password123!',
    ])->assertRedirect($guest->dashboardPath());

    $this->assertDatabaseHas('conversations', [
        'conversation_id' => $matchingConversationId,
        'guest_user_id' => $guest->user_id,
    ]);
    $this->assertDatabaseHas('conversations', [
        'conversation_id' => $otherConversationId,
        'guest_user_id' => null,
    ]);
});

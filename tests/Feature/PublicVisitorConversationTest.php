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
        ->assertSee('Visitor (no account)')
        ->assertSee('Property owner')
        ->assertSee($owner->email)
        ->assertSee('data-chat-poll-url', false)
        ->assertDontSee('guest-chat-launcher');

    $this->flushSession();
    $this->get(route('public.visitor-conversations.show', $conversation->conversation_id))
        ->assertNotFound();
    $this->getJson(route('public.visitor-conversations.updates', $conversation->conversation_id))
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

test('anonymous conversation polling returns only replies after the supplied message cursor', function () {
    Mail::fake();
    $owner = User::create([
        'role_id' => Role::query()->where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Polling',
        'last_name' => 'Owner',
        'email' => 'visitor-poll-owner@example.com',
        'password' => 'Password123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Visitor Poll House',
        'address' => '1 Poll Road',
        'city' => 'Baguio',
        'contact_number' => '5557004',
        'is_active' => true,
    ]);

    $this->post(route('public.visitor-conversations.store'), [
        'house_id' => $house->house_id,
        'name' => 'Polling Visitor',
        'email' => 'visitor-poll@example.com',
        'message' => 'Can the team help with my question?',
    ])->assertRedirect();
    $conversation = Conversation::query()->firstOrFail();
    $cursor = (int) $conversation->messages()->max('message_id');
    $reply = $conversation->messages()->create([
        'sender_id' => $owner->user_id,
        'sender_type' => 'user',
        'message' => 'The team has replied.',
        'message_type' => 'user',
        'sent_at' => now(),
    ]);

    $this->getJson(route('public.visitor-conversations.updates', [
        'conversationId' => $conversation->conversation_id,
        'after' => $cursor,
    ]))
        ->assertOk()
        ->assertJsonCount(1, 'messages')
        ->assertJsonPath('messages.0.id', $reply->message_id)
        ->assertJsonPath('messages.0.text', 'The team has replied.');
});

test('anonymous visitors can archive chats and permanently delete their own copy', function () {
    Mail::fake();
    $owner = User::create([
        'role_id' => Role::query()->where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Folder',
        'last_name' => 'Owner',
        'email' => 'visitor-folder-owner@example.com',
        'password' => 'Password123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Visitor Folder House',
        'address' => '1 Folder Road',
        'city' => 'Baguio',
        'contact_number' => '5557003',
        'is_active' => true,
    ]);

    $this->post(route('public.visitor-conversations.store'), [
        'house_id' => $house->house_id,
        'name' => 'Folder Visitor',
        'email' => 'visitor-folder@example.com',
        'subject' => 'Folder test conversation',
        'message' => 'Can the team help with my question?',
    ])->assertRedirect();
    $conversation = Conversation::query()->firstOrFail();
    $messageCount = $conversation->messages()->count();
    $this->get(route('public.visitor-conversations.index'))
        ->assertSee('Show archive')
        ->assertDontSee('chat-folder-links', false);

    $this->post(route('public.visitor-conversations.archive', $conversation->conversation_id))
        ->assertRedirect(route('public.visitor-conversations.index', ['folder' => 'archived']));
    $this->get(route('public.visitor-conversations.index', ['folder' => 'archived']))
        ->assertSee('Folder test conversation')
        ->assertSee('Show inbox');

    $signedUrl = URL::temporarySignedRoute(
        'public.visitor-conversations.access',
        now()->addDay(),
        ['conversationId' => $conversation->conversation_id]
    );
    $this->flushSession();
    $this->get($signedUrl)
        ->assertRedirect(route('public.visitor-conversations.show', $conversation->conversation_id));
    $this->get(route('public.visitor-conversations.show', $conversation->conversation_id))
        ->assertSee('Visitor (no account)');
    $this->get(route('public.visitor-conversations.index', ['folder' => 'archived']))
        ->assertSee('Folder test conversation');

    $this->post(route('public.visitor-conversations.delete', $conversation->conversation_id))
        ->assertRedirect(route('public.visitor-conversations.index', ['folder' => 'inbox']));
    $this->get(route('public.visitor-conversations.index'))
        ->assertDontSee('Folder test conversation');
    $this->get(route('public.visitor-conversations.show', $conversation->conversation_id))
        ->assertNotFound();
    $this->get($signedUrl)->assertNotFound();
    $this->post(route('public.visitor-conversations.restore', $conversation->conversation_id))
        ->assertNotFound();

    $this->post(route('public.visitor-conversations.store'), [
        'house_id' => $house->house_id,
        'name' => 'Folder Visitor',
        'email' => 'visitor-folder@example.com',
        'subject' => 'Fresh visitor conversation',
        'message' => 'This is a new inquiry with fresh chat history.',
    ])->assertRedirect();
    $freshConversation = Conversation::query()
        ->where('conversation_id', '!=', $conversation->conversation_id)
        ->where('visitor_email', 'visitor-folder@example.com')
        ->firstOrFail();
    $this->get(route('public.visitor-conversations.index'))
        ->assertSee('Fresh visitor conversation')
        ->assertDontSee('Folder test conversation');
    $this->actingAs($owner)
        ->get(route('owner.conversations.index'))
        ->assertSee('Folder test conversation')
        ->assertSee('Fresh visitor conversation');

    expect($conversation->fresh())->not->toBeNull()
        ->and($conversation->messages()->count())->toBe($messageCount)
        ->and($conversation->fresh()->visitor_deleted_at)->not->toBeNull()
        ->and($freshConversation->fresh()->visitor_deleted_at)->toBeNull();
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

    $this->post(route('public.visitor-conversations.archive', $matchingConversationId))
        ->assertRedirect(route('public.visitor-conversations.index', ['folder' => 'archived']));

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
    expect(Conversation::query()->findOrFail($matchingConversationId)
        ->participants()
        ->where('user_id', $guest->user_id)
        ->value('archived_at'))->not->toBeNull();
});

test('public visitors can unsend their own messages and view pinned owner messages with timestamps', function () {
    Mail::fake();
    $owner = User::create([
        'role_id' => Role::query()->where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Modal',
        'last_name' => 'Owner',
        'email' => 'public-message-actions-owner@example.com',
        'password' => 'Password123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Public Message Actions House',
        'address' => '1 Actions Road',
        'city' => 'Baguio',
        'contact_number' => '5557005',
        'is_active' => true,
    ]);

    $this->post(route('public.visitor-conversations.store'), [
        'house_id' => $house->house_id,
        'name' => 'Actions Visitor',
        'email' => 'public-message-actions@example.com',
        'message' => 'Visitor message to unsend.',
    ])->assertRedirect();
    $conversation = Conversation::query()->firstOrFail();
    $visitorMessage = $conversation->messages()->where('sender_type', 'visitor')->firstOrFail();
    $ownerSentAt = now()->setTime(7, 15);
    $ownerMessage = $conversation->messages()->create([
        'sender_id' => $owner->user_id,
        'sender_type' => 'user',
        'message' => 'Pinned owner reply with the exact date.',
        'message_type' => 'user',
        'sent_at' => $ownerSentAt,
    ]);

    $this->postJson(route('public.visitor-conversations.pins', [
        'conversationId' => $conversation->conversation_id,
        'messageId' => $ownerMessage->message_id,
    ]))
        ->assertOk()
        ->assertJsonPath('pins.0.timeLabel', $ownerSentAt->format('M j, g:i A'));

    $this->get(route('public.visitor-conversations.show', $conversation->conversation_id))
        ->assertOk()
        ->assertSee('data-chat-pinned-modal', false)
        ->assertSee('Pinned owner reply with the exact date.')
        ->assertSee('7:15 AM')
        ->assertSee('Unsend')
        ->assertDontSee('data-chat-react', false);

    $this->postJson(route('public.visitor-conversations.unsend', [
        'conversationId' => $conversation->conversation_id,
        'messageId' => $visitorMessage->message_id,
    ]))
        ->assertOk()
        ->assertJsonPath('messageId', $visitorMessage->message_id);

    expect($visitorMessage->fresh()->message)->toBe('')
        ->and($visitorMessage->fresh()->unsent_at)->not->toBeNull();

    $this->getJson(route('public.visitor-conversations.updates', [
        'conversationId' => $conversation->conversation_id,
    ]))
        ->assertOk()
        ->assertJsonPath('unsentMessageIds.0', $visitorMessage->message_id)
        ->assertJsonMissing(['text' => 'Visitor message to unsend.']);

    $this->postJson(route('public.visitor-conversations.unsend', [
        'conversationId' => $conversation->conversation_id,
        'messageId' => $ownerMessage->message_id,
    ]))->assertForbidden();

    $this->postJson(route('public.visitor-conversations.pins', [
        'conversationId' => $conversation->conversation_id,
        'messageId' => $visitorMessage->message_id,
    ]))->assertNotFound();
});

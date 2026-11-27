<?php

use App\Models\ConversationParticipant;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('opening a conversation clears unread messages for that user only', function () {
    $owner = unreadConversationUser('Owner', 'unread-owner@example.com');
    $guest = unreadConversationUser('Guest', 'unread-guest@example.com');
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Unread House',
        'address' => '1 Unread Street',
        'city' => 'Baguio',
        'contact_number' => '5554000',
        'is_active' => true,
    ]);
    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id);
    app(ConversationService::class)->sendMessage($guest, $conversation, 'Please help with my booking.');

    $this->actingAs($owner)
        ->get(route('owner.conversations.index'))
        ->assertOk()
        ->assertSee('1 unread')
        ->assertSee('title="1 unread conversations"', false);

    $this->actingAs($owner)
        ->get(route('owner.conversations.show', $conversation->conversation_id))
        ->assertOk();

    $ownerParticipant = $conversation->participants()->where('user_id', $owner->user_id)->firstOrFail();
    expect($ownerParticipant->last_read_message_id)->toBe($conversation->messages()->max('message_id'));

    $this->actingAs($owner)
        ->get(route('owner.conversations.index'))
        ->assertDontSee('title="1 unread conversations"', false);

    $this->actingAs($guest)
        ->get(route('guest.conversations.index'))
        ->assertOk()
        ->assertDontSee('1 unread');

    $this->actingAs($owner)
        ->post(route('owner.conversations.store', $conversation->conversation_id), [
            'message' => 'We are happy to help.',
        ])
        ->assertRedirect();

    $this->actingAs($guest)
        ->get(route('guest.conversations.index'))
        ->assertOk()
        ->assertSee('1 unread');

    $this->actingAs($guest)
        ->get(route('guest.conversations.show', $conversation->conversation_id))
        ->assertOk();

    $this->actingAs($guest)
        ->get(route('guest.conversations.index'))
        ->assertOk()
        ->assertDontSee('1 unread');
});

test('opening another property conversation does not advance a read cursor', function () {
    $owner = unreadConversationUser('Owner', 'unread-scope-owner@example.com');
    $otherOwner = unreadConversationUser('Owner', 'unread-scope-other@example.com');
    $guest = unreadConversationUser('Guest', 'unread-scope-guest@example.com');
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Private House',
        'address' => '1 Private Street',
        'city' => 'Baguio',
        'contact_number' => '5555000',
        'is_active' => true,
    ]);
    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id);

    $this->actingAs($otherOwner)
        ->get(route('owner.conversations.show', $conversation->conversation_id))
        ->assertForbidden();

    expect(ConversationParticipant::query()
        ->where('conversation_id', $conversation->conversation_id)
        ->where('user_id', $otherOwner->user_id)
        ->exists())->toBeFalse();
});

function unreadConversationUser(string $role, string $email): User
{
    return User::create([
        'role_id' => Role::query()->where('name', $role)->firstOrFail()->role_id,
        'first_name' => ucfirst($role),
        'last_name' => 'Unread',
        'email' => $email,
        'password' => 'Password123!',
        'is_active' => true,
    ]);
}

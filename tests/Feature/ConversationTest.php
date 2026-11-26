<?php

use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\User;
use App\Services\ConversationService;
use App\Support\BookingStatus;
use App\Support\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function conversationUser(string $role, string $email): User
{
    return User::create([
        'role_id' => Role::where('name', $role)->firstOrFail()->role_id,
        'first_name' => ucfirst($role),
        'last_name' => 'Tester',
        'email' => $email,
        'password' => 'Password123!',
        'is_active' => true,
    ]);
}

function conversationHouse(User $owner, string $name = 'Test House'): TransientHouse
{
    return TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => $name,
        'address' => '1 Test Road',
        'city' => 'Baguio',
        'contact_number' => '5551234',
        'is_active' => true,
    ]);
}

test('a guest can start a conversation and only the property owner can access it', function () {
    $owner = conversationUser('Owner', 'owner@example.com');
    $otherOwner = conversationUser('Owner', 'other-owner@example.com');
    $guest = conversationUser('Guest', 'guest@example.com');
    $house = conversationHouse($owner);

    $this->actingAs($guest)
        ->post(route('guest.conversations.store'), [
            'house_id' => $house->house_id,
            'subject' => 'Check-in question',
            'message' => 'What time is check-in?',
        ])
        ->assertRedirect();

    $conversation = Conversation::query()->firstOrFail();

    expect($conversation->guest_user_id)->toBe($guest->user_id)
        ->and($conversation->house_id)->toBe($house->house_id)
        ->and($conversation->status)->toBe('pending_staff')
        ->and(Message::query()->where('conversation_id', $conversation->conversation_id)->where('sender_type', 'user')->count())->toBe(1)
        ->and(Message::query()->where('conversation_id', $conversation->conversation_id)->count())->toBe(2)
        ->and(Notification::query()->where('user_id', $owner->user_id)->where('conversation_id', $conversation->conversation_id)->exists())->toBeTrue();

    $this->actingAs($owner)
        ->get(route('owner.conversations.show', $conversation->conversation_id))
        ->assertOk();

    $this->actingAs($guest)
        ->get(route('guest.conversations.show', $conversation->conversation_id))
        ->assertOk();

    $this->actingAs($guest)
        ->post(route('guest.conversations.reply', $conversation->conversation_id), [
            'message' => 'And what time is checkout?',
        ])
        ->assertRedirect();

    $this->actingAs($otherOwner)
        ->get(route('owner.conversations.show', $conversation->conversation_id))
        ->assertForbidden();
});

test('staff can access only conversations for an invited property', function () {
    $owner = conversationUser('Owner', 'owner-staff@example.com');
    $otherOwner = conversationUser('Owner', 'other-owner-staff@example.com');
    $staff = conversationUser('Staff', 'staff@example.com');
    $guest = conversationUser('Guest', 'guest-staff@example.com');
    $house = conversationHouse($owner, 'Assigned House');
    $otherHouse = conversationHouse($otherOwner, 'Other House');
    $staffRoleId = Role::where('name', 'Staff')->firstOrFail()->role_id;

    $owner->invitationsSent()->create([
        'house_id' => $house->house_id,
        'role_id' => $staffRoleId,
        'email' => $staff->email,
        'token' => 'staff-token',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);

    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id);
    app(ConversationService::class)->sendMessage($guest, $conversation, 'Hello');
    $otherConversation = app(ConversationService::class)->createForGuest($guest, $otherHouse->house_id);

    $this->actingAs($staff)
        ->get(route('staff.conversations.show', $conversation->conversation_id))
        ->assertOk();

    $this->actingAs($staff)
        ->get(route('staff.conversations.show', $otherConversation->conversation_id))
        ->assertForbidden();
});

test('sending a message with the same client token does not duplicate it', function () {
    $owner = conversationUser('Owner', 'owner-token@example.com');
    $guest = conversationUser('Guest', 'guest-token@example.com');
    $house = conversationHouse($owner, 'Token House');
    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id);
    $service = app(ConversationService::class);

    $service->sendMessage($guest, $conversation, 'First message', 'client-123');
    $service->sendMessage($guest, $conversation, 'First message', 'client-123');

    expect($conversation->messages()->where('sender_type', 'user')->count())->toBe(1);
});

test('staff can assign and resolve a property conversation', function () {
    $owner = conversationUser('Owner', 'owner-assignment@example.com');
    $staff = conversationUser('Staff', 'staff-assignment@example.com');
    $guest = conversationUser('Guest', 'guest-assignment@example.com');
    $house = conversationHouse($owner, 'Assignment House');
    $staffRoleId = Role::where('name', 'Staff')->firstOrFail()->role_id;

    $owner->invitationsSent()->create([
        'house_id' => $house->house_id,
        'role_id' => $staffRoleId,
        'email' => $staff->email,
        'token' => 'assignment-token',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);

    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id);

    $this->actingAs($owner)
        ->post(route('owner.conversations.assign', $conversation->conversation_id), [
            'assigned_to' => $staff->user_id,
        ])
        ->assertRedirect();

    expect($conversation->fresh()->assigned_to)->toBe($staff->user_id)
        ->and($conversation->fresh()->status)->toBe('assigned');

    $this->actingAs($staff)
        ->post(route('staff.conversations.resolve', $conversation->conversation_id))
        ->assertRedirect();

    expect($conversation->fresh()->status)->toBe('resolved')
        ->and($conversation->fresh()->resolved_at)->not->toBeNull();
});

test('guest contextual assistance uses only the guest booking and does not mutate payment records', function () {
    $owner = conversationUser('Owner', 'owner-context@example.com');
    $guest = conversationUser('Guest', 'guest-context@example.com');
    $otherGuest = conversationUser('Guest', 'other-context@example.com');
    $house = conversationHouse($owner, 'Context House');
    $unit = Unit::create([
        'house_id' => $house->house_id,
        'unit_type' => 'Context Room',
        'capacity' => 2,
        'maximum_capacity' => 2,
        'price' => 1800,
        'status' => 'Available',
    ]);
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->addDays(3),
        'check_out' => today()->addDays(5),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'booking_reference' => 'CTX-001',
        'status' => BookingStatus::CONFIRMED,
    ]);
    $payment = Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
    ]);
    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id);

    $this->actingAs($guest)
        ->post(route('guest.conversations.reply', $conversation->conversation_id), [
            'message' => 'What is my payment status?',
        ])
        ->assertRedirect();

    expect($conversation->messages()->where('message_type', 'context')->value('message'))
        ->toContain(PaymentStatus::VERIFIED)
        ->and($payment->fresh()->payment_status)->toBe(PaymentStatus::VERIFIED);

    $otherConversation = app(ConversationService::class)->createForGuest($otherGuest, $house->house_id);
    $this->actingAs($otherGuest)
        ->get(route('guest.conversations.show', $otherConversation->conversation_id))
        ->assertOk()
        ->assertDontSee('CTX-001');
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

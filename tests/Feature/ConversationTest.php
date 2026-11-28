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
use Illuminate\Support\Facades\Mail;

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

test('staff replies without assigning or resolving a property conversation', function () {
    Mail::fake();
    $owner = conversationUser('Owner', 'owner-reply@example.com');
    $staff = conversationUser('Staff', 'staff-reply@example.com');
    $guest = conversationUser('Guest', 'guest-reply@example.com');
    $house = conversationHouse($owner, 'Reply House');
    $staffRoleId = Role::where('name', 'Staff')->firstOrFail()->role_id;

    $owner->invitationsSent()->create([
        'house_id' => $house->house_id,
        'role_id' => $staffRoleId,
        'email' => $staff->email,
        'token' => 'reply-token',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);

    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id);

    $this->actingAs($staff)
        ->post(route('staff.conversations.store', $conversation->conversation_id), [
            'message' => 'We can help with that.',
        ])
        ->assertRedirect();

    expect($conversation->fresh()->assigned_to)->toBeNull()
        ->and($conversation->fresh()->status)->toBe('open')
        ->and($conversation->fresh()->resolved_at)->toBeNull()
        ->and($conversation->messages()->where('message', 'We can help with that.')->exists())->toBeTrue();
});

test('owner inbox filters chats by each guest property latest booking status', function () {
    $owner = conversationUser('Owner', 'owner-status-filter@example.com');
    $guest = conversationUser('Guest', 'guest-status-filter@example.com');
    $acceptedGuest = conversationUser('Guest', 'accepted-status-filter@example.com');
    $legacyGuest = conversationUser('Guest', 'legacy-status-filter@example.com');
    $noBookingGuest = conversationUser('Guest', 'no-booking-status-filter@example.com');
    $house = conversationHouse($owner, 'Status Filter House');
    $unit = Unit::create([
        'house_id' => $house->house_id,
        'unit_type' => 'Status Suite',
        'capacity' => 2,
        'price' => 1800,
        'status' => 'Available',
    ]);

    $createBooking = fn (User $bookingGuest, string $status, int $daysAgo): Booking => Booking::create([
        'user_id' => $bookingGuest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->subDays($daysAgo),
        'check_out' => now()->subDays($daysAgo)->addDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'status' => $status,
    ]);

    $createBooking($guest, BookingStatus::ACCEPTED, 10);
    $createBooking($guest, BookingStatus::COMPLETED, 5);
    $createBooking($acceptedGuest, BookingStatus::ACCEPTED, 3);
    $createBooking($legacyGuest, BookingStatus::CHECKED_OUT, 4);

    app(ConversationService::class)->createForGuest($guest, $house->house_id, 'Latest complete chat');
    app(ConversationService::class)->createForGuest($acceptedGuest, $house->house_id, 'Accepted chat');
    app(ConversationService::class)->createForGuest($legacyGuest, $house->house_id, 'Legacy checked-out chat');
    app(ConversationService::class)->createForGuest($noBookingGuest, $house->house_id, 'No booking chat');

    $this->actingAs($owner)
        ->get(route('owner.conversations.index', ['booking_status' => BookingStatus::COMPLETED]))
        ->assertOk()
        ->assertSee('Latest complete chat')
        ->assertSee('Legacy checked-out chat')
        ->assertDontSee('Accepted chat')
        ->assertDontSee('No booking chat');

    $this->actingAs($owner)
        ->get(route('owner.conversations.index', ['booking_status' => BookingStatus::ACCEPTED]))
        ->assertSee('Accepted chat')
        ->assertDontSee('Latest complete chat');

    $this->actingAs($owner)
        ->get(route('owner.conversations.index', ['booking_status' => 'none']))
        ->assertSee('No booking chat')
        ->assertDontSee('Accepted chat');
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

    expect($conversation->messages()->where('sender_type', 'assistant')->value('message'))
        ->toBe('Your latest booking payment status is '.PaymentStatus::VERIFIED.'.')
        ->and($payment->fresh()->payment_status)->toBe(PaymentStatus::VERIFIED);

    $this->actingAs($guest)
        ->post(route('guest.conversations.reply', $conversation->conversation_id), [
            'message' => 'What time are check-in and check-out?',
        ])
        ->assertRedirect();

    expect($conversation->messages()->where('sender_type', 'assistant')->latest('message_id')->value('message'))
        ->toBe('Your check-in date is '.$booking->check_in->format('M j, Y').' at 2:00 PM and check-out date is '
            .$booking->check_out->format('M j, Y').' at 12:00 NN.');

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

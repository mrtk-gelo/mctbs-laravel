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

test('conversation updates return only new messages and mark them read', function () {
    $owner = conversationUser('Owner', 'owner-poll@example.com');
    $guest = conversationUser('Guest', 'guest-poll@example.com');
    $house = conversationHouse($owner, 'Polling House');
    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id);
    $firstMessage = $conversation->messages()->create([
        'sender_id' => $owner->user_id,
        'sender_type' => 'user',
        'message' => 'Earlier update',
        'message_type' => 'user',
        'sent_at' => now()->subMinute(),
    ]);
    $newMessage = $conversation->messages()->create([
        'sender_id' => $owner->user_id,
        'sender_type' => 'user',
        'message' => 'A new update',
        'message_type' => 'user',
        'sent_at' => now(),
    ]);

    $this->actingAs($guest)
        ->getJson(route('guest.conversations.updates', [
            'conversationId' => $conversation->conversation_id,
            'after' => $firstMessage->message_id,
        ]))
        ->assertOk()
        ->assertJsonCount(1, 'messages')
        ->assertJsonPath('messages.0.id', $newMessage->message_id)
        ->assertJsonPath('messages.0.text', 'A new update');

    expect((int) $conversation->participants()->where('user_id', $guest->user_id)->value('last_read_message_id'))
        ->toBe($newMessage->message_id);
});

test('conversation archiving is reversible but deletion is permanent for the deleting participant', function () {
    Mail::fake();
    $owner = conversationUser('Owner', 'owner-folders@example.com');
    $guest = conversationUser('Guest', 'guest-folders@example.com');
    $house = conversationHouse($owner, 'Folder House');
    $conversation = app(ConversationService::class)->createForGuest(
        $guest,
        $house->house_id,
        'Private folder topic'
    );

    $this->actingAs($owner)
        ->get(route('owner.conversations.index'))
        ->assertSee('Show archive')
        ->assertDontSee('chat-folder-links', false);
    $this->actingAs($guest)
        ->get(route('guest.conversations.index'))
        ->assertSee('Show archive')
        ->assertDontSee('chat-folder-links', false);

    $this->actingAs($owner)
        ->post(route('owner.conversations.archive', $conversation->conversation_id))
        ->assertRedirect(route('owner.conversations.index', ['folder' => 'archived']));

    $this->actingAs($owner)
        ->get(route('owner.conversations.index'))
        ->assertDontSee('Private folder topic');
    $this->actingAs($owner)
        ->get(route('owner.conversations.index', ['folder' => 'archived']))
        ->assertSee('Private folder topic');
    $this->actingAs($guest)
        ->get(route('guest.conversations.index'))
        ->assertSee('Private folder topic');

    $this->actingAs($guest)
        ->post(route('guest.conversations.delete', $conversation->conversation_id))
        ->assertRedirect(route('guest.conversations.index', ['folder' => 'inbox']));
    $this->actingAs($guest)
        ->get(route('guest.conversations.index'))
        ->assertDontSee('Private folder topic');
    $this->actingAs($guest)
        ->get(route('guest.conversations.show', $conversation->conversation_id))
        ->assertForbidden();
    $this->actingAs($guest)
        ->post(route('guest.conversations.restore', $conversation->conversation_id))
        ->assertForbidden();
    $this->actingAs($owner)
        ->get(route('owner.conversations.index', ['folder' => 'archived']))
        ->assertSee('Private folder topic');

    $this->actingAs($owner)
        ->post(route('owner.conversations.restore', $conversation->conversation_id))
        ->assertRedirect(route('owner.conversations.index', ['folder' => 'inbox']));

    $this->actingAs($guest)
        ->post(route('guest.conversations.store'), [
            'house_id' => $house->house_id,
            'subject' => 'Fresh question',
            'message' => 'Starting a new conversation after deleting the old one.',
        ])
        ->assertRedirect();
    $freshConversation = Conversation::query()
        ->where('guest_user_id', $guest->user_id)
        ->where('conversation_id', '!=', $conversation->conversation_id)
        ->firstOrFail();

    app(ConversationService::class)->sendMessage($owner, $conversation, 'A reply to the old conversation.');

    $this->actingAs($guest)
        ->get(route('guest.conversations.index'))
        ->assertSee('Fresh question')
        ->assertDontSee('Private folder topic');
    $this->actingAs($owner)
        ->get(route('owner.conversations.index'))
        ->assertSee('Private folder topic')
        ->assertSee('Fresh question');
    $this->actingAs($owner)
        ->post(route('owner.conversations.delete', $freshConversation->conversation_id))
        ->assertRedirect(route('owner.conversations.index', ['folder' => 'inbox']));
    $this->actingAs($owner)
        ->get(route('owner.conversations.show', $freshConversation->conversation_id))
        ->assertForbidden();
    $this->actingAs($guest)
        ->get(route('guest.conversations.show', $freshConversation->conversation_id))
        ->assertSee('Fresh question');

    expect($conversation->fresh())->not->toBeNull()
        ->and($conversation->participants()->where('user_id', $owner->user_id)->value('archived_at'))->toBeNull()
        ->and($conversation->participants()->where('user_id', $guest->user_id)->value('deleted_at'))->not->toBeNull()
        ->and($freshConversation->participants()->where('user_id', $owner->user_id)->value('deleted_at'))->not->toBeNull()
        ->and($freshConversation->participants()->where('user_id', $guest->user_id)->value('deleted_at'))->toBeNull();
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

test('owner inbox filters all, unread, and anonymous guest conversations', function () {
    Mail::fake();
    $owner = conversationUser('Owner', 'owner-inbox-filter@example.com');
    $guest = conversationUser('Guest', 'registered-inbox-guest@example.com');
    $house = conversationHouse($owner, 'Inbox Filter House');
    $registeredConversation = app(ConversationService::class)->createForGuest(
        $guest,
        $house->house_id,
        'Registered guest question'
    );
    app(ConversationService::class)->sendMessage($guest, $registeredConversation, 'Can someone help me?');

    $this->post(route('public.visitor-conversations.store'), [
        'house_id' => $house->house_id,
        'name' => 'Anonymous Visitor',
        'email' => 'anonymous-inbox-guest@example.com',
        'message' => 'Can someone call me about this property?',
    ])->assertRedirect();

    $this->actingAs($owner)
        ->get(route('owner.conversations.index', ['filter' => 'all']))
        ->assertOk()
        ->assertSee('All')
        ->assertSee('Unread')
        ->assertSee('Guest')
        ->assertSee('Registered guest question')
        ->assertSee('Anonymous Visitor');

    $this->actingAs($owner)
        ->get(route('owner.conversations.index', ['filter' => 'unread']))
        ->assertSee('Registered guest question')
        ->assertSee('Anonymous Visitor');

    $this->actingAs($owner)
        ->get(route('owner.conversations.index', ['filter' => 'guest']))
        ->assertSee('Anonymous Visitor')
        ->assertDontSee('Registered guest question');
});

test('owners can start conversations only with registered guests booked at their properties', function () {
    Mail::fake();
    $owner = conversationUser('Owner', 'owner-new-chat@example.com');
    $otherOwner = conversationUser('Owner', 'other-owner-new-chat@example.com');
    $guest = conversationUser('Guest', 'booked-new-chat@example.com');
    $unbookedGuest = conversationUser('Guest', 'unbooked-new-chat@example.com');
    $otherPropertyGuest = conversationUser('Guest', 'other-property-new-chat@example.com');
    $house = conversationHouse($owner, 'New Chat House');
    $otherHouse = conversationHouse($otherOwner, 'Other New Chat House');
    $unit = Unit::create([
        'house_id' => $house->house_id,
        'unit_type' => 'New Chat Suite',
        'capacity' => 2,
        'maximum_capacity' => 2,
        'price' => 1800,
        'status' => 'Available',
    ]);
    $otherUnit = Unit::create([
        'house_id' => $otherHouse->house_id,
        'unit_type' => 'Other New Chat Suite',
        'capacity' => 2,
        'maximum_capacity' => 2,
        'price' => 1800,
        'status' => 'Available',
    ]);

    foreach ([[$guest, $unit], [$otherPropertyGuest, $otherUnit]] as [$bookedGuest, $bookedUnit]) {
        Booking::create([
            'user_id' => $bookedGuest->user_id,
            'unit_id' => $bookedUnit->unit_id,
            'check_in' => today()->addDays(3),
            'check_out' => today()->addDays(5),
            'adult_count' => 1,
            'child_count' => 0,
            'total_price' => 3600,
            'status' => BookingStatus::ACCEPTED,
        ]);
    }

    $this->actingAs($owner)
        ->get(route('owner.conversations.create', ['house_id' => $house->house_id]))
        ->assertOk()
        ->assertSee($guest->email)
        ->assertDontSee($unbookedGuest->email)
        ->assertDontSee($otherPropertyGuest->email);

    $this->actingAs($owner)
        ->post(route('owner.conversations.create.store'), [
            'house_id' => $house->house_id,
            'guest_user_id' => $guest->user_id,
            'subject' => 'Check-in details',
            'message' => 'Please confirm your expected arrival time.',
        ])
        ->assertRedirect();

    $conversation = Conversation::query()->firstOrFail();
    expect($conversation->guest_user_id)->toBe($guest->user_id)
        ->and($conversation->house_id)->toBe($house->house_id)
        ->and($conversation->subject)->toBe('Check-in details')
        ->and($conversation->messages()->where('sender_type', 'user')->value('message'))
        ->toBe('Please confirm your expected arrival time.');

    $this->actingAs($owner)
        ->post(route('owner.conversations.create.store'), [
            'house_id' => $house->house_id,
            'guest_user_id' => $unbookedGuest->user_id,
            'message' => 'This guest has no booking here.',
        ])
        ->assertNotFound();

    $this->actingAs($owner)
        ->post(route('owner.conversations.create.store'), [
            'house_id' => $otherHouse->house_id,
            'guest_user_id' => $otherPropertyGuest->user_id,
            'message' => 'This property is not authorized.',
        ])
        ->assertNotFound();
});

test('staff can start a conversation with a guest booked at an assigned property', function () {
    Mail::fake();
    $owner = conversationUser('Owner', 'owner-staff-new-chat@example.com');
    $staff = conversationUser('Staff', 'staff-new-chat@example.com');
    $guest = conversationUser('Guest', 'guest-staff-new-chat@example.com');
    $house = conversationHouse($owner, 'Staff New Chat House');
    $unit = Unit::create([
        'house_id' => $house->house_id,
        'unit_type' => 'Staff New Chat Suite',
        'capacity' => 2,
        'maximum_capacity' => 2,
        'price' => 1800,
        'status' => 'Available',
    ]);
    $owner->invitationsSent()->create([
        'house_id' => $house->house_id,
        'role_id' => Role::query()->where('name', 'Staff')->firstOrFail()->role_id,
        'email' => $staff->email,
        'token' => 'staff-new-chat-token',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);
    Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->addDays(3),
        'check_out' => today()->addDays(5),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::ACCEPTED,
    ]);

    $this->actingAs($staff)
        ->post(route('staff.conversations.create.store'), [
            'house_id' => $house->house_id,
            'guest_user_id' => $guest->user_id,
            'message' => 'We are confirming your check-in time.',
        ])
        ->assertRedirect();

    $conversation = Conversation::query()->where('guest_user_id', $guest->user_id)->firstOrFail();
    $this->actingAs($staff)
        ->get(route('staff.conversations.show', $conversation->conversation_id))
        ->assertOk()
        ->assertSee('Accepted')
        ->assertSee('Guest status');
});

test('conversation details show booking status and identify public visitors', function () {
    Mail::fake();
    $owner = conversationUser('Owner', 'owner-guest-status@example.com');
    $guest = conversationUser('Guest', 'guest-status@example.com');
    $house = conversationHouse($owner, 'Guest Status House');
    $unit = Unit::create([
        'house_id' => $house->house_id,
        'unit_type' => 'Guest Status Suite',
        'capacity' => 2,
        'maximum_capacity' => 2,
        'price' => 1800,
        'status' => 'Available',
    ]);
    Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->addDays(3),
        'check_out' => today()->addDays(5),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::ACCEPTED,
    ]);
    $registeredConversation = app(ConversationService::class)->createForGuest($guest, $house->house_id);

    $this->post(route('public.visitor-conversations.store'), [
        'house_id' => $house->house_id,
        'name' => 'Public Visitor',
        'email' => 'public-visitor-status@example.com',
        'message' => 'Can the team help with a question?',
    ])->assertRedirect();
    $visitorConversation = Conversation::query()
        ->where('visitor_email', 'public-visitor-status@example.com')
        ->firstOrFail();

    $this->actingAs($owner)
        ->get(route('owner.conversations.show', $registeredConversation->conversation_id))
        ->assertOk()
        ->assertSee('Guest status')
        ->assertSee('Accepted');

    $this->actingAs($owner)
        ->get(route('owner.conversations.show', $visitorConversation->conversation_id))
        ->assertOk()
        ->assertSee('Guest status')
        ->assertSee('Public visitor');
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

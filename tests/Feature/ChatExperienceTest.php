<?php

use App\Models\ChatFaq;
use App\Models\Conversation;
use App\Models\Notification;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function chatExperienceUser(string $role, string $email): User
{
    return User::create([
        'role_id' => Role::query()->where('name', $role)->firstOrFail()->role_id,
        'first_name' => ucfirst($role),
        'last_name' => 'Chat',
        'email' => $email,
        'password' => 'Password123!',
        'is_active' => true,
    ]);
}

function chatExperienceHouse(User $owner, string $name): TransientHouse
{
    return TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => $name,
        'address' => '1 Chat Street',
        'city' => 'Baguio',
        'contact_number' => '5557000',
        'is_active' => true,
    ]);
}

function chatExperienceUnit(TransientHouse $house, string $unitType): Unit
{
    return Unit::create([
        'house_id' => $house->house_id,
        'unit_type' => $unitType,
        'capacity' => 2,
        'price' => 1800,
        'status' => 'Available',
    ]);
}

function chatExperienceFaq(TransientHouse $house, Unit $unit, string $answer): ChatFaq
{
    return ChatFaq::query()->create([
        'house_id' => $house->house_id,
        'unit_id' => $unit->unit_id,
        'category' => 'Amenities',
        'question' => 'Does this room include breakfast?',
        'answer' => $answer,
        'keywords' => 'breakfast morning',
        'is_active' => true,
    ]);
}

test('public unit pages show only FAQs for that unit and answer matching questions', function () {
    $owner = chatExperienceUser('Owner', 'faq-owner@example.com');
    $house = chatExperienceHouse($owner, 'FAQ House');
    $unitA = chatExperienceUnit($house, 'Suite A');
    $unitB = chatExperienceUnit($house, 'Suite B');
    chatExperienceFaq($house, $unitA, 'Breakfast is included in Suite A.');
    chatExperienceFaq($house, $unitB, 'Breakfast is not included in Suite B.');

    $this->get(route('public.unit', $unitA))
        ->assertOk()
        ->assertSee('guest-chat-launcher')
        ->assertSee('public-chat-card', false)
        ->assertSee('public-chat-message--assistant', false)
        ->assertSee('Quick questions')
        ->assertSee('What is the price per night?')
        ->assertSee('Breakfast is included in Suite A.')
        ->assertDontSee('Breakfast is not included in Suite B.');

    $this->from(route('public.unit', $unitA))
        ->post(route('public.units.questions.store', $unitA), [
            'question' => 'What is the price per night?',
        ])
        ->assertRedirect(route('public.unit', $unitA))
        ->assertSessionHas('unitFaqAnswer.answer', 'The current listed rate for Suite A is ₱1,800.00 per night.');

    $this->from(route('public.unit', $unitA))
        ->post(route('public.units.questions.store', $unitA), [
            'question' => 'Does this room include breakfast?',
        ])
        ->assertRedirect(route('public.unit', $unitA))
        ->assertSessionHas('unitFaqAnswer.answer', 'Breakfast is included in Suite A.');

    expect(Conversation::query()->count())->toBe(0);
});

test('guest unit questions keep unit context and automatic FAQs out of staff attention', function () {
    Mail::fake();
    $owner = chatExperienceUser('Owner', 'chat-faq-owner@example.com');
    $guest = chatExperienceUser('Guest', 'chat-faq-guest@example.com');
    $house = chatExperienceHouse($owner, 'Unit Chat House');
    $unit = chatExperienceUnit($house, 'Garden Suite');
    chatExperienceFaq($house, $unit, 'Breakfast is included.');

    $this->actingAs($guest)
        ->post(route('guest.conversations.store'), [
            'house_id' => $house->house_id,
            'unit_id' => $unit->unit_id,
            'subject' => 'Breakfast question',
            'message' => 'Does this room include breakfast?',
        ])
        ->assertRedirect();

    $conversation = Conversation::query()->firstOrFail();
    expect($conversation->unit_id)->toBe($unit->unit_id)
        ->and($conversation->status)->toBe('open')
        ->and($conversation->messages()->where('sender_type', 'assistant')->value('message'))->toBe('Breakfast is included.')
        ->and(Notification::query()->where('user_id', $owner->user_id)->count())->toBe(0);

    $this->actingAs($owner)
        ->get(route('owner.conversations.index'))
        ->assertOk()
        ->assertSee('Breakfast question')
        ->assertDontSee('title="1 unread conversations"', false);

    $this->actingAs($owner)
        ->get(route('owner.conversations.index', ['booking_status' => 'none']))
        ->assertSee('Breakfast question')
        ->assertDontSee('1 unread');

    $this->actingAs($guest)
        ->get(route('guest.conversations.index'))
        ->assertOk()
        ->assertSee('chat-workspace--guest', false)
        ->assertSee('guest-chat-launcher')
        ->assertSee('Breakfast question')
        ->assertDontSee('Messages</a>');
});

test('guest and staff see the conversation as a message thread beside their chat list', function () {
    Mail::fake();
    $owner = chatExperienceUser('Owner', 'thread-owner@example.com');
    $staff = chatExperienceUser('Staff', 'thread-staff@example.com');
    $guest = chatExperienceUser('Guest', 'thread-guest@example.com');
    $house = chatExperienceHouse($owner, 'Thread House');
    $owner->invitationsSent()->create([
        'house_id' => $house->house_id,
        'role_id' => Role::query()->where('name', 'Staff')->firstOrFail()->role_id,
        'email' => $staff->email,
        'token' => 'thread-staff-token',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);

    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id, 'Check-in question');
    app(ConversationService::class)->sendMessage($guest, $conversation, 'What time can I check in?');
    app(ConversationService::class)->sendMessage($staff, $conversation, 'Check-in starts at 3 PM.');

    $this->actingAs($guest)
        ->get(route('guest.conversations.show', $conversation->conversation_id))
        ->assertSee('chat-workspace--thread', false)
        ->assertSee('chat-list-item', false)
        ->assertSee('chat-message-row--outgoing', false)
        ->assertSee('chat-message-row--incoming', false)
        ->assertSee('What time can I check in?')
        ->assertSee('Check-in starts at 3 PM.');

    $this->actingAs($staff)
        ->get(route('staff.conversations.show', $conversation->conversation_id))
        ->assertSee('chat-workspace--staff chat-workspace--thread', false)
        ->assertSee('chat-details', false)
        ->assertSee('chat-message-row--incoming', false)
        ->assertSee('chat-message-row--outgoing', false)
        ->assertSee('Booking context')
        ->assertSee('Check-in starts at 3 PM.');
});

test('unit questions without a matched FAQ are not saved until a guest signs in', function () {
    $owner = chatExperienceUser('Owner', 'unanswered-owner@example.com');
    $house = chatExperienceHouse($owner, 'Unanswered House');
    $unit = chatExperienceUnit($house, 'Quiet Room');

    $this->from(route('public.unit', $unit))
        ->post(route('public.units.questions.store', $unit), [
            'question' => 'Can I bring a pet?',
        ])
        ->assertRedirect(route('public.unit', $unit))
        ->assertSessionHas('unitFaqUnanswered', true)
        ->assertSessionHas('url.intended');

    $this->get(route('public.unit', $unit))
        ->assertSee('Sign in as a guest')
        ->assertSee('Can I bring a pet?');

    expect(Conversation::query()->count())->toBe(0);
});

test('guest conversation creation rejects a unit from a different property', function () {
    $owner = chatExperienceUser('Owner', 'unit-scope-owner@example.com');
    $guest = chatExperienceUser('Guest', 'unit-scope-guest@example.com');
    $otherOwner = chatExperienceUser('Owner', 'unit-scope-other@example.com');
    $house = chatExperienceHouse($owner, 'Selected House');
    $otherHouse = chatExperienceHouse($otherOwner, 'Different House');
    $otherUnit = chatExperienceUnit($otherHouse, 'Private Suite');

    $this->actingAs($guest)
        ->post(route('guest.conversations.store'), [
            'house_id' => $house->house_id,
            'unit_id' => $otherUnit->unit_id,
            'message' => 'I have a question about this unit.',
        ])
        ->assertUnprocessable();

    expect(Conversation::query()->count())->toBe(0);
});

test('assistance FAQs can only target units owned by the selected property', function () {
    $owner = chatExperienceUser('Owner', 'faq-scope-owner@example.com');
    $otherOwner = chatExperienceUser('Owner', 'faq-scope-other@example.com');
    $house = chatExperienceHouse($owner, 'FAQ Scope House');
    $otherHouse = chatExperienceHouse($otherOwner, 'Other FAQ House');
    $unit = chatExperienceUnit($house, 'Allowed Suite');
    $otherUnit = chatExperienceUnit($otherHouse, 'Private Suite');
    $faqData = [
        'house_id' => $house->house_id,
        'category' => 'Amenities',
        'question' => 'Is breakfast included?',
        'answer' => 'Breakfast is included.',
    ];

    $this->actingAs($owner)
        ->post(route('owner.assistance.faqs.store'), $faqData + ['unit_id' => $otherUnit->unit_id])
        ->assertUnprocessable();

    $this->actingAs($owner)
        ->post(route('owner.assistance.faqs.store'), $faqData + ['unit_id' => $unit->unit_id])
        ->assertRedirect();

    expect(ChatFaq::query()->where('house_id', $house->house_id)->value('unit_id'))->toBe($unit->unit_id);
});

test('staff inbox navigation shows unread conversations only within authorized properties', function () {
    Mail::fake();
    $owner = chatExperienceUser('Owner', 'chat-badge-owner@example.com');
    $staff = chatExperienceUser('Staff', 'chat-badge-staff@example.com');
    $guest = chatExperienceUser('Guest', 'chat-badge-guest@example.com');
    $house = chatExperienceHouse($owner, 'Staff Badge House');
    $owner->invitationsSent()->create([
        'house_id' => $house->house_id,
        'role_id' => Role::query()->where('name', 'Staff')->firstOrFail()->role_id,
        'email' => $staff->email,
        'token' => 'chat-badge-staff-token',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);
    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id, 'Needs staff');
    app(ConversationService::class)->sendMessage($guest, $conversation, 'I need help with a late check-in.');

    $this->actingAs($staff)
        ->get(route('staff.conversations.index'))
        ->assertOk()
        ->assertSee('chat-workspace--staff', false)
        ->assertSee('title="1 unread conversations"', false)
        ->assertSee('Needs staff');

    $this->actingAs($staff)
        ->get(route('staff.conversations.show', $conversation->conversation_id))
        ->assertOk();

    $this->actingAs($staff)
        ->get(route('staff.conversations.index'))
        ->assertDontSee('title="1 unread conversations"', false);
});

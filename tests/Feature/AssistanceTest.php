<?php

use App\Models\AssistanceSetting;
use App\Models\ChatFaq;
use App\Models\ChatQuickReply;
use App\Models\ConversationNote;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function assistanceUser(string $role, string $email): User
{
    return User::create([
        'role_id' => Role::where('name', $role)->firstOrFail()->role_id,
        'first_name' => ucfirst($role),
        'last_name' => 'Assistance',
        'email' => $email,
        'password' => 'Password123!',
        'is_active' => true,
    ]);
}

function assistanceHouse(User $owner): TransientHouse
{
    return TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Assistance House',
        'address' => '1 Assistance Road',
        'city' => 'Baguio',
        'contact_number' => '5551234',
        'is_active' => true,
    ]);
}

test('a guest receives an approved FAQ response without staff impersonation', function () {
    $owner = assistanceUser('Owner', 'faq-owner@example.com');
    $guest = assistanceUser('Guest', 'faq-guest@example.com');
    $house = assistanceHouse($owner);
    ChatFaq::create([
        'house_id' => $house->house_id,
        'created_by' => $owner->user_id,
        'category' => 'Payments',
        'question' => 'How do I submit payment?',
        'answer' => 'Open your booking and choose Submit Payment.',
        'keywords' => 'payment, receipt',
        'is_active' => true,
    ]);
    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id);

    app(ConversationService::class)->sendMessage($guest, $conversation, 'How do I submit payment?');

    $assistantMessage = $conversation->messages()->where('message_type', 'assistant')->first();
    expect($assistantMessage)->not->toBeNull()
        ->and($assistantMessage->sender_id)->toBeNull()
        ->and($assistantMessage->sender_type)->toBe('assistant')
        ->and($assistantMessage->message)->toContain('Submit Payment')
        ->and($conversation->fresh()->status)->toBe('resolved')
        ->and($conversation->fresh()->last_message_at)->toEqual($assistantMessage->sent_at);
});

test('an unsupported guest question creates a human handoff message', function () {
    $owner = assistanceUser('Owner', 'handoff-owner@example.com');
    $guest = assistanceUser('Guest', 'handoff-guest@example.com');
    $house = assistanceHouse($owner);
    AssistanceSetting::create([
        'house_id' => $house->house_id,
        'welcome_message' => 'Welcome.',
        'handoff_message' => 'A team member will review this shortly.',
    ]);
    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id);

    app(ConversationService::class)->sendMessage($guest, $conversation, 'Can you make a special exception?');

    $systemMessage = $conversation->messages()->where('message_type', 'system')->first();
    expect($systemMessage->sender_id)->toBeNull()
        ->and($systemMessage->message)->toBe('A team member will review this shortly.')
        ->and($conversation->fresh()->status)->toBe('pending_staff');
});

test('owners can manage assistance content only for their own property', function () {
    $owner = assistanceUser('Owner', 'manage-owner@example.com');
    $otherOwner = assistanceUser('Owner', 'manage-other-owner@example.com');
    $house = assistanceHouse($owner);

    $this->actingAs($owner)
        ->post(route('owner.assistance.faqs.store'), [
            'house_id' => $house->house_id,
            'category' => 'General',
            'question' => 'What time is check-in?',
            'answer' => 'Check-in starts at 2 PM.',
        ])
        ->assertRedirect();

    $faq = ChatFaq::query()->firstOrFail();
    expect($faq->house_id)->toBe($house->house_id);

    $this->actingAs($owner)
        ->post(route('owner.assistance.settings.update'), [
            'house_id' => $house->house_id,
            'welcome_message' => 'Welcome to our property.',
            'handoff_message' => 'Our team will respond soon.',
        ])
        ->assertRedirect();

    expect(AssistanceSetting::query()->where('house_id', $house->house_id)->value('welcome_message'))
        ->toBe('Welcome to our property.');

    $this->actingAs($owner)
        ->post(route('owner.assistance.quick-replies.store'), [
            'house_id' => $house->house_id,
            'title' => 'Payment instructions',
            'content' => 'Please open your booking payment page.',
        ])
        ->assertRedirect();

    expect(ChatQuickReply::query()->where('house_id', $house->house_id)->count())->toBe(1);

    $this->actingAs($otherOwner)
        ->put(route('owner.assistance.faqs.update', $faq), [
            'category' => 'General',
            'question' => 'Changed question',
            'answer' => 'Changed answer',
        ])
        ->assertForbidden();
});

test('private notes are available to staff but not guests', function () {
    $owner = assistanceUser('Owner', 'note-owner@example.com');
    $guest = assistanceUser('Guest', 'note-guest@example.com');
    $house = assistanceHouse($owner);
    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id);

    $this->actingAs($owner)
        ->post(route('owner.conversations.notes.store', $conversation->conversation_id), [
            'note' => 'Guest needs a quiet room.',
        ])
        ->assertRedirect();

    expect(ConversationNote::query()->where('conversation_id', $conversation->conversation_id)->exists())->toBeTrue();

    $this->actingAs($guest)
        ->get(route('guest.conversations.show', $conversation->conversation_id))
        ->assertOk()
        ->assertDontSee('Guest needs a quiet room.');

    $this->actingAs($owner)
        ->get(route('owner.conversations.show', $conversation->conversation_id))
        ->assertOk()
        ->assertSee('Guest needs a quiet room.');
});

test('inactive quick replies are not offered in a conversation composer', function () {
    $owner = assistanceUser('Owner', 'reply-owner@example.com');
    $guest = assistanceUser('Guest', 'reply-guest@example.com');
    $house = assistanceHouse($owner);
    ChatQuickReply::create([
        'house_id' => $house->house_id,
        'created_by' => $owner->user_id,
        'title' => 'Active reply',
        'content' => 'Active content',
        'is_active' => true,
    ]);
    ChatQuickReply::create([
        'house_id' => $house->house_id,
        'created_by' => $owner->user_id,
        'title' => 'Inactive reply',
        'content' => 'Inactive content',
        'is_active' => false,
    ]);
    $conversation = app(ConversationService::class)->createForGuest($guest, $house->house_id);

    $this->actingAs($owner)
        ->get(route('owner.conversations.show', $conversation->conversation_id))
        ->assertOk()
        ->assertSee('Active reply')
        ->assertDontSee('Inactive reply');
});
test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

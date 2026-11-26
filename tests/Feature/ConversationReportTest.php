<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\User;
use App\Services\ConversationReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('conversation analytics are property scoped and measure assistance outcomes', function () {
    $owner = reportUser('Owner', 'report-owner@example.com');
    $guest = reportUser('Guest', 'report-guest@example.com');
    $otherOwner = reportUser('Owner', 'other-report-owner@example.com');
    $otherGuest = reportUser('Guest', 'other-report-guest@example.com');
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Analytics House',
        'address' => '1 Analytics Road',
        'city' => 'Baguio',
        'contact_number' => '5551000',
        'is_active' => true,
    ]);
    $otherHouse = TransientHouse::create([
        'owner_id' => $otherOwner->user_id,
        'name' => 'Other Analytics House',
        'address' => '2 Analytics Road',
        'city' => 'Baguio',
        'contact_number' => '5552000',
        'is_active' => true,
    ]);
    $startedAt = Carbon::create(2026, 11, 24, 10, 0);
    $conversation = Conversation::create([
        'house_id' => $house->house_id,
        'guest_user_id' => $guest->user_id,
        'status' => 'resolved',
    ]);
    $conversation->forceFill([
        'created_at' => $startedAt,
        'updated_at' => $startedAt->copy()->addMinutes(15),
    ])->save();
    Message::create([
        'conversation_id' => $conversation->conversation_id,
        'sender_id' => $guest->user_id,
        'sender_type' => 'user',
        'message' => 'Hello',
        'message_type' => 'user',
        'sent_at' => $startedAt,
    ]);
    Message::create([
        'conversation_id' => $conversation->conversation_id,
        'sender_id' => $owner->user_id,
        'sender_type' => 'user',
        'message' => 'How can we help?',
        'message_type' => 'user',
        'sent_at' => $startedAt->copy()->addMinutes(10),
    ]);
    Message::create([
        'conversation_id' => $conversation->conversation_id,
        'sender_type' => 'assistant',
        'message' => 'FAQ answer',
        'message_type' => 'assistant',
        'sent_at' => $startedAt->copy()->addMinutes(11),
    ]);
    Message::create([
        'conversation_id' => $conversation->conversation_id,
        'sender_type' => 'system',
        'message' => 'Handoff',
        'message_type' => 'system',
        'sent_at' => $startedAt->copy()->addMinutes(12),
    ]);
    $otherConversation = Conversation::create([
        'house_id' => $otherHouse->house_id,
        'guest_user_id' => $otherGuest->user_id,
        'status' => 'open',
    ]);
    $otherConversation->forceFill([
        'created_at' => $startedAt,
        'updated_at' => $startedAt,
    ])->save();

    $metrics = app(ConversationReportService::class)->generate(
        collect([$house->house_id]),
        [
            'house_id' => null,
            'from_date' => '2026-11-24',
            'to_date' => '2026-11-24',
        ]
    );

    expect($metrics['conversationCount'])->toBe(1)
        ->and($metrics['resolvedCount'])->toBe(1)
        ->and($metrics['handoffCount'])->toBe(1)
        ->and($metrics['faqResponseCount'])->toBe(1)
        ->and($metrics['respondedConversationCount'])->toBe(1)
        ->and($metrics['averageFirstResponseMinutes'])->toBe(10.0)
        ->and($metrics['resolutionRate'])->toBe(100.0)
        ->and($otherConversation->exists)->toBeTrue();
});

function reportUser(string $role, string $email): User
{
    return User::create([
        'role_id' => Role::query()->where('name', $role)->firstOrFail()->role_id,
        'first_name' => $role,
        'last_name' => 'Report',
        'email' => $email,
        'password' => 'Password123!',
        'is_active' => true,
    ]);
}

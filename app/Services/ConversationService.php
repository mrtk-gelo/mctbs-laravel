<?php

namespace App\Services;

use App\Mail\ConversationMessage as ConversationMessageMail;
use App\Models\Conversation;
use App\Models\Invitation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ConversationService
{
    public function __construct(private BookingContextService $bookingContext) {}

    public function accessibleHouseIds(User $user): Collection
    {
        if ($user->role?->name === 'Owner') {
            return $user->transientHouses()->pluck('house_id');
        }

        if ($user->role?->name !== 'Staff') {
            return collect();
        }

        $staffRoleId = Role::query()->where('name', 'Staff')->value('role_id');

        return Invitation::query()
            ->where('email', $user->email)
            ->where('role_id', $staffRoleId)
            ->whereNotNull('used_at')
            ->whereNotNull('house_id')
            ->pluck('house_id')
            ->unique()
            ->values();
    }

    public function canAccess(User $user, Conversation $conversation): bool
    {
        if ($user->role?->name === 'Guest') {
            return $conversation->guest_user_id === $user->user_id;
        }

        return in_array($conversation->house_id, $this->accessibleHouseIds($user)->all(), true);
    }

    public function conversationQueryFor(User $user): Builder
    {
        $query = $user->role?->name === 'Guest'
            ? Conversation::query()->where('guest_user_id', $user->user_id)
            : Conversation::query()->whereIn('house_id', $this->accessibleHouseIds($user));

        return $query->withCount([
            'messages as unread_message_count' => function (Builder $messageQuery) use ($user): void {
                if ($user->role?->name !== 'Guest') {
                    $messageQuery->whereIn('conversations.status', ['open', 'pending_staff', 'assigned']);
                }

                $messageQuery
                    ->whereNotIn('sender_type', ['assistant', 'system'])
                    ->where(function (Builder $query) use ($user): void {
                        $query->whereNull('sender_id')
                            ->orWhere('sender_id', '!=', $user->user_id);
                    })
                    ->whereRaw(
                        'messages.message_id > COALESCE((SELECT last_read_message_id FROM conversation_participants WHERE conversation_participants.conversation_id = messages.conversation_id AND conversation_participants.user_id = ?), 0)',
                        [$user->user_id]
                    );
            },
        ]);
    }

    public function markRead(User $reader, Conversation $conversation): void
    {
        abort_unless($this->canAccess($reader, $conversation), 403);

        $participant = $conversation->participants()->firstOrCreate([
            'user_id' => $reader->user_id,
        ]);
        $latestMessageId = $conversation->messages()->max('message_id');

        if ($latestMessageId !== null && $participant->last_read_message_id !== (int) $latestMessageId) {
            $participant->update(['last_read_message_id' => $latestMessageId]);
        }
    }

    public function unreadConversationCountFor(User $user): int
    {
        $query = $user->role?->name === 'Guest'
            ? Conversation::query()->where('guest_user_id', $user->user_id)
            : Conversation::query()
                ->whereIn('house_id', $this->accessibleHouseIds($user))
                ->whereIn('status', ['open', 'pending_staff', 'assigned']);

        return $query
            ->whereHas('messages', function (Builder $messageQuery) use ($user): void {
                $messageQuery
                    ->whereNotIn('sender_type', ['assistant', 'system'])
                    ->where(function (Builder $query) use ($user): void {
                        $query->whereNull('sender_id')
                            ->orWhere('sender_id', '!=', $user->user_id);
                    })
                    ->whereRaw(
                        'messages.message_id > COALESCE((SELECT last_read_message_id FROM conversation_participants WHERE conversation_participants.conversation_id = messages.conversation_id AND conversation_participants.user_id = ?), 0)',
                        [$user->user_id]
                    );
            })
            ->count();
    }

    public function createForGuest(User $guest, int $houseId, ?string $subject = null, ?int $unitId = null): Conversation
    {
        if ($unitId !== null) {
            abort_unless(
                Unit::query()
                    ->where('house_id', $houseId)
                    ->where('unit_id', $unitId)
                    ->where('status', 'Available')
                    ->whereNull('archived_at')
                    ->exists(),
                422,
                'The selected unit is not available for questions.'
            );
        }

        $conversation = Conversation::query()->create([
            'house_id' => $houseId,
            'unit_id' => $unitId,
            'guest_user_id' => $guest->user_id,
            'status' => 'open',
            'subject' => $subject,
        ]);

        $conversation->participants()->create([
            'user_id' => $guest->user_id,
        ]);

        return $conversation;
    }

    public function sendMessage(User $sender, Conversation $conversation, string $body, ?string $clientToken = null): Message
    {
        abort_unless($this->canAccess($sender, $conversation), 403);

        $body = trim($body);
        if ($body === '') {
            throw ValidationException::withMessages([
                'message' => 'Enter a message before sending.',
            ]);
        }

        return DB::transaction(function () use ($sender, $conversation, $body, $clientToken): Message {
            $message = $clientToken === null
                ? $conversation->messages()->create([
                    'sender_id' => $sender->user_id,
                    'message' => $body,
                    'message_type' => 'user',
                    'sent_at' => now(),
                ])
                : $conversation->messages()->firstOrCreate(
                    ['client_token' => $clientToken],
                    [
                        'sender_id' => $sender->user_id,
                        'message' => $body,
                        'message_type' => 'user',
                        'sent_at' => now(),
                    ]
                );

            if ($message->wasRecentlyCreated) {
                $conversation->forceFill([
                    'last_message_at' => $message->sent_at,
                    'status' => $sender->role?->name === 'Guest' ? 'open' : 'assigned',
                    'assigned_to' => $sender->role?->name === 'Guest'
                        ? $conversation->assigned_to
                        : $sender->user_id,
                    'resolved_at' => null,
                ])->save();

                $this->addParticipant($conversation, $sender);

                if ($sender->role?->name === 'Guest') {
                    $assistance = app(AssistanceService::class);
                    $faq = $assistance->findFaq($conversation->house_id, $body, $conversation->unit_id);

                    if ($faq) {
                        $assistance->addFaqResponse($conversation, $faq);
                    } elseif ($contextualAnswer = $this->bookingContext->answerGuestQuestion($conversation, $body)) {
                        $assistantMessage = $conversation->messages()->create([
                            'message' => $contextualAnswer,
                            'message_type' => 'context',
                            'sender_type' => 'assistant',
                            'sent_at' => now(),
                        ]);
                        $conversation->update([
                            'last_message_at' => $assistantMessage->sent_at,
                            'status' => 'resolved',
                            'resolved_at' => now(),
                        ]);
                    } else {
                        $assistance->handoff($conversation);
                        $this->notifyRecipients($sender, $conversation, $message);
                    }
                } else {
                    $this->notifyRecipients($sender, $conversation, $message);
                }
            }

            return $message;
        });
    }

    public function assign(User $actor, Conversation $conversation, User $assignee): void
    {
        abort_unless($this->canAccess($actor, $conversation), 403);
        abort_unless($this->canAccess($assignee, $conversation), 422, 'The selected user cannot access this property.');
        abort_unless(in_array($assignee->role?->name, ['Owner', 'Staff'], true), 422);

        $conversation->update([
            'assigned_to' => $assignee->user_id,
            'status' => 'assigned',
            'resolved_at' => null,
        ]);
        $this->addParticipant($conversation, $assignee);
    }

    public function resolve(User $actor, Conversation $conversation): void
    {
        abort_unless($this->canAccess($actor, $conversation), 403);

        $conversation->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function reopen(User $actor, Conversation $conversation): void
    {
        abort_unless($this->canAccess($actor, $conversation), 403);

        $conversation->update([
            'status' => 'open',
            'resolved_at' => null,
        ]);
    }

    private function addParticipant(Conversation $conversation, User $user): void
    {
        $conversation->participants()->firstOrCreate([
            'user_id' => $user->user_id,
        ]);
    }

    private function notifyRecipients(User $sender, Conversation $conversation, Message $message): void
    {
        $recipientIds = $sender->role?->name === 'Guest'
            ? $this->staffRecipients($conversation)
                ->push($conversation->house()->value('owner_id'))
            : collect([$conversation->guest_user_id]);

        $recipientIds
            ->filter(fn (?int $userId): bool => $userId !== null && $userId !== $sender->user_id)
            ->unique()
            ->each(function (int $userId) use ($conversation, $message): void {
                $recipient = User::query()->find($userId);
                if (! $recipient || ! $recipient->email) {
                    return;
                }

                Notification::query()->create([
                    'user_id' => $userId,
                    'conversation_id' => $conversation->conversation_id,
                    'type' => 'conversation_message',
                    'title' => 'New conversation message',
                    'message' => 'You have a new message in MCTBS Connect.',
                ]);
                Mail::to($recipient->email)->send(new ConversationMessageMail(
                    $conversation->loadMissing('house'),
                    $message
                ));
            });
    }

    /**
     * @return Collection<int, int>
     */
    private function staffRecipients(Conversation $conversation): Collection
    {
        $staffRoleId = Role::query()->where('name', 'Staff')->value('role_id');
        $staffEmails = Invitation::query()
            ->where('house_id', $conversation->house_id)
            ->where('role_id', $staffRoleId)
            ->whereNotNull('used_at')
            ->pluck('email');

        return User::query()
            ->whereIn('email', $staffEmails)
            ->where('is_active', true)
            ->pluck('user_id');
    }
}

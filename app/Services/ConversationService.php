<?php

namespace App\Services;

use App\Mail\ConversationMessage as ConversationMessageMail;
use App\Mail\PublicVisitorConversationNotice;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Invitation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ConversationService
{
    public const REACTION_EMOJIS = ['👍', '❤️', '😂', '😮', '😢', '🙏'];

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
        $hasConversationAccess = $user->role?->name === 'Guest'
            ? $conversation->guest_user_id === $user->user_id
            : in_array($conversation->house_id, $this->accessibleHouseIds($user)->all(), true);

        if (! $hasConversationAccess) {
            return false;
        }

        return ! $conversation->participants()
            ->where('user_id', $user->user_id)
            ->whereNotNull('deleted_at')
            ->exists();
    }

    public function visitorSessionHash(Request $request): string
    {
        $sessionKey = $request->session()->get('mctbs_visitor_chat_key');

        if (! is_string($sessionKey) || $sessionKey === '') {
            $sessionKey = Str::random(64);
            $request->session()->put('mctbs_visitor_chat_key', $sessionKey);
        }

        return hash('sha256', $sessionKey);
    }

    public function visitorConversationsFor(Request $request, string $folder = 'inbox'): EloquentCollection
    {
        if (! in_array($folder, ['all', 'inbox', 'archived'], true)) {
            throw new \InvalidArgumentException('Unsupported conversation folder.');
        }

        $sessionKey = $request->session()->get('mctbs_visitor_chat_key');
        $sessionHash = is_string($sessionKey) && $sessionKey !== ''
            ? hash('sha256', $sessionKey)
            : null;
        $grantedConversationIds = $this->visitorConversationIdsFromSession($request);

        if ($sessionHash === null && $grantedConversationIds === []) {
            return new EloquentCollection;
        }

        $query = Conversation::query()
            ->whereNull('guest_user_id')
            ->where(function (Builder $query) use ($sessionHash, $grantedConversationIds): void {
                if ($sessionHash !== null) {
                    $query->where('visitor_session_hash', $sessionHash);
                }

                if ($grantedConversationIds !== []) {
                    $query->orWhereIn('conversation_id', $grantedConversationIds);
                }
            });

        $query->whereNull('visitor_deleted_at');

        if ($folder === 'archived') {
            $query->whereNotNull('visitor_archived_at');
        } elseif ($folder === 'inbox') {
            $query->whereNull('visitor_archived_at');
        }

        return $query
            ->with(['house', 'unit', 'latestMessage'])
            ->latest('last_message_at')
            ->latest('created_at')
            ->get();
    }

    public function visitorFolderForConversation(Conversation $conversation): string
    {
        return $conversation->visitor_archived_at !== null ? 'archived' : 'inbox';
    }

    public function canAccessVisitor(Request $request, Conversation $conversation): bool
    {
        if ($conversation->guest_user_id !== null) {
            return false;
        }

        if ($conversation->visitor_deleted_at !== null) {
            return false;
        }

        $sessionKey = $request->session()->get('mctbs_visitor_chat_key');
        if (
            is_string($sessionKey)
            && $sessionKey !== ''
            && $conversation->visitor_session_hash !== null
            && hash_equals($conversation->visitor_session_hash, hash('sha256', $sessionKey))
        ) {
            return true;
        }

        return in_array(
            $conversation->conversation_id,
            $this->visitorConversationIdsFromSession($request),
            true
        );
    }

    public function grantVisitorConversationAccess(
        Request $request,
        Conversation $conversation,
        bool $verifiedEmail = false
    ): void {
        abort_if($conversation->visitor_deleted_at !== null, 404);

        $conversationIds = $this->visitorConversationIdsFromSession($request);
        $conversationIds[] = $conversation->conversation_id;
        $request->session()->put('mctbs_visitor_chat_access', array_values(array_unique($conversationIds)));

        if ($verifiedEmail) {
            $verifiedIds = $request->session()->get('mctbs_visitor_chat_verified', []);
            $verifiedIds[] = $conversation->conversation_id;
            $request->session()->put('mctbs_visitor_chat_verified', array_values(array_unique($verifiedIds)));
        }
    }

    public function claimVisitorConversations(User $user, Request $request): int
    {
        if ($user->role?->name !== 'Guest') {
            return 0;
        }

        $sessionKey = $request->session()->get('mctbs_visitor_chat_key');
        $sessionHash = is_string($sessionKey) && $sessionKey !== ''
            ? hash('sha256', $sessionKey)
            : null;
        $verifiedIds = $this->visitorConversationIdsFromSession($request, 'mctbs_visitor_chat_verified');

        if ($sessionHash === null && $verifiedIds === []) {
            return 0;
        }

        $conversations = Conversation::query()
            ->whereNull('guest_user_id')
            ->where('visitor_email', Str::lower(trim($user->email)))
            ->where(function (Builder $query) use ($sessionHash, $verifiedIds): void {
                if ($sessionHash !== null) {
                    $query->where('visitor_session_hash', $sessionHash);
                }

                if ($verifiedIds !== []) {
                    $query->orWhereIn('conversation_id', $verifiedIds);
                }
            })
            ->get();
        foreach ($conversations as $conversation) {
            $archivedAt = $conversation->visitor_archived_at;
            $deletedAt = $conversation->visitor_deleted_at;
            $visitorActorKey = 'visitor:'.$conversation->visitor_session_hash;
            $userActorKey = 'user:'.$user->user_id;
            $messageIds = $conversation->messages()->pluck('message_id');
            $conversation->forceFill([
                'guest_user_id' => $user->user_id,
                'visitor_session_hash' => null,
                'visitor_archived_at' => null,
                'visitor_deleted_at' => null,
            ])->save();
            $participant = $conversation->participants()->firstOrCreate([
                'user_id' => $user->user_id,
            ]);
            $participant->forceFill([
                'archived_at' => $archivedAt,
                'deleted_at' => $deletedAt,
            ])->save();
            DB::table('conversation_message_reactions')
                ->whereIn('message_id', $messageIds)
                ->where('actor_key', $visitorActorKey)
                ->update(['actor_key' => $userActorKey]);
            DB::table('conversation_message_pins')
                ->where('conversation_id', $conversation->conversation_id)
                ->where('actor_key', $visitorActorKey)
                ->update(['actor_key' => $userActorKey]);
        }

        $request->session()->forget('mctbs_visitor_chat_verified');

        return $conversations->count();
    }

    public function createForVisitor(
        TransientHouse $house,
        ?Unit $unit,
        string $name,
        string $email,
        string $sessionHash,
        ?string $subject = null
    ): Conversation {
        return Conversation::query()->create([
            'house_id' => $house->house_id,
            'unit_id' => $unit?->unit_id,
            'guest_user_id' => null,
            'visitor_name' => trim($name),
            'visitor_email' => Str::lower(trim($email)),
            'visitor_session_hash' => $sessionHash,
            'status' => 'open',
            'subject' => $subject,
        ]);
    }

    public function visitorQuestionRequiresSignIn(string $question): bool
    {
        $question = Str::lower(preg_replace('/[^a-z0-9]+/i', ' ', $question) ?? '');
        $sensitiveTerms = [
            'my booking',
            'my reservation',
            'existing booking',
            'existing reservation',
            'booking status',
            'reservation status',
            'booking details',
            'reservation details',
            'my stay',
            'payment',
            'paid',
            'refund',
            'balance due',
            'amount due',
            'transaction',
            'receipt',
            'my account',
            'account details',
            'personal information',
        ];
        $bookingChanges = ['cancel', 'change', 'modify', 'edit', 'reschedule', 'move'];

        return Str::contains($question, $sensitiveTerms)
            || (
                Str::contains($question, $bookingChanges)
                && Str::contains($question, ['booking', 'reservation', 'stay', 'dates'])
            );
    }

    public function sendVisitorMessage(
        Request $request,
        Conversation $conversation,
        string $body,
        ?int $replyToMessageId = null
    ): Message {
        abort_unless($this->canAccessVisitor($request, $conversation), 404);
        $this->assertReplyTarget($conversation, $replyToMessageId);

        $body = trim($body);
        if ($body === '') {
            throw ValidationException::withMessages([
                'message' => 'Enter a message before sending.',
            ]);
        }

        $message = DB::transaction(function () use ($conversation, $body, $replyToMessageId): Message {
            $message = $conversation->messages()->create([
                'sender_id' => null,
                'sender_type' => 'visitor',
                'reply_to_message_id' => $replyToMessageId,
                'message' => $body,
                'message_type' => 'user',
                'sent_at' => now(),
            ]);
            $conversation->forceFill([
                'last_message_at' => $message->sent_at,
                'status' => 'open',
                'resolved_at' => null,
                'visitor_archived_at' => null,
            ])->save();
            $conversation->participants()->update([
                'archived_at' => null,
            ]);

            $assistance = app(AssistanceService::class);
            $unitAnswer = $conversation->unit
                ? $assistance->answerUnitQuestion($conversation->unit, $body)
                : $assistance->answerHouseQuestion($conversation->house, $body);
            $faq = $unitAnswer
                ? null
                : $assistance->findFaq($conversation->house_id, $body, $conversation->unit_id);

            if ($unitAnswer !== null) {
                $assistance->addAssistantResponse($conversation, $unitAnswer);
            } elseif ($faq !== null) {
                $assistance->addFaqResponse($conversation, $faq);
            } else {
                $assistance->handoff($conversation);
                $this->notifyPropertyTeam($conversation, $message);
                $this->sendVisitorNotice($conversation);
            }

            return $message;
        });

        return $message;
    }

    public function moveVisitorConversationToFolder(Request $request, Conversation $conversation, string $folder): void
    {
        abort_unless($this->canAccessVisitor($request, $conversation), 404);

        $timestamps = match ($folder) {
            'inbox' => ['visitor_archived_at' => null],
            'archived' => ['visitor_archived_at' => now()],
            'deleted' => ['visitor_archived_at' => null, 'visitor_deleted_at' => now()],
            default => throw new \InvalidArgumentException('Unsupported conversation folder.'),
        };

        $conversation->forceFill($timestamps)->save();
    }

    public function moveUserConversationToFolder(User $user, Conversation $conversation, string $folder): void
    {
        abort_unless($this->canAccess($user, $conversation), 403);

        $timestamps = match ($folder) {
            'inbox' => ['archived_at' => null],
            'archived' => ['archived_at' => now()],
            'deleted' => ['archived_at' => null, 'deleted_at' => now()],
            default => throw new \InvalidArgumentException('Unsupported conversation folder.'),
        };
        $participant = $conversation->participants()->firstOrCreate([
            'user_id' => $user->user_id,
        ]);

        $participant->forceFill($timestamps)->save();
    }

    public function userFolderForConversation(User $user, Conversation $conversation): string
    {
        $participant = $conversation->participants()
            ->where('user_id', $user->user_id)
            ->first();

        return $participant?->archived_at !== null ? 'archived' : 'inbox';
    }

    /**
     * @return EloquentCollection<int, Message>
     */
    public function messagesAfter(Conversation $conversation, int $messageId): EloquentCollection
    {
        return $conversation->messages()
            ->with(['sender', 'replyToMessage.sender'])
            ->where('message_id', '>', $messageId)
            ->orderBy('message_id')
            ->limit(100)
            ->get();
    }

    /**
     * @param  list<array{emoji: string, count: int, reactedByMe: bool}>  $reactions
     * @return array{id: int, senderType: string, senderId: ?int, senderName: string, text: string, sentAt: string, timeLabel: string, replyTo: ?array{id: int, senderName: string, text: string}, reactions: list<array{emoji: string, count: int, reactedByMe: bool}>}
     */
    public function messageUpdatePayload(
        Message $message,
        Conversation $conversation,
        array $reactions = []
    ): array {
        return [
            'id' => (int) $message->message_id,
            'senderType' => (string) $message->sender_type,
            'senderId' => $message->sender_id === null ? null : (int) $message->sender_id,
            'senderName' => $this->senderName($message, $conversation),
            'text' => (string) $message->message,
            'sentAt' => $message->sent_at->toIso8601String(),
            'timeLabel' => $message->sent_at->format('M j, g:i A'),
            'replyTo' => $message->replyToMessage
                ? $this->messageSummaryPayload($message->replyToMessage, $conversation)
                : null,
            'reactions' => $reactions,
        ];
    }

    public function interactionActorKey(?User $viewer, Conversation $conversation): string
    {
        if ($viewer !== null) {
            return 'user:'.$viewer->user_id;
        }

        abort_if($conversation->visitor_session_hash === null, 404);

        return 'visitor:'.$conversation->visitor_session_hash;
    }

    /**
     * @return array<string, list<array{emoji: string, count: int, reactedByMe: bool}>>
     */
    public function reactionSummariesForMessages(EloquentCollection $messages, string $actorKey): array
    {
        if ($messages->isEmpty()) {
            return [];
        }

        $rowsByMessage = DB::table('conversation_message_reactions')
            ->whereIn('message_id', $messages->pluck('message_id'))
            ->get(['message_id', 'actor_key', 'emoji'])
            ->groupBy('message_id');

        return $this->formatReactionGroups($rowsByMessage, $actorKey);
    }

    /**
     * @return array<string, list<array{emoji: string, count: int, reactedByMe: bool}>>
     */
    public function reactionSummariesForConversation(Conversation $conversation, string $actorKey): array
    {
        $rowsByMessage = DB::table('conversation_message_reactions')
            ->join('messages', 'messages.message_id', '=', 'conversation_message_reactions.message_id')
            ->where('messages.conversation_id', $conversation->conversation_id)
            ->get([
                'conversation_message_reactions.message_id',
                'conversation_message_reactions.actor_key',
                'conversation_message_reactions.emoji',
            ])
            ->groupBy('message_id');

        return $this->formatReactionGroups($rowsByMessage, $actorKey);
    }

    /**
     * @return list<array{emoji: string, count: int, reactedByMe: bool}>
     */
    public function toggleMessageReaction(
        Conversation $conversation,
        Message $message,
        string $actorKey,
        string $emoji
    ): array {
        abort_unless((int) $message->conversation_id === (int) $conversation->conversation_id, 404);
        abort_unless(in_array($emoji, self::REACTION_EMOJIS, true), 422);

        $existing = DB::table('conversation_message_reactions')
            ->where('message_id', $message->message_id)
            ->where('actor_key', $actorKey)
            ->first();

        if ($existing?->emoji === $emoji) {
            DB::table('conversation_message_reactions')
                ->where('message_id', $message->message_id)
                ->where('actor_key', $actorKey)
                ->delete();
        } elseif ($existing !== null) {
            DB::table('conversation_message_reactions')
                ->where('message_id', $message->message_id)
                ->where('actor_key', $actorKey)
                ->update(['emoji' => $emoji]);
        } else {
            DB::table('conversation_message_reactions')->insert([
                'message_id' => $message->message_id,
                'actor_key' => $actorKey,
                'emoji' => $emoji,
            ]);
        }

        $summaries = $this->reactionSummariesForMessages(
            new EloquentCollection([$message]),
            $actorKey
        );

        return $summaries[(string) $message->message_id] ?? [];
    }

    /**
     * @return EloquentCollection<int, Message>
     */
    public function pinnedMessagesFor(Conversation $conversation, string $actorKey): EloquentCollection
    {
        $pins = DB::table('conversation_message_pins')
            ->where('conversation_id', $conversation->conversation_id)
            ->where('actor_key', $actorKey)
            ->orderBy('pinned_at')
            ->get(['message_id']);

        if ($pins->isEmpty()) {
            return new EloquentCollection;
        }

        $messages = Message::query()
            ->with(['sender', 'replyToMessage.sender'])
            ->whereIn('message_id', $pins->pluck('message_id'))
            ->get()
            ->keyBy('message_id');

        return new EloquentCollection(
            $pins->map(fn (object $pin): ?Message => $messages->get($pin->message_id))
                ->filter()
                ->values()
                ->all()
        );
    }

    /**
     * @return list<array{id: int, senderName: string, text: string}>
     */
    public function pinnedMessagePayloadsFor(Conversation $conversation, string $actorKey): array
    {
        return $this->pinnedMessagesFor($conversation, $actorKey)
            ->map(fn (Message $message): array => $this->messageSummaryPayload($message, $conversation))
            ->all();
    }

    /**
     * @return array{pinnedMessageIds: list<int>, pins: list<array{id: int, senderName: string, text: string}>}
     */
    public function toggleMessagePin(
        Conversation $conversation,
        Message $message,
        string $actorKey
    ): array {
        abort_unless((int) $message->conversation_id === (int) $conversation->conversation_id, 404);

        $pinQuery = DB::table('conversation_message_pins')
            ->where('conversation_id', $conversation->conversation_id)
            ->where('message_id', $message->message_id)
            ->where('actor_key', $actorKey);

        if ($pinQuery->exists()) {
            $pinQuery->delete();
        } else {
            DB::table('conversation_message_pins')->insert([
                'conversation_id' => $conversation->conversation_id,
                'message_id' => $message->message_id,
                'actor_key' => $actorKey,
                'pinned_at' => now(),
            ]);
        }

        $pinnedMessages = $this->pinnedMessagesFor($conversation, $actorKey);

        return [
            'pinnedMessageIds' => $pinnedMessages->map(fn (Message $pinned): int => (int) $pinned->message_id)->all(),
            'pins' => $pinnedMessages
                ->map(fn (Message $pinned): array => $this->messageSummaryPayload($pinned, $conversation))
                ->all(),
        ];
    }

    /**
     * @param  Collection<int|string, Collection<int, object>>  $rowsByMessage
     * @return array<string, list<array{emoji: string, count: int, reactedByMe: bool}>>
     */
    private function formatReactionGroups(Collection $rowsByMessage, string $actorKey): array
    {
        return $rowsByMessage->map(function (Collection $rows) use ($actorKey): array {
            return $rows->groupBy('emoji')
                ->map(fn (Collection $emojiRows, string $emoji): array => [
                    'emoji' => $emoji,
                    'count' => $emojiRows->count(),
                    'reactedByMe' => $emojiRows->contains('actor_key', $actorKey),
                ])
                ->values()
                ->all();
        })->all();
    }

    public function conversationQueryFor(User $user, string $folder = 'inbox'): Builder
    {
        $query = $user->role?->name === 'Guest'
            ? Conversation::query()->where('guest_user_id', $user->user_id)
            : Conversation::query()->whereIn('house_id', $this->accessibleHouseIds($user));

        $this->applyConversationFolder($query, $user, $folder);

        return $query
            ->addSelect([
                'booking_status' => Booking::query()
                    ->select('status')
                    ->whereColumn('bookings.user_id', 'conversations.guest_user_id')
                    ->whereHas('unit', fn (Builder $unitQuery) => $unitQuery->whereColumn('units.house_id', 'conversations.house_id'))
                    ->orderByDesc('check_in')
                    ->orderByDesc('booking_id')
                    ->limit(1),
            ])
            ->withCount([
                'messages as unread_message_count' => function (Builder $messageQuery) use ($user): void {
                    if ($user->role?->name !== 'Guest') {
                        $messageQuery->whereIn('conversations.status', ['pending_staff', 'assigned']);
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
                ->whereIn('status', ['pending_staff', 'assigned']);

        return $query
            ->whereDoesntHave('participants', function (Builder $participantQuery) use ($user): void {
                $participantQuery
                    ->where('user_id', $user->user_id)
                    ->where(function (Builder $stateQuery): void {
                        $stateQuery->whereNotNull('archived_at')->orWhereNotNull('deleted_at');
                    });
            })
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

    public function sendMessage(
        User $sender,
        Conversation $conversation,
        string $body,
        ?string $clientToken = null,
        ?int $replyToMessageId = null
    ): Message {
        abort_unless($this->canAccess($sender, $conversation), 403);
        $this->assertReplyTarget($conversation, $replyToMessageId);

        $body = trim($body);
        if ($body === '') {
            throw ValidationException::withMessages([
                'message' => 'Enter a message before sending.',
            ]);
        }

        return DB::transaction(function () use ($sender, $conversation, $body, $clientToken, $replyToMessageId): Message {
            $message = $clientToken === null
                ? $conversation->messages()->create([
                    'sender_id' => $sender->user_id,
                    'reply_to_message_id' => $replyToMessageId,
                    'message' => $body,
                    'message_type' => 'user',
                    'sent_at' => now(),
                ])
                : $conversation->messages()->firstOrCreate(
                    ['client_token' => $clientToken],
                    [
                        'sender_id' => $sender->user_id,
                        'reply_to_message_id' => $replyToMessageId,
                        'message' => $body,
                        'message_type' => 'user',
                        'sent_at' => now(),
                    ]
                );

            if ($message->wasRecentlyCreated) {
                $conversation->forceFill([
                    'last_message_at' => $message->sent_at,
                    'status' => 'open',
                    'resolved_at' => null,
                    'visitor_archived_at' => null,
                ])->save();
                $conversation->participants()->update([
                    'archived_at' => null,
                ]);

                $this->addParticipant($conversation, $sender);

                if ($sender->role?->name === 'Guest') {
                    $assistance = app(AssistanceService::class);
                    $contextualAnswer = $this->bookingContext->answerGuestQuestion($conversation, $body);
                    $unitAnswer = $conversation->unit
                        ? $assistance->answerUnitQuestion($conversation->unit, $body)
                        : null;
                    $faq = $contextualAnswer || $unitAnswer
                        ? null
                        : $assistance->findFaq($conversation->house_id, $body, $conversation->unit_id);

                    if ($contextualAnswer) {
                        $assistance->addAssistantResponse($conversation, $contextualAnswer);
                    } elseif ($unitAnswer) {
                        $assistance->addAssistantResponse($conversation, $unitAnswer);
                    } elseif ($faq) {
                        $assistance->addFaqResponse($conversation, $faq);
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

    private function addParticipant(Conversation $conversation, User $user): void
    {
        $conversation->participants()->firstOrCreate([
            'user_id' => $user->user_id,
        ]);
    }

    private function assertReplyTarget(Conversation $conversation, ?int $replyToMessageId): void
    {
        if ($replyToMessageId === null) {
            return;
        }

        if (! $conversation->messages()->where('message_id', $replyToMessageId)->exists()) {
            throw ValidationException::withMessages([
                'reply_to_message_id' => 'Choose a message from this conversation.',
            ]);
        }
    }

    private function senderName(Message $message, Conversation $conversation): string
    {
        return match ($message->sender_type) {
            'assistant' => 'MCTBS Connect · automated answer',
            'system' => 'MCTBS Connect',
            'visitor' => trim((string) $conversation->visitor_name) ?: 'Public visitor',
            default => trim(($message->sender?->first_name ?? '').' '.($message->sender?->last_name ?? '')) ?: 'Property team',
        };
    }

    /**
     * @return array{id: int, senderName: string, text: string}
     */
    private function messageSummaryPayload(Message $message, Conversation $conversation): array
    {
        return [
            'id' => (int) $message->message_id,
            'senderName' => $this->senderName($message, $conversation),
            'text' => (string) $message->message,
        ];
    }

    private function applyConversationFolder(Builder $query, User $user, string $folder): void
    {
        if (! in_array($folder, ['all', 'inbox', 'archived'], true)) {
            throw new \InvalidArgumentException('Unsupported conversation folder.');
        }

        if ($folder === 'all') {
            $query->whereDoesntHave('participants', function (Builder $participantQuery) use ($user): void {
                $participantQuery
                    ->where('user_id', $user->user_id)
                    ->whereNotNull('deleted_at');
            });

            return;
        }

        if ($folder === 'inbox') {
            $query->whereDoesntHave('participants', function (Builder $participantQuery) use ($user): void {
                $participantQuery
                    ->where('user_id', $user->user_id)
                    ->where(function (Builder $stateQuery): void {
                        $stateQuery->whereNotNull('archived_at')->orWhereNotNull('deleted_at');
                    });
            });

            return;
        }

        $query->whereHas('participants', function (Builder $participantQuery) use ($user): void {
            $participantQuery->where('user_id', $user->user_id);

            $participantQuery->whereNotNull('archived_at')->whereNull('deleted_at');

        });
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

        if ($sender->role?->name !== 'Guest' && $conversation->guest_user_id === null) {
            $this->sendVisitorNotice($conversation);
        }
    }

    private function notifyPropertyTeam(Conversation $conversation, Message $message): void
    {
        $ownerId = $conversation->house()->value('owner_id');
        $recipientIds = $this->staffRecipients($conversation)->push($ownerId);

        $recipientIds
            ->filter(fn (?int $userId): bool => $userId !== null)
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

    private function sendVisitorNotice(Conversation $conversation): void
    {
        if (! $conversation->visitor_email) {
            return;
        }

        $conversation->loadMissing('house');
        $conversationUrl = URL::temporarySignedRoute(
            'public.visitor-conversations.access',
            now()->addDays(14),
            ['conversationId' => $conversation->conversation_id]
        );

        Mail::to($conversation->visitor_email)->send(new PublicVisitorConversationNotice(
            $conversation->house->name,
            $conversationUrl
        ));
    }

    /**
     * @return list<int>
     */
    private function visitorConversationIdsFromSession(Request $request, string $key = 'mctbs_visitor_chat_access'): array
    {
        $conversationIds = $request->session()->get($key, []);
        if (! is_array($conversationIds)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_map('intval', $conversationIds),
            fn (int $conversationId): bool => $conversationId > 0
        )));
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

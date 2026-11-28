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
        if (! in_array($folder, ['all', 'inbox', 'archived', 'deleted'], true)) {
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

        if ($folder === 'archived') {
            $query->whereNotNull('visitor_archived_at')->whereNull('visitor_deleted_at');
        } elseif ($folder === 'deleted') {
            $query->whereNotNull('visitor_deleted_at');
        } elseif ($folder === 'inbox') {
            $query->whereNull('visitor_archived_at')->whereNull('visitor_deleted_at');
        }

        return $query
            ->with(['house', 'unit', 'latestMessage'])
            ->latest('last_message_at')
            ->latest('created_at')
            ->get();
    }

    /**
     * @return array{inbox: int, archived: int, deleted: int}
     */
    public function visitorConversationCountsFor(Request $request): array
    {
        $conversations = $this->visitorConversationsFor($request, 'all');

        return [
            'inbox' => $conversations->whereNull('visitor_archived_at')->whereNull('visitor_deleted_at')->count(),
            'archived' => $conversations->whereNotNull('visitor_archived_at')->whereNull('visitor_deleted_at')->count(),
            'deleted' => $conversations->whereNotNull('visitor_deleted_at')->count(),
        ];
    }

    public function visitorFolderForConversation(Conversation $conversation): string
    {
        if ($conversation->visitor_deleted_at !== null) {
            return 'deleted';
        }

        return $conversation->visitor_archived_at !== null ? 'archived' : 'inbox';
    }

    public function canAccessVisitor(Request $request, Conversation $conversation): bool
    {
        if ($conversation->guest_user_id !== null) {
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
        string $body
    ): Message {
        abort_unless($this->canAccessVisitor($request, $conversation), 404);

        $body = trim($body);
        if ($body === '') {
            throw ValidationException::withMessages([
                'message' => 'Enter a message before sending.',
            ]);
        }

        $message = DB::transaction(function () use ($conversation, $body): Message {
            $message = $conversation->messages()->create([
                'sender_id' => null,
                'sender_type' => 'visitor',
                'message' => $body,
                'message_type' => 'user',
                'sent_at' => now(),
            ]);
            $conversation->forceFill([
                'last_message_at' => $message->sent_at,
                'status' => 'open',
                'resolved_at' => null,
                'visitor_archived_at' => null,
                'visitor_deleted_at' => null,
            ])->save();
            $conversation->participants()->update([
                'archived_at' => null,
                'deleted_at' => null,
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
            'inbox' => ['visitor_archived_at' => null, 'visitor_deleted_at' => null],
            'archived' => ['visitor_archived_at' => now(), 'visitor_deleted_at' => null],
            'deleted' => ['visitor_archived_at' => null, 'visitor_deleted_at' => now()],
            default => throw new \InvalidArgumentException('Unsupported conversation folder.'),
        };

        $conversation->forceFill($timestamps)->save();
    }

    public function moveUserConversationToFolder(User $user, Conversation $conversation, string $folder): void
    {
        abort_unless($this->canAccess($user, $conversation), 403);

        $timestamps = match ($folder) {
            'inbox' => ['archived_at' => null, 'deleted_at' => null],
            'archived' => ['archived_at' => now(), 'deleted_at' => null],
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

        if ($participant?->deleted_at !== null) {
            return 'deleted';
        }

        return $participant?->archived_at !== null ? 'archived' : 'inbox';
    }

    /**
     * @return EloquentCollection<int, Message>
     */
    public function messagesAfter(Conversation $conversation, int $messageId): EloquentCollection
    {
        return $conversation->messages()
            ->with('sender')
            ->where('message_id', '>', $messageId)
            ->orderBy('message_id')
            ->limit(100)
            ->get();
    }

    /**
     * @return array{id: int, senderType: string, senderId: ?int, senderName: string, text: string, sentAt: string, timeLabel: string}
     */
    public function messageUpdatePayload(Message $message, Conversation $conversation): array
    {
        $senderName = match ($message->sender_type) {
            'assistant' => 'MCTBS Connect · automated answer',
            'system' => 'MCTBS Connect',
            'visitor' => trim((string) $conversation->visitor_name) ?: 'Public visitor',
            default => trim(($message->sender?->first_name ?? '').' '.($message->sender?->last_name ?? '')) ?: 'Property team',
        };

        return [
            'id' => (int) $message->message_id,
            'senderType' => (string) $message->sender_type,
            'senderId' => $message->sender_id === null ? null : (int) $message->sender_id,
            'senderName' => $senderName,
            'text' => (string) $message->message,
            'sentAt' => $message->sent_at->toIso8601String(),
            'timeLabel' => $message->sent_at->format('M j, g:i A'),
        ];
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
                    'status' => 'open',
                    'resolved_at' => null,
                    'visitor_archived_at' => null,
                    'visitor_deleted_at' => null,
                ])->save();
                $conversation->participants()->update([
                    'archived_at' => null,
                    'deleted_at' => null,
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

    private function applyConversationFolder(Builder $query, User $user, string $folder): void
    {
        if (! in_array($folder, ['all', 'inbox', 'archived', 'deleted'], true)) {
            throw new \InvalidArgumentException('Unsupported conversation folder.');
        }

        if ($folder === 'all') {
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

        $query->whereHas('participants', function (Builder $participantQuery) use ($user, $folder): void {
            $participantQuery->where('user_id', $user->user_id);

            if ($folder === 'archived') {
                $participantQuery->whereNotNull('archived_at')->whereNull('deleted_at');

                return;
            }

            if ($folder === 'deleted') {
                $participantQuery->whereNotNull('deleted_at');

                return;
            }

            throw new \InvalidArgumentException('Unsupported conversation folder.');
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

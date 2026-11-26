<?php

namespace App\Services;

use App\Mail\ConversationMessage as ConversationMessageMail;
use App\Models\Conversation;
use App\Models\ConversationAttachment;
use App\Models\Invitation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

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
                $messageQuery
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

    public function createForGuest(User $guest, int $houseId, ?string $subject = null): Conversation
    {
        $conversation = Conversation::query()->create([
            'house_id' => $houseId,
            'guest_user_id' => $guest->user_id,
            'status' => 'open',
            'subject' => $subject,
            'external_channel' => 'postmark',
            'external_token' => Str::random(48),
        ]);

        $conversation->participants()->create([
            'user_id' => $guest->user_id,
        ]);

        return $conversation;
    }

    /**
     * @param  list<UploadedFile>  $attachments
     */
    public function sendMessage(
        User $sender,
        Conversation $conversation,
        string $body,
        ?string $clientToken = null,
        array $attachments = []
    ): Message {
        abort_unless($this->canAccess($sender, $conversation), 403);

        $body = trim($body);
        if ($body === '') {
            throw ValidationException::withMessages([
                'message' => 'Enter a message before sending.',
            ]);
        }

        /** @var list<array{disk: string, path: string}> $storedAttachments */
        $storedAttachments = [];

        try {
            return DB::transaction(function () use ($sender, $conversation, $body, $clientToken, $attachments, &$storedAttachments): Message {
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

                    foreach ($attachments as $file) {
                        $attachment = $this->addAttachment($sender, $conversation, $message, $file);
                        $storedAttachments[] = [
                            'disk' => $attachment->disk,
                            'path' => $attachment->path,
                        ];
                    }

                    $this->addParticipant($conversation, $sender);
                    $this->notifyRecipients($sender, $conversation, $message);

                    if ($sender->role?->name === 'Guest') {
                        $assistance = app(AssistanceService::class);
                        $faq = $assistance->findFaq($conversation->house_id, $body);

                        if ($faq) {
                            $assistance->addFaqResponse($conversation, $faq);
                        } elseif ($contextualAnswer = $this->bookingContext->answerGuestQuestion($conversation, $body)) {
                            $conversation->messages()->create([
                                'message' => $contextualAnswer,
                                'message_type' => 'context',
                                'sender_type' => 'assistant',
                                'sent_at' => now(),
                            ]);
                        } else {
                            $assistance->handoff($conversation);
                        }
                    }
                }

                return $message;
            });
        } catch (Throwable $exception) {
            foreach ($storedAttachments as $attachment) {
                $this->deleteStoredAttachment(
                    $conversation->conversation_id,
                    $attachment['disk'],
                    $attachment['path']
                );
            }

            throw $exception;
        }
    }

    public function receiveGuestEmail(
        Conversation $conversation,
        string $fromEmail,
        string $body,
        string $externalMessageId
    ): Message {
        $guest = $conversation->guest;
        abort_unless($guest && strcasecmp((string) $guest->email, trim($fromEmail)) === 0, 403);

        return DB::transaction(function () use ($conversation, $guest, $body, $externalMessageId): Message {
            $message = $conversation->messages()->firstOrCreate(
                ['external_message_id' => $externalMessageId],
                [
                    'sender_id' => $guest->user_id,
                    'sender_type' => 'external_email',
                    'message' => trim($body),
                    'message_type' => 'user',
                    'client_token' => 'postmark:'.$externalMessageId,
                    'sent_at' => now(),
                ]
            );

            if ($message->wasRecentlyCreated) {
                $conversation->forceFill([
                    'last_message_at' => $message->sent_at,
                    'status' => 'open',
                    'resolved_at' => null,
                ])->save();
                $this->addParticipant($conversation, $guest);
                $this->notifyRecipients($guest, $conversation, $message);
            }

            return $message;
        });
    }

    public function addAttachment(
        User $sender,
        Conversation $conversation,
        Message $message,
        UploadedFile $file
    ): ConversationAttachment {
        abort_unless($this->canAccess($sender, $conversation), 403);
        abort_unless($message->conversation_id === $conversation->conversation_id, 404);

        $disk = config('filesystems.conversation_attachments', 'local');
        if (! is_string($disk) || $disk === '') {
            throw new RuntimeException('The conversation attachment storage disk is not configured.');
        }

        $path = $file->store('conversation-attachments/'.$conversation->conversation_id, $disk);
        if (! is_string($path) || $path === '') {
            throw new RuntimeException('The conversation attachment could not be stored.');
        }

        try {
            return $message->attachments()->create([
                'disk' => $disk,
                'path' => $path,
                'original_name' => Str::limit(
                    basename(str_replace('\\', '/', $file->getClientOriginalName())) ?: 'attachment',
                    255,
                    ''
                ),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => (int) $file->getSize(),
            ]);
        } catch (Throwable $exception) {
            $this->deleteStoredAttachment($conversation->conversation_id, $disk, $path);

            throw $exception;
        }
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

    private function deleteStoredAttachment(int $conversationId, string $disk, string $path): void
    {
        if (! Storage::disk($disk)->delete($path)) {
            Log::error('Conversation attachment cleanup failed.', [
                'conversation_id' => $conversationId,
                'disk' => $disk,
                'path' => $path,
            ]);
        }
    }

    public function externalReplyAddress(Conversation $conversation): ?string
    {
        $domain = trim((string) config('services.postmark.inbound_domain'));

        return $domain !== '' && $conversation->external_token
            ? 'reply+'.$conversation->external_token.'@'.$domain
            : null;
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
                    $message,
                    $this->externalReplyAddress($conversation)
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

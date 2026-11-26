<?php

namespace App\Services;

use App\Models\AssistanceSetting;
use App\Models\ChatFaq;
use App\Models\ChatQuickReply;
use App\Models\Conversation;
use App\Models\ConversationNote;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class AssistanceService
{
    public function accessibleHouseIds(User $user): \Illuminate\Support\Collection
    {
        return app(ConversationService::class)->accessibleHouseIds($user);
    }

    public function canManage(User $user, int $houseId): bool
    {
        if ($user->role?->name === 'Owner') {
            return $user->transientHouses()->where('house_id', $houseId)->exists();
        }

        if ($user->role?->name !== 'Staff') {
            return false;
        }

        $staffRoleId = Role::query()->where('name', 'Staff')->value('role_id');

        return Invitation::query()
            ->where('email', $user->email)
            ->where('role_id', $staffRoleId)
            ->where('house_id', $houseId)
            ->whereNotNull('used_at')
            ->exists();
    }

    public function settingsFor(int $houseId): AssistanceSetting
    {
        return AssistanceSetting::query()->firstOrCreate(
            ['house_id' => $houseId],
            [
                'welcome_message' => 'Hello! Welcome to MCTBS. How can we help you today?',
                'handoff_message' => 'Your message has been received. A member of our team will assist you as soon as possible.',
            ]
        );
    }

    public function saveSettings(User $user, int $houseId, string $welcomeMessage, string $handoffMessage): AssistanceSetting
    {
        abort_unless($this->canManage($user, $houseId), 403);

        $settings = $this->settingsFor($houseId);
        $settings->update([
            'updated_by' => $user->user_id,
            'welcome_message' => trim($welcomeMessage),
            'handoff_message' => trim($handoffMessage),
        ]);

        return $settings->fresh();
    }

    public function findFaq(int $houseId, string $question): ?ChatFaq
    {
        $tokens = $this->tokens($question);
        if ($tokens === []) {
            return null;
        }

        $faqs = ChatFaq::query()
            ->where('is_active', true)
            ->where(function ($query) use ($houseId): void {
                $query->where('house_id', $houseId)->orWhereNull('house_id');
            })
            ->orderByRaw('CASE WHEN house_id = ? THEN 0 ELSE 1 END', [$houseId])
            ->get();

        $bestMatch = $faqs
            ->map(fn (ChatFaq $faq): array => [$faq, count(array_intersect($tokens, $this->tokens($faq->question.' '.$faq->keywords)))])
            ->filter(fn (array $match): bool => $match[1] >= 2)
            ->sortByDesc(fn (array $match): int => $match[1])
            ->first();

        return $bestMatch[0] ?? null;
    }

    /**
     * @return Collection<int, ChatFaq>
     */
    public function activeFaqsForGuest(int $houseId): Collection
    {
        return ChatFaq::query()
            ->where('is_active', true)
            ->where(function ($query) use ($houseId): void {
                $query->where('house_id', $houseId)->orWhereNull('house_id');
            })
            ->orderBy('category')
            ->orderBy('question')
            ->get();
    }

    public function addFaqResponse(Conversation $conversation, ChatFaq $faq): void
    {
        $conversation->messages()->create([
            'message' => $faq->answer.($faq->related_url ? "\n\n".$faq->related_url : ''),
            'message_type' => 'assistant',
            'sender_type' => 'assistant',
            'sent_at' => now(),
        ]);
    }

    public function handoff(Conversation $conversation): void
    {
        $settings = $this->settingsFor($conversation->house_id);
        $conversation->messages()->create([
            'message' => $settings->handoff_message,
            'message_type' => 'system',
            'sender_type' => 'system',
            'sent_at' => now(),
        ]);
        $conversation->update([
            'status' => 'pending_staff',
            'resolved_at' => null,
        ]);
    }

    public function addNote(User $author, Conversation $conversation, string $note): ConversationNote
    {
        abort_unless($author->role?->name !== 'Guest' && app(ConversationService::class)->canAccess($author, $conversation), 403);

        return $conversation->notes()->create([
            'author_id' => $author->user_id,
            'note' => trim($note),
        ]);
    }

    /**
     * @return Collection<int, ChatFaq>
     */
    public function faqsFor(User $user, int $houseId): Collection
    {
        abort_unless($this->canManage($user, $houseId), 403);

        return ChatFaq::query()->where('house_id', $houseId)->latest()->get();
    }

    /**
     * @return Collection<int, ChatQuickReply>
     */
    public function quickRepliesFor(User $user, int $houseId): Collection
    {
        abort_unless($this->canManage($user, $houseId), 403);

        return ChatQuickReply::query()
            ->where('house_id', $houseId)
            ->where('is_active', true)
            ->latest()
            ->get();
    }

    public function createFaq(User $user, int $houseId, array $data): ChatFaq
    {
        abort_unless($this->canManage($user, $houseId), 403);

        return ChatFaq::query()->create($data + [
            'house_id' => $houseId,
            'created_by' => $user->user_id,
        ]);
    }

    public function updateFaq(User $user, ChatFaq $faq, array $data): ChatFaq
    {
        abort_unless($faq->house_id !== null && $this->canManage($user, $faq->house_id), 403);
        $faq->update($data);

        return $faq->fresh();
    }

    public function createQuickReply(User $user, int $houseId, array $data): ChatQuickReply
    {
        abort_unless($this->canManage($user, $houseId), 403);

        return ChatQuickReply::query()->create($data + [
            'house_id' => $houseId,
            'created_by' => $user->user_id,
        ]);
    }

    public function updateQuickReply(User $user, ChatQuickReply $quickReply, array $data): ChatQuickReply
    {
        abort_unless($quickReply->house_id !== null && $this->canManage($user, $quickReply->house_id), 403);
        $quickReply->update($data);

        return $quickReply->fresh();
    }

    /**
     * @return list<string>
     */
    private function tokens(string $value): array
    {
        $parts = preg_split('/[^a-z0-9]+/i', Str::lower($value), -1, PREG_SPLIT_NO_EMPTY);

        return collect($parts ?: [])
            ->reject(fn (string $token): bool => strlen($token) < 3)
            ->reject(fn (string $token): bool => in_array($token, ['the', 'and', 'for', 'how', 'what', 'are', 'can', 'you', 'does', 'our', 'your'], true))
            ->unique()
            ->values()
            ->all();
    }
}

<?php

namespace App\Services;

use App\Models\AssistanceSetting;
use App\Models\ChatFaq;
use App\Models\ChatQuickReply;
use App\Models\Conversation;
use App\Models\ConversationNote;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\User;
use App\Support\UnitTimeFormatter;
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

    public function findFaq(int $houseId, string $question, ?int $unitId = null): ?ChatFaq
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
            ->where(function ($query) use ($unitId): void {
                if ($unitId === null) {
                    $query->whereNull('unit_id');
                } else {
                    $query->where('unit_id', $unitId)->orWhereNull('unit_id');
                }
            })
            ->when($unitId !== null, fn ($query) => $query->orderByRaw('CASE WHEN unit_id = ? THEN 0 ELSE 1 END', [$unitId]))
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
    public function activeFaqsForGuest(int $houseId, ?int $unitId = null): Collection
    {
        return ChatFaq::query()
            ->where('is_active', true)
            ->where(function ($query) use ($houseId): void {
                $query->where('house_id', $houseId)->orWhereNull('house_id');
            })
            ->where(function ($query) use ($unitId): void {
                if ($unitId === null) {
                    $query->whereNull('unit_id');
                } else {
                    $query->where('unit_id', $unitId)->orWhereNull('unit_id');
                }
            })
            ->when($unitId !== null, fn ($query) => $query->orderByRaw('CASE WHEN unit_id = ? THEN 0 ELSE 1 END', [$unitId]))
            ->orderBy('category')
            ->orderBy('question')
            ->get();
    }

    /**
     * @return list<string>
     */
    public function quickQuestionsForUnit(): array
    {
        return [
            'What is the price per night?',
            'What time are check-in and check-out?',
            'How many guests can stay?',
            'What amenities are included?',
            'What are the property policies?',
            'Is there an extra guest fee?',
        ];
    }

    /**
     * @return list<string>
     */
    public function quickQuestionsForHouse(): array
    {
        return [
            'What is the property address?',
            'What amenities are available?',
            'What are the property policies?',
            'How can I contact the property?',
        ];
    }

    public function answerHouseQuestion(TransientHouse $house, string $question): ?string
    {
        $house->loadMissing('amenities');
        $question = Str::lower(preg_replace('/[^a-z0-9]+/i', ' ', $question) ?? '');

        if ($this->questionContainsAny($question, ['address', 'location', 'where is'])) {
            return 'The property address is '.implode(', ', array_filter([
                $house->address,
                $house->barangay,
                $house->city,
            ])).'.';
        }

        if ($this->questionContainsAny($question, ['amenity', 'amenities', 'facilities'])) {
            $amenities = $house->amenities->pluck('name')->filter()->values();

            return $amenities->isNotEmpty()
                ? 'Amenities listed for this property include: '.$amenities->join(', ').'.'
                : 'No amenities have been listed for this property yet.';
        }

        if ($this->questionContainsAny($question, ['policy', 'policies', 'house rule', 'rules'])) {
            $policies = trim((string) $house->policies);

            return $policies !== ''
                ? 'The property policies are: '.$policies
                : 'No property policies have been added yet. A team member can help with specific questions.';
        }

        if ($this->questionContainsAny($question, ['contact', 'phone number', 'contact number'])) {
            return $house->contact_number
                ? 'You can contact the property at '.$house->contact_number.'.'
                : 'No contact number has been listed for this property yet.';
        }

        return null;
    }

    public function answerUnitQuestion(Unit $unit, string $question): ?string
    {
        $unit->loadMissing('house.amenities');
        $question = Str::lower(preg_replace('/[^a-z0-9]+/i', ' ', $question) ?? '');

        if (str_contains($question, 'check in') && str_contains($question, 'check out')) {
            return 'Check-in is '.UnitTimeFormatter::format((string) $unit->check_in_time)
                .' and check-out is '.UnitTimeFormatter::format((string) $unit->check_out_time).'.';
        }

        if ($this->questionContainsAny($question, ['price', 'rate', 'cost', 'how much', 'per night'])) {
            return "The current listed rate for {$unit->unit_type} is ₱".number_format((float) $unit->price, 2).' per night.';
        }

        if ($this->questionContainsAny($question, ['check in', 'arrival time'])) {
            return 'Check-in time for '.$unit->unit_type.' is '.UnitTimeFormatter::format((string) $unit->check_in_time).'.';
        }

        if ($this->questionContainsAny($question, ['check out', 'departure time'])) {
            return 'Check-out time for '.$unit->unit_type.' is '.UnitTimeFormatter::format((string) $unit->check_out_time).'.';
        }

        if ($this->questionContainsAny($question, ['capacity', 'how many guest', 'how many people', 'how many person', 'sleeps'])) {
            $answer = "The unit accommodates {$unit->capacity} standard guests";
            if ($unit->maximum_capacity && $unit->maximum_capacity > $unit->capacity) {
                $answer .= " and up to {$unit->maximum_capacity} counted guests";
            }

            return $answer.'.';
        }

        if ($this->questionContainsAny($question, ['extra guest', 'additional guest'])) {
            if (! $unit->maximum_capacity || $unit->maximum_capacity <= $unit->capacity) {
                return 'There is no extra-guest capacity listed for this unit.';
            }

            return 'The extra guest fee is ₱'.number_format((float) $unit->extra_guest_fee, 2).' per guest per night above the standard capacity.';
        }

        if ($this->questionContainsAny($question, ['free child', 'child age', 'children free'])) {
            return $unit->child_free_through_age === null
                ? 'There is no free-child age policy listed for this unit.'
                : "Children aged {$unit->child_free_through_age} and under are not counted toward the unit capacity.";
        }

        if ($this->questionContainsAny($question, ['amenity', 'amenities', 'facilities', 'included'])) {
            $amenities = $unit->house?->amenities->pluck('name')->filter()->values();

            return $amenities && $amenities->isNotEmpty()
                ? 'Amenities listed for this property include: '.$amenities->join(', ').'.'
                : 'No amenities have been listed for this property yet.';
        }

        if ($this->questionContainsAny($question, ['policy', 'policies', 'house rule', 'rules'])) {
            $policies = trim((string) $unit->house?->policies);

            return $policies !== ''
                ? 'The property policies are: '.$policies
                : 'No property policies have been added yet. A team member can help with specific questions.';
        }

        if ($this->questionContainsAny($question, ['address', 'location', 'where is', 'contact number', 'phone number'])) {
            $house = $unit->house;
            if (! $house) {
                return null;
            }

            $details = array_filter([
                $house->address,
                $house->barangay,
                $house->city,
                $house->contact_number ? 'Contact: '.$house->contact_number : null,
            ]);

            return 'Property details: '.implode(', ', $details).'.';
        }

        return null;
    }

    private function questionContainsAny(string $question, array $phrases): bool
    {
        foreach ($phrases as $phrase) {
            if (str_contains($question, $phrase)) {
                return true;
            }
        }

        return false;
    }

    public function addFaqResponse(Conversation $conversation, ChatFaq $faq): void
    {
        $this->addAssistantResponse(
            $conversation,
            $faq->answer.($faq->related_url ? "\n\n".$faq->related_url : '')
        );
    }

    public function addAssistantResponse(Conversation $conversation, string $answer): void
    {
        $message = $conversation->messages()->create([
            'message' => $answer,
            'message_type' => 'assistant',
            'sender_type' => 'assistant',
            'sent_at' => now(),
        ]);
        $conversation->update([
            'last_message_at' => $message->sent_at,
            'status' => 'open',
            'resolved_at' => null,
        ]);
    }

    public function handoff(Conversation $conversation): void
    {
        $message = $conversation->messages()->create([
            'message' => 'Your message has been received. A member of our team will assist you as soon as possible.',
            'message_type' => 'system',
            'sender_type' => 'system',
            'sent_at' => now(),
        ]);
        $conversation->update([
            'last_message_at' => $message->sent_at,
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

        return ChatFaq::query()->with('unit')->where('house_id', $houseId)->latest()->get();
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
        $this->ensureFaqUnitBelongsToHouse($houseId, $data['unit_id'] ?? null);

        return ChatFaq::query()->create($data + [
            'house_id' => $houseId,
            'created_by' => $user->user_id,
        ]);
    }

    public function updateFaq(User $user, ChatFaq $faq, array $data): ChatFaq
    {
        abort_unless($faq->house_id !== null && $this->canManage($user, $faq->house_id), 403);
        $this->ensureFaqUnitBelongsToHouse($faq->house_id, $data['unit_id'] ?? $faq->unit_id);
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

    private function ensureFaqUnitBelongsToHouse(int $houseId, mixed $unitId): void
    {
        if ($unitId === null) {
            return;
        }

        abort_unless(
            Unit::query()->where('house_id', $houseId)->where('unit_id', $unitId)->exists(),
            422,
            'The selected unit does not belong to this property.'
        );
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

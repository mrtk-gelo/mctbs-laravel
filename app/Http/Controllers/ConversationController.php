<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\User;
use App\Services\AssistanceService;
use App\Services\BookingContextService;
use App\Services\ConversationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function __construct(
        private ConversationService $conversations,
        private AssistanceService $assistance,
        private BookingContextService $bookingContext
    ) {}

    public function guestIndex(Request $request): View
    {
        $conversations = $this->conversations->conversationQueryFor($request->user())
            ->with(['house', 'unit', 'latestMessage'])
            ->latest('last_message_at')
            ->latest('created_at')
            ->get();

        return view('conversations.guest-index', [
            'conversations' => $conversations,
            'houses' => TransientHouse::query()
                ->where('is_active', true)
                ->whereNull('archived_at')
                ->whereHas('owner', fn ($query) => $query->where('is_active', true))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function guestShow(Request $request, int $conversationId): View
    {
        $conversation = $this->conversation($request->user(), $conversationId);
        $this->conversations->markRead($request->user(), $conversation);
        $conversation->load(['house', 'unit', 'messages.sender']);

        return view('conversations.guest-show', [
            'conversation' => $conversation,
            'conversations' => $this->conversations->conversationQueryFor($request->user())
                ->with(['house', 'unit', 'latestMessage'])
                ->latest('last_message_at')
                ->latest('created_at')
                ->get(),
            'settings' => $this->assistance->settingsFor($conversation->house_id),
            'faqs' => $this->assistance->activeFaqsForGuest($conversation->house_id, $conversation->unit_id),
            'faqPrompts' => $conversation->unit ? $this->assistance->quickQuestionsForUnit() : [],
            'context' => $this->bookingContext->forConversation($request->user(), $conversation),
        ]);
    }

    public function guestStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'house_id' => ['required', 'integer', 'exists:transient_houses,house_id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,unit_id'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'attachments' => ['prohibited'],
        ]);

        $house = TransientHouse::query()
            ->where('is_active', true)
            ->whereNull('archived_at')
            ->whereHas('owner', fn ($query) => $query->where('is_active', true))
            ->findOrFail($data['house_id']);
        $unitId = null;
        if (! empty($data['unit_id'])) {
            $unitId = Unit::query()
                ->where('house_id', $house->house_id)
                ->where('unit_id', $data['unit_id'])
                ->where('status', 'Available')
                ->whereNull('archived_at')
                ->whereHas('house', fn ($query) => $query->where('is_active', true)->whereNull('archived_at'))
                ->value('unit_id');
            abort_if($unitId === null, 422, 'The selected unit is not available for questions.');
        }
        $conversation = $this->conversations->createForGuest(
            $request->user(),
            $house->house_id,
            $data['subject'] ?? null,
            $unitId
        );
        $this->conversations->sendMessage($request->user(), $conversation, $data['message']);

        return redirect()->route('guest.conversations.show', $conversation->conversation_id)
            ->with('success', 'Your message has been sent.');
    }

    public function guestReply(Request $request, int $conversationId): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'attachments' => ['prohibited'],
        ]);
        $conversation = $this->conversation($request->user(), $conversationId);
        $this->conversations->sendMessage($request->user(), $conversation, $data['message']);

        return back()->with('success', 'Your message has been sent.');
    }

    public function staffIndex(Request $request): View
    {
        $allConversations = $this->staffConversationsFor($request->user());
        $filter = $this->conversationFilter($request);

        return view('conversations.staff-index', [
            'conversations' => $this->filterStaffConversations($allConversations, $filter),
            'filter' => $filter,
            'counts' => [
                'all' => $allConversations->count(),
                'unread' => $allConversations->filter(fn (Conversation $conversation): bool => $conversation->unread_message_count > 0)->count(),
                'guest' => $allConversations->filter(fn (Conversation $conversation): bool => $conversation->guest_user_id === null)->count(),
            ],
        ]);
    }

    public function staffShow(Request $request, int $conversationId): View
    {
        $conversation = $this->conversation($request->user(), $conversationId);
        $allConversations = $this->staffConversationsFor($request->user());
        $filter = $this->conversationFilter($request);
        $this->conversations->markRead($request->user(), $conversation);

        return view('conversations.staff-show', [
            'conversation' => $conversation->load(['guest', 'house', 'unit', 'messages.sender', 'notes.author']),
            'conversations' => $this->filterStaffConversations($allConversations, $filter),
            'quickReplies' => $this->assistance->quickRepliesFor($request->user(), $conversation->house_id),
            'context' => $this->bookingContext->forConversation($request->user(), $conversation),
            'filter' => $filter,
        ]);
    }

    public function staffStore(Request $request, int $conversationId): RedirectResponse
    {
        $conversation = $this->conversation($request->user(), $conversationId);
        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'attachments' => ['prohibited'],
        ]);

        $this->conversations->sendMessage($request->user(), $conversation, $data['message']);

        return back()->with('success', 'Your reply has been sent.');
    }

    public function noteStore(Request $request, int $conversationId): RedirectResponse
    {
        $data = $request->validate([
            'note' => ['required', 'string', 'max:5000'],
        ]);
        $conversation = $this->conversation($request->user(), $conversationId);
        $this->assistance->addNote($request->user(), $conversation, $data['note']);

        return back()->with('success', 'Internal note added.');
    }

    private function conversation(User $user, int $conversationId): Conversation
    {
        $conversation = Conversation::query()->findOrFail($conversationId);
        abort_unless($this->conversations->canAccess($user, $conversation), 403);

        return $conversation;
    }

    private function staffConversationsFor(User $user): Collection
    {
        return $this->conversations->conversationQueryFor($user)
            ->with(['guest', 'house', 'unit', 'latestMessage', 'latestHumanMessage'])
            ->latest('last_message_at')
            ->latest('created_at')
            ->get();
    }

    private function conversationFilter(Request $request): string
    {
        $filter = $request->string('filter')->toString();

        return in_array($filter, ['all', 'unread', 'guest'], true) ? $filter : 'all';
    }

    private function filterStaffConversations(Collection $conversations, string $filter): Collection
    {
        return match ($filter) {
            'unread' => $conversations->filter(fn (Conversation $conversation): bool => $conversation->unread_message_count > 0)->values(),
            'guest' => $conversations->filter(fn (Conversation $conversation): bool => $conversation->guest_user_id === null)->values(),
            default => $conversations,
        };
    }
}

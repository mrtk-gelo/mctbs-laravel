<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\User;
use App\Services\AssistanceService;
use App\Services\BookingContextService;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
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
        $folder = $this->conversationFolder($request);
        $user = $request->user();
        $conversations = $this->conversations->conversationQueryFor($user, $folder)
            ->with(['house', 'unit', 'latestMessage'])
            ->latest('last_message_at')
            ->latest('created_at')
            ->get();

        return view('conversations.guest-index', [
            'conversations' => $conversations,
            'folder' => $folder,
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
        $user = $request->user();
        $conversation = $this->conversation($user, $conversationId);
        $this->conversations->markRead($user, $conversation);
        $conversation->load(['house.owner', 'unit', 'messages.sender', 'messages.replyToMessage.sender']);
        $actorKey = $this->conversations->interactionActorKey($user, $conversation);
        $pinnedMessages = $this->conversations->pinnedMessagesFor($conversation, $actorKey);
        $folder = $request->query->has('folder')
            ? $this->conversationFolder($request)
            : $this->conversations->userFolderForConversation($user, $conversation);

        return view('conversations.guest-show', [
            'conversation' => $conversation,
            'conversations' => $this->conversations->conversationQueryFor($user, $folder)
                ->with(['house', 'unit', 'latestMessage'])
                ->latest('last_message_at')
                ->latest('created_at')
                ->get(),
            'folder' => $folder,
            'reactionSummaries' => $this->conversations->reactionSummariesForMessages($conversation->messages, $actorKey),
            'pinnedMessages' => $pinnedMessages,
            'interactionActorKey' => $actorKey,
            'reactionRoute' => 'guest.conversations.reactions',
            'pinRoute' => 'guest.conversations.pins',
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
            'reply_to_message_id' => ['nullable', 'integer', 'min:1'],
            'attachments' => ['prohibited'],
        ]);
        $conversation = $this->conversation($request->user(), $conversationId);
        $this->conversations->sendMessage(
            $request->user(),
            $conversation,
            $data['message'],
            replyToMessageId: isset($data['reply_to_message_id']) ? (int) $data['reply_to_message_id'] : null
        );

        return redirect()->route('guest.conversations.show', [
            'conversationId' => $conversation->conversation_id,
            'folder' => 'inbox',
        ])->with('success', 'Your message has been sent.');
    }

    public function staffIndex(Request $request): View
    {
        $folder = $this->conversationFolder($request);
        $filter = $folder === 'inbox' ? $this->conversationFilter($request) : 'all';
        $conversations = $this->staffConversationsFor($request->user(), $folder);

        return view('conversations.staff-index', [
            'conversations' => $this->filterStaffConversations($conversations, $filter),
            'filter' => $filter,
            'folder' => $folder,
            'counts' => $this->staffConversationCounts($request->user()),
        ]);
    }

    public function staffCreate(Request $request): View
    {
        $allConversations = $this->staffConversationsFor($request->user());
        $filter = $this->conversationFilter($request);
        $houses = TransientHouse::query()
            ->whereIn('house_id', $this->conversations->accessibleHouseIds($request->user()))
            ->where('is_active', true)
            ->whereNull('archived_at')
            ->orderBy('name')
            ->get();
        $selectedHouse = null;
        $guests = collect();

        if ($request->query('house_id') !== null) {
            $data = $request->validate([
                'house_id' => ['required', 'integer'],
            ]);
            $selectedHouse = $houses->firstWhere('house_id', (int) $data['house_id']);
            abort_if($selectedHouse === null, 404);
            $guests = User::query()
                ->where('is_active', true)
                ->whereHas('role', fn ($query) => $query->where('name', 'Guest'))
                ->whereHas('bookings.unit', fn ($query) => $query->where('house_id', $selectedHouse->house_id))
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();
        }

        return view('conversations.staff-create', [
            'conversations' => $this->filterStaffConversations($allConversations, $filter),
            'filter' => $filter,
            'counts' => $this->staffConversationCounts($request->user()),
            'folder' => 'inbox',
            'houses' => $houses,
            'selectedHouse' => $selectedHouse,
            'guests' => $guests,
        ]);
    }

    public function staffCreateStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'house_id' => ['required', 'integer'],
            'guest_user_id' => ['required', 'integer'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);
        $house = TransientHouse::query()
            ->whereIn('house_id', $this->conversations->accessibleHouseIds($request->user()))
            ->where('is_active', true)
            ->whereNull('archived_at')
            ->findOrFail($data['house_id']);
        $guest = User::query()
            ->where('is_active', true)
            ->whereHas('role', fn ($query) => $query->where('name', 'Guest'))
            ->whereHas('bookings.unit', fn ($query) => $query->where('house_id', $house->house_id))
            ->findOrFail($data['guest_user_id']);
        $conversation = $this->conversations->createForGuest(
            $guest,
            $house->house_id,
            $data['subject'] ?? null
        );
        $this->conversations->sendMessage($request->user(), $conversation, $data['message']);
        $route = $request->user()->role?->name === 'Staff'
            ? 'staff.conversations.show'
            : 'owner.conversations.show';

        return redirect()->route($route, $conversation->conversation_id)
            ->with('success', 'Your conversation has been started.');
    }

    public function staffShow(Request $request, int $conversationId): View
    {
        $user = $request->user();
        $conversation = $this->conversation($user, $conversationId);
        $folder = $request->query->has('folder')
            ? $this->conversationFolder($request)
            : $this->conversations->userFolderForConversation($user, $conversation);
        $filter = $folder === 'inbox' ? $this->conversationFilter($request) : 'all';
        $conversations = $this->staffConversationsFor($user, $folder);
        $this->conversations->markRead($user, $conversation);
        $conversation->load(['guest', 'house.owner', 'unit', 'messages.sender', 'messages.replyToMessage.sender', 'notes.author']);
        $actorKey = $this->conversations->interactionActorKey($user, $conversation);
        $isStaff = $user->role?->name === 'Staff';

        return view('conversations.staff-show', [
            'conversation' => $conversation,
            'conversations' => $this->filterStaffConversations($conversations, $filter),
            'quickReplies' => $this->assistance->quickRepliesFor($user, $conversation->house_id),
            'context' => $this->bookingContext->forConversation($user, $conversation),
            'filter' => $filter,
            'folder' => $folder,
            'counts' => $this->staffConversationCounts($user),
            'reactionSummaries' => $this->conversations->reactionSummariesForMessages($conversation->messages, $actorKey),
            'interactionActorKey' => $actorKey,
            'reactionRoute' => $isStaff ? 'staff.conversations.reactions' : 'owner.conversations.reactions',
            'pinRoute' => null,
        ]);
    }

    public function staffStore(Request $request, int $conversationId): RedirectResponse
    {
        $conversation = $this->conversation($request->user(), $conversationId);
        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'reply_to_message_id' => ['nullable', 'integer', 'min:1'],
            'attachments' => ['prohibited'],
        ]);

        $this->conversations->sendMessage(
            $request->user(),
            $conversation,
            $data['message'],
            replyToMessageId: isset($data['reply_to_message_id']) ? (int) $data['reply_to_message_id'] : null
        );

        $showRoute = $request->user()->role?->name === 'Staff'
            ? 'staff.conversations.show'
            : 'owner.conversations.show';

        return redirect()->route($showRoute, [
            'conversationId' => $conversation->conversation_id,
            'folder' => 'inbox',
            'filter' => $this->conversationFilter($request),
        ])->with('success', 'Your reply has been sent.');
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

    public function updates(Request $request, int $conversationId): JsonResponse
    {
        $conversation = $this->conversation($request->user(), $conversationId);
        $data = $request->validate([
            'after' => ['nullable', 'integer', 'min:0'],
        ]);
        $after = (int) ($data['after'] ?? 0);
        $messages = $this->conversations->messagesAfter($conversation, $after);
        $actorKey = $this->conversations->interactionActorKey($request->user(), $conversation);
        $reactionSummaries = $this->conversations->reactionSummariesForConversation($conversation, $actorKey);
        if ($messages->isNotEmpty()) {
            $this->conversations->markRead($request->user(), $conversation);
        }

        return response()->json([
            'messages' => $messages
                ->map(fn (Message $message): array => $this->conversations->messageUpdatePayload(
                    $message,
                    $conversation,
                    $reactionSummaries[(string) $message->message_id] ?? []
                ))
                ->values(),
            'lastMessageId' => $messages->last()?->message_id ?? $after,
            'reactionSummaries' => $reactionSummaries,
        ])->header('Cache-Control', 'no-store, private');
    }

    public function toggleReaction(Request $request, int $conversationId, int $messageId): JsonResponse
    {
        $conversation = $this->conversation($request->user(), $conversationId);
        $data = $request->validate([
            'emoji' => ['required', 'string', Rule::in(ConversationService::REACTION_EMOJIS)],
        ]);
        $message = $conversation->messages()->findOrFail($messageId);
        $actorKey = $this->conversations->interactionActorKey($request->user(), $conversation);

        return response()->json([
            'messageId' => $message->message_id,
            'reactions' => $this->conversations->toggleMessageReaction(
                $conversation,
                $message,
                $actorKey,
                $data['emoji']
            ),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function togglePin(Request $request, int $conversationId, int $messageId): JsonResponse
    {
        abort_unless($request->user()->role?->name === 'Guest', 404);
        $conversation = $this->conversation($request->user(), $conversationId);
        $message = $conversation->messages()->findOrFail($messageId);
        $actorKey = $this->conversations->interactionActorKey($request->user(), $conversation);

        return response()->json([
            'messageId' => $message->message_id,
            ...$this->conversations->toggleMessagePin($conversation, $message, $actorKey),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function archive(Request $request, int $conversationId): RedirectResponse
    {
        return $this->moveConversation($request, $conversationId, 'archived');
    }

    public function deleteConversation(Request $request, int $conversationId): RedirectResponse
    {
        return $this->moveConversation($request, $conversationId, 'deleted');
    }

    public function restore(Request $request, int $conversationId): RedirectResponse
    {
        return $this->moveConversation($request, $conversationId, 'inbox');
    }

    private function conversation(User $user, int $conversationId): Conversation
    {
        $conversation = $this->conversations->conversationQueryFor($user, 'all')->find($conversationId);
        if ($conversation !== null) {
            return $conversation;
        }

        $conversation = Conversation::query()->findOrFail($conversationId);
        abort_unless($this->conversations->canAccess($user, $conversation), 403);

        return $conversation;
    }

    private function staffConversationsFor(User $user, string $folder = 'inbox'): Collection
    {
        return $this->conversations->conversationQueryFor($user, $folder)
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

    private function conversationFolder(Request $request): string
    {
        $folder = $request->string('folder')->toString();

        return in_array($folder, ['inbox', 'archived'], true) ? $folder : 'inbox';
    }

    /**
     * @return array{all: int, unread: int, guest: int, inbox: int}
     */
    private function staffConversationCounts(User $user): array
    {
        $inbox = $this->conversations->conversationQueryFor($user);
        $inboxCount = (clone $inbox)->count();

        return [
            'all' => $inboxCount,
            'inbox' => $inboxCount,
            'unread' => $this->conversations->unreadConversationCountFor($user),
            'guest' => (clone $inbox)->whereNull('guest_user_id')->count(),
        ];
    }

    private function moveConversation(Request $request, int $conversationId, string $folder): RedirectResponse
    {
        $user = $request->user();
        $conversation = $this->conversation($user, $conversationId);
        $this->conversations->moveUserConversationToFolder($user, $conversation, $folder);
        $returnFolder = $folder === 'deleted' ? 'inbox' : $folder;

        if ($user->role?->name === 'Guest') {
            return redirect()->route('guest.conversations.index', ['folder' => $returnFolder])
                ->with('success', $this->conversationFolderMessage($folder));
        }

        $indexRoute = $user->role?->name === 'Staff'
            ? 'staff.conversations.index'
            : 'owner.conversations.index';

        return redirect()->route($indexRoute, ['folder' => $returnFolder])
            ->with('success', $this->conversationFolderMessage($folder));
    }

    private function conversationFolderMessage(string $folder): string
    {
        return match ($folder) {
            'archived' => 'Conversation moved to Archived.',
            'deleted' => 'Conversation permanently deleted from your chat list. Other participants keep their copy.',
            default => 'Conversation restored to your inbox.',
        };
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

<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Services\AssistanceService;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PublicVisitorConversationController extends Controller
{
    public function __construct(
        private ConversationService $conversations,
        private AssistanceService $assistance
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()?->role?->name === 'Guest') {
            return redirect()->route('guest.conversations.index');
        }

        abort_if($request->user() !== null, 404);
        $folder = $this->conversationFolder($request);

        return view('public-conversations.index', [
            'conversations' => $this->conversations->visitorConversationsFor($request, $folder),
            'folder' => $folder,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user() !== null, 404);

        $data = $request->validate([
            'house_id' => ['required', 'integer'],
            'unit_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'attachments' => ['prohibited'],
        ]);

        if ($this->conversations->visitorQuestionRequiresSignIn($data['message'])) {
            return back()
                ->withInput()
                ->with('visitorSignInRequired', true);
        }

        $house = TransientHouse::query()
            ->where('is_active', true)
            ->whereNull('archived_at')
            ->whereHas('owner', fn ($query) => $query->where('is_active', true))
            ->findOrFail($data['house_id']);

        $unit = null;
        if (! empty($data['unit_id'])) {
            $unit = Unit::query()
                ->where('house_id', $house->house_id)
                ->where('unit_id', $data['unit_id'])
                ->where('status', 'Available')
                ->whereNull('archived_at')
                ->whereHas('house', fn ($query) => $query->where('is_active', true)->whereNull('archived_at'))
                ->first();

            abort_if($unit === null, 422, 'The selected unit is not available for questions.');
        }

        $conversation = $this->conversations->createForVisitor(
            $house,
            $unit,
            $data['name'],
            $data['email'],
            $this->conversations->visitorSessionHash($request),
            $data['subject'] ?? null
        );
        $this->conversations->sendVisitorMessage($request, $conversation, $data['message']);

        return redirect()->route('public.visitor-conversations.show', $conversation->conversation_id)
            ->with('success', 'Your message has been sent.');
    }

    public function context(Request $request): JsonResponse
    {
        $data = $request->validate([
            'house_id' => ['required', 'integer'],
            'unit_id' => ['nullable', 'integer'],
        ]);
        $house = $this->publicHouse((int) $data['house_id']);
        $unit = $this->publicUnit($house, isset($data['unit_id']) ? (int) $data['unit_id'] : null);

        return response()->json([
            'units' => $house->units()
                ->where('status', 'Available')
                ->whereNull('archived_at')
                ->orderBy('unit_type')
                ->get(['unit_id', 'unit_type'])
                ->map(fn (Unit $availableUnit): array => [
                    'id' => $availableUnit->unit_id,
                    'name' => $availableUnit->unit_type,
                ]),
            'quickQuestions' => $unit
                ? $this->assistance->quickQuestionsForUnit()
                : $this->assistance->quickQuestionsForHouse(),
            'faqQuestions' => $this->assistance
                ->activeFaqsForGuest($house->house_id, $unit?->unit_id)
                ->map(fn ($faq): string => $faq->question)
                ->values(),
        ]);
    }

    public function answer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'house_id' => ['required', 'integer'],
            'unit_id' => ['nullable', 'integer'],
            'question' => ['required', 'string', 'max:5000'],
        ]);
        $question = trim($data['question']);

        if ($this->conversations->visitorQuestionRequiresSignIn($question)) {
            return response()->json([
                'requiresSignIn' => true,
                'signInUrl' => route('login'),
            ]);
        }

        $house = $this->publicHouse((int) $data['house_id']);
        $unit = $this->publicUnit($house, isset($data['unit_id']) ? (int) $data['unit_id'] : null);
        $answer = $unit
            ? $this->assistance->answerUnitQuestion($unit, $question)
            : $this->assistance->answerHouseQuestion($house, $question);
        $faq = $answer === null
            ? $this->assistance->findFaq($house->house_id, $question, $unit?->unit_id)
            : null;

        if ($faq !== null) {
            $answer = $faq->answer;
        }

        return response()->json([
            'answered' => $answer !== null,
            'answer' => $answer,
            'relatedUrl' => $faq?->related_url,
        ]);
    }

    public function show(Request $request, int $conversationId): View|RedirectResponse
    {
        $conversation = Conversation::query()->findOrFail($conversationId);
        if ($request->user()?->role?->name === 'Guest' && $conversation->guest_user_id === $request->user()->user_id) {
            return redirect()->route('guest.conversations.show', $conversation->conversation_id);
        }

        abort_if(in_array($request->user()?->role?->name, ['Owner', 'Staff'], true), 404);
        abort_unless($this->conversations->canAccessVisitor($request, $conversation), 404);

        $conversation->load(['house.owner', 'unit', 'messages.sender', 'messages.replyToMessage.sender']);
        $actorKey = $this->conversations->interactionActorKey(null, $conversation);
        $folder = $request->query->has('folder')
            ? $this->conversationFolder($request)
            : $this->conversations->visitorFolderForConversation($conversation);

        return view('public-conversations.show', [
            'conversation' => $conversation,
            'conversations' => $this->conversations->visitorConversationsFor($request, $folder),
            'folder' => $folder,
            'reactionSummaries' => $this->conversations->reactionSummariesForMessages($conversation->messages, $actorKey),
            'pinnedMessages' => $this->conversations->pinnedMessagesFor($conversation, $actorKey),
            'interactionActorKey' => $actorKey,
            'reactionRoute' => 'public.visitor-conversations.reactions',
            'pinRoute' => 'public.visitor-conversations.pins',
            'settings' => $this->assistance->settingsFor($conversation->house_id),
            'faqPrompts' => $conversation->unit
                ? $this->assistance->quickQuestionsForUnit()
                : $this->assistance->quickQuestionsForHouse(),
            'faqs' => $this->assistance->activeFaqsForGuest($conversation->house_id, $conversation->unit_id),
            'signInUrl' => route('public.visitor-conversations.sign-in', $conversation->conversation_id),
        ]);
    }

    public function reply(Request $request, int $conversationId): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'reply_to_message_id' => ['nullable', 'integer', 'min:1'],
            'attachments' => ['prohibited'],
        ]);
        $conversation = Conversation::query()->findOrFail($conversationId);

        if ($this->conversations->visitorQuestionRequiresSignIn($data['message'])) {
            abort_unless($this->conversations->canAccessVisitor($request, $conversation), 404);

            return back()
                ->withInput()
                ->with('visitorSignInRequired', true);
        }

        $this->conversations->sendVisitorMessage(
            $request,
            $conversation,
            $data['message'],
            isset($data['reply_to_message_id']) ? (int) $data['reply_to_message_id'] : null
        );

        return redirect()->route('public.visitor-conversations.show', [
            'conversationId' => $conversation->conversation_id,
            'folder' => 'inbox',
        ])->with('success', 'Your message has been sent.');
    }

    public function updates(Request $request, int $conversationId): JsonResponse
    {
        $conversation = Conversation::query()->findOrFail($conversationId);
        abort_unless($this->conversations->canAccessVisitor($request, $conversation), 404);
        $data = $request->validate([
            'after' => ['nullable', 'integer', 'min:0'],
        ]);
        $after = (int) ($data['after'] ?? 0);
        $messages = $this->conversations->messagesAfter($conversation, $after);
        $actorKey = $this->conversations->interactionActorKey(null, $conversation);
        $reactionSummaries = $this->conversations->reactionSummariesForConversation($conversation, $actorKey);
        $pinnedMessages = $this->conversations->pinnedMessagePayloadsFor($conversation, $actorKey);

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
            'pinnedMessageIds' => array_column($pinnedMessages, 'id'),
            'pins' => $pinnedMessages,
        ])->header('Cache-Control', 'no-store, private');
    }

    public function toggleReaction(Request $request, int $conversationId, int $messageId): JsonResponse
    {
        $conversation = Conversation::query()->findOrFail($conversationId);
        abort_unless($this->conversations->canAccessVisitor($request, $conversation), 404);
        $data = $request->validate([
            'emoji' => ['required', 'string', Rule::in(ConversationService::REACTION_EMOJIS)],
        ]);
        $message = $conversation->messages()->findOrFail($messageId);
        $actorKey = $this->conversations->interactionActorKey(null, $conversation);

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
        $conversation = Conversation::query()->findOrFail($conversationId);
        abort_unless($this->conversations->canAccessVisitor($request, $conversation), 404);
        $message = $conversation->messages()->findOrFail($messageId);
        $actorKey = $this->conversations->interactionActorKey(null, $conversation);

        return response()->json([
            'messageId' => $message->message_id,
            ...$this->conversations->toggleMessagePin($conversation, $message, $actorKey),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function archive(Request $request, int $conversationId): RedirectResponse
    {
        return $this->moveVisitorConversation($request, $conversationId, 'archived');
    }

    public function deleteConversation(Request $request, int $conversationId): RedirectResponse
    {
        return $this->moveVisitorConversation($request, $conversationId, 'deleted');
    }

    public function restore(Request $request, int $conversationId): RedirectResponse
    {
        return $this->moveVisitorConversation($request, $conversationId, 'inbox');
    }

    public function access(Request $request, int $conversationId): RedirectResponse
    {
        $conversation = Conversation::query()->findOrFail($conversationId);

        abort_if(in_array($request->user()?->role?->name, ['Owner', 'Staff'], true), 404);

        if ($request->user()?->role?->name === 'Guest') {
            $this->conversations->grantVisitorConversationAccess($request, $conversation, true);
            $this->conversations->claimVisitorConversations($request->user(), $request);

            if ($conversation->fresh()->guest_user_id === $request->user()->user_id) {
                return redirect()->route('guest.conversations.show', $conversation->conversation_id);
            }
        } else {
            $this->conversations->grantVisitorConversationAccess($request, $conversation, true);
        }

        return redirect()->route('public.visitor-conversations.show', $conversation->conversation_id);
    }

    public function signIn(Request $request, int $conversationId): RedirectResponse
    {
        $conversation = Conversation::query()->findOrFail($conversationId);
        abort_unless($this->conversations->canAccessVisitor($request, $conversation), 404);

        $request->session()->put(
            'url.intended',
            route('public.visitor-conversations.show', $conversation->conversation_id)
        );

        return redirect()->route('login');
    }

    private function publicHouse(int $houseId): TransientHouse
    {
        return TransientHouse::query()
            ->where('is_active', true)
            ->whereNull('archived_at')
            ->whereHas('owner', fn ($query) => $query->where('is_active', true))
            ->findOrFail($houseId);
    }

    private function publicUnit(TransientHouse $house, ?int $unitId): ?Unit
    {
        if ($unitId === null) {
            return null;
        }

        $unit = Unit::query()
            ->where('house_id', $house->house_id)
            ->where('unit_id', $unitId)
            ->where('status', 'Available')
            ->whereNull('archived_at')
            ->first();

        abort_if($unit === null, 404);

        return $unit;
    }

    private function conversationFolder(Request $request): string
    {
        $folder = $request->string('folder')->toString();

        return in_array($folder, ['inbox', 'archived'], true) ? $folder : 'inbox';
    }

    private function moveVisitorConversation(Request $request, int $conversationId, string $folder): RedirectResponse
    {
        $conversation = Conversation::query()->findOrFail($conversationId);
        $this->conversations->moveVisitorConversationToFolder($request, $conversation, $folder);
        $returnFolder = $folder === 'deleted' ? 'inbox' : $folder;

        return redirect()->route('public.visitor-conversations.index', ['folder' => $returnFolder])
            ->with('success', match ($folder) {
                'archived' => 'Conversation moved to Archived.',
                'deleted' => 'Conversation permanently deleted from your chat list. Other participants keep their copy.',
                default => 'Conversation restored to your inbox.',
            });
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\User;
use App\Services\AssistanceService;
use App\Services\ConversationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        return view('public-conversations.index', [
            'conversations' => $this->conversations->visitorConversationsFor($request),
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

        $conversation->load(['house', 'unit', 'messages.sender']);

        return view('public-conversations.show', [
            'conversation' => $conversation,
            'conversations' => $this->conversations->visitorConversationsFor($request),
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
            'attachments' => ['prohibited'],
        ]);
        $conversation = Conversation::query()->findOrFail($conversationId);

        if ($this->conversations->visitorQuestionRequiresSignIn($data['message'])) {
            abort_unless($this->conversations->canAccessVisitor($request, $conversation), 404);

            return back()
                ->withInput()
                ->with('visitorSignInRequired', true);
        }

        $this->conversations->sendVisitorMessage($request, $conversation, $data['message']);

        return back()->with('success', 'Your message has been sent.');
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
}

<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\User;
use App\Services\AssistanceService;
use App\Services\BookingContextService;
use App\Services\ConversationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $query = $this->conversations->conversationQueryFor($request->user())
            ->with(['guest', 'house', 'unit', 'assignee', 'latestMessage'])
            ->latest('last_message_at')
            ->latest('created_at');

        $status = $request->string('status')->toString();
        if ($status === 'attention' || $status === '') {
            $query->whereIn('status', ['open', 'pending_staff', 'assigned']);
        } elseif ($status !== 'all' && in_array($status, ['open', 'pending_staff', 'assigned', 'resolved'], true)) {
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($conversationQuery) use ($search): void {
                $conversationQuery
                    ->where('subject', 'like', '%'.$search.'%')
                    ->orWhereHas('guest', function ($guestQuery) use ($search): void {
                        $guestQuery->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        return view('conversations.staff-index', [
            'conversations' => $query->get(),
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function staffShow(Request $request, int $conversationId): View
    {
        $conversation = $this->conversation($request->user(), $conversationId);
        $this->conversations->markRead($request->user(), $conversation);

        return view('conversations.staff-show', [
            'conversation' => $conversation->load(['guest', 'house', 'unit', 'assignee', 'messages.sender', 'notes.author']),
            'conversations' => $this->conversations->conversationQueryFor($request->user())
                ->with(['guest', 'house', 'unit', 'assignee', 'latestMessage'])
                ->latest('last_message_at')
                ->latest('created_at')
                ->get(),
            'assignees' => $this->assignees($conversation),
            'quickReplies' => $this->assistance->quickRepliesFor($request->user(), $conversation->house_id),
            'context' => $this->bookingContext->forConversation($request->user(), $conversation),
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

    public function assign(Request $request, int $conversationId): RedirectResponse
    {
        $conversation = $this->conversation($request->user(), $conversationId);
        $data = $request->validate([
            'assigned_to' => ['required', 'integer', 'exists:users,user_id'],
        ]);

        $this->conversations->assign(
            $request->user(),
            $conversation,
            User::query()->findOrFail($data['assigned_to'])
        );

        return back()->with('success', 'Conversation assignment updated.');
    }

    public function resolve(Request $request, int $conversationId): RedirectResponse
    {
        $this->conversations->resolve(
            $request->user(),
            $this->conversation($request->user(), $conversationId)
        );

        return back()->with('success', 'Conversation marked as resolved.');
    }

    public function reopen(Request $request, int $conversationId): RedirectResponse
    {
        $this->conversations->reopen(
            $request->user(),
            $this->conversation($request->user(), $conversationId)
        );

        return back()->with('success', 'Conversation reopened.');
    }

    private function conversation(User $user, int $conversationId): Conversation
    {
        $conversation = Conversation::query()->findOrFail($conversationId);
        abort_unless($this->conversations->canAccess($user, $conversation), 403);

        return $conversation;
    }

    private function assignees(Conversation $conversation): Collection
    {
        return User::query()
            ->where(function ($query) use ($conversation): void {
                $query->where('user_id', $conversation->house->owner_id)
                    ->orWhereIn('email', function ($subquery) use ($conversation): void {
                        $subquery->select('email')
                            ->from('invitations')
                            ->where('house_id', $conversation->house_id)
                            ->whereNotNull('used_at');
                    });
            })
            ->where('is_active', true)
            ->with('role')
            ->orderBy('first_name')
            ->get();
    }
}

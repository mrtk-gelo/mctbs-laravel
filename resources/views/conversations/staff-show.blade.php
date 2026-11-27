<x-shell title="Conversation | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'messages'])</x-slot:nav>

    @php
        $isStaff = auth()->user()->role->name === 'Staff';
        $conversationRoute = $isStaff ? 'staff.conversations.show' : 'owner.conversations.show';
        $inboxRoute = $isStaff ? 'staff.conversations.index' : 'owner.conversations.index';
        $replyRoute = $isStaff ? 'staff.conversations.store' : 'owner.conversations.store';
        $assignRoute = $isStaff ? 'staff.conversations.assign' : 'owner.conversations.assign';
        $resolveRoute = $isStaff ? 'staff.conversations.resolve' : 'owner.conversations.resolve';
        $reopenRoute = $isStaff ? 'staff.conversations.reopen' : 'owner.conversations.reopen';
        $notesRoute = $isStaff ? 'staff.conversations.notes.store' : 'owner.conversations.notes.store';
    @endphp

    <div class="container chat-page">
        <header class="chat-page-heading">
            <div>
                <span class="chat-eyebrow">MCTBS Connect · Team inbox</span>
                <h1>Messages</h1>
                <p>Review the guest's conversation and reply from one workspace.</p>
            </div>
            <a class="chat-header-link" href="{{ route($inboxRoute) }}">
                <i class="fa fa-arrow-left" aria-hidden="true"></i> Back to inbox
            </a>
        </header>

        <div class="chat-workspace chat-workspace--staff chat-workspace--thread">
            <aside class="chat-sidebar" aria-label="Team conversations">
                <div class="chat-sidebar-header">
                    <div>
                        <h2>Inbox</h2>
                        <p>{{ $conversations->count() }} {{ \Illuminate\Support\Str::plural('chat', $conversations->count()) }}</p>
                    </div>
                    <a class="chat-sidebar-icon" href="{{ route($inboxRoute) }}" aria-label="Open inbox filters">
                        <i class="fa fa-filter" aria-hidden="true"></i>
                    </a>
                </div>
                <nav class="chat-conversation-list" aria-label="Inbox conversation list">
                    @forelse ($conversations as $thread)
                        @include('conversations.partials.list-item', [
                            'conversation' => $thread,
                            'conversationRoute' => $conversationRoute,
                            'listType' => 'staff',
                            'activeConversationId' => $conversation->conversation_id,
                        ])
                    @empty
                        <div class="chat-list-empty">
                            <span class="chat-empty-icon"><i class="fa fa-inbox" aria-hidden="true"></i></span>
                            <strong>No conversations found</strong>
                            <span>New guest messages will appear here.</span>
                        </div>
                    @endforelse
                </nav>
            </aside>

            <main class="chat-thread">
                <header class="chat-thread-header">
                    <a class="chat-back-link" href="{{ route($inboxRoute) }}" aria-label="Back to inbox">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>
                    <span class="chat-thread-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($conversation->guest->first_name, 0, 1)) }}</span>
                    <div class="chat-thread-heading">
                        <span class="chat-eyebrow">{{ $conversation->house->name }}{{ $conversation->unit ? ' · '.$conversation->unit->unit_type : '' }}</span>
                        <h2>{{ $conversation->subject ?: $conversation->guest->first_name.' '.$conversation->guest->last_name }}</h2>
                        <p>Guest conversation · {{ $conversation->guest->email }}</p>
                    </div>
                    <span class="chat-status chat-status--{{ $conversation->status }}">{{ str_replace('_', ' ', ucfirst($conversation->status)) }}</span>
                </header>

                <section class="chat-message-list" aria-label="Conversation messages" aria-live="polite">
                    @if ($conversation->messages->isNotEmpty())
                        @include('conversations.partials.messages', [
                            'messages' => $conversation->messages,
                            'viewerUserId' => auth()->id(),
                        ])
                    @else
                        <div class="chat-list-empty">
                            <span class="chat-empty-icon"><i class="fa fa-message" aria-hidden="true"></i></span>
                            <strong>No messages yet</strong>
                            <span>Send a reply to start the conversation.</span>
                        </div>
                    @endif
                </section>

                <div class="chat-composer">
                    @if ($quickReplies->isNotEmpty())
                        <label class="chat-quick-reply-label" for="quick_reply">Quick reply</label>
                        <select class="form-control chat-quick-reply-select" id="quick_reply" data-quick-reply-select>
                            <option value="">Choose a saved reply</option>
                            @foreach ($quickReplies as $quickReply)
                                <option value="{{ $quickReply->content }}">{{ $quickReply->title }}</option>
                            @endforeach
                        </select>
                    @endif
                    <form method="POST" action="{{ route($replyRoute, $conversation->conversation_id) }}" class="chat-composer-form">
                        @csrf
                        <label class="sr-only" for="message">Write a reply to the guest</label>
                        <textarea class="form-control" id="message" name="message" rows="2" maxlength="5000" required placeholder="Write a reply..." data-message-composer></textarea>
                        @error('message')<div class="text-danger fs-sm mt-1">{{ $message }}</div>@enderror
                        <button class="btn btn-primary" type="submit" aria-label="Send reply">
                            <i class="fa fa-paper-plane" aria-hidden="true"></i> Send
                        </button>
                    </form>
                    <p class="chat-composer-note"><i class="fa fa-lock" aria-hidden="true"></i> Replies are sent in the guest’s MCTBS conversation.</p>
                </div>
            </main>

            <aside class="chat-details" aria-label="Conversation details">
                <section class="chat-detail-section">
                    <div class="chat-detail-heading">
                        <span class="chat-detail-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($conversation->guest->first_name, 0, 1)) }}</span>
                        <div>
                            <h2>{{ $conversation->guest->first_name }} {{ $conversation->guest->last_name }}</h2>
                            <p>{{ $conversation->guest->email }}</p>
                        </div>
                    </div>
                    <div class="chat-detail-fact">
                        <span>Property</span>
                        <strong>{{ $conversation->house->name }}</strong>
                    </div>
                    @if ($conversation->unit)
                        <div class="chat-detail-fact">
                            <span>Unit</span>
                            <strong>{{ $conversation->unit->unit_type }}</strong>
                        </div>
                    @endif
                    <form method="POST" action="{{ route($assignRoute, $conversation->conversation_id) }}" class="chat-assignment-form">
                        @csrf
                        <label class="form-label" for="assigned_to">Assigned to</label>
                        <select class="form-control" id="assigned_to" name="assigned_to" required>
                            @foreach ($assignees as $assignee)
                                <option value="{{ $assignee->user_id }}" @selected($conversation->assigned_to === $assignee->user_id)>{{ $assignee->first_name }} {{ $assignee->last_name }} · {{ $assignee->role->name }}</option>
                            @endforeach
                        </select>
                        @error('assigned_to')<div class="text-danger fs-sm mt-1">{{ $message }}</div>@enderror
                        <button class="btn btn-outline-primary btn-sm" type="submit">Update assignment</button>
                    </form>
                </section>

                <section class="chat-detail-section">
                    <h3><i class="fa fa-calendar-check" aria-hidden="true"></i> Booking context</h3>
                    @if ($context['booking'])
                        <div class="chat-detail-fact">
                            <span>Stay</span>
                            <strong>{{ $context['booking']->unit->house->name }} · {{ $context['booking']->unit->unit_type }}</strong>
                        </div>
                        <div class="chat-detail-fact">
                            <span>Dates</span>
                            <strong>{{ $context['booking']->check_in->format('M j, Y') }} - {{ $context['booking']->check_out->format('M j, Y') }}</strong>
                        </div>
                        <div class="chat-detail-fact">
                            <span>Booking</span>
                            <strong>{{ $context['booking']->booking_reference ?: '#'.$context['booking']->booking_id }} · {{ $context['booking']->status }}</strong>
                        </div>
                        <div class="chat-detail-fact">
                            <span>Payment</span>
                            <strong>{{ $context['paymentStatus'] ?: 'No payment record' }}</strong>
                        </div>
                        @if ($context['bookingUrl'])
                            <a href="{{ $context['bookingUrl'] }}" class="btn btn-outline-primary btn-sm">Open booking</a>
                        @endif
                    @else
                        <p class="chat-detail-empty">No booking is linked to this conversation.</p>
                    @endif
                    <div class="chat-detail-fact">
                        <span>Property address</span>
                        <strong>{{ $conversation->house->address }}</strong>
                    </div>
                    @if ($context['policies'])
                        <details class="chat-detail-collapsible">
                            <summary>House policies</summary>
                            <p>{{ $context['policies'] }}</p>
                        </details>
                    @endif
                    @if ($context['amenities']->isNotEmpty())
                        <details class="chat-detail-collapsible">
                            <summary>Amenities</summary>
                            <p>{{ $context['amenities']->pluck('name')->join(', ') }}</p>
                        </details>
                    @endif
                </section>

                <section class="chat-detail-section">
                    <div class="chat-detail-actions">
                        @if ($conversation->status === 'resolved')
                            <form method="POST" action="{{ route($reopenRoute, $conversation->conversation_id) }}">
                                @csrf
                                <button class="btn btn-outline-primary btn-sm" type="submit">Reopen conversation</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route($resolveRoute, $conversation->conversation_id) }}">
                                @csrf
                                <button class="btn btn-outline-primary btn-sm" type="submit">Mark as resolved</button>
                            </form>
                        @endif
                    </div>
                </section>

                <details class="chat-internal-notes">
                    <summary><i class="fa fa-note-sticky" aria-hidden="true"></i> Internal notes <span>{{ $conversation->notes->count() }}</span></summary>
                    <div class="chat-notes-body">
                        @forelse ($conversation->notes as $note)
                            <article class="chat-note">
                                <div><strong>{{ $note->author->first_name }}</strong><time datetime="{{ $note->created_at->toIso8601String() }}">{{ $note->created_at->format('M j, g:i A') }}</time></div>
                                <p>{{ $note->note }}</p>
                            </article>
                        @empty
                            <p class="chat-detail-empty">No private notes yet.</p>
                        @endforelse
                        <form method="POST" action="{{ route($notesRoute, $conversation->conversation_id) }}" class="chat-note-form">
                            @csrf
                            <label class="form-label" for="note">Add a private note</label>
                            <textarea class="form-control" id="note" name="note" rows="3" maxlength="5000" required placeholder="Visible only to your team"></textarea>
                            @error('note')<div class="text-danger fs-sm mt-1">{{ $message }}</div>@enderror
                            <button class="btn btn-outline-primary btn-sm" type="submit">Save note</button>
                        </form>
                    </div>
                </details>
            </aside>
        </div>
    </div>

    <script>
        const quickReplySelect = document.querySelector('[data-quick-reply-select]');
        const messageComposer = document.querySelector('[data-message-composer]');
        const messageList = document.querySelector('.chat-message-list');
        if (messageList) {
            messageList.scrollTop = messageList.scrollHeight;
        }

        quickReplySelect?.addEventListener('change', () => {
            if (quickReplySelect.value && messageComposer) {
                messageComposer.value = quickReplySelect.value;
                messageComposer.focus();
            }
        });
        const refreshConversation = () => {
            if (messageComposer && (messageComposer.value.trim() || document.activeElement === messageComposer)) {
                window.setTimeout(refreshConversation, 15000);
                return;
            }
            window.location.reload();
        };
        window.setTimeout(refreshConversation, 15000);
    </script>
</x-shell>

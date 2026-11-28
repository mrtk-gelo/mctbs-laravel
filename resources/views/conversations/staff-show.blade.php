<x-shell title="Conversation | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'messages'])</x-slot:nav>

    @php
        $isStaff = auth()->user()->role->name === 'Staff';
        $conversationRoute = $isStaff ? 'staff.conversations.show' : 'owner.conversations.show';
        $inboxRoute = $isStaff ? 'staff.conversations.index' : 'owner.conversations.index';
        $replyRoute = $isStaff ? 'staff.conversations.store' : 'owner.conversations.store';
        $notesRoute = $isStaff ? 'staff.conversations.notes.store' : 'owner.conversations.notes.store';
        $archiveRoute = $isStaff ? 'staff.conversations.archive' : 'owner.conversations.archive';
        $deleteRoute = $isStaff ? 'staff.conversations.delete' : 'owner.conversations.delete';
        $restoreRoute = $isStaff ? 'staff.conversations.restore' : 'owner.conversations.restore';
        $guestName = $conversation->guest
            ? trim($conversation->guest->first_name.' '.$conversation->guest->last_name)
            : (trim((string) $conversation->visitor_name) ?: 'Public visitor');
        $guestEmail = $conversation->guest?->email ?? $conversation->visitor_email;
        $guestStatus = $conversation->guest
            ? ($conversation->booking_status ? \App\Support\BookingStatus::canonical($conversation->booking_status) : 'Registered guest')
            : 'Public visitor';
        $guestStatusClass = \Illuminate\Support\Str::slug($guestStatus);
    @endphp

    <div class="container chat-page">
        <a class="chat-page-back text-muted fs-sm" href="{{ route($inboxRoute, ['filter' => $filter, 'folder' => $folder]) }}">
            <i class="fa fa-arrow-left" aria-hidden="true"></i> Back to inbox
        </a>
        <header class="chat-page-heading">
            <div>
                <span class="chat-eyebrow">MCTBS Connect · Team inbox</span>
                <h1>Messages</h1>
                <p>Review the guest's conversation and reply from one workspace.</p>
            </div>
        </header>

        <div class="chat-workspace chat-workspace--staff chat-workspace--thread">
            @include('conversations.partials.staff-sidebar', [
                'conversations' => $conversations,
                'counts' => $counts,
                'filter' => $filter,
                'folder' => $folder,
                'conversationRoute' => $conversationRoute,
                'inboxRoute' => $inboxRoute,
                'createRoute' => $isStaff ? 'staff.conversations.create' : 'owner.conversations.create',
                'activeConversationId' => $conversation->conversation_id,
            ])

            <main class="chat-thread">
                <header class="chat-thread-header">
                    <span class="chat-thread-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($guestName, 0, 1)) }}</span>
                    <div class="chat-thread-heading">
                        <span class="chat-eyebrow">{{ $conversation->house->name }}{{ $conversation->unit ? ' · '.$conversation->unit->unit_type : '' }}</span>
                        <h2>{{ $conversation->subject ?: $guestName }}</h2>
                        <p>{{ $conversation->guest ? 'Registered guest' : 'Guest visitor' }}{{ $guestEmail ? ' · '.$guestEmail : '' }}</p>
                    </div>
                    @if ($conversation->booking_status)
                        <span class="chat-status chat-status--{{ \Illuminate\Support\Str::slug(\App\Support\BookingStatus::canonical($conversation->booking_status)) }}" title="{{ $conversation->booking_status === \App\Support\BookingStatus::ACCEPTED ? 'Dates are reserved while downpayment is pending.' : \App\Support\BookingStatus::canonical($conversation->booking_status) }}">
                            {{ \App\Support\BookingStatus::canonical($conversation->booking_status) }}
                        </span>
                    @endif
                    <div class="chat-thread-actions">
                        @include('conversations.partials.actions-menu', [
                            'folder' => $folder,
                            'archiveRoute' => $archiveRoute,
                            'deleteRoute' => $deleteRoute,
                            'restoreRoute' => $restoreRoute,
                            'conversationId' => $conversation->conversation_id,
                        ])
                        <button
                            class="chat-info-toggle"
                            type="button"
                            aria-label="Show conversation information"
                            aria-controls="chat-details"
                            aria-expanded="false"
                            title="Show conversation information"
                            data-chat-details-toggle
                        >
                            <i class="fa fa-circle-info" aria-hidden="true"></i>
                        </button>
                    </div>
                </header>

                <section
                    class="chat-message-list"
                    aria-label="Conversation messages"
                    aria-live="polite"
                    data-chat-poll-url="{{ route($isStaff ? 'staff.conversations.updates' : 'owner.conversations.updates', $conversation->conversation_id) }}"
                    data-reaction-url-template="{{ route($reactionRoute, ['conversationId' => $conversation->conversation_id, 'messageId' => '__message_id__']) }}"
                    data-csrf-token="{{ csrf_token() }}"
                    data-conversation-id="{{ $conversation->conversation_id }}"
                    data-viewer-type="team"
                    data-viewer-id="{{ auth()->id() }}"
                    data-last-message-id="{{ $conversation->messages->max('message_id') ?? 0 }}"
                >
                    @if ($conversation->messages->isNotEmpty())
                        @include('conversations.partials.messages', [
                            'messages' => $conversation->messages,
                            'viewerUserId' => auth()->id(),
                            'conversationGuestId' => $conversation->guest_user_id,
                            'visitorName' => $guestName,
                            'hideSystemMessages' => true,
                            'conversationId' => $conversation->conversation_id,
                            'reactionRoute' => $reactionRoute,
                            'reactionSummaries' => $reactionSummaries,
                            'pinnedMessageIds' => [],
                            'pinRoute' => null,
                            'canPin' => false,
                        ])
                    @else
                        <div class="chat-list-empty">
                            <span class="chat-empty-icon"><i class="fa fa-message" aria-hidden="true"></i></span>
                            <strong>No messages yet</strong>
                            <span>Send a reply to start the conversation.</span>
                        </div>
                    @endif
                </section>

                <p class="chat-sync-status" role="status" aria-live="polite" data-chat-sync-status hidden></p>

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
                    @include('conversations.partials.reply-preview')
                    <form method="POST" action="{{ route($replyRoute, $conversation->conversation_id) }}" class="chat-composer-form">
                        @csrf
                        <input type="hidden" name="reply_to_message_id" value="{{ old('reply_to_message_id') }}" data-chat-reply-id>
                        <label class="sr-only" for="message">Write a reply to the guest</label>
                        <textarea class="form-control" id="message" name="message" rows="2" maxlength="5000" required placeholder="Write a reply..." data-message-composer></textarea>
                        @error('message')<div class="text-danger fs-sm mt-1">{{ $message }}</div>@enderror
                        <button class="btn btn-primary" type="submit" aria-label="Send reply">
                            <i class="fa fa-paper-plane" aria-hidden="true"></i> Send
                        </button>
                    </form>
                </div>
            </main>

            <aside class="chat-details" id="chat-details" aria-label="Conversation details" aria-hidden="true" data-chat-details-panel>
                <section class="chat-detail-section">
                    <div class="chat-detail-heading">
                        <span class="chat-detail-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($guestName, 0, 1)) }}</span>
                        <div>
                            <h2>{{ $guestName }}</h2>
                            <p>{{ $guestEmail ?: 'No email provided' }}</p>
                        </div>
                    </div>
                    <div class="chat-detail-fact">
                        <span>Guest status</span>
                        <strong><span class="chat-status chat-status--{{ $guestStatusClass }}">{{ $guestStatus }}</span></strong>
                    </div>
                    @if ($conversation->guest?->phone)
                        <div class="chat-detail-fact">
                            <span>Phone</span>
                            <strong>{{ $conversation->guest->phone }}</strong>
                        </div>
                    @endif
                    <div class="chat-detail-fact">
                        <span>Property</span>
                        <strong>{{ $conversation->house->name }}</strong>
                    </div>
                    @if ($conversation->unit)
                        <div class="chat-detail-fact">
                            <span>Unit</span>
                            <strong>{{ $conversation->unit->unit_type }}</strong>
                        </div>
                        <div class="chat-detail-fact">
                            <span>Nightly rate</span>
                            <strong>₱{{ number_format((float) $conversation->unit->price, 2) }}</strong>
                        </div>
                        <div class="chat-detail-fact">
                            <span>Capacity</span>
                            <strong>{{ $conversation->unit->capacity }} guests</strong>
                        </div>
                    @endif
                    @if ($conversation->house->contact_number)
                        <div class="chat-detail-fact">
                            <span>Property phone</span>
                            <strong>{{ $conversation->house->contact_number }}</strong>
                        </div>
                    @endif
                </section>

                <section class="chat-detail-section">
                    <h3><i class="fa fa-user-tie" aria-hidden="true"></i> Property owner</h3>
                    <div class="chat-detail-fact">
                        <span>Name</span>
                        <strong>{{ trim($conversation->house->owner->first_name.' '.$conversation->house->owner->last_name) }}</strong>
                    </div>
                    <div class="chat-detail-fact">
                        <span>Email</span>
                        <strong>{{ $conversation->house->owner->email }}</strong>
                    </div>
                    @if ($conversation->house->owner->phone)
                        <div class="chat-detail-fact">
                            <span>Phone</span>
                            <strong>{{ $conversation->house->owner->phone }}</strong>
                        </div>
                    @endif
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
    </script>
</x-shell>

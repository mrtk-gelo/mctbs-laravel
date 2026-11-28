<x-shell title="Conversation | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'messages'])</x-slot:nav>

    <div class="container chat-page">
        <a class="chat-page-back text-muted fs-sm" href="{{ route('guest.conversations.index', ['folder' => $folder]) }}">
            <i class="fa fa-arrow-left" aria-hidden="true"></i> Back to conversations
        </a>
        <header class="chat-page-heading">
            <div>
                <span class="chat-eyebrow">MCTBS Connect</span>
                <h1>Your conversations</h1>
                <p>Messages with {{ $conversation->house->name }} are kept here.</p>
            </div>
        </header>

        <div class="chat-workspace chat-workspace--guest chat-workspace--thread">
            @include('conversations.partials.guest-sidebar', [
                'conversations' => $conversations,
                'folder' => $folder,
                'activeConversationId' => $conversation->conversation_id,
            ])

            <main class="chat-thread">
                <header class="chat-thread-header">
                    <span class="chat-thread-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($conversation->house->name, 0, 1)) }}</span>
                    <div class="chat-thread-heading">
                        <span class="chat-eyebrow">Property conversation</span>
                        <h2>{{ $conversation->subject ?: $conversation->house->name }}</h2>
                        <p>{{ $conversation->house->name }}{{ $conversation->unit ? ' · '.$conversation->unit->unit_type : '' }}</p>
                    </div>
                    @if ($conversation->booking_status)
                        <span class="chat-status chat-status--{{ \Illuminate\Support\Str::slug(\App\Support\BookingStatus::canonical($conversation->booking_status)) }}" title="{{ $conversation->booking_status === \App\Support\BookingStatus::ACCEPTED ? 'Dates are reserved while the downpayment is pending.' : \App\Support\BookingStatus::canonical($conversation->booking_status) }}">
                            {{ \App\Support\BookingStatus::canonical($conversation->booking_status) }}
                        </span>
                    @endif
                    <div class="chat-thread-actions">
                        @include('conversations.partials.actions-menu', [
                            'folder' => $folder,
                            'archiveRoute' => 'guest.conversations.archive',
                            'deleteRoute' => 'guest.conversations.delete',
                            'restoreRoute' => 'guest.conversations.restore',
                            'conversationId' => $conversation->conversation_id,
                            'showPinnedMessages' => true,
                            'pinnedCount' => $pinnedMessages->count(),
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

                <div class="chat-thread-welcome">
                    <span class="chat-welcome-avatar"><i class="fa fa-headset" aria-hidden="true"></i></span>
                    <p><strong>MCTBS Connect</strong><br>{{ $settings->welcome_message }}</p>
                </div>

                @include('conversations.partials.pinned-messages', [
                    'pinnedMessages' => $pinnedMessages,
                    'pinRoute' => $pinRoute,
                    'conversationId' => $conversation->conversation_id,
                    'visitorName' => trim(auth()->user()->first_name.' '.auth()->user()->last_name),
                ])

                <section
                    class="chat-message-list"
                    aria-label="Conversation messages"
                    aria-live="polite"
                    data-chat-poll-url="{{ route('guest.conversations.updates', $conversation->conversation_id) }}"
                    data-pin-url-template="{{ route($pinRoute, ['conversationId' => $conversation->conversation_id, 'messageId' => '__message_id__']) }}"
                    data-unsend-url-template="{{ route($unsendRoute, ['conversationId' => $conversation->conversation_id, 'messageId' => '__message_id__']) }}"
                    data-csrf-token="{{ csrf_token() }}"
                    data-conversation-id="{{ $conversation->conversation_id }}"
                    data-viewer-type="guest"
                    data-viewer-id="{{ auth()->id() }}"
                    data-last-message-id="{{ $conversation->messages->max('message_id') ?? 0 }}"
                >
                    @if ($conversation->messages->isNotEmpty())
                        @include('conversations.partials.messages', [
                            'messages' => $conversation->messages,
                            'viewerUserId' => auth()->id(),
                            'conversationGuestId' => $conversation->guest_user_id,
                            'visitorName' => trim(auth()->user()->first_name.' '.auth()->user()->last_name),
                            'conversationId' => $conversation->conversation_id,
                            'pinnedMessageIds' => $pinnedMessages->map(fn ($message): int => (int) $message->message_id)->all(),
                            'pinRoute' => $pinRoute,
                            'unsendRoute' => $unsendRoute,
                            'canPin' => true,
                        ])
                    @else
                        <div class="chat-list-empty">
                            <span class="chat-empty-icon"><i class="fa fa-message" aria-hidden="true"></i></span>
                            <strong>Start the conversation</strong>
                            <span>Send a message to the property team below.</span>
                        </div>
                    @endif
                </section>

                <p class="chat-sync-status" role="status" aria-live="polite" data-chat-sync-status hidden></p>

                <div class="chat-composer">
                    @if (! empty($faqPrompts) || $faqs->isNotEmpty())
                        <details class="chat-quick-questions">
                            <summary>Quick questions</summary>
                            <div class="chat-quick-question-list">
                                @foreach ($faqPrompts as $prompt)
                                    <form method="POST" action="{{ route('guest.conversations.reply', $conversation->conversation_id) }}">
                                        @csrf
                                        <input type="hidden" name="message" value="{{ $prompt }}">
                                        <button class="chat-quick-question" type="submit">{{ $prompt }}</button>
                                    </form>
                                @endforeach
                                @foreach ($faqs->take(5) as $faq)
                                    <form method="POST" action="{{ route('guest.conversations.reply', $conversation->conversation_id) }}">
                                        @csrf
                                        <input type="hidden" name="message" value="{{ $faq->question }}">
                                        <button class="chat-quick-question" type="submit">{{ $faq->question }}</button>
                                    </form>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    @include('conversations.partials.reply-preview')
                    <form method="POST" action="{{ route('guest.conversations.reply', $conversation->conversation_id) }}" class="chat-composer-form">
                        @csrf
                        <input type="hidden" name="reply_to_message_id" value="{{ old('reply_to_message_id') }}" data-chat-reply-id>
                        <label class="sr-only" for="message">Write a reply</label>
                        <textarea class="form-control" id="message" name="message" rows="1" maxlength="5000" required placeholder="Write a message..." data-message-composer></textarea>
                        @error('message')<div class="text-danger fs-sm mt-1">{{ $message }}</div>@enderror
                        <button class="btn btn-primary" type="submit" aria-label="Send reply">
                            <i class="fa fa-paper-plane" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </main>

            <aside class="chat-details" id="chat-details" aria-label="Conversation information" aria-hidden="true" data-chat-details-panel>
                <section class="chat-detail-section">
                    <div class="chat-detail-heading">
                        <span class="chat-detail-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->first_name, 0, 1)) }}</span>
                        <div>
                            <h2>{{ trim(auth()->user()->first_name.' '.auth()->user()->last_name) }}</h2>
                            <p>{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <div class="chat-detail-fact">
                        <span>Your contact number</span>
                        <strong>{{ auth()->user()->phone ?: 'Not provided' }}</strong>
                    </div>
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
                            <span>Booking status</span>
                            <strong>{{ \App\Support\BookingStatus::canonical($context['booking']->status) }}</strong>
                        </div>
                        <div class="chat-detail-fact">
                            <span>Payment</span>
                            <strong>{{ $context['paymentStatus'] ?: 'No payment record' }}</strong>
                        </div>
                        <div class="chat-detail-actions">
                            @if ($context['bookingUrl'])
                                <a href="{{ $context['bookingUrl'] }}" class="btn btn-outline-primary btn-sm">View booking</a>
                            @endif
                            @if ($context['ticketUrl'])
                                <a href="{{ $context['ticketUrl'] }}" class="btn btn-outline-primary btn-sm">View ticket</a>
                            @endif
                        </div>
                    @else
                        <p class="chat-detail-empty">No booking is linked to this property yet.</p>
                    @endif
                </section>

                <section class="chat-detail-section">
                    <h3><i class="fa fa-house" aria-hidden="true"></i> Property and unit</h3>
                    <div class="chat-detail-fact">
                        <span>Property</span>
                        <strong>{{ $conversation->house->name }}</strong>
                    </div>
                    <div class="chat-detail-fact">
                        <span>Address</span>
                        <strong>{{ $conversation->house->address }}</strong>
                    </div>
                    @if ($conversation->house->contact_number)
                        <div class="chat-detail-fact">
                            <span>Property contact</span>
                            <strong>{{ $conversation->house->contact_number }}</strong>
                        </div>
                    @endif
                    @if ($conversation->unit)
                        <div class="chat-detail-fact">
                            <span>Unit</span>
                            <strong>{{ $conversation->unit->unit_type }}</strong>
                        </div>
                        <div class="chat-detail-fact">
                            <span>Rate</span>
                            <strong>₱{{ number_format((float) $conversation->unit->price, 2) }} per night</strong>
                        </div>
                    @endif
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
                    <h3><i class="fa fa-user-tie" aria-hidden="true"></i> Property owner</h3>
                    <div class="chat-detail-fact">
                        <span>Name</span>
                        <strong>{{ trim($conversation->house->owner->first_name.' '.$conversation->house->owner->last_name) }}</strong>
                    </div>
                    <div class="chat-detail-fact">
                        <span>Contact</span>
                        <strong>{{ $conversation->house->owner->email }}{{ $conversation->house->owner->phone ? ' · '.$conversation->house->owner->phone : '' }}</strong>
                    </div>
                </section>
            </aside>
        </div>
    </div>
    <script>
        const messageList = document.querySelector('.chat-message-list');
        if (messageList) {
            messageList.scrollTop = messageList.scrollHeight;
        }

    </script>
</x-shell>

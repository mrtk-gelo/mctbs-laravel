<x-shell title="Conversation | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'messages'])</x-slot:nav>

    <div class="container chat-page">
        <header class="chat-page-heading">
            <div>
                <span class="chat-eyebrow">MCTBS Connect</span>
                <h1>Your conversations</h1>
                <p>Messages with {{ $conversation->house->name }} are kept here.</p>
            </div>
            <a class="text-muted fs-sm" href="{{ route('guest.conversations.index') }}">
                <i class="fa fa-arrow-left" aria-hidden="true"></i> All conversations
            </a>
        </header>

        <div class="chat-workspace chat-workspace--guest chat-workspace--thread">
            <aside class="chat-sidebar" aria-label="Your conversations">
                <div class="chat-sidebar-header">
                    <div>
                        <h2>Chats</h2>
                        <p>{{ $conversations->count() }} {{ \Illuminate\Support\Str::plural('conversation', $conversations->count()) }}</p>
                    </div>
                    <a class="chat-new-button" href="{{ route('guest.conversations.index') }}#start-conversation" aria-label="Start a new conversation" title="Start a new conversation">
                        <i class="fa fa-pen-to-square" aria-hidden="true"></i>
                    </a>
                </div>
                <nav class="chat-conversation-list" aria-label="Conversation list">
                    @forelse ($conversations as $thread)
                        @include('conversations.partials.list-item', [
                            'conversation' => $thread,
                            'conversationRoute' => 'guest.conversations.show',
                            'listType' => 'guest',
                            'activeConversationId' => $conversation->conversation_id,
                        ])
                    @empty
                        <div class="chat-list-empty">
                            <span class="chat-empty-icon"><i class="fa fa-comments" aria-hidden="true"></i></span>
                            <strong>No chats yet</strong>
                            <span>Your messages with property teams will appear here.</span>
                        </div>
                    @endforelse
                </nav>
            </aside>

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
                </header>

                <div class="chat-thread-welcome">
                    <span class="chat-welcome-avatar"><i class="fa fa-headset" aria-hidden="true"></i></span>
                    <p><strong>MCTBS Connect</strong><br>{{ $settings->welcome_message }}</p>
                </div>

                <details class="chat-context">
                    <summary><i class="fa fa-circle-info" aria-hidden="true"></i> Booking and property information</summary>
                    <div class="chat-context-body">
                        @if ($context['booking'])
                            <p><strong>{{ $context['booking']->unit->house->name }} · {{ $context['booking']->unit->unit_type }}</strong></p>
                            <p>{{ $context['booking']->check_in->format('M j, Y') }} - {{ $context['booking']->check_out->format('M j, Y') }}</p>
                            <p>Booking {{ $context['booking']->booking_reference ?: '#'.$context['booking']->booking_id }} · {{ $context['booking']->status }}</p>
                            <p>Payment: {{ $context['paymentStatus'] ?: 'No payment record' }}</p>
                            @if ($context['bookingUrl'])
                                <a href="{{ $context['bookingUrl'] }}" class="btn btn-outline-primary btn-sm">View booking</a>
                            @endif
                            @if ($context['ticketUrl'])
                                <a href="{{ $context['ticketUrl'] }}" class="btn btn-outline-primary btn-sm">View ticket</a>
                            @endif
                        @else
                            <p>No booking is linked to this property yet.</p>
                        @endif
                        <p>{{ $conversation->house->address }}{{ $conversation->house->contact_number ? ' · '.$conversation->house->contact_number : '' }}</p>
                        @if ($context['policies'])
                            <p class="chat-context-prewrap">{{ $context['policies'] }}</p>
                        @endif
                        @if ($context['amenities']->isNotEmpty())
                            <p>Amenities: {{ $context['amenities']->pluck('name')->join(', ') }}</p>
                        @endif
                    </div>
                </details>

                <section class="chat-message-list" aria-label="Conversation messages" aria-live="polite">
                    @if ($conversation->messages->isNotEmpty())
                        @include('conversations.partials.messages', [
                            'messages' => $conversation->messages,
                            'viewerUserId' => auth()->id(),
                        ])
                    @else
                        <div class="chat-list-empty">
                            <span class="chat-empty-icon"><i class="fa fa-message" aria-hidden="true"></i></span>
                            <strong>Start the conversation</strong>
                            <span>Send a message to the property team below.</span>
                        </div>
                    @endif
                </section>

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

                    <form method="POST" action="{{ route('guest.conversations.reply', $conversation->conversation_id) }}" class="chat-composer-form">
                        @csrf
                        <label class="sr-only" for="message">Write a reply</label>
                        <textarea class="form-control" id="message" name="message" rows="2" maxlength="5000" required placeholder="Write a message..." data-message-composer></textarea>
                        @error('message')<div class="text-danger fs-sm mt-1">{{ $message }}</div>@enderror
                        <button class="btn btn-primary" type="submit" aria-label="Send reply">
                            <i class="fa fa-paper-plane" aria-hidden="true"></i> Send
                        </button>
                    </form>
                    <p class="chat-composer-note"><i class="fa fa-lock" aria-hidden="true"></i> Your reply is visible only to you and the property team.</p>
                </div>
            </main>
        </div>
    </div>
    <script>
        const messageList = document.querySelector('.chat-message-list');
        if (messageList) {
            messageList.scrollTop = messageList.scrollHeight;
        }

        const refreshConversation = () => {
            const composer = document.querySelector('[data-message-composer]');
            if (composer && (composer.value.trim() || document.activeElement === composer)) {
                window.setTimeout(refreshConversation, 15000);
                return;
            }
            window.location.reload();
        };
        window.setTimeout(refreshConversation, 15000);
    </script>
</x-shell>

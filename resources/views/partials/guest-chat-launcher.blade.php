<details class="guest-chat-launcher" data-guest-chat-launcher>
    <summary class="guest-chat-trigger" aria-label="Open MCTBS Connect chat">
        <i class="fa fa-message" aria-hidden="true"></i>
        <span>Chat with us</span>
        @if ($unreadChatCount > 0)
            <span class="guest-chat-count" aria-label="{{ $unreadChatCount }} unread conversations">{{ $unreadChatCount > 99 ? '99+' : $unreadChatCount }}</span>
        @endif
    </summary>
    <section class="guest-chat-panel" aria-label="MCTBS Connect conversations">
        <header class="guest-chat-panel-header">
            <span class="guest-chat-brand-icon"><i class="fa fa-headset" aria-hidden="true"></i></span>
            <div>
                <h2>MCTBS Connect</h2>
                <p>Property questions and guest support</p>
            </div>
            <button class="guest-chat-close" type="button" aria-label="Close chat" data-close-guest-chat>
                <i class="fa fa-xmark" aria-hidden="true"></i>
            </button>
        </header>
        <div class="guest-chat-panel-body">
            @auth
                @if (auth()->user()->role?->name === 'Guest')
                    <div class="guest-chat-welcome">
                        <span class="guest-chat-welcome-icon"><i class="fa fa-comments" aria-hidden="true"></i></span>
                        <p>Welcome back. Continue a conversation or start a new chat with a property team.</p>
                    </div>
                    @forelse ($recentConversations as $conversation)
                        <a class="guest-chat-item" href="{{ route('guest.conversations.show', $conversation->conversation_id) }}">
                            <span class="guest-chat-item-title">
                                <span>{{ $conversation->subject ?: $conversation->house->name }}</span>
                                @if ($conversation->unread_message_count > 0)
                                    <span class="guest-chat-count">{{ $conversation->unread_message_count }}</span>
                                @endif
                            </span>
                            <span class="guest-chat-item-preview">
                                {{ $conversation->latestMessage ? \Illuminate\Support\Str::limit($conversation->latestMessage->message, 70) : $conversation->house->name }}
                            </span>
                        </a>
                    @empty
                        <p class="text-muted fs-sm mb-2">Your conversation history will appear here.</p>
                    @endforelse
                    <div class="guest-chat-panel-actions">
                        <a class="btn btn-primary" href="{{ route('guest.conversations.index') }}">View all conversations</a>
                        <a class="btn btn-outline-primary" href="{{ route('guest.conversations.index') }}#start-conversation">Start a conversation</a>
                    </div>
                @endif
            @else
                <div
                    class="guest-chat-public"
                    data-public-chat-widget
                    data-context-url="{{ route('public.visitor-chat.context') }}"
                    data-answer-url="{{ route('public.visitor-chat.answer') }}"
                    data-fixed-house-id="{{ $chatHouseId ?? '' }}"
                    data-fixed-unit-id="{{ $chatUnitId ?? '' }}"
                >
                    <div class="guest-chat-transcript" data-chat-transcript aria-live="polite">
                        <article class="guest-chat-message guest-chat-message--assistant">
                            <span>MCTBS Connect</span>
                            <p>Hi! Choose a question below or ask something about this property. I can answer from the details currently saved in MCTBS.</p>
                        </article>
                    </div>

                    @if (! $chatHouseId)
                        <label class="sr-only" for="guest-chat-house">Choose a property</label>
                        <select class="form-control guest-chat-select" id="guest-chat-house" data-chat-house-select>
                            <option value="">Choose a property to see its FAQs</option>
                            @foreach ($chatHouses as $availableHouse)
                                <option value="{{ $availableHouse->house_id }}">{{ $availableHouse->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="hidden" value="{{ $chatHouseId }}" data-chat-house-select>
                    @endif

                    @if (! $chatUnitId)
                        <div class="guest-chat-unit-picker" data-chat-unit-picker @if (! $chatHouseId) hidden @endif>
                            <label class="sr-only" for="guest-chat-unit">Choose a unit</label>
                            <select class="form-control guest-chat-select" id="guest-chat-unit" data-chat-unit-select>
                                <option value="">General property question</option>
                            </select>
                        </div>
                    @else
                        <input type="hidden" value="{{ $chatUnitId }}" data-chat-unit-select>
                    @endif

                    <div class="guest-chat-faqs" data-chat-faqs aria-label="Quick questions" @if (! $chatHouseId) hidden @endif>
                        <span class="guest-chat-section-label">Quick questions</span>
                        @foreach ($chatQuickQuestions as $question)
                            <button class="guest-chat-question" type="button" data-chat-question="{{ $question }}">
                                <i class="fa fa-circle-question" aria-hidden="true"></i>
                                <span>{{ $question }}</span>
                                <i class="fa fa-arrow-right" aria-hidden="true"></i>
                            </button>
                        @endforeach
                        @foreach ($chatFaqs as $faq)
                            <button class="guest-chat-question" type="button" data-chat-question="{{ $faq->question }}">
                                <i class="fa fa-circle-question" aria-hidden="true"></i>
                                <span>{{ $faq->question }}</span>
                                <i class="fa fa-arrow-right" aria-hidden="true"></i>
                            </button>
                        @endforeach
                    </div>

                    <form class="guest-chat-question-form" method="POST" action="{{ route('public.visitor-chat.answer') }}" data-chat-question-form>
                        @csrf
                        <label class="sr-only" for="guest-chat-question">Ask a question</label>
                        <input
                            class="form-control"
                            id="guest-chat-question"
                            name="question"
                            maxlength="5000"
                            autocomplete="off"
                            placeholder="Ask a question..."
                            data-chat-question-input
                        >
                        <button class="btn btn-primary" type="submit" aria-label="Send question">
                            <i class="fa fa-paper-plane" aria-hidden="true"></i>
                        </button>
                    </form>

                    <form
                        class="guest-chat-contact-form"
                        method="POST"
                        action="{{ route('public.visitor-conversations.store') }}"
                        data-chat-contact-form
                        hidden
                    >
                        @csrf
                        <input type="hidden" name="house_id" value="{{ $chatHouseId ?? '' }}" data-chat-contact-house>
                        <input type="hidden" name="unit_id" value="{{ $chatUnitId ?? '' }}" data-chat-contact-unit>
                        <input type="hidden" name="subject" value="Property question" data-chat-contact-subject>
                        <input type="hidden" name="message" value="" data-chat-contact-message>
                        <p class="guest-chat-contact-prompt" data-chat-contact-prompt></p>
                        <label for="guest-chat-name">Your name</label>
                        <input class="form-control" id="guest-chat-name" name="name" maxlength="100" required autocomplete="name">
                        <label for="guest-chat-email">Email address</label>
                        <input class="form-control" id="guest-chat-email" name="email" type="email" maxlength="255" required autocomplete="email">
                        <button class="btn btn-primary" type="submit">Send to the property team</button>
                    </form>

                    <div class="guest-chat-public-footer">
                        <span><i class="fa fa-lock" aria-hidden="true"></i> Your messages stay in MCTBS</span>
                        <a href="{{ route('login') }}">Already have a booking? Sign in</a>
                    </div>
                </div>

                @if ($visitorConversations->isNotEmpty())
                    <div class="guest-chat-recent">
                        <span class="guest-chat-section-label">Recent conversations</span>
                        @foreach ($visitorConversations as $conversation)
                            <a class="guest-chat-item" href="{{ route('public.visitor-conversations.show', $conversation->conversation_id) }}">
                                <span class="guest-chat-item-title">{{ $conversation->subject ?: $conversation->house->name }}</span>
                                <span class="guest-chat-item-preview">
                                    {{ $conversation->latestMessage ? \Illuminate\Support\Str::limit($conversation->latestMessage->message, 70) : $conversation->house->name }}
                                </span>
                            </a>
                        @endforeach
                        <a class="btn btn-outline-primary" href="{{ route('public.visitor-conversations.index') }}">View all conversations</a>
                    </div>
                @endif
            @endauth
        </div>
    </section>
</details>

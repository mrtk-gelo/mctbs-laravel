<details class="guest-chat-launcher">
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
                @endif
            @else
                <div class="guest-chat-welcome">
                    <span class="guest-chat-welcome-icon"><i class="fa fa-comments" aria-hidden="true"></i></span>
                    <p>Browse approved unit answers or sign in as a guest to save a conversation and contact the property team.</p>
                </div>
            @endauth
        </div>
        <div class="guest-chat-panel-actions">
            @if ($unit)
                <a class="btn btn-outline-primary" href="#unit-faqs">Ask about {{ $unit->unit_type }}</a>
            @endif
            @auth
                @if (auth()->user()->role?->name === 'Guest')
                    <a class="btn btn-primary" href="{{ route('guest.conversations.index') }}">All conversations</a>
                    <a class="btn btn-outline-primary" href="{{ route('guest.conversations.index') }}#start-conversation">Start a conversation</a>
                @endif
            @else
                <a class="btn btn-primary" href="{{ route('login') }}">Guest sign in</a>
            @endauth
        </div>
    </section>
</details>

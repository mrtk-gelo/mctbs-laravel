<style>
    .guest-chat-launcher {
        position: fixed;
        z-index: 1050;
        right: 24px;
        bottom: 24px;
        font-family: inherit;
    }
    .guest-chat-launcher summary {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        min-width: 62px;
        height: 56px;
        padding: 0 18px;
        border-radius: 999px;
        background: var(--primary, #d97706);
        color: #fff;
        box-shadow: 0 8px 26px rgb(15 23 42 / 24%);
        cursor: pointer;
        font-weight: 700;
        list-style: none;
    }
    .guest-chat-launcher summary::-webkit-details-marker { display: none; }
    .guest-chat-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
        border-radius: 999px;
        background: #dc2626;
        color: #fff;
        font-size: 11px;
    }
    .guest-chat-panel {
        position: absolute;
        right: 0;
        bottom: 68px;
        width: min(360px, calc(100vw - 32px));
        max-height: min(520px, calc(100vh - 120px));
        overflow-y: auto;
        border: 1px solid var(--border, #d1d5db);
        border-radius: 14px;
        background: var(--card, #fff);
        box-shadow: 0 16px 42px rgb(15 23 42 / 20%);
    }
    .guest-chat-panel-header { padding: 18px; border-bottom: 1px solid var(--border, #d1d5db); }
    .guest-chat-panel-header h2 { margin: 0; font-size: 17px; }
    .guest-chat-panel-header p { margin: 5px 0 0; color: var(--text-muted, #6b7280); font-size: 13px; }
    .guest-chat-panel-body { padding: 12px; }
    .guest-chat-item {
        display: block;
        padding: 11px 12px;
        border-radius: 9px;
        color: inherit;
        text-decoration: none;
    }
    .guest-chat-item:hover { background: var(--bg, #f3f4f6); }
    .guest-chat-item-title { display: flex; justify-content: space-between; gap: 10px; font-size: 13px; font-weight: 700; }
    .guest-chat-item-preview { margin-top: 4px; color: var(--text-muted, #6b7280); font-size: 12px; }
    .guest-chat-panel-actions { display: grid; gap: 8px; padding: 0 12px 12px; }
    .guest-chat-panel-actions a { text-align: center; }
    @media (max-width: 480px) {
        .guest-chat-launcher { right: 14px; bottom: 14px; }
    }
</style>

<details class="guest-chat-launcher">
    <summary aria-label="Open MCTBS chat">
        <i class="fa fa-comments" aria-hidden="true"></i>
        <span>Chat</span>
        @if ($unreadChatCount > 0)
            <span class="guest-chat-count" aria-label="{{ $unreadChatCount }} unread conversations">{{ $unreadChatCount > 99 ? '99+' : $unreadChatCount }}</span>
        @endif
    </summary>
    <section class="guest-chat-panel" aria-label="MCTBS conversations">
        <header class="guest-chat-panel-header">
            <h2>MCTBS Connect</h2>
            <p>Ask a question or continue a conversation with a property.</p>
        </header>
        <div class="guest-chat-panel-body">
            @auth
                @if (auth()->user()->role?->name === 'Guest')
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
                <p class="text-muted fs-sm mb-2">Browse approved answers or sign in to save a conversation and contact the property team.</p>
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

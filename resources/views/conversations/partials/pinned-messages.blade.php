<dialog class="chat-pinned-modal" data-chat-pinned-modal aria-labelledby="chat-pinned-modal-title">
    <header class="chat-pinned-modal-header">
        <div>
            <h3 id="chat-pinned-modal-title">Pinned messages</h3>
            <p><span data-chat-pin-count>{{ $pinnedMessages->count() }}</span> saved in this conversation</p>
        </div>
        <button type="button" class="chat-pinned-close" aria-label="Close pinned messages" data-chat-pinned-close>
            <i class="fa fa-xmark" aria-hidden="true"></i>
        </button>
    </header>
    <div class="chat-pinned-list" data-chat-pinned-list>
        @forelse ($pinnedMessages as $pinnedMessage)
            @php
                $pinnedSenderName = match ($pinnedMessage->sender_type) {
                    'assistant' => 'MCTBS Connect · automated answer',
                    'system' => 'MCTBS Connect',
                    'visitor' => $visitorName ?? 'Public visitor',
                    default => trim(($pinnedMessage->sender?->first_name ?? '').' '.($pinnedMessage->sender?->last_name ?? '')) ?: 'Property team',
                };
            @endphp
            <article class="chat-pinned-item" data-pinned-message-id="{{ $pinnedMessage->message_id }}">
                <span class="chat-pinned-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($pinnedSenderName, 0, 1)) }}</span>
                <button
                    type="button"
                    class="chat-pinned-message-link"
                    data-chat-jump-to-message
                    data-message-id="{{ $pinnedMessage->message_id }}"
                >
                    <div class="chat-pinned-meta">
                        <strong>{{ $pinnedSenderName }}</strong>
                        <time datetime="{{ $pinnedMessage->sent_at->toIso8601String() }}">{{ $pinnedMessage->sent_at->format('M j, g:i A') }}</time>
                    </div>
                    <p class="chat-pinned-bubble">{{ $pinnedMessage->message }}</p>
                </button>
                <button
                    type="button"
                    class="chat-message-action chat-pinned-unpin"
                    aria-label="Unpin message"
                    data-chat-pin-toggle
                    data-message-id="{{ $pinnedMessage->message_id }}"
                    data-is-pinned="true"
                    data-pin-url="{{ route($pinRoute, ['conversationId' => $conversationId, 'messageId' => $pinnedMessage->message_id]) }}"
                >
                    <i class="fa fa-thumbtack" aria-hidden="true"></i>
                </button>
            </article>
        @empty
            <p class="chat-pinned-empty" data-chat-pins-empty>No messages pinned yet.</p>
        @endforelse
    </div>
</dialog>

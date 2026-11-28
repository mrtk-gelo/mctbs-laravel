<section class="chat-pinned-panel" aria-label="Pinned messages" data-chat-pinned-panel hidden>
    <header class="chat-pinned-panel-header">
        <h3>Pinned messages <span data-chat-pin-count>{{ $pinnedMessages->count() }}</span></h3>
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
            <div class="chat-pinned-item" data-pinned-message-id="{{ $pinnedMessage->message_id }}">
                <button
                    type="button"
                    class="chat-pinned-message-link"
                    data-chat-jump-to-message
                    data-message-id="{{ $pinnedMessage->message_id }}"
                >
                    <strong>{{ $pinnedSenderName }}</strong>
                    <span>{{ \Illuminate\Support\Str::limit($pinnedMessage->message, 150) }}</span>
                </button>
                <button
                    type="button"
                    class="chat-message-action"
                    data-chat-pin-toggle
                    data-message-id="{{ $pinnedMessage->message_id }}"
                    data-is-pinned="true"
                    data-pin-url="{{ route($pinRoute, ['conversationId' => $conversationId, 'messageId' => $pinnedMessage->message_id]) }}"
                >Unpin</button>
            </div>
        @empty
            <p class="chat-pinned-empty" data-chat-pins-empty>No messages pinned yet.</p>
        @endforelse
    </div>
</section>

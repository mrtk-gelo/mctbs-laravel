@foreach (($hideSystemMessages ?? false) ? $messages->where('sender_type', '!=', 'system') : $messages as $message)
    @if ($message->unsent_at !== null)
        @continue
    @endif

    @php
        $isSystemMessage = $message->sender_type === 'system';
        $isOutgoing = ! $isSystemMessage && (
            ($message->sender_id !== null && (int) $message->sender_id === (int) $viewerUserId)
            || (
                $message->sender_type === 'visitor'
                && (int) ($conversationGuestId ?? 0) === (int) ($viewerUserId ?? 0)
            )
        );
        $senderName = match ($message->sender_type) {
            'assistant' => 'MCTBS Connect · automated answer',
            'system' => 'MCTBS Connect',
            'visitor' => $visitorName ?? 'Public visitor',
            default => trim(($message->sender?->first_name ?? '').' '.($message->sender?->last_name ?? '')) ?: 'Property team',
        };
        $replySenderName = $message->replyToMessage
            ? match ($message->replyToMessage->sender_type) {
                'assistant' => 'MCTBS Connect · automated answer',
                'system' => 'MCTBS Connect',
                'visitor' => $visitorName ?? 'Public visitor',
                default => trim(($message->replyToMessage->sender?->first_name ?? '').' '.($message->replyToMessage->sender?->last_name ?? '')) ?: 'Property team',
            }
            : null;
        $replyText = $message->replyToMessage?->unsent_at !== null
            ? 'This message was unsent.'
            : $message->replyToMessage?->message;
        $messageId = (int) $message->message_id;
        $isPinned = in_array($messageId, $pinnedMessageIds ?? [], true);
        $canUnsend = $isOutgoing && in_array($message->sender_type, ['user', 'visitor'], true);
        $canPinMessage = ($canPin ?? false) && ! $isOutgoing;
    @endphp

    <article
        class="chat-message-row{{ $isSystemMessage ? ' chat-message-row--system' : ($isOutgoing ? ' chat-message-row--outgoing' : ' chat-message-row--incoming') }}"
        data-message-id="{{ $messageId }}"
        data-is-pinned="{{ $isPinned ? 'true' : 'false' }}"
    >
        <div class="chat-message-meta">
            <span>{{ $senderName }}</span>
            <time datetime="{{ $message->sent_at->toIso8601String() }}">{{ $message->sent_at->format('M j, g:i A') }}</time>
        </div>
        <span class="chat-message-pin-indicator" data-message-pin-indicator @if (! $isPinned) hidden @endif>
            <i class="fa fa-thumbtack" aria-hidden="true"></i> Pinned for you
        </span>
        @if ($message->replyToMessage)
            <button
                type="button"
                class="chat-message-reply-quote"
                data-chat-jump-to-message
                data-message-id="{{ $message->replyToMessage->message_id }}"
            >
                <strong>Replying to {{ $replySenderName }}</strong>
                <span>{{ \Illuminate\Support\Str::limit($replyText, 120) }}</span>
            </button>
        @endif
        <div class="chat-message-bubble">{{ $message->message }}</div>
        <details class="chat-message-menu" data-chat-message-menu>
            <summary class="chat-message-menu-trigger" aria-label="Message options" title="Message options">
                <i class="fa fa-ellipsis" aria-hidden="true"></i>
            </summary>
            <div class="chat-message-menu-panel">
                <button
                    class="chat-message-action"
                    type="button"
                    data-chat-reply-message
                    data-message-id="{{ $messageId }}"
                ><i class="fa fa-reply" aria-hidden="true"></i> Reply</button>
                @if ($canPinMessage)
                    <button
                        class="chat-message-action"
                        type="button"
                        data-chat-pin-toggle
                        data-message-id="{{ $messageId }}"
                        data-is-pinned="{{ $isPinned ? 'true' : 'false' }}"
                        data-pin-url="{{ route($pinRoute, ['conversationId' => $conversationId, 'messageId' => $messageId]) }}"
                    ><i class="fa fa-thumbtack" aria-hidden="true"></i> {{ $isPinned ? 'Unpin' : 'Pin' }}</button>
                @endif
                @if ($canUnsend)
                    <button
                        class="chat-message-action is-danger"
                        type="button"
                        data-chat-unsend
                        data-message-id="{{ $messageId }}"
                        data-unsend-url="{{ route($unsendRoute, ['conversationId' => $conversationId, 'messageId' => $messageId]) }}"
                    ><i class="fa fa-trash" aria-hidden="true"></i> Unsend</button>
                @endif
            </div>
        </details>
    </article>
@endforeach

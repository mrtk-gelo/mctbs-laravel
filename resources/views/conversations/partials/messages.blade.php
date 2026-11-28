@foreach (($hideSystemMessages ?? false) ? $messages->where('sender_type', '!=', 'system') : $messages as $message)
    @php
        $isSystemMessage = in_array($message->sender_type, ['assistant', 'system'], true);
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
        $messageId = (int) $message->message_id;
        $isPinned = in_array($messageId, $pinnedMessageIds ?? [], true);
        $messageReactions = $reactionSummaries[(string) $messageId] ?? [];
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
            <div class="chat-message-reply-quote">
                <strong>Replying to {{ $replySenderName }}</strong>
                <span>{{ \Illuminate\Support\Str::limit($message->replyToMessage->message, 120) }}</span>
            </div>
        @endif
        <div class="chat-message-bubble">{{ $message->message }}</div>
        <div class="chat-message-interactions">
            <div class="chat-reaction-list" data-chat-reaction-list>
                @foreach ($messageReactions as $reaction)
                    <button
                        class="chat-reaction-chip{{ $reaction['reactedByMe'] ? ' is-selected' : '' }}"
                        type="button"
                        data-chat-react
                        data-emoji="{{ $reaction['emoji'] }}"
                        data-reaction-url="{{ route($reactionRoute, ['conversationId' => $conversationId, 'messageId' => $messageId]) }}"
                        aria-pressed="{{ $reaction['reactedByMe'] ? 'true' : 'false' }}"
                        aria-label="{{ $reaction['emoji'] }} reaction, {{ $reaction['count'] }}"
                    >
                        <span>{{ $reaction['emoji'] }}</span><span>{{ $reaction['count'] }}</span>
                    </button>
                @endforeach
            </div>
            <details class="chat-reaction-picker">
                <summary aria-label="React to message" title="React to message">☺</summary>
                <div class="chat-reaction-picker-panel">
                    @foreach (\App\Services\ConversationService::REACTION_EMOJIS as $emoji)
                        <button
                            type="button"
                            data-chat-react
                            data-emoji="{{ $emoji }}"
                            data-reaction-url="{{ route($reactionRoute, ['conversationId' => $conversationId, 'messageId' => $messageId]) }}"
                            aria-label="React with {{ $emoji }}"
                        >{{ $emoji }}</button>
                    @endforeach
                </div>
            </details>
            <button
                class="chat-message-action"
                type="button"
                data-chat-reply-message
                data-message-id="{{ $messageId }}"
            >Reply</button>
            @if ($canPin ?? false)
                <button
                    class="chat-message-action"
                    type="button"
                    data-chat-pin-toggle
                    data-message-id="{{ $messageId }}"
                    data-is-pinned="{{ $isPinned ? 'true' : 'false' }}"
                    data-pin-url="{{ route($pinRoute, ['conversationId' => $conversationId, 'messageId' => $messageId]) }}"
                >
                    <i class="fa fa-thumbtack" aria-hidden="true"></i>
                    <span>{{ $isPinned ? 'Unpin' : 'Pin' }}</span>
                </button>
            @endif
        </div>
    </article>
@endforeach

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
    @endphp

    <article
        class="chat-message-row{{ $isSystemMessage ? ' chat-message-row--system' : ($isOutgoing ? ' chat-message-row--outgoing' : ' chat-message-row--incoming') }}"
        data-message-id="{{ $message->message_id }}"
    >
        <div class="chat-message-meta">
            <span>{{ $senderName }}</span>
            <time datetime="{{ $message->sent_at->toIso8601String() }}">{{ $message->sent_at->format('M j, g:i A') }}</time>
        </div>
        <div class="chat-message-bubble">{{ $message->message }}</div>
    </article>
@endforeach

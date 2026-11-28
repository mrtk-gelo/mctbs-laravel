@php
    $isStaffInbox = $listType === 'staff';
    $guestName = $conversation->guest
        ? trim($conversation->guest->first_name.' '.$conversation->guest->last_name)
        : (trim((string) $conversation->visitor_name) ?: 'Public visitor');
    $avatarName = $isStaffInbox
        ? $guestName
        : $conversation->house->name;
    $title = $isStaffInbox
        ? $avatarName
        : ($conversation->subject ?: $conversation->house->name);
    $subtitle = $isStaffInbox
        ? $conversation->house->name.($conversation->unit ? ' · '.$conversation->unit->unit_type : '')
        : ($conversation->unit ? $conversation->unit->unit_type : 'Property conversation');
    $previewMessage = $isStaffInbox ? $conversation->latestHumanMessage : $conversation->latestMessage;
    $lastMessageAt = $previewMessage?->sent_at ?? $conversation->created_at;
    $isActive = (int) $activeConversationId === (int) $conversation->conversation_id;
    $bookingStatus = $conversation->booking_status
        ? \App\Support\BookingStatus::canonical($conversation->booking_status)
        : 'No booking';
    $statusClass = \Illuminate\Support\Str::slug($bookingStatus);
    $statusHint = $bookingStatus === \App\Support\BookingStatus::ACCEPTED
        ? 'Dates are reserved while the guest submits the downpayment.'
        : $bookingStatus;
    $conversationUrl = route($conversationRoute, array_merge(
        ['conversationId' => $conversation->conversation_id],
        $isStaffInbox ? request()->only('filter') : []
    ));
@endphp

<a
    class="chat-list-item{{ $isActive ? ' is-active' : '' }}"
    href="{{ $conversationUrl }}"
    data-chat-inbox-item
    data-chat-guest-name="{{ \Illuminate\Support\Str::lower($guestName) }}"
    data-chat-unread="{{ ($conversation->unread_message_count ?? 0) > 0 ? 'true' : 'false' }}"
    data-chat-anonymous="{{ $conversation->guest_user_id === null ? 'true' : 'false' }}"
    @if ($isActive) aria-current="page" @endif
>
    <span class="chat-list-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($avatarName, 0, 1)) }}</span>
    <span class="chat-list-copy">
        <span class="chat-list-topline">
            <strong>{{ $title }}</strong>
            @if ($lastMessageAt)
                <time datetime="{{ $lastMessageAt->toIso8601String() }}">{{ $lastMessageAt->diffForHumans() }}</time>
            @endif
        </span>
        @if ($isStaffInbox && $conversation->subject)
            <span class="chat-list-subject">{{ $conversation->subject }}</span>
        @endif
        <span class="chat-list-subtitle">{{ $subtitle }}</span>
        <span class="chat-list-preview">
            {{ $previewMessage ? \Illuminate\Support\Str::limit($previewMessage->message, 76) : 'No messages yet' }}
        </span>
        <span class="chat-list-foot">
            <span class="chat-status chat-status--{{ $statusClass }}" title="{{ $statusHint }}">{{ $bookingStatus }}</span>
            @if (($conversation->unread_message_count ?? 0) > 0)
                <span class="chat-unread-count" aria-label="{{ $conversation->unread_message_count }} unread messages">{{ $conversation->unread_message_count }}</span>
            @endif
        </span>
    </span>
</a>

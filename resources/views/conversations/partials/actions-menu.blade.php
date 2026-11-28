<details class="chat-actions-menu">
    <summary class="chat-actions-trigger" aria-label="Conversation actions" title="Conversation actions">
        <i class="fa fa-ellipsis" aria-hidden="true"></i>
    </summary>
    <div class="chat-actions-menu-panel">
        @if ($showPinnedMessages ?? false)
            <button type="button" data-chat-pinned-toggle>
                <i class="fa fa-thumbtack" aria-hidden="true"></i>
                View pinned messages <span data-chat-pin-count>{{ $pinnedCount }}</span>
            </button>
        @endif
        @if ($folder === 'archived')
            <form method="POST" action="{{ route($restoreRoute, $conversationId) }}">
                @csrf
                <button type="submit"><i class="fa fa-inbox" aria-hidden="true"></i> Move to Inbox</button>
            </form>
        @else
            <form method="POST" action="{{ route($archiveRoute, $conversationId) }}">
                @csrf
                <button type="submit"><i class="fa fa-box-archive" aria-hidden="true"></i> Archive</button>
            </form>
        @endif
        <form
            method="POST"
            action="{{ route($deleteRoute, $conversationId) }}"
            onsubmit="return confirm('Delete this conversation from your chat list permanently? Other participants will keep their copy.');"
        >
            @csrf
            <button class="is-danger" type="submit"><i class="fa fa-trash" aria-hidden="true"></i> Delete for me</button>
        </form>
        <p>Deleting permanently removes this chat from your list. Other participants keep their copy.</p>
    </div>
</details>

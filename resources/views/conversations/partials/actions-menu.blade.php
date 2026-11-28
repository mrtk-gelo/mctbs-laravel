<details class="chat-actions-menu">
    <summary class="chat-actions-trigger" aria-label="Conversation actions" title="Conversation actions">
        <i class="fa fa-ellipsis" aria-hidden="true"></i>
    </summary>
    <div class="chat-actions-menu-panel">
        @if ($folder === 'deleted')
            <form method="POST" action="{{ route($restoreRoute, $conversationId) }}">
                @csrf
                <button type="submit"><i class="fa fa-rotate-left" aria-hidden="true"></i> Restore to Inbox</button>
            </form>
        @else
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
            <form method="POST" action="{{ route($deleteRoute, $conversationId) }}">
                @csrf
                <button class="is-danger" type="submit"><i class="fa fa-trash" aria-hidden="true"></i> Move to Deleted</button>
            </form>
        @endif
        <p>Only changes your chat list. Other participants keep their conversation.</p>
    </div>
</details>

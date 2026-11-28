<aside class="chat-sidebar" aria-label="Your conversations">
    <div class="chat-sidebar-header">
        <div>
            <h2>Chats</h2>
            <p>{{ $conversations->count() }} {{ \Illuminate\Support\Str::plural('conversation', $conversations->count()) }}</p>
        </div>
        <div class="chat-sidebar-header-actions">
            <a
                class="chat-new-button"
                href="{{ route('guest.conversations.index') }}#start-conversation"
                aria-label="Start a new conversation"
                title="Start a new conversation"
            >
                <i class="fa fa-pen-to-square" aria-hidden="true"></i>
            </a>
            @include('conversations.partials.folder-menu', [
                'folder' => $folder,
                'inboxUrl' => route('guest.conversations.index', ['folder' => 'inbox']),
                'archiveUrl' => route('guest.conversations.index', ['folder' => 'archived']),
            ])
        </div>
    </div>

    <div class="chat-sidebar-section-title">
        <span>{{ $folder === 'archived' ? 'Archived conversations' : 'Recent conversations' }}</span>
        <span class="chat-recent-count">{{ $conversations->count() }}</span>
    </div>
    <nav class="chat-conversation-list" aria-label="Conversation list">
        @forelse ($conversations as $thread)
            @include('conversations.partials.list-item', [
                'conversation' => $thread,
                'conversationRoute' => 'guest.conversations.show',
                'listType' => 'guest',
                'activeConversationId' => $activeConversationId,
            ])
        @empty
            <div class="chat-list-empty">
                <span class="chat-empty-icon"><i class="fa fa-comments" aria-hidden="true"></i></span>
                <strong>{{ $folder === 'inbox' ? 'No chats yet' : 'No archived chats' }}</strong>
                <span>{{ $folder === 'inbox' ? 'Your messages with property teams will appear here.' : 'Chats you archive will appear here.' }}</span>
            </div>
        @endforelse
    </nav>
</aside>

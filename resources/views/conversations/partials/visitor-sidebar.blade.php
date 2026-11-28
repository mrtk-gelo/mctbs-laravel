<aside class="chat-sidebar" aria-label="Your conversations">
    <div class="chat-sidebar-header">
        <div>
            <h2>Chats</h2>
            <p>{{ $conversations->count() }} {{ \Illuminate\Support\Str::plural('conversation', $conversations->count()) }}</p>
        </div>
        <div class="chat-sidebar-header-actions">
            <a class="chat-new-button" href="{{ route('home') }}" aria-label="Browse properties" title="Browse properties">
                <i class="fa fa-house" aria-hidden="true"></i>
            </a>
            @include('conversations.partials.folder-menu', [
                'folder' => $folder,
                'inboxUrl' => route('public.visitor-conversations.index', ['folder' => 'inbox']),
                'archiveUrl' => route('public.visitor-conversations.index', ['folder' => 'archived']),
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
                'conversationRoute' => 'public.visitor-conversations.show',
                'listType' => 'visitor',
                'activeConversationId' => $activeConversationId,
            ])
        @empty
            <div class="chat-list-empty">
                <span class="chat-empty-icon"><i class="fa fa-comments" aria-hidden="true"></i></span>
                <strong>{{ $folder === 'inbox' ? 'No conversations yet' : 'No archived conversations' }}</strong>
                <span>{{ $folder === 'inbox' ? 'Start with a quick question using Chat with us.' : 'Chats you archive will appear here.' }}</span>
            </div>
        @endforelse
    </nav>
</aside>

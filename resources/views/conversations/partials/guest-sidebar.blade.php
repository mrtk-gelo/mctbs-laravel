<aside class="chat-sidebar" aria-label="Your conversations">
    <div class="chat-sidebar-header">
        <div>
            <h2>Chats</h2>
            <p>{{ $conversations->count() }} {{ \Illuminate\Support\Str::plural('conversation', $conversations->count()) }}</p>
        </div>
        <a
            class="chat-new-button"
            href="{{ route('guest.conversations.index') }}#start-conversation"
            aria-label="Start a new conversation"
            title="Start a new conversation"
        >
            <i class="fa fa-pen-to-square" aria-hidden="true"></i>
        </a>
    </div>

    <nav class="chat-folder-links" aria-label="Conversation folders">
        @foreach (['inbox' => ['Inbox', 'inbox'], 'archived' => ['Archived', 'box-archive'], 'deleted' => ['Deleted', 'trash']] as $folderKey => [$folderLabel, $folderIcon])
            <a
                class="chat-folder-link{{ $folder === $folderKey ? ' is-active' : '' }}"
                href="{{ route('guest.conversations.index', ['folder' => $folderKey]) }}"
                @if ($folder === $folderKey) aria-current="page" @endif
            >
                <span><i class="fa fa-{{ $folderIcon }}" aria-hidden="true"></i> {{ $folderLabel }}</span>
                <span>{{ $folderCounts[$folderKey] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="chat-sidebar-section-title">
        <span>{{ ['inbox' => 'Recent conversations', 'archived' => 'Archived conversations', 'deleted' => 'Deleted conversations'][$folder] }}</span>
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
                <strong>{{ $folder === 'inbox' ? 'No chats yet' : 'Nothing here yet' }}</strong>
                <span>{{ $folder === 'inbox' ? 'Your messages with property teams will appear here.' : 'Conversations in this folder will appear here.' }}</span>
            </div>
        @endforelse
    </nav>
</aside>

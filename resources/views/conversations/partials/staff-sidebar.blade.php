<aside class="chat-sidebar" aria-label="Team conversations">
    <div class="chat-sidebar-header">
        <div>
            <h2>Inbox</h2>
            <p>{{ $conversations->count() }} {{ \Illuminate\Support\Str::plural('chat', $conversations->count()) }}</p>
        </div>
        <a
            class="chat-new-button"
            href="{{ route($createRoute, ['filter' => $filter, 'folder' => 'inbox']) }}"
            aria-label="Start a new chat"
            title="Start a new chat"
        >
            <i class="fa fa-plus" aria-hidden="true"></i>
        </a>
    </div>

    <form class="chat-filter-form" role="search" data-chat-search-form>
        <label class="sr-only" for="chat-search">Search guest names</label>
        <div class="chat-search-field">
            <i class="fa fa-magnifying-glass" aria-hidden="true"></i>
            <input id="chat-search" type="search" placeholder="Search guest names" autocomplete="off" data-chat-search-input>
        </div>
        <button class="chat-search-clear" type="button" aria-label="Clear search" title="Clear search" data-chat-search-clear hidden>
            <i class="fa fa-xmark" aria-hidden="true"></i>
        </button>
    </form>

    @if ($folder === 'inbox')
        <nav class="chat-status-tabs" aria-label="Filter conversations" data-chat-inbox-filters>
            @foreach (['all' => 'All', 'unread' => 'Unread', 'guest' => 'Guest'] as $filterKey => $filterLabel)
                <a
                    class="chat-status-tab{{ $filter === $filterKey ? ' is-active' : '' }}{{ $counts[$filterKey] > 0 ? ' has-chats' : '' }}"
                    href="{{ route($inboxRoute, ['filter' => $filterKey, 'folder' => 'inbox']) }}"
                    @if ($filter === $filterKey) aria-current="page" @endif
                >
                    <span>{{ $filterLabel }}</span>
                    <span class="chat-tab-count">{{ $counts[$filterKey] }}</span>
                </a>
            @endforeach
        </nav>
    @endif

    <nav class="chat-folder-links" aria-label="Conversation folders">
        @foreach (['inbox' => ['Inbox', 'inbox'], 'archived' => ['Archived', 'box-archive'], 'deleted' => ['Deleted', 'trash']] as $folderKey => [$folderLabel, $folderIcon])
            <a
                class="chat-folder-link{{ $folder === $folderKey ? ' is-active' : '' }}"
                href="{{ route($inboxRoute, ['folder' => $folderKey, 'filter' => 'all']) }}"
                @if ($folder === $folderKey) aria-current="page" @endif
            >
                <span><i class="fa fa-{{ $folderIcon }}" aria-hidden="true"></i> {{ $folderLabel }}</span>
                <span>{{ $counts[$folderKey] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="chat-sidebar-section-title">
        <span>{{ ['inbox' => 'Recent conversations', 'archived' => 'Archived conversations', 'deleted' => 'Deleted conversations'][$folder] }}</span>
        <span class="chat-recent-count">{{ $conversations->count() }}</span>
    </div>
    <nav class="chat-conversation-list" aria-label="Inbox conversation list" data-chat-inbox-list>
        @forelse ($conversations as $conversation)
            @include('conversations.partials.list-item', [
                'conversation' => $conversation,
                'conversationRoute' => $conversationRoute,
                'listType' => 'staff',
                'activeConversationId' => $activeConversationId,
            ])
        @empty
            <div class="chat-list-empty">
                <span class="chat-empty-icon"><i class="fa fa-inbox" aria-hidden="true"></i></span>
                <strong>No conversations found</strong>
                <span>New guest messages will appear here.</span>
            </div>
        @endforelse
    </nav>
    <div class="chat-list-empty" data-chat-search-empty hidden>
        <span class="chat-empty-icon"><i class="fa fa-magnifying-glass" aria-hidden="true"></i></span>
        <strong>No matching guests</strong>
        <span>Try another name or clear the search.</span>
    </div>
</aside>

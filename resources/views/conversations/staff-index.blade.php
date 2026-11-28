<x-shell title="Messages | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'messages'])</x-slot:nav>

    @php
        $isStaff = auth()->user()->role->name === 'Staff';
        $conversationRoute = $isStaff ? 'staff.conversations.show' : 'owner.conversations.show';
        $inboxRoute = $isStaff ? 'staff.conversations.index' : 'owner.conversations.index';
    @endphp

    <div class="container chat-page">
        <header class="chat-page-heading">
            <div>
                <span class="chat-eyebrow">MCTBS Connect · Team inbox</span>
                <h1>Messages</h1>
                <p>One shared place to reply to guests across your authorized properties.</p>
            </div>
            <span class="chat-secure-note"><i class="fa fa-shield-halved" aria-hidden="true"></i> Property-scoped inbox</span>
        </header>

        <div class="chat-workspace chat-workspace--staff">
            <aside class="chat-sidebar" aria-label="Team conversations">
                <div class="chat-sidebar-header">
                    <div>
                        <h2>Inbox</h2>
                        <p>{{ $conversations->count() }} {{ \Illuminate\Support\Str::plural('chat', $conversations->count()) }}</p>
                    </div>
                    <span class="chat-sidebar-icon"><i class="fa fa-inbox" aria-hidden="true"></i></span>
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

                <nav class="chat-status-tabs" aria-label="Filter conversations" data-chat-inbox-filters>
                    @foreach (['all' => 'All', 'unread' => 'Unread', 'guest' => 'Guest'] as $filterKey => $filterLabel)
                        <a
                            class="chat-status-tab{{ $filter === $filterKey ? ' is-active' : '' }}{{ $counts[$filterKey] > 0 ? ' has-chats' : '' }}"
                            href="{{ route($inboxRoute, ['filter' => $filterKey]) }}"
                            @if ($filter === $filterKey) aria-current="page" @endif
                        >
                            <span>{{ $filterLabel }}</span>
                            <span class="chat-tab-count">{{ $counts[$filterKey] }}</span>
                        </a>
                    @endforeach
                </nav>

                <nav class="chat-conversation-list" aria-label="Inbox conversation list" data-chat-inbox-list>
                    @forelse ($conversations as $conversation)
                        @include('conversations.partials.list-item', [
                            'conversation' => $conversation,
                            'conversationRoute' => $conversationRoute,
                            'listType' => 'staff',
                            'activeConversationId' => null,
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

            <main class="chat-main chat-inbox-welcome">
                <span class="chat-start-icon"><i class="fa fa-comments" aria-hidden="true"></i></span>
                <span class="chat-eyebrow">Guest support</span>
                <h2>Every conversation, in one place</h2>
                <p>Select a chat to read the full history and reply. Search the guest name or switch between all, unread, and anonymous guest conversations.</p>
                <div class="chat-inbox-tip">
                    <i class="fa fa-lightbulb" aria-hidden="true"></i>
                    <span>Data-backed quick answers appear automatically. Unanswered questions are flagged for the team, and chats stay open for follow-up.</span>
                </div>
            </main>
        </div>
    </div>
</x-shell>

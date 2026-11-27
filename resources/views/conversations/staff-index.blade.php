<x-shell title="Messages | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'messages'])</x-slot:nav>

    @php
        $conversationRoute = auth()->user()->role->name === 'Staff'
            ? 'staff.conversations.show'
            : 'owner.conversations.show';
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

                <form method="GET" class="chat-filter-form" role="search">
                    <label class="sr-only" for="chat-search">Search conversations</label>
                    <div class="chat-search-field">
                        <i class="fa fa-magnifying-glass" aria-hidden="true"></i>
                        <input id="chat-search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search chats">
                    </div>
                    <label class="sr-only" for="chat-status">Filter by status</label>
                    <select id="chat-status" name="status">
                        <option value="attention" @selected(in_array(($filters['status'] ?? ''), ['', 'attention'], true))>Needs attention</option>
                        <option value="all" @selected(($filters['status'] ?? '') === 'all')>All conversations</option>
                        @foreach (['open', 'pending_staff', 'assigned', 'resolved'] as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-outline-primary btn-sm" type="submit">Apply filters</button>
                </form>

                <nav class="chat-conversation-list" aria-label="Inbox conversation list">
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
                            <span>Try another filter or search term.</span>
                        </div>
                    @endforelse
                </nav>
            </aside>

            <main class="chat-main chat-inbox-welcome">
                <span class="chat-start-icon"><i class="fa fa-comments" aria-hidden="true"></i></span>
                <span class="chat-eyebrow">Guest support</span>
                <h2>Every conversation, in one place</h2>
                <p>Select a chat to read the full history, review booking context, assign the inquiry, or reply. Unread messages are marked in your list and dashboard navigation.</p>
                <div class="chat-inbox-tip">
                    <i class="fa fa-lightbulb" aria-hidden="true"></i>
                    <span>Approved FAQ answers appear in the guest conversation automatically; only questions needing your help stay in the attention queue.</span>
                </div>
            </main>
        </div>
    </div>
</x-shell>

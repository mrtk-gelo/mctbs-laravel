<x-shell title="Messages | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'messages'])</x-slot:nav>

    @php
        $isStaff = auth()->user()->role->name === 'Staff';
        $conversationRoute = $isStaff ? 'staff.conversations.show' : 'owner.conversations.show';
        $inboxRoute = $isStaff ? 'staff.conversations.index' : 'owner.conversations.index';
        $createRoute = $isStaff ? 'staff.conversations.create' : 'owner.conversations.create';
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
            @include('conversations.partials.staff-sidebar', [
                'conversations' => $conversations,
                'counts' => $counts,
                'filter' => $filter,
                'conversationRoute' => $conversationRoute,
                'inboxRoute' => $inboxRoute,
                'createRoute' => $createRoute,
                'activeConversationId' => null,
            ])

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

<div>
    <!-- I begin to speak only when I am certain what I will say is not better left unsaid. - Cato the Younger -->
</div>
<x-shell title="Your conversations | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'messages'])</x-slot:nav>

    <div class="container chat-page">
        <header class="chat-page-heading">
            <div>
                <span class="chat-eyebrow">MCTBS Connect</span>
                <h1>Your conversations</h1>
                <p>Continue an earlier question or return to browsing properties.</p>
            </div>
            <a class="text-muted fs-sm" href="{{ route('home') }}">
                <i class="fa fa-arrow-left" aria-hidden="true"></i> Back to MCTBS
            </a>
        </header>

        <div class="chat-workspace chat-workspace--guest">
            <aside class="chat-sidebar" aria-label="Your conversations">
                <div class="chat-sidebar-header">
                    <div>
                        <h2>Chats</h2>
                        <p>{{ $conversations->count() }} {{ \Illuminate\Support\Str::plural('conversation', $conversations->count()) }}</p>
                    </div>
                    <span class="chat-sidebar-icon"><i class="fa fa-inbox" aria-hidden="true"></i></span>
                </div>
                <nav class="chat-conversation-list" aria-label="Conversation list">
                    @forelse ($conversations as $conversation)
                        @include('conversations.partials.list-item', [
                            'conversation' => $conversation,
                            'conversationRoute' => 'public.visitor-conversations.show',
                            'listType' => 'visitor',
                            'activeConversationId' => null,
                        ])
                    @empty
                        <div class="chat-list-empty">
                            <span class="chat-empty-icon"><i class="fa fa-comments" aria-hidden="true"></i></span>
                            <strong>No conversations yet</strong>
                            <span>Start with a quick question using Chat with us.</span>
                        </div>
                    @endforelse
                </nav>
            </aside>

            <main class="chat-main chat-start-panel">
                <div class="chat-start-card">
                    <span class="chat-start-icon"><i class="fa fa-message" aria-hidden="true"></i></span>
                    <span class="chat-eyebrow">MCTBS Connect</span>
                    <h2>Your chats, all together</h2>
                    <p>Open Chat with us on any property page to get an instant answer or send a question to the property team.</p>
                    <a class="btn btn-primary" href="{{ route('home') }}">Browse properties</a>
                </div>
            </main>
        </div>
    </div>
</x-shell>

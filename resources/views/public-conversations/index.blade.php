<x-shell title="Your conversations | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'messages'])</x-slot:nav>

    <div class="container chat-page">
        <a class="chat-page-back text-muted fs-sm" href="{{ route('home') }}">
            <i class="fa fa-arrow-left" aria-hidden="true"></i> Back to MCTBS
        </a>
        <header class="chat-page-heading">
            <div>
                <span class="chat-eyebrow">MCTBS Connect</span>
                <h1>Your conversations</h1>
                <p>Continue an earlier question or return to browsing properties.</p>
            </div>
        </header>

        <div class="chat-workspace chat-workspace--guest">
            @include('conversations.partials.visitor-sidebar', [
                'conversations' => $conversations,
                'folder' => $folder,
                'activeConversationId' => null,
            ])

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

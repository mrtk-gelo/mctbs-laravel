<x-shell title="Messages | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'messages'])</x-slot:nav>

    <div class="container chat-page">
        <header class="chat-page-heading">
            <div>
                <span class="chat-eyebrow">MCTBS Connect</span>
                <h1>Your conversations</h1>
                <p>Ask a property team a question or pick up where you left off.</p>
            </div>
            <span class="chat-secure-note"><i class="fa fa-lock" aria-hidden="true"></i> Private to your account</span>
        </header>

        <div class="chat-workspace chat-workspace--guest">
            <aside class="chat-sidebar" aria-label="Your conversations">
                <div class="chat-sidebar-header">
                    <div>
                        <h2>Chats</h2>
                        <p>{{ $conversations->count() }} {{ \Illuminate\Support\Str::plural('conversation', $conversations->count()) }}</p>
                    </div>
                    <a class="chat-new-button" href="#start-conversation" aria-label="Start a new conversation" title="Start a new conversation">
                        <i class="fa fa-pen-to-square" aria-hidden="true"></i>
                    </a>
                </div>

                <nav class="chat-conversation-list" aria-label="Conversation list">
                    @forelse ($conversations as $conversation)
                        @include('conversations.partials.list-item', [
                            'conversation' => $conversation,
                            'conversationRoute' => 'guest.conversations.show',
                            'listType' => 'guest',
                            'activeConversationId' => null,
                        ])
                    @empty
                        <div class="chat-list-empty">
                            <span class="chat-empty-icon"><i class="fa fa-comments" aria-hidden="true"></i></span>
                            <strong>No chats yet</strong>
                            <span>Your messages with property teams will appear here.</span>
                        </div>
                    @endforelse
                </nav>
            </aside>

            <main class="chat-main chat-start-panel" id="start-conversation">
                <div class="chat-start-card">
                    <span class="chat-start-icon"><i class="fa fa-message" aria-hidden="true"></i></span>
                    <span class="chat-eyebrow">Start a conversation</span>
                    <h2>How can we help?</h2>
                    <p>Choose the property you have a question about. The team will reply here, and your conversation will stay in your chat history.</p>

                    <form method="POST" action="{{ route('guest.conversations.store') }}" class="chat-start-form">
                        @csrf
                        <div class="chat-form-field">
                            <label class="form-label" for="house_id">Property</label>
                            <select class="form-control" id="house_id" name="house_id" required>
                                <option value="">Choose a property</option>
                                @foreach ($houses as $house)
                                    <option value="{{ $house->house_id }}" @selected(old('house_id') == $house->house_id)>{{ $house->name }}</option>
                                @endforeach
                            </select>
                            @error('house_id')<div class="text-danger fs-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="chat-form-field">
                            <label class="form-label" for="subject">Topic <span class="text-muted">(optional)</span></label>
                            <input class="form-control" id="subject" name="subject" value="{{ old('subject') }}" maxlength="255" placeholder="For example, check-in time">
                        </div>

                        <div class="chat-form-field">
                            <label class="form-label" for="message">Your message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" maxlength="5000" required placeholder="Write your question here...">{{ old('message') }}</textarea>
                            @error('message')<div class="text-danger fs-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <button class="btn btn-primary chat-submit-button" type="submit">
                            Send message <i class="fa fa-arrow-right" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </main>
        </div>
    </div>
</x-shell>

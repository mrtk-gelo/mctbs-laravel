<div>
    <!-- Do what you can, with what you have, where you are. - Theodore Roosevelt -->
</div>
<x-shell title="Conversation | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'messages'])</x-slot:nav>

    <div class="container chat-page">
        <header class="chat-page-heading">
            <div>
                <span class="chat-eyebrow">MCTBS Connect</span>
                <h1>Your conversation</h1>
                <p>Messages with {{ $conversation->house->name }} are kept here.</p>
            </div>
            <a class="text-muted fs-sm" href="{{ route('public.visitor-conversations.index') }}">
                <i class="fa fa-arrow-left" aria-hidden="true"></i> All conversations
            </a>
        </header>

        <div class="chat-workspace chat-workspace--guest chat-workspace--thread">
            <aside class="chat-sidebar" aria-label="Your conversations">
                <div class="chat-sidebar-header">
                    <div>
                        <h2>Chats</h2>
                        <p>{{ $conversations->count() }} {{ \Illuminate\Support\Str::plural('conversation', $conversations->count()) }}</p>
                    </div>
                    <a class="chat-new-button" href="{{ route('home') }}" aria-label="Browse properties" title="Browse properties">
                        <i class="fa fa-house" aria-hidden="true"></i>
                    </a>
                </div>
                <nav class="chat-conversation-list" aria-label="Conversation list">
                    @forelse ($conversations as $thread)
                        @include('conversations.partials.list-item', [
                            'conversation' => $thread,
                            'conversationRoute' => 'public.visitor-conversations.show',
                            'listType' => 'visitor',
                            'activeConversationId' => $conversation->conversation_id,
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

            <main class="chat-thread">
                <header class="chat-thread-header">
                    <span class="chat-thread-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($conversation->house->name, 0, 1)) }}</span>
                    <div class="chat-thread-heading">
                        <span class="chat-eyebrow">Property conversation</span>
                        <h2>{{ $conversation->subject ?: $conversation->house->name }}</h2>
                        <p>{{ $conversation->house->name }}{{ $conversation->unit ? ' · '.$conversation->unit->unit_type : '' }}</p>
                    </div>
                </header>

                <div class="chat-thread-welcome">
                    <span class="chat-welcome-avatar"><i class="fa fa-headset" aria-hidden="true"></i></span>
                    <p><strong>MCTBS Connect</strong><br>{{ $settings->welcome_message }}</p>
                </div>

                <section class="chat-message-list" aria-label="Conversation messages" aria-live="polite">
                    @if ($conversation->messages->isNotEmpty())
                        @include('conversations.partials.messages', [
                            'messages' => $conversation->messages,
                            'viewerUserId' => null,
                            'conversationGuestId' => $conversation->guest_user_id,
                            'visitorName' => $conversation->visitor_name,
                        ])
                    @else
                        <div class="chat-list-empty">
                            <span class="chat-empty-icon"><i class="fa fa-message" aria-hidden="true"></i></span>
                            <strong>Start the conversation</strong>
                            <span>Send a message to the property team below.</span>
                        </div>
                    @endif
                </section>

                <div class="chat-composer">
                    @if ($faqPrompts !== [] || $faqs->isNotEmpty())
                        <details class="chat-quick-questions">
                            <summary>Quick questions</summary>
                            <div class="chat-quick-question-list">
                                @foreach ($faqPrompts as $prompt)
                                    <form method="POST" action="{{ route('public.visitor-conversations.reply', $conversation->conversation_id) }}">
                                        @csrf
                                        <input type="hidden" name="message" value="{{ $prompt }}">
                                        <button class="chat-quick-question" type="submit">{{ $prompt }}</button>
                                    </form>
                                @endforeach
                                @foreach ($faqs->take(5) as $faq)
                                    <form method="POST" action="{{ route('public.visitor-conversations.reply', $conversation->conversation_id) }}">
                                        @csrf
                                        <input type="hidden" name="message" value="{{ $faq->question }}">
                                        <button class="chat-quick-question" type="submit">{{ $faq->question }}</button>
                                    </form>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    <form method="POST" action="{{ route('public.visitor-conversations.reply', $conversation->conversation_id) }}" class="chat-composer-form">
                        @csrf
                        <label class="sr-only" for="visitor-message">Write a reply</label>
                        <textarea class="form-control" id="visitor-message" name="message" rows="2" maxlength="5000" required placeholder="Write a message..." data-message-composer>{{ old('message') }}</textarea>
                        @error('message')<div class="text-danger fs-sm mt-1">{{ $message }}</div>@enderror
                        <button class="btn btn-primary" type="submit" aria-label="Send reply">
                            <i class="fa fa-paper-plane" aria-hidden="true"></i> Send
                        </button>
                    </form>
                    <p class="chat-composer-note"><i class="fa fa-lock" aria-hidden="true"></i> Replies stay in MCTBS. We will email you a secure link when there is an update.</p>
                    <a class="guest-chat-sign-in-link" href="{{ $signInUrl }}">Already have a booking? Sign in</a>
                </div>
            </main>
        </div>
    </div>

    <script>
        const visitorMessageList = document.querySelector('.chat-message-list');
        if (visitorMessageList) {
            visitorMessageList.scrollTop = visitorMessageList.scrollHeight;
        }

        const refreshVisitorConversation = () => {
            const composer = document.querySelector('[data-message-composer]');
            if (composer && (composer.value.trim() || document.activeElement === composer)) {
                window.setTimeout(refreshVisitorConversation, 15000);
                return;
            }
            window.location.reload();
        };
        window.setTimeout(refreshVisitorConversation, 15000);
    </script>
</x-shell>

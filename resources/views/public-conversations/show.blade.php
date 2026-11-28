<x-shell title="Conversation | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'messages'])</x-slot:nav>

    <div class="container chat-page">
        <a class="chat-page-back text-muted fs-sm" href="{{ route('public.visitor-conversations.index', ['folder' => $folder]) }}">
            <i class="fa fa-arrow-left" aria-hidden="true"></i> Back to conversations
        </a>
        <header class="chat-page-heading">
            <div>
                <span class="chat-eyebrow">MCTBS Connect</span>
                <h1>Your conversation</h1>
                <p>Messages with {{ $conversation->house->name }} are kept here.</p>
            </div>
        </header>

        <div class="chat-workspace chat-workspace--guest chat-workspace--thread">
            @include('conversations.partials.visitor-sidebar', [
                'conversations' => $conversations,
                'folder' => $folder,
                'folderCounts' => $folderCounts,
                'activeConversationId' => $conversation->conversation_id,
            ])

            <main class="chat-thread">
                <header class="chat-thread-header">
                    <span class="chat-thread-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($conversation->house->name, 0, 1)) }}</span>
                    <div class="chat-thread-heading">
                        <span class="chat-eyebrow">Property conversation</span>
                        <h2>{{ $conversation->subject ?: $conversation->house->name }}</h2>
                        <p>{{ $conversation->house->name }}{{ $conversation->unit ? ' · '.$conversation->unit->unit_type : '' }}</p>
                    </div>
                    <div class="chat-thread-actions">
                        @include('conversations.partials.actions-menu', [
                            'folder' => $folder,
                            'archiveRoute' => 'public.visitor-conversations.archive',
                            'deleteRoute' => 'public.visitor-conversations.delete',
                            'restoreRoute' => 'public.visitor-conversations.restore',
                            'conversationId' => $conversation->conversation_id,
                        ])
                        <button
                            class="chat-info-toggle"
                            type="button"
                            aria-label="Show conversation information"
                            aria-controls="chat-details"
                            aria-expanded="false"
                            title="Show conversation information"
                            data-chat-details-toggle
                        >
                            <i class="fa fa-circle-info" aria-hidden="true"></i>
                        </button>
                    </div>
                </header>

                <div class="chat-thread-welcome">
                    <span class="chat-welcome-avatar"><i class="fa fa-headset" aria-hidden="true"></i></span>
                    <p><strong>MCTBS Connect</strong><br>{{ $settings->welcome_message }}</p>
                </div>

                <section
                    class="chat-message-list"
                    aria-label="Conversation messages"
                    aria-live="polite"
                    data-chat-poll-url="{{ route('public.visitor-conversations.updates', $conversation->conversation_id) }}"
                    data-conversation-id="{{ $conversation->conversation_id }}"
                    data-viewer-type="visitor"
                    data-last-message-id="{{ $conversation->messages->max('message_id') ?? 0 }}"
                >
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

                <p class="chat-sync-status" role="status" aria-live="polite" data-chat-sync-status hidden></p>

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

            <aside class="chat-details" id="chat-details" aria-label="Conversation information" aria-hidden="true" data-chat-details-panel>
                <section class="chat-detail-section">
                    <div class="chat-detail-heading">
                        <span class="chat-detail-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($conversation->visitor_name, 0, 1)) }}</span>
                        <div>
                            <h2>{{ $conversation->visitor_name }}</h2>
                            <p>{{ $conversation->visitor_email }}</p>
                        </div>
                    </div>
                    <div class="chat-detail-fact">
                        <span>Guest status</span>
                        <strong><span class="chat-status chat-status--public-visitor">Visitor (no account)</span></strong>
                    </div>
                </section>

                <section class="chat-detail-section">
                    <h3><i class="fa fa-house" aria-hidden="true"></i> Property</h3>
                    <div class="chat-detail-fact">
                        <span>Name</span>
                        <strong>{{ $conversation->house->name }}</strong>
                    </div>
                    <div class="chat-detail-fact">
                        <span>Address</span>
                        <strong>{{ $conversation->house->address }}</strong>
                    </div>
                    @if ($conversation->house->contact_number)
                        <div class="chat-detail-fact">
                            <span>Property contact</span>
                            <strong>{{ $conversation->house->contact_number }}</strong>
                        </div>
                    @endif
                    @if ($conversation->unit)
                        <div class="chat-detail-fact">
                            <span>Unit</span>
                            <strong>{{ $conversation->unit->unit_type }}</strong>
                        </div>
                        <div class="chat-detail-fact">
                            <span>Rate</span>
                            <strong>₱{{ number_format((float) $conversation->unit->price, 2) }} per night</strong>
                        </div>
                    @endif
                </section>

                <section class="chat-detail-section">
                    <h3><i class="fa fa-user-tie" aria-hidden="true"></i> Property owner</h3>
                    <div class="chat-detail-fact">
                        <span>Name</span>
                        <strong>{{ trim($conversation->house->owner->first_name.' '.$conversation->house->owner->last_name) }}</strong>
                    </div>
                    <div class="chat-detail-fact">
                        <span>Email</span>
                        <strong>{{ $conversation->house->owner->email }}</strong>
                    </div>
                    @if ($conversation->house->owner->phone)
                        <div class="chat-detail-fact">
                            <span>Phone</span>
                            <strong>{{ $conversation->house->owner->phone }}</strong>
                        </div>
                    @endif
                </section>
            </aside>
        </div>
    </div>

    <script>
        const visitorMessageList = document.querySelector('.chat-message-list');
        if (visitorMessageList) {
            visitorMessageList.scrollTop = visitorMessageList.scrollHeight;
        }

    </script>
</x-shell>

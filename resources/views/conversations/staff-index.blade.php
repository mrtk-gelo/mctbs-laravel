<x-shell title="Messages | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'messages'])</x-slot:nav>

    @php
        $isStaff = auth()->user()->role->name === 'Staff';
        $conversationRoute = $isStaff ? 'staff.conversations.show' : 'owner.conversations.show';
        $inboxRoute = $isStaff ? 'staff.conversations.index' : 'owner.conversations.index';
        $activeBookingStatus = $filters['booking_status'] ?? '';
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
                    <input type="hidden" name="booking_status" value="{{ $activeBookingStatus }}">
                    <label class="sr-only" for="chat-search">Search conversations</label>
                    <div class="chat-search-field">
                        <i class="fa fa-magnifying-glass" aria-hidden="true"></i>
                        <input id="chat-search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search chats">
                    </div>
                    <button class="chat-search-submit" type="submit" aria-label="Search conversations">
                        <i class="fa fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>

                <nav class="chat-status-tabs" aria-label="Filter chats by booking status">
                    <a class="chat-status-tab{{ $activeBookingStatus === '' ? ' is-active' : '' }}" href="{{ route($inboxRoute, array_filter(['search' => $filters['search'] ?? null])) }}" @if ($activeBookingStatus === '') aria-current="page" @endif>
                        <span>All chats</span><span class="chat-tab-count">{{ $allCount }}</span>
                    </a>
                    @foreach ($bookingStatuses as $bookingStatus)
                        @php
                            $tabCount = $bookingStatusCounts[$bookingStatus] ?? 0;
                            $statusLabel = \App\Support\BookingStatus::canonical($bookingStatus);
                            $statusHint = $bookingStatus === \App\Support\BookingStatus::ACCEPTED
                                ? 'Dates are reserved while downpayment is pending.'
                                : $statusLabel.' bookings';
                        @endphp
                        <a
                            class="chat-status-tab{{ $activeBookingStatus === $bookingStatus ? ' is-active' : '' }}{{ $tabCount > 0 ? ' has-chats' : '' }}"
                            href="{{ route($inboxRoute, array_filter(['booking_status' => $bookingStatus, 'search' => $filters['search'] ?? null])) }}"
                            title="{{ $statusHint }}"
                            @if ($activeBookingStatus === $bookingStatus) aria-current="page" @endif
                        >
                            <span>{{ $statusLabel }}</span>
                            <span class="chat-tab-count">{{ $tabCount }}</span>
                        </a>
                    @endforeach
                    <a class="chat-status-tab{{ $activeBookingStatus === 'none' ? ' is-active' : '' }}{{ $noBookingCount > 0 ? ' has-chats' : '' }}" href="{{ route($inboxRoute, array_filter(['booking_status' => 'none', 'search' => $filters['search'] ?? null])) }}" @if ($activeBookingStatus === 'none') aria-current="page" @endif>
                        <span>No booking</span><span class="chat-tab-count">{{ $noBookingCount }}</span>
                    </a>
                </nav>

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
                <p>Select a chat to read the full history, review booking context, and reply. Unread messages are marked in your list and dashboard navigation.</p>
                <div class="chat-inbox-tip">
                    <i class="fa fa-lightbulb" aria-hidden="true"></i>
                    <span>Data-backed quick answers appear automatically. Unanswered questions are flagged for the team, and chats stay open for follow-up.</span>
                </div>
            </main>
        </div>
    </div>
</x-shell>

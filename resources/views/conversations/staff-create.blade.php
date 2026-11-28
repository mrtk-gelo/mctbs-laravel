<x-shell title="New conversation | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'messages'])</x-slot:nav>

    @php
        $isStaff = auth()->user()->role->name === 'Staff';
        $conversationRoute = $isStaff ? 'staff.conversations.show' : 'owner.conversations.show';
        $inboxRoute = $isStaff ? 'staff.conversations.index' : 'owner.conversations.index';
        $createRoute = $isStaff ? 'staff.conversations.create' : 'owner.conversations.create';
        $createStoreRoute = $isStaff ? 'staff.conversations.create.store' : 'owner.conversations.create.store';
    @endphp

    <div class="container chat-page">
        <a class="chat-page-back text-muted fs-sm" href="{{ route($inboxRoute, ['filter' => $filter, 'folder' => 'inbox']) }}">
            <i class="fa fa-arrow-left" aria-hidden="true"></i> Back to inbox
        </a>
        <header class="chat-page-heading">
            <div>
                <span class="chat-eyebrow">MCTBS Connect · Team inbox</span>
                <h1>New conversation</h1>
                <p>Start a private chat with a registered guest who has booked one of your properties.</p>
            </div>
        </header>

        <div class="chat-workspace chat-workspace--staff">
            @include('conversations.partials.staff-sidebar', [
                'conversations' => $conversations,
                'counts' => $counts,
                'filter' => $filter,
                'folder' => $folder,
                'conversationRoute' => $conversationRoute,
                'inboxRoute' => $inboxRoute,
                'createRoute' => $createRoute,
                'activeConversationId' => null,
            ])

            <main class="chat-main chat-start-panel">
                <div class="chat-start-card">
                    <span class="chat-start-icon"><i class="fa fa-message" aria-hidden="true"></i></span>
                    <span class="chat-eyebrow">Start a conversation</span>
                    <h2>Message a guest</h2>
                    <p>Select one of your properties, then choose a registered guest with a booking there.</p>

                    <form method="GET" action="{{ route($createRoute) }}" class="chat-start-form">
                        <input type="hidden" name="filter" value="{{ $filter }}">
                        <div class="chat-form-field">
                            <label class="form-label" for="new-chat-house">Property</label>
                            <select class="form-control" id="new-chat-house" name="house_id" required>
                                <option value="">Choose a property</option>
                                @foreach ($houses as $house)
                                    <option value="{{ $house->house_id }}" @selected($selectedHouse?->house_id === $house->house_id)>
                                        {{ $house->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('house_id')<div class="text-danger fs-sm mt-1">{{ $message }}</div>@enderror
                        </div>
                        <button class="btn btn-outline-primary chat-submit-button" type="submit">Find guests</button>
                    </form>

                    @if ($houses->isEmpty())
                        <p class="chat-detail-empty">There are no active properties available to your account.</p>
                    @endif

                    @if ($selectedHouse)
                        @if ($guests->isNotEmpty())
                            <form method="POST" action="{{ route($createStoreRoute) }}" class="chat-start-form">
                                @csrf
                                <input type="hidden" name="house_id" value="{{ $selectedHouse->house_id }}">
                                <div class="chat-form-field">
                                    <label class="form-label" for="new-chat-guest">Registered guest</label>
                                    <select class="form-control" id="new-chat-guest" name="guest_user_id" required>
                                        <option value="">Choose a guest</option>
                                        @foreach ($guests as $guest)
                                            <option value="{{ $guest->user_id }}" @selected(old('guest_user_id') == $guest->user_id)>
                                                {{ trim($guest->first_name.' '.$guest->last_name) }} · {{ $guest->email }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('guest_user_id')<div class="text-danger fs-sm mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="chat-form-field">
                                    <label class="form-label" for="new-chat-subject">Topic <span class="text-muted">(optional)</span></label>
                                    <input class="form-control" id="new-chat-subject" name="subject" value="{{ old('subject') }}" maxlength="255" placeholder="For example, check-in details">
                                </div>
                                <div class="chat-form-field">
                                    <label class="form-label" for="new-chat-message">First message</label>
                                    <textarea class="form-control" id="new-chat-message" name="message" rows="4" maxlength="5000" required placeholder="Write a message to the guest...">{{ old('message') }}</textarea>
                                    @error('message')<div class="text-danger fs-sm mt-1">{{ $message }}</div>@enderror
                                </div>
                                <button class="btn btn-primary chat-submit-button" type="submit">
                                    Start chat <i class="fa fa-paper-plane" aria-hidden="true"></i>
                                </button>
                            </form>
                        @else
                            <p class="chat-detail-empty">No registered guests with bookings were found for {{ $selectedHouse->name }}.</p>
                        @endif
                    @endif
                </div>
            </main>
        </div>
    </div>
</x-shell>

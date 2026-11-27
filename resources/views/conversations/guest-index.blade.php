<x-shell title="Messages | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'messages'])</x-slot:nav>

    <div class="container">
        <div class="page-header mt-3">
            <h1>My conversations</h1>
            <p>Ask a property question or continue a previous conversation.</p>
        </div>

        @if ($conversations->isNotEmpty())
            <div class="grid gap-3 mb-4">
                @foreach ($conversations as $conversation)
                    <a href="{{ route('guest.conversations.show', $conversation->conversation_id) }}" class="card" style="text-decoration:none;color:inherit">
                        <div class="card-body">
                            <div class="flex-between">
                                <strong>{{ $conversation->subject ?: 'Conversation with '.$conversation->house->name }}</strong>
                                <span>
                                    @if ($conversation->unread_message_count > 0)
                                        <span class="badge">{{ $conversation->unread_message_count }} unread</span>
                                    @endif
                                    <span class="badge">{{ str_replace('_', ' ', ucfirst($conversation->status)) }}</span>
                                </span>
                            </div>
                            <div class="text-muted fs-sm mt-1">{{ $conversation->house->name }}{{ $conversation->unit ? ' · '.$conversation->unit->unit_type : '' }}</div>
                            @if ($conversation->latestMessage)
                                <p class="mt-2 mb-0">{{ \Illuminate\Support\Str::limit($conversation->latestMessage->message, 120) }}</p>
                                <div class="text-muted fs-sm mt-1">{{ $conversation->latestMessage->sent_at->diffForHumans() }}</div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state mb-4">
                <i class="fa fa-comments"></i>
                <h3>No conversations yet</h3>
                <p>Start a conversation with a property below.</p>
            </div>
        @endif

        <div class="card" id="start-conversation">
            <div class="card-body">
                <h2>Start a conversation</h2>
                <form method="POST" action="{{ route('guest.conversations.store') }}">
                    @csrf
                    <label class="form-label" for="house_id">Property</label>
                    <select class="form-control" id="house_id" name="house_id" required>
                        <option value="">Choose a property</option>
                        @foreach ($houses as $house)
                            <option value="{{ $house->house_id }}" @selected(old('house_id') == $house->house_id)>{{ $house->name }}</option>
                        @endforeach
                    </select>
                    <label class="form-label mt-3" for="subject">Subject <span class="text-muted">(optional)</span></label>
                    <input class="form-control" id="subject" name="subject" value="{{ old('subject') }}" maxlength="255">
                    <label class="form-label mt-3" for="message">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="4" maxlength="5000" required>{{ old('message') }}</textarea>
                    @error('house_id')<div class="text-danger fs-sm mt-1">{{ $message }}</div>@enderror
                    @error('message')<div class="text-danger fs-sm mt-1">{{ $message }}</div>@enderror
                    <button class="btn btn-primary mt-3" type="submit"><i class="fa fa-paper-plane"></i> Send message</button>
                </form>
            </div>
        </div>
    </div>
</x-shell>

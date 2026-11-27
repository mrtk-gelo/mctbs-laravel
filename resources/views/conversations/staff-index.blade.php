<x-shell title="Messages | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'messages'])</x-slot:nav>

    <div class="container">
        <div class="page-header mt-3">
            <h1>Messages</h1>
            <p>Manage guest conversations for your authorized properties.</p>
        </div>

        <form method="GET" class="card mb-3">
            <div class="card-body flex-between" style="gap:12px;flex-wrap:wrap">
                <input class="form-control" style="max-width:320px" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search conversations">
                <select class="form-control" style="max-width:220px" name="status">
                    <option value="attention" @selected(in_array(($filters['status'] ?? ''), ['', 'attention'], true))>Needs attention</option>
                    <option value="all" @selected(($filters['status'] ?? '') === 'all')>All statuses</option>
                    @foreach (['open', 'pending_staff', 'assigned', 'resolved'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-outline-primary" type="submit">Filter</button>
            </div>
        </form>

        <div class="card">
            <div class="card-body">
                @forelse ($conversations as $conversation)
                    <a href="{{ route(auth()->user()->role->name === 'Staff' ? 'staff.conversations.show' : 'owner.conversations.show', $conversation->conversation_id) }}" class="card mb-2" style="display:block;text-decoration:none;color:inherit">
                        <div class="card-body">
                            <div class="flex-between">
                                <strong>{{ $conversation->guest->first_name }} {{ $conversation->guest->last_name }}</strong>
                                <span>
                                    @if ($conversation->unread_message_count > 0)
                                        <span class="badge">{{ $conversation->unread_message_count }} unread</span>
                                    @endif
                                    <span class="badge">{{ str_replace('_', ' ', ucfirst($conversation->status)) }}</span>
                                </span>
                            </div>
                            @if ($conversation->subject)
                                <div class="fs-sm mt-1"><strong>{{ $conversation->subject }}</strong></div>
                            @endif
                            <div class="text-muted fs-sm">{{ $conversation->house->name }}{{ $conversation->unit ? ' · '.$conversation->unit->unit_type : '' }} · {{ $conversation->assignee ? 'Assigned to '.$conversation->assignee->first_name : 'Unassigned' }}</div>
                            @if ($conversation->latestMessage)
                                <p class="mb-0 mt-1">{{ \Illuminate\Support\Str::limit($conversation->latestMessage->message, 140) }}</p>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="empty-state"><i class="fa fa-comments"></i><h3>No conversations found</h3></div>
                @endforelse
            </div>
        </div>
    </div>
</x-shell>

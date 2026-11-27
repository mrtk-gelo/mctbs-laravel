<x-shell title="Conversation | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'messages'])</x-slot:nav>

    <div class="container">
        <div class="page-header mt-3 flex-between">
            <div>
                <h1>{{ $conversation->guest->first_name }} {{ $conversation->guest->last_name }}</h1>
                <p>{{ $conversation->house->name }}{{ $conversation->unit ? ' · '.$conversation->unit->unit_type : '' }} · {{ str_replace('_', ' ', ucfirst($conversation->status)) }}</p>
            </div>
            <a class="btn btn-outline-primary" href="{{ route(auth()->user()->role->name === 'Staff' ? 'staff.conversations.index' : 'owner.conversations.index') }}">Inbox</a>
        </div>

        <div class="grid gap-3" style="grid-template-columns:minmax(0,1fr) 280px">
            <div class="card">
                <div class="card-body">
                    @foreach ($conversation->messages as $message)
                        <div class="mb-3">
                            <div class="text-muted fs-sm">
                                {{ $message->sender_type === 'assistant' ? 'Automated assistance' : ($message->sender_type === 'system' ? 'MCTBS Connect' : $message->sender->first_name.' '.$message->sender->last_name) }}
                                · {{ $message->sent_at->format('M j, Y g:i A') }}
                            </div>
                            <div class="mt-1" style="white-space:pre-wrap">{{ $message->message }}</div>
                        </div>
                    @endforeach
                    <form method="POST" action="{{ route(auth()->user()->role->name === 'Staff' ? 'staff.conversations.store' : 'owner.conversations.store', $conversation->conversation_id) }}" class="mt-4">
                        @csrf
                        @if ($quickReplies->isNotEmpty())
                            <label class="form-label" for="quick_reply">Quick reply</label>
                            <select class="form-control mb-2" id="quick_reply" data-quick-reply-select>
                                <option value="">Choose a template</option>
                                @foreach ($quickReplies as $quickReply)
                                    <option value="{{ $quickReply->content }}">{{ $quickReply->title }}</option>
                                @endforeach
                            </select>
                        @endif
                        <label class="form-label" for="message">Reply</label>
                        <textarea class="form-control" id="message" name="message" rows="4" maxlength="5000" required data-message-composer></textarea>
                        @error('message')<div class="text-danger fs-sm mt-1">{{ $message }}</div>@enderror
                        <button class="btn btn-primary mt-3" type="submit"><i class="fa fa-paper-plane"></i> Send reply</button>
                    </form>
                </div>
            </div>
            <aside class="card">
                <div class="card-body">
                    <h2>Conversation details</h2>
                    <p class="fs-sm mb-1">{{ $conversation->guest->email }}</p>
                    <p class="fs-sm">Status: <strong>{{ str_replace('_', ' ', ucfirst($conversation->status)) }}</strong></p>
                    <hr class="my-3">
                    <h3>Booking context</h3>
                    @if ($context['booking'])
                        <p class="fs-sm mb-1">{{ $context['booking']->unit->house->name }} · {{ $context['booking']->unit->unit_type }}</p>
                        <p class="fs-sm mb-1">{{ $context['booking']->check_in->format('M j, Y') }} - {{ $context['booking']->check_out->format('M j, Y') }}</p>
                        <p class="fs-sm mb-1">Booking: {{ $context['booking']->booking_reference ?: '#'.$context['booking']->booking_id }}</p>
                        <p class="fs-sm mb-1">Booking status: {{ $context['booking']->status }}</p>
                        <p class="fs-sm mb-2">Payment status: {{ $context['paymentStatus'] ?: 'No payment record' }}</p>
                        @if ($context['bookingUrl'])
                            <a href="{{ $context['bookingUrl'] }}" class="btn btn-outline-primary btn-sm">Open booking</a>
                        @endif
                    @else
                        <p class="text-muted fs-sm">No booking is linked to this conversation.</p>
                    @endif
                    <p class="fs-sm mb-0 mt-2">{{ $conversation->house->address }}{{ $conversation->house->contact_number ? ' · '.$conversation->house->contact_number : '' }}</p>
                    @if ($context['policies'])
                        <p class="fs-sm mb-0 mt-1" style="white-space:pre-wrap">{{ $context['policies'] }}</p>
                    @endif
                    @if ($context['amenities']->isNotEmpty())
                        <p class="fs-sm mb-0 mt-1">Amenities: {{ $context['amenities']->pluck('name')->join(', ') }}</p>
                    @endif
                    <form method="POST" action="{{ route(auth()->user()->role->name === 'Staff' ? 'staff.conversations.assign' : 'owner.conversations.assign', $conversation->conversation_id) }}">
                        @csrf
                        <label class="form-label" for="assigned_to">Assigned to</label>
                        <select class="form-control" id="assigned_to" name="assigned_to" required>
                            @foreach ($assignees as $assignee)
                                <option value="{{ $assignee->user_id }}" @selected($conversation->assigned_to === $assignee->user_id)>{{ $assignee->first_name }} {{ $assignee->last_name }} ({{ $assignee->role->name }})</option>
                            @endforeach
                        </select>
                        <button class="btn btn-outline-primary mt-2" type="submit">Update assignment</button>
                    </form>
                    @if ($conversation->status === 'resolved')
                        <form method="POST" action="{{ route(auth()->user()->role->name === 'Staff' ? 'staff.conversations.reopen' : 'owner.conversations.reopen', $conversation->conversation_id) }}" class="mt-2">
                            @csrf
                            <button class="btn btn-outline-primary" type="submit">Reopen</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route(auth()->user()->role->name === 'Staff' ? 'staff.conversations.resolve' : 'owner.conversations.resolve', $conversation->conversation_id) }}" class="mt-2">
                            @csrf
                            <button class="btn btn-outline-primary" type="submit">Mark resolved</button>
                        </form>
                    @endif

                    <hr class="my-3">
                    <h3>Internal notes</h3>
                    @forelse ($conversation->notes as $note)
                        <div class="mb-2 fs-sm" style="white-space:pre-wrap">
                            <strong>{{ $note->author->first_name }}</strong> · {{ $note->created_at->format('M j, Y g:i A') }}<br>
                            {{ $note->note }}
                        </div>
                    @empty
                        <p class="text-muted fs-sm">No private notes yet.</p>
                    @endforelse
                    <form method="POST" action="{{ route(auth()->user()->role->name === 'Staff' ? 'staff.conversations.notes.store' : 'owner.conversations.notes.store', $conversation->conversation_id) }}">
                        @csrf
                        <label class="form-label" for="note">Private note</label>
                        <textarea class="form-control" id="note" name="note" rows="3" maxlength="5000" required></textarea>
                        <button class="btn btn-outline-primary mt-2" type="submit">Add internal note</button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
    <script>
        const quickReplySelect = document.querySelector('[data-quick-reply-select]');
        const messageComposer = document.querySelector('[data-message-composer]');
        quickReplySelect?.addEventListener('change', () => {
            if (quickReplySelect.value) {
                messageComposer.value = quickReplySelect.value;
            }
        });
        window.setTimeout(() => window.location.reload(), 15000);
    </script>
</x-shell>

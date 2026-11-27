<x-shell title="Conversation | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'messages'])</x-slot:nav>

    <div class="container">
        <div class="page-header mt-3 flex-between">
            <div>
                <h1>{{ $conversation->subject ?: 'Conversation' }}</h1>
                <p>{{ $conversation->house->name }}{{ $conversation->unit ? ' · '.$conversation->unit->unit_type : '' }} · {{ str_replace('_', ' ', ucfirst($conversation->status)) }}</p>
            </div>
            <a class="btn btn-outline-primary" href="{{ route('guest.conversations.index') }}">All conversations</a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="card mb-3">
                    <div class="card-body">
                        <strong>MCTBS Connect</strong>
                        <p class="mb-0 mt-1">{{ $settings->welcome_message }}</p>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <strong>Your booking context</strong>
                        @if ($context['booking'])
                            <p class="fs-sm mb-1 mt-2">
                                {{ $context['booking']->unit->house->name }} · {{ $context['booking']->unit->unit_type }}
                            </p>
                            <p class="fs-sm mb-1">
                                {{ $context['booking']->check_in->format('M j, Y') }} - {{ $context['booking']->check_out->format('M j, Y') }}
                            </p>
                            <p class="fs-sm mb-1">
                                Booking {{ $context['booking']->booking_reference ?: '#'.$context['booking']->booking_id }}
                                · {{ $context['booking']->status }}
                            </p>
                            <p class="fs-sm mb-2">Payment: {{ $context['paymentStatus'] ?: 'No payment record' }}</p>
                            @if ($context['bookingUrl'])
                                <a href="{{ $context['bookingUrl'] }}" class="btn btn-outline-primary btn-sm">View booking</a>
                            @endif
                            @if ($context['ticketUrl'])
                                <a href="{{ $context['ticketUrl'] }}" class="btn btn-outline-primary btn-sm">View ticket</a>
                            @endif
                        @else
                            <p class="text-muted fs-sm mb-0 mt-2">No booking is linked to this property yet.</p>
                        @endif
                        <p class="fs-sm mb-0 mt-2">{{ $conversation->house->address }}{{ $conversation->house->contact_number ? ' · '.$conversation->house->contact_number : '' }}</p>
                        @if ($context['policies'])
                            <p class="fs-sm mb-0 mt-1" style="white-space:pre-wrap">{{ $context['policies'] }}</p>
                        @endif
                        @if ($context['amenities']->isNotEmpty())
                            <p class="fs-sm mb-0 mt-1">Amenities: {{ $context['amenities']->pluck('name')->join(', ') }}</p>
                        @endif
                    </div>
                </div>
                @forelse ($conversation->messages as $message)
                    <div class="mb-3" style="max-width:760px">
                        <div class="text-muted fs-sm">
                            {{ $message->sender_type === 'assistant' ? 'Automated assistance' : ($message->sender_type === 'system' ? 'MCTBS Connect' : $message->sender->first_name) }}
                            · {{ $message->sent_at->format('M j, Y g:i A') }}
                        </div>
                        <div class="mt-1" style="white-space:pre-wrap">{{ $message->message }}</div>
                    </div>
                @empty
                    <div class="empty-state"><h3>No messages yet</h3></div>
                @endforelse

                @if ($conversation->status === 'resolved')
                    <form method="POST" action="{{ route('guest.conversations.reopen', $conversation->conversation_id) }}">
                        @csrf
                        <button class="btn btn-outline-primary" type="submit">Reopen conversation</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('guest.conversations.reply', $conversation->conversation_id) }}" class="mt-4">
                    @csrf
                    <label class="form-label" for="message">Reply</label>
                    <textarea class="form-control" id="message" name="message" rows="4" maxlength="5000" required></textarea>
                    @error('message')<div class="text-danger fs-sm mt-1">{{ $message }}</div>@enderror
                    <button class="btn btn-primary mt-3" type="submit"><i class="fa fa-paper-plane"></i> Send reply</button>
                </form>

                @if ($faqs->isNotEmpty())
                    <div class="mt-4">
                        <div class="text-muted fs-sm mb-2">Common questions</div>
                        @foreach ($faqs->take(5) as $faq)
                            <form method="POST" action="{{ route('guest.conversations.reply', $conversation->conversation_id) }}" style="display:inline-block;margin:0 6px 6px 0">
                                @csrf
                                <input type="hidden" name="message" value="{{ $faq->question }}">
                                <button class="btn btn-outline-primary btn-sm" type="submit">{{ $faq->question }}</button>
                            </form>
                        @endforeach
                    </div>
                @endif

                @if ($conversation->status !== 'pending_staff' && $conversation->status !== 'resolved')
                    <form method="POST" action="{{ route('guest.conversations.reply', $conversation->conversation_id) }}" class="mt-3">
                        @csrf
                        <input type="hidden" name="message" value="I would like to talk to a real person.">
                        <button class="btn btn-outline-primary" type="submit">Talk to a real person</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    <script>window.setTimeout(() => window.location.reload(), 15000);</script>
</x-shell>

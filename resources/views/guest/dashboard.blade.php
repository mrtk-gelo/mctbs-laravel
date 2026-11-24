<x-shell title="Bookings | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'bookings'])</x-slot:nav>

    <div class="container" data-booking-filter-root>
        <div class="page-header mt-3">
            <h1>Bookings</h1>
            <p>Track and manage all your reservations</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success mb-3" role="status">{{ session('success') }}</div>
        @endif

        <div class="mb-3">
            <a href="{{ route('public.transients') }}" class="btn btn-primary">
                <i class="fa fa-search"></i> Browse transient houses
            </a>
        </div>

        <div class="tabs">
            <button type="button" class="tab-btn active" data-tab="tab-pending">Pending ({{ $pending->count() }})</button>
            <button type="button" class="tab-btn" data-tab="tab-accepted">Accepted ({{ $accepted->count() }})</button>
            <button type="button" class="tab-btn" data-tab="tab-completed">History ({{ $completed->count() }})</button>
        </div>

        @foreach (['tab-pending' => $pending, 'tab-accepted' => $accepted, 'tab-completed' => $completed] as $tabId => $list)
            <div class="tab-pane @if ($loop->first) active @endif" id="{{ $tabId }}" data-booking-filter-panel>
                @if ($list->isEmpty())
                    <div class="empty-state" data-filter-empty><i class="fa fa-calendar"></i><h3>No bookings here</h3></div>
                @else
                    <div data-booking-list>
                        <div data-booking-items>
                            @foreach ($list as $b)
                                @php
                                    $canonicalStatus = \App\Support\BookingStatus::canonical($b->status);
                                @endphp
                                <div class="card mb-3" data-booking-item data-created-at="{{ $b->created_at?->timestamp ?? 0 }}" data-check-in="{{ \Illuminate\Support\Carbon::parse($b->check_in)->toDateString() }}" data-check-out="{{ \Illuminate\Support\Carbon::parse($b->check_out)->toDateString() }}">
                                    <div class="card-body flex-between" style="flex-wrap:wrap;gap:12px">
                                        <div>
                                            <div class="fw-bold" style="font-size:16px">{{ $b->unit->unit_type }}</div>
                                            <div class="text-muted fs-sm">{{ $b->unit->house->name }} — {{ $b->unit->house->city }}</div>
                                            <div class="fs-sm mt-1">
                                                <i class="fa fa-calendar-alt"></i> {{ \Illuminate\Support\Carbon::parse($b->check_in)->format('M j, Y') }} → {{ \Illuminate\Support\Carbon::parse($b->check_out)->format('M j, Y') }}
                                            </div>
                                            <a href="{{ route('guest.bookings.show', $b->booking_id) }}" class="btn btn-outline-primary btn-sm mt-2">View booking</a>
                                        </div>
                                        <div style="text-align:right">
                                            <div class="fw-bold" style="font-size:18px;color:var(--accent)">₱{{ number_format($b->amountDueBeforeCheckout(), 2) }}</div>
                                            @include('bookings.partials.status-indicator', [
                                                'booking' => $b,
                                                'statusBadge' => 'badge-'.strtolower($canonicalStatus),
                                                'statusLabel' => $canonicalStatus,
                                            ])
                                            <div class="fs-sm text-muted mt-1">Booking #{{ $b->booking_id }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="empty-state" data-filter-empty hidden><i class="fa fa-filter"></i><h3>No bookings match these filters</h3></div>
                @endif
            </div>
        @endforeach
    </div>

    <script>
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById(btn.dataset.tab).classList.add('active');
            });
        });
    </script>
</x-shell>

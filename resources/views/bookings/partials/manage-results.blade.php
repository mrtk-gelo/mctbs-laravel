@php
    $selectedTab = request('tab');
    $pendingIsActive = ! in_array($selectedTab, ['accepted', 'history', 'archive'], true);
    $acceptedIsActive = $selectedTab === 'accepted';
    $historyIsActive = $selectedTab === 'history';
    $archiveIsActive = $selectedTab === 'archive';
    $archivableStatuses = [
        ...\App\Support\BookingStatus::completedStatuses(),
        \App\Support\BookingStatus::CANCELLED,
        \App\Support\BookingStatus::REJECTED,
    ];
    $hasArchivableBookings = $historyBookings->contains(
        fn ($booking): bool => ! $booking->archive && in_array($booking->status, $archivableStatuses, true)
    );
@endphp

<div id="booking-results">
    <div class="tabs" role="tablist">
        <button type="button" class="tab-btn {{ $pendingIsActive ? 'active' : '' }}" data-tab="pending-bookings">
            Pending <span class="badge badge-pending">{{ $pendingBookings->count() }}</span>
        </button>
        <button type="button" class="tab-btn {{ $acceptedIsActive ? 'active' : '' }}" data-tab="accepted-bookings">
            Accepted <span class="badge badge-accepted">{{ $acceptedBookings->count() }}</span>
        </button>
        <button type="button" class="tab-btn {{ $historyIsActive ? 'active' : '' }}" data-tab="history-bookings">
            History <span class="badge badge-completed">{{ $historyBookings->count() }}</span>
        </button>
        <button type="button" class="tab-btn {{ $archiveIsActive ? 'active' : '' }}" data-tab="archive-bookings">
            Archive <span class="badge badge-cancelled">{{ $archivedBookings->count() }}</span>
        </button>
    </div>

    <div class="tab-pane {{ $pendingIsActive ? 'active' : '' }}" id="pending-bookings" data-booking-filter-panel>
        @if ($pendingBookings->isEmpty())
            <div class="empty-state" data-filter-empty><i class="fa fa-clock"></i><h3>No pending requests</h3><p>New booking requests will appear here.</p></div>
        @else
            <div class="card" data-booking-list>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Booking</th><th>Guest</th><th>Unit</th><th>Check-in</th><th>Check-out</th><th>Guests</th><th>Total</th><th>Requested</th><th>Action</th></tr></thead>
                        <tbody data-booking-items>
                            @foreach ($pendingBookings as $booking)
                                <tr data-booking-item data-created-at="{{ $booking->created_at?->timestamp ?? 0 }}" data-check-in="{{ $booking->check_in->toDateString() }}" data-check-out="{{ $booking->check_out->toDateString() }}">
                                    <td><strong>#{{ $booking->booking_id }}</strong><br><span class="badge badge-pending">Pending</span></td>
                                    <td>{{ $booking->displayGuestName() }}@if ($booking->displayGuestEmail())<br><span class="text-muted fs-sm">{{ $booking->displayGuestEmail() }}</span>@endif</td>
                                    <td>{{ $booking->unit->unit_type }}<br><span class="text-muted fs-sm">{{ $booking->unit->house->name }}</span></td>
                                    <td>{{ $booking->check_in->format('M j, Y') }}</td>
                                    <td>{{ $booking->check_out->format('M j, Y') }}</td>
                                    <td>{{ $booking->adult_count + $booking->child_count }}</td>
                                    <td>₱{{ number_format($booking->amountDueBeforeCheckout(), 2) }}</td>
                                    <td>{{ $booking->created_at->format('M j, Y') }}</td>
                                    <td><a href="{{ route($dashboardPrefix.'.bookings.show', $booking->booking_id) }}" class="btn btn-outline btn-sm">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="empty-state" data-filter-empty hidden><i class="fa fa-filter"></i><h3>No bookings match these filters</h3></div>
        @endif
    </div>

    <div class="tab-pane {{ $acceptedIsActive ? 'active' : '' }}" id="accepted-bookings" data-booking-filter-panel>
        @if ($acceptedBookings->isEmpty())
            <div class="empty-state" data-filter-empty><i class="fa fa-check-circle"></i><h3>No accepted bookings</h3></div>
        @else
            <div class="card" data-booking-list>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Booking</th><th>Guest</th><th>Unit</th><th>Check-in</th><th>Check-out</th><th>Total</th><th>Payment</th><th>Deadline</th><th>Action</th></tr></thead>
                        <tbody data-booking-items>
                            @foreach ($acceptedBookings as $booking)
                                @php
                                    $latestPayment = $booking->payments->sortByDesc('payment_date')->first();
                                    $paymentBadge = match ($latestPayment?->payment_status) {
                                        \App\Support\PaymentStatus::VERIFIED => 'badge-verified',
                                        \App\Support\PaymentStatus::UNDER_REVIEW => 'badge-pending',
                                        default => 'badge-unpaid',
                                    };
                                    $acceptedBadge = $booking->status === \App\Support\BookingStatus::CONFIRMED ? 'badge-completed' : 'badge-accepted';
                                @endphp
                                <tr data-booking-item data-created-at="{{ $booking->created_at?->timestamp ?? 0 }}" data-check-in="{{ $booking->check_in->toDateString() }}" data-check-out="{{ $booking->check_out->toDateString() }}">
                                    <td><strong>#{{ $booking->booking_id }}</strong><br><span class="badge {{ $acceptedBadge }}">{{ \App\Support\BookingStatus::canonical($booking->status) }}</span></td>
                                    <td>{{ $booking->displayGuestName() }}</td>
                                    <td>{{ $booking->unit->unit_type }}<br><span class="text-muted fs-sm">{{ $booking->unit->house->name }}</span></td>
                                    <td>{{ $booking->check_in->format('M j, Y') }}</td>
                                    <td>{{ $booking->check_out->format('M j, Y') }}</td>
                                    <td>₱{{ number_format($booking->amountDueBeforeCheckout(), 2) }}</td>
                                    <td>@if ($latestPayment)<span class="badge {{ $paymentBadge }}">{{ $latestPayment->payment_status }}</span>@else<span class="badge badge-unpaid">Unpaid</span>@endif</td>
                                    <td>
                                        @if ($booking->status === \App\Support\BookingStatus::ACCEPTED)
                                            @if ($booking->payment_overdue)
                                                Overdue since {{ $booking->payment_deadline?->format('M j, Y g:i A') }}
                                            @else
                                                {{ $booking->payment_deadline?->format('M j, Y g:i A') }}
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td><a href="{{ route($dashboardPrefix.'.bookings.show', $booking->booking_id) }}" class="btn btn-outline btn-sm">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="empty-state" data-filter-empty hidden><i class="fa fa-filter"></i><h3>No bookings match these filters</h3></div>
        @endif
    </div>

    <div class="tab-pane {{ $historyIsActive ? 'active' : '' }}" id="history-bookings" data-booking-filter-panel>
        @if ($historyBookings->isEmpty())
            <div class="empty-state" data-filter-empty><i class="fa fa-history"></i><h3>No booking history</h3></div>
        @else
            <form method="POST" action="{{ route($dashboardPrefix.'.bookings.archive-selected') }}" data-history-archive-form>
                @csrf
                @if ($hasArchivableBookings)
                    <div class="booking-archive-toolbar">
                        <label class="booking-archive-select-all">
                            <input type="checkbox" data-archive-select-all>
                            <span>Select all</span>
                        </label>
                        <span class="booking-archive-count" data-archive-count aria-live="polite">0 selected</span>
                        <button type="submit" class="btn btn-outline" data-archive-submit disabled>Archive selected</button>
                    </div>
                @endif
                <div class="card" data-booking-list>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th><span class="sr-only">Select</span></th><th>Booking</th><th>Guest</th><th>Unit</th><th>Check-in</th><th>Check-out</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody data-booking-items>
                                @foreach ($historyBookings as $booking)
                                    @php
                                        $canonicalStatus = \App\Support\BookingStatus::canonical($booking->status);
                                        $historyBadge = match ($canonicalStatus) {
                                            \App\Support\BookingStatus::COMPLETED => 'badge-completed',
                                            \App\Support\BookingStatus::REJECTED => 'badge-rejected',
                                            \App\Support\BookingStatus::CANCELLED => 'badge-cancelled',
                                            \App\Support\BookingStatus::PENDING => 'badge-pending',
                                            default => 'badge-accepted',
                                        };
                                        $canArchive = ! $booking->archive && in_array($booking->status, $archivableStatuses, true);
                                    @endphp
                                    <tr data-booking-item data-created-at="{{ $booking->created_at?->timestamp ?? 0 }}" data-check-in="{{ $booking->check_in->toDateString() }}" data-check-out="{{ $booking->check_out->toDateString() }}">
                                        <td>
                                            @if ($canArchive)
                                                <input type="checkbox" name="booking_ids[]" value="{{ $booking->booking_id }}" aria-label="Select booking #{{ $booking->booking_id }}" data-archive-booking>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>#{{ $booking->booking_id }}</strong>
                                            @if ($booking->archive)
                                                <br><span class="badge badge-cancelled">Archived</span>
                                            @endif
                                        </td>
                                        <td>{{ $booking->displayGuestName() }}</td>
                                        <td>{{ $booking->unit->unit_type }}<br><span class="text-muted fs-sm">{{ $booking->unit->house->name }}</span></td>
                                        <td>{{ $booking->check_in->format('M j, Y') }}</td>
                                        <td>{{ $booking->check_out->format('M j, Y') }}</td>
                                        <td>₱{{ number_format($booking->amountDueBeforeCheckout(), 2) }}</td>
                                        <td>
                                            @include('bookings.partials.status-indicator', [
                                                'booking' => $booking,
                                                'statusBadge' => $historyBadge,
                                                'statusLabel' => $canonicalStatus,
                                            ])
                                        </td>
                                        <td><a href="{{ route($dashboardPrefix.'.bookings.show', $booking->booking_id) }}" class="btn btn-outline btn-sm">View</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="empty-state" data-filter-empty hidden><i class="fa fa-filter"></i><h3>No bookings match these filters</h3></div>
            </form>
        @endif
    </div>

    <div class="tab-pane {{ $archiveIsActive ? 'active' : '' }}" id="archive-bookings" data-booking-filter-panel>
        @if ($archivedBookings->isEmpty())
            <div class="empty-state" data-filter-empty><i class="fa fa-archive"></i><h3>No archived bookings</h3></div>
        @else
            <div class="card" data-booking-list>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Booking</th><th>Guest</th><th>Unit</th><th>Check-in</th><th>Check-out</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody data-booking-items>
                            @foreach ($archivedBookings as $booking)
                                @php
                                    $canonicalStatus = \App\Support\BookingStatus::canonical($booking->status);
                                    $historyBadge = match ($canonicalStatus) {
                                        \App\Support\BookingStatus::COMPLETED => 'badge-completed',
                                        \App\Support\BookingStatus::REJECTED => 'badge-rejected',
                                        \App\Support\BookingStatus::CANCELLED => 'badge-cancelled',
                                        \App\Support\BookingStatus::PENDING => 'badge-pending',
                                        default => 'badge-accepted',
                                    };
                                @endphp
                                <tr data-booking-item data-created-at="{{ $booking->created_at?->timestamp ?? 0 }}" data-check-in="{{ $booking->check_in->toDateString() }}" data-check-out="{{ $booking->check_out->toDateString() }}">
                                    <td><strong>#{{ $booking->booking_id }}</strong></td>
                                    <td>{{ $booking->displayGuestName() }}</td>
                                    <td>{{ $booking->unit->unit_type }}<br><span class="text-muted fs-sm">{{ $booking->unit->house->name }}</span></td>
                                    <td>{{ $booking->check_in->format('M j, Y') }}</td>
                                    <td>{{ $booking->check_out->format('M j, Y') }}</td>
                                    <td>₱{{ number_format($booking->amountDueBeforeCheckout(), 2) }}</td>
                                    <td>
                                        @include('bookings.partials.status-indicator', [
                                            'booking' => $booking,
                                            'statusBadge' => $historyBadge,
                                            'statusLabel' => $canonicalStatus,
                                        ])
                                    </td>
                                    <td><a href="{{ route($dashboardPrefix.'.bookings.show', $booking->booking_id) }}" class="btn btn-outline btn-sm">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="empty-state" data-filter-empty hidden><i class="fa fa-filter"></i><h3>No bookings match these filters</h3></div>
        @endif
    </div>
</div>

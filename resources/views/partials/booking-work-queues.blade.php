<div class="container workspace-page staff-dashboard-page">
    <div class="page-header-row page-header">
        <div>
            <h1>Dashboard</h1>
            <p>Daily operations and items requiring attention.</p>
        </div>
    </div>

    @if ($errors->has('check_in'))
        <div class="alert alert-danger mt-3" role="alert">{{ $errors->first('check_in') }}</div>
    @endif
    @if (session('success'))
        <div class="alert alert-success mt-3" role="status">{{ session('success') }}</div>
    @endif

    <section class="operations-section" aria-labelledby="action-required-heading">
        <div class="operations-section-heading"><h2 id="action-required-heading">Action Required</h2></div>
        <div class="staff-dashboard-queues">
            <section class="card operations-card">
                <div class="card-header">
                    <span><i class="fa fa-calendar-check"></i> New Booking Requests</span>
                    <span class="badge badge-pending">{{ $pendingBookingsTotal }} pending</span>
                </div>
                <div class="operations-card-body">
                    @forelse ($pendingBookings as $booking)
                        <article class="operations-row">
                            <div class="operations-row-main">
                                <strong>{{ $booking->displayGuestName() }}</strong>
                                <span class="operations-row-meta">{{ $booking->unit->house->name }} · {{ $booking->unit->unit_type }}</span>
                                <span class="operations-row-detail">{{ $booking->check_in->format('M j') }}–{{ $booking->check_out->format('M j, Y') }} · {{ $booking->adult_count + $booking->child_count }} guests</span>
                            </div>
                            <div class="operations-row-side">
                                <span class="text-muted fs-sm">{{ $booking->booking_reference ?? '#'.$booking->booking_id }}</span>
                                <span class="text-muted fs-sm">Submitted {{ $booking->created_at->format('M j, g:i A') }}</span>
                            </div>
                            <a href="{{ route($dashboardPrefix.'.bookings.show', $booking->booking_id) }}" class="btn btn-outline btn-sm operations-action">Review</a>
                        </article>
                    @empty
                        <div class="operations-empty"><i class="fa fa-circle-check"></i><p>You're all caught up. No booking requests are waiting for review.</p></div>
                    @endforelse
                </div>
                @if ($pendingBookingsTotal > $pendingBookings->count())
                    <div class="operations-card-footer"><a href="{{ route($dashboardPrefix.'.bookings.index', ['tab' => 'pending']) }}" class="text-link">View all <span aria-hidden="true">→</span></a></div>
                @endif
            </section>

            <section class="card operations-card">
                <div class="card-header">
                    <span><i class="fa fa-receipt"></i> Payments to Verify</span>
                    <span class="badge badge-pending">{{ $paymentReviewsTotal }} pending</span>
                </div>
                <div class="operations-card-body">
                    @forelse ($paymentReviews as $booking)
                        @foreach ($booking->payments as $payment)
                            <article class="operations-row">
                                <div class="operations-row-main">
                                    <strong>{{ $booking->displayGuestName() }}</strong>
                                    <span class="operations-row-meta">{{ $booking->unit->house->name }} · {{ $payment->payment_method }}</span>
                                    <span class="operations-row-detail">₱{{ number_format($payment->amount, 2) }} submitted · {{ $payment->payment_date->format('M j, g:i A') }}</span>
                                </div>
                                <div class="operations-row-side">
                                    <span class="text-muted fs-sm">{{ $booking->booking_reference ?? '#'.$booking->booking_id }}</span>
                                    @if ($booking->payment_overdue)<span class="badge badge-cancelled">Deadline passed</span>@endif
                                </div>
                                <a href="{{ route($dashboardPrefix.'.bookings.show', $booking->booking_id) }}" class="btn btn-outline btn-sm operations-action">Verify</a>
                            </article>
                        @endforeach
                        @if ($booking->payments->isEmpty())
                            <article class="operations-row">
                                <div class="operations-row-main">
                                    <strong>{{ $booking->displayGuestName() }}</strong>
                                    <span class="operations-row-meta">{{ $booking->unit->house->name }}</span>
                                    <span class="operations-row-detail">₱{{ number_format($booking->downpaymentAmount(), 2) }} minimum amount</span>
                                </div>
                                <div class="operations-row-side">
                                    <span class="text-muted fs-sm">{{ $booking->booking_reference ?? '#'.$booking->booking_id }}</span>
                                    @if ($booking->payment_overdue)<span class="badge badge-cancelled">Deadline passed</span>@endif
                                </div>
                                <a href="{{ route($dashboardPrefix.'.bookings.show', $booking->booking_id) }}" class="btn btn-outline btn-sm operations-action">Follow up</a>
                            </article>
                        @endif
                    @empty
                        <div class="operations-empty"><i class="fa fa-circle-check"></i><p>No submitted payments currently require verification.</p></div>
                    @endforelse
                </div>
                @if ($paymentReviewsTotal > $paymentReviews->count())
                    <div class="operations-card-footer"><a href="{{ route($dashboardPrefix.'.bookings.index', ['tab' => 'accepted']) }}" class="text-link">View all <span aria-hidden="true">→</span></a></div>
                @endif
            </section>
        </div>
    </section>

    <section class="operations-section" aria-labelledby="guest-operations-heading">
        <div class="operations-section-heading"><h2 id="guest-operations-heading">Guest Operations</h2></div>
        <div class="staff-dashboard-queues">
            <section class="card operations-card">
                <div class="card-header">
                    <span><i class="fa fa-right-to-bracket"></i> Upcoming Check-ins</span>
                    <span class="badge badge-pending">{{ $upcomingCheckInsTotal }} arrivals</span>
                </div>
                <div class="operations-card-body">
                    @forelse ($upcomingCheckIns as $booking)
                        <article class="operations-row">
                            <div class="operations-row-main">
                                <strong>{{ $booking->displayGuestName() }}</strong>
                                <span class="operations-row-meta">{{ $booking->unit->house->name }} · {{ $booking->unit->unit_type }}</span>
                                <span class="operations-row-detail">{{ $booking->check_in->format('M j, Y') }} · {{ $booking->adult_count + $booking->child_count }} guests</span>
                            </div>
                            <div class="operations-row-side">
                                <span class="text-muted fs-sm">{{ $booking->booking_reference ?? '#'.$booking->booking_id }}</span>
                                @if ($booking->check_in->isToday())<span class="badge badge-pending">Today</span>@elseif ($booking->check_in->isTomorrow())<span class="badge badge-pending">Tomorrow</span>@endif
                            </div>
                            <button
                                type="button"
                                class="btn btn-primary btn-sm operations-action"
                                data-open-arrival-review="{{ $booking->booking_id }}"
                                aria-haspopup="dialog"
                                aria-controls="arrival-review-{{ $booking->booking_id }}"
                            >Review &amp; Check-in</button>
                        </article>
                        @include('partials.arrival-review-dialog', ['booking' => $booking, 'dashboardPrefix' => $dashboardPrefix, 'allowCheckIn' => true])
                    @empty
                        <div class="operations-empty"><i class="fa fa-calendar"></i><p>No upcoming arrivals scheduled.</p></div>
                    @endforelse
                </div>
                @if ($upcomingCheckInsTotal > $upcomingCheckIns->count())
                    <div class="operations-card-footer"><a href="{{ route($dashboardPrefix.'.bookings.index', ['tab' => 'accepted']) }}" class="text-link">View all <span aria-hidden="true">→</span></a></div>
                @endif
            </section>

            <section class="card operations-card">
                <div class="card-header">
                    <span><i class="fa fa-person-circle-check"></i> Current Guests</span>
                    <span class="badge badge-completed">{{ $currentGuestsTotal }} staying</span>
                </div>
                <div class="operations-card-body">
                    @forelse ($currentGuests as $booking)
                        <article class="operations-row">
                            <div class="operations-row-main">
                                <strong>{{ $booking->displayGuestName() }}</strong>
                                <span class="operations-row-meta">{{ $booking->unit->house->name }} · {{ $booking->unit->unit_type }}</span>
                                <span class="operations-row-detail">Check-out {{ $booking->check_out->format('M j, Y') }}</span>
                            </div>
                            <div class="operations-row-side">
                                <span class="text-muted fs-sm">{{ $booking->booking_reference ?? '#'.$booking->booking_id }}</span>
                                @if ($booking->check_out->isToday())<span class="badge badge-pending">Checking out today</span>@endif
                            </div>
                            <div class="operations-row-actions">
                                @if ($booking->discounts->isNotEmpty())
                                    <button
                                        type="button"
                                        class="btn btn-outline btn-sm"
                                        data-open-arrival-review="{{ $booking->booking_id }}"
                                        aria-haspopup="dialog"
                                        aria-controls="arrival-review-{{ $booking->booking_id }}"
                                    >Review IDs</button>
                                @endif
                                <a href="{{ route($dashboardPrefix.'.guests.check-in', $booking->booking_id) }}" class="btn btn-outline btn-sm">Manage Guest</a>
                            </div>
                        </article>
                        @if ($booking->discounts->isNotEmpty())
                            @include('partials.arrival-review-dialog', ['booking' => $booking, 'dashboardPrefix' => $dashboardPrefix, 'allowCheckIn' => false])
                        @endif
                    @empty
                        <div class="operations-empty"><i class="fa fa-bed"></i><p>No guests are currently checked in.</p></div>
                    @endforelse
                </div>
                @if ($currentGuestsTotal > $currentGuests->count())
                    <div class="operations-card-footer"><a href="{{ route($dashboardPrefix.'.bookings.index', ['tab' => 'accepted']) }}" class="text-link">View all <span aria-hidden="true">→</span></a></div>
                @endif
            </section>
        </div>
    </section>

    @if ($focusedReviewBooking && ! $upcomingCheckIns->contains('booking_id', $focusedReviewBooking->booking_id) && ! $currentGuests->contains('booking_id', $focusedReviewBooking->booking_id))
        @php
            $focusedReviewCanCheckIn = $focusedReviewBooking->status === \App\Support\BookingStatus::CONFIRMED
                && $focusedReviewBooking->payments->isNotEmpty()
                && (float) $focusedReviewBooking->payments->sum('amount') >= $focusedReviewBooking->downpaymentAmount();
        @endphp
        @include('partials.arrival-review-dialog', [
            'booking' => $focusedReviewBooking,
            'dashboardPrefix' => $dashboardPrefix,
            'allowCheckIn' => $focusedReviewCanCheckIn,
        ])
    @endif

    @if ($houses->isNotEmpty())
        <div class="dashboard-assigned-properties flex-center gap-1 mt-3 text-muted fs-sm" aria-label="Assigned properties">
            @foreach ($houses as $house)
                <span>{{ $house->name }}</span>
            @endforeach
        </div>
    @endif
</div>

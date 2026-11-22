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
                        <dialog class="arrival-review-dialog" id="arrival-review-{{ $booking->booking_id }}" aria-labelledby="arrival-review-title-{{ $booking->booking_id }}" aria-describedby="arrival-review-description-{{ $booking->booking_id }}">
                            <div class="arrival-review-header">
                                <div class="arrival-review-heading">
                                    <span class="arrival-review-icon" aria-hidden="true"><i class="fa fa-clipboard-check"></i></span>
                                    <div>
                                        <h2 id="arrival-review-title-{{ $booking->booking_id }}">Review arrival details</h2>
                                        <p id="arrival-review-description-{{ $booking->booking_id }}">Compare these details with the booking ticket and the guests present before completing check-in.</p>
                                    </div>
                                </div>
                                <button type="button" class="arrival-review-close" data-close-arrival-review aria-label="Close arrival review">&times;</button>
                            </div>
                            <div class="arrival-review-body">
                                <div class="arrival-review-summary">
                                    <div class="arrival-review-detail">
                                        <span>Booking ID</span>
                                        <strong>#{{ $booking->booking_id }}</strong>
                                    </div>
                                    <div class="arrival-review-detail">
                                        <span>Ticket reference</span>
                                        <strong>{{ $booking->booking_reference ?? 'Booking #'.$booking->booking_id }}</strong>
                                    </div>
                                    <div class="arrival-review-detail">
                                        <span>Primary guest</span>
                                        <strong>{{ $booking->displayGuestName() }}</strong>
                                    </div>
                                    <div class="arrival-review-detail">
                                        <span>Property &amp; unit</span>
                                        <strong>{{ $booking->unit->house->name }} · {{ $booking->unit->unit_type }}</strong>
                                    </div>
                                    <div class="arrival-review-detail">
                                        <span>Arrival date</span>
                                        <strong>{{ $booking->check_in->format('M j, Y') }}</strong>
                                    </div>
                                    <div class="arrival-review-detail">
                                        <span>Headcount</span>
                                        <strong>{{ $booking->adult_count }} {{ \Illuminate\Support\Str::plural('adult', $booking->adult_count) }}, {{ $booking->child_count }} {{ \Illuminate\Support\Str::plural('child', $booking->child_count) }} · {{ $booking->adult_count + $booking->child_count }} total</strong>
                                    </div>
                                </div>
                                <section class="arrival-review-guests" aria-labelledby="arrival-review-guests-{{ $booking->booking_id }}">
                                    <h3 id="arrival-review-guests-{{ $booking->booking_id }}">Guest names on the booking</h3>
                                    @if ($booking->guests->isNotEmpty())
                                        <ul>
                                            @foreach ($booking->guests as $bookingGuest)
                                                <li>
                                                    <span>{{ $bookingGuest->full_name }}</span>
                                                    <span class="arrival-review-guest-meta">{{ ucfirst($bookingGuest->guest_type) }} · {{ $bookingGuest->roster_status }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p>{{ $booking->displayGuestName() }}</p>
                                    @endif
                                </section>
                                <p class="arrival-review-note"><i class="fa fa-circle-info" aria-hidden="true"></i> Senior/PWD ID verification and headcount corrections remain available on the full Guest Check-in page.</p>
                                <a href="{{ route($dashboardPrefix.'.guests.check-in', $booking->booking_id) }}" class="arrival-review-manage-link">Open full Guest Check-in</a>
                                <form method="POST" action="{{ route($dashboardPrefix.'.bookings.check-in', $booking->booking_id) }}" class="arrival-review-form">
                                    @csrf
                                    <label class="arrival-review-confirm">
                                        <input type="checkbox" name="guest_details_confirmed" value="1" required>
                                        <span>I confirmed the ticket reference and guest names, and the headcount matches the guests present.</span>
                                    </label>
                                    <div class="arrival-review-actions">
                                        <button type="button" class="btn btn-outline" data-close-arrival-review>Cancel</button>
                                        <button type="submit" class="btn btn-primary">Confirm and check in</button>
                                    </div>
                                </form>
                            </div>
                        </dialog>
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
                            <a href="{{ route($dashboardPrefix.'.guests.check-in', $booking->booking_id) }}" class="btn btn-outline btn-sm operations-action">Manage Guest</a>
                        </article>
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

    @if ($houses->isNotEmpty())
        <div class="dashboard-assigned-properties flex-center gap-1 mt-3 text-muted fs-sm" aria-label="Assigned properties">
            @foreach ($houses as $house)
                <span>{{ $house->name }}</span>
            @endforeach
        </div>
    @endif
</div>

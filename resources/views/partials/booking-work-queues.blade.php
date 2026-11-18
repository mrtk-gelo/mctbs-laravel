<div class="container workspace-page staff-dashboard-page">
    <div class="page-header-row page-header">
        <div>
            <h1>Work Queue</h1>
            <p>Start with booking requests and payments that need your attention.</p>
        </div>
    </div>

    @if ($errors->has('check_in'))
        <div class="alert alert-danger mt-3" role="alert">{{ $errors->first('check_in') }}</div>
    @endif
    @if (session('success'))
        <div class="alert alert-success mt-3" role="status">{{ session('success') }}</div>
    @endif

    <div class="staff-dashboard-queues mt-3">
        <section class="card">
            <div class="card-header">
                <span><i class="fa fa-right-to-bracket"></i> Upcoming Check-ins</span>
                <span class="badge badge-pending">{{ $upcomingCheckIns->count() }}</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Guest / Unit</th><th>Check-in date</th><th>Booking reference</th><th>Guests</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($upcomingCheckIns as $booking)
                            <tr>
                                <td>
                                    <strong>{{ $booking->displayGuestName() }}</strong>
                                    <div class="text-muted fs-sm">{{ $booking->unit->unit_type }}</div>
                                </td>
                                <td>{{ $booking->check_in->format('M j, Y') }}                                </td>
                                <td>{{ $booking->booking_reference ?? 'Booking #'.$booking->booking_id }}</td>
                                <td>{{ $booking->adult_count + $booking->child_count }}</td>
                                <td><a href="{{ route($dashboardPrefix.'.guests.check-in', $booking->booking_id) }}" class="btn btn-primary btn-sm">Manage Check-in</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted">No upcoming guests expected.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <span><i class="fa fa-person-circle-check"></i> Current Guests</span>
                <span class="badge badge-pending">{{ $currentGuests->count() }}</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Guest / Unit</th><th>Booking reference</th><th>Check-out</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($currentGuests as $booking)
                            <tr>
                                <td>
                                    <strong>{{ $booking->displayGuestName() }}</strong>
                                    <div class="text-muted fs-sm">{{ $booking->unit->unit_type }}</div>
                                </td>
                                <td>{{ $booking->booking_reference ?? 'Booking #'.$booking->booking_id }}</td>
                                <td>{{ $booking->check_out->format('M j, Y') }}</td>
                                <td><a href="{{ route($dashboardPrefix.'.guests.check-in', $booking->booking_id) }}" class="btn btn-outline btn-sm">Manage Guest</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">No current guests.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @if ($houses->isEmpty())
        <div class="page-header-row page-header mt-3">
            <div>
                <h2 class="section-page-title">Transient Houses</h2>
            </div>
        </div>
        <div class="empty-state">
            <i class="fa fa-building"></i>
            <h3>No assigned properties</h3>
            <p>Booking requests and payment reviews will appear here when a property is assigned.</p>
        </div>
    @else
        <div class="page-header-row page-header mt-3">
            <div>
                <h2 class="section-page-title">Transient Houses</h2>
            </div>
        </div>
        <div class="staff-dashboard-queues">
            <section class="card">
                <div class="card-header">
                    <span><i class="fa fa-calendar-check"></i> New Booking Requests</span>
                    <span class="badge badge-pending">{{ $pendingBookings->count() }}</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Guest / Property</th><th>Stay</th><th>Requested</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingBookings as $booking)
                                <tr>
                                    <td>
                                        <strong>#{{ $booking->booking_id }} · {{ $booking->displayGuestName() }}</strong>
                                        <div class="text-muted fs-sm">{{ $booking->unit->unit_type }} · {{ $booking->unit->house->name }}</div>
                                    </td>
                                    <td>{{ $booking->check_in->format('M j') }}–{{ $booking->check_out->format('M j, Y') }}</td>
                                    <td>{{ $booking->created_at->diffForHumans() }}</td>
                                    <td><a href="{{ route($dashboardPrefix.'.bookings.show', $booking->booking_id) }}" class="btn btn-outline btn-sm">Review</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted">No booking requests need review.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="card">
                <div class="card-header">
                    <span><i class="fa fa-receipt"></i> Payments to Verify</span>
                    <span class="badge badge-pending">{{ $paymentReviews->sum(fn ($booking) => max(1, $booking->payments->count())) }}</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Booking / Guest</th><th>Submission / Deadline</th><th>Amount / Minimum</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse ($paymentReviews as $booking)
                                @foreach ($booking->payments as $payment)
                                    <tr>
                                        <td>
                                            <strong>#{{ $booking->booking_id }} · {{ $booking->displayGuestName() }}</strong>
                                            <div class="text-muted fs-sm">{{ $payment->payment_method }} · {{ $booking->unit->house->name }}</div>
                                        </td>
                                        <td>
                                            Submitted {{ $payment->payment_date->format('M j, g:i A') }}
                                            <div class="text-muted fs-sm">
                                                @if ($booking->payment_overdue)
                                                    Deadline passed; booking hold remains until Owner/Staff resolves it.
                                                @else
                                                    Deadline {{ $booking->payment_deadline?->format('M j, g:i A') }} · hold active while payment is reviewed.
                                                @endif
                                            </div>
                                        </td>
                                        <td>₱{{ number_format($payment->amount, 2) }}</td>
                                        <td><a href="{{ route($dashboardPrefix.'.bookings.show', $booking->booking_id) }}" class="btn btn-outline btn-sm">Verify</a></td>
                                    </tr>
                                @endforeach
                                @if ($booking->payments->isEmpty())
                                    <tr>
                                        <td><strong>#{{ $booking->booking_id }} · {{ $booking->displayGuestName() }}</strong><div class="text-muted fs-sm">{{ $booking->unit->house->name }}</div></td>
                                        <td>
                                            @if ($booking->payment_overdue)
                                                Overdue since {{ $booking->payment_deadline?->format('M j, g:i A') }}
                                            @else
                                                Due {{ $booking->payment_deadline?->format('M j, g:i A') }}
                                            @endif
                                        </td>
                                        <td>₱{{ number_format($booking->downpaymentAmount(), 2) }} min.</td>
                                        <td><a href="{{ route($dashboardPrefix.'.bookings.show', $booking->booking_id) }}" class="btn btn-outline btn-sm">Follow up</a></td>
                                    </tr>
                                @endif
                            @empty
                                <tr><td colspan="4" class="text-muted">No payment submissions need review.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

        </div>

        <div class="flex-center gap-1 mt-3 text-muted fs-sm" aria-label="Assigned properties">
            <strong>Properties:</strong>
            @foreach ($houses as $house)
                <span>{{ $house->name }} · {{ $house->city }}</span>
            @endforeach
        </div>
    @endif
</div>

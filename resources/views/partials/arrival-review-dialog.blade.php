@php
    $arrivalReviewAutoOpen = (int) session('openArrivalReview', 0) === $booking->booking_id
        || (int) request('review', 0) === $booking->booking_id;
@endphp

<dialog
    class="arrival-review-dialog"
    id="arrival-review-{{ $booking->booking_id }}"
    aria-labelledby="arrival-review-title-{{ $booking->booking_id }}"
    aria-describedby="arrival-review-description-{{ $booking->booking_id }}"
    data-auto-open-arrival-review="{{ $arrivalReviewAutoOpen ? 'true' : 'false' }}"
>
    <div class="arrival-review-header">
        <div class="arrival-review-heading">
            <span class="arrival-review-icon" aria-hidden="true"><i class="fa fa-clipboard-check"></i></span>
            <div>
                <h2 id="arrival-review-title-{{ $booking->booking_id }}">
                    {{ $allowCheckIn ? 'Review arrival details' : 'Review Senior/PWD IDs' }}
                </h2>
                <p id="arrival-review-description-{{ $booking->booking_id }}">
                    {{ $allowCheckIn
                        ? 'Compare these details with the booking ticket and the guests present before completing check-in.'
                        : 'Compare each submitted ID with the physical document and record the verification result.' }}
                </p>
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

        @if ($booking->discounts->isNotEmpty())
            <section class="arrival-review-discounts" aria-labelledby="arrival-review-discounts-{{ $booking->booking_id }}">
                <h3 id="arrival-review-discounts-{{ $booking->booking_id }}">Senior/PWD Verification</h3>
                <div class="arrival-review-discount-list">
                    @foreach ($booking->discounts as $discount)
                        @php
                            $discountStatusBadge = $discount->verification_status === 'Verified'
                                ? 'badge-verified'
                                : (in_array($discount->verification_status, ['Invalid', 'Rejected'], true) ? 'badge-unpaid' : 'badge-pending');
                            $canReviewDiscount = $discount->verification_status === \App\Support\BookingStatus::PENDING
                                && $discount->guest->roster_status === \App\Models\BookingGuest::ROSTER_ACTIVE;
                            $discountHasModalError = (int) old('dashboard_discount_id', 0) === $discount->discount_id;
                        @endphp
                        <article class="arrival-review-discount">
                            <div class="arrival-review-discount-header">
                                <strong>{{ $discount->guest->full_name }} · {{ $discount->discount_type }}</strong>
                                <span class="badge {{ $discountStatusBadge }}">{{ $discount->verification_status }}</span>
                            </div>
                            <p class="arrival-review-id-number">ID number: <strong>{{ $discount->id_reference }}</strong></p>
                            @if ($discountHasModalError && $errors->has('discount'))
                                <p class="form-hint text-danger" role="alert">{{ $errors->first('discount') }}</p>
                            @endif

                            @if ($discount->verification_status === 'Verified')
                                <div class="arrival-review-discount-amount">
                                    <span>{{ $discount->discount_type }} discount for {{ $discount->guest->full_name }}</span>
                                    <strong>−₱{{ number_format($discount->discount_amount, 2) }}</strong>
                                </div>
                            @elseif ($discount->verification_status === 'Invalid')
                                <p class="arrival-review-discount-note is-invalid">ID is invalid. Please tell the guest in person.</p>
                            @elseif ($canReviewDiscount)
                                <p class="arrival-review-discount-note">Compare the ID number with the guest’s physical ID before recording the result.</p>
                                <div class="arrival-review-discount-actions">
                                    <form method="POST" action="{{ route($dashboardPrefix.'.discounts.review', $discount->discount_id) }}">
                                        @csrf
                                        <input type="hidden" name="dashboard_modal" value="1">
                                        <input type="hidden" name="dashboard_booking_id" value="{{ $booking->booking_id }}">
                                        <input type="hidden" name="dashboard_discount_id" value="{{ $discount->discount_id }}">
                                        <button type="submit" name="status" value="Verified" class="btn btn-success btn-sm">Verify ID</button>
                                    </form>
                                    <details class="arrival-review-reject" @if ($discountHasModalError && $errors->has('verification_note')) open @endif>
                                        <summary class="btn btn-outline btn-sm">Reject ID</summary>
                                        <form method="POST" action="{{ route($dashboardPrefix.'.discounts.review', $discount->discount_id) }}" class="arrival-review-reject-form">
                                            @csrf
                                            <input type="hidden" name="dashboard_modal" value="1">
                                            <input type="hidden" name="dashboard_booking_id" value="{{ $booking->booking_id }}">
                                            <input type="hidden" name="dashboard_discount_id" value="{{ $discount->discount_id }}">
                                            <label class="required" for="arrival-review-note-{{ $discount->discount_id }}">Why can’t this ID be accepted?</label>
                                            <textarea id="arrival-review-note-{{ $discount->discount_id }}" name="verification_note" rows="2" maxlength="500" required>{{ $discountHasModalError ? old('verification_note') : '' }}</textarea>
                                            @if ($discountHasModalError && $errors->has('verification_note'))
                                                <p class="form-hint text-danger" role="alert">{{ $errors->first('verification_note') }}</p>
                                            @endif
                                            <button type="submit" name="status" value="Invalid" class="btn btn-danger btn-sm">Confirm rejection</button>
                                        </form>
                                    </details>
                                </div>
                            @elseif ($discount->verification_status === \App\Support\BookingStatus::PENDING)
                                <p class="arrival-review-discount-note">This guest was removed from the reservation, so the discount cannot be verified.</p>
                            @endif

                            @if (in_array($discount->verification_status, ['Invalid', 'Rejected'], true) && $discount->verification_note)
                                <p class="arrival-review-discount-note is-invalid">Reason: {{ $discount->verification_note }}</p>
                            @endif
                            @if ($discount->verifiedBy)
                                <p class="arrival-review-reviewer">Reviewed by {{ $discount->verifiedBy->first_name }} {{ $discount->verifiedBy->last_name }} · {{ $discount->verified_at?->format('M j, Y g:i A') }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
                <p class="arrival-review-discount-footnote">Each eligible discount applies only to the guest named above, not to the whole booking. A verified discount is reflected in the checkout breakdown.</p>
            </section>
        @endif

        @if ($allowCheckIn)
            <p class="arrival-review-note"><i class="fa fa-circle-info" aria-hidden="true"></i> Use the full Guest Check-in page for headcount corrections or other stay management.</p>
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
        @else
            <p class="arrival-review-note"><i class="fa fa-circle-info" aria-hidden="true"></i> Use the full Guest Check-in page for headcount corrections or other stay management.</p>
            <a href="{{ route($dashboardPrefix.'.guests.check-in', $booking->booking_id) }}" class="arrival-review-manage-link">Open full Guest Check-in</a>
        @endif
    </div>
</dialog>

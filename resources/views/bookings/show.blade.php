<x-shell title="Booking #{{ $booking->booking_id }} | MCTBS">
    <x-slot:nav>
        @if ($dashboardPrefix === 'owner')
            @include('partials.owner-nav', ['activePage' => 'bookings'])
        @else
            @include('partials.staff-nav', ['activePage' => 'bookings'])
        @endif
    </x-slot:nav>

    <div class="container" style="margin-top:24px">
        <div class="page-header-row page-header">
            <div>
                @if ($booking->status === \App\Support\BookingStatus::CHECKED_IN)
                    <a href="{{ route($dashboardPrefix.'.guests.check-in', $booking->booking_id) }}" class="text-muted fs-sm"><i class="fa fa-arrow-left"></i> Back to Guest Check-in</a>
                @else
                    <a href="{{ route($dashboardPrefix.'.bookings.index') }}" class="text-muted fs-sm"><i class="fa fa-arrow-left"></i> Back to bookings</a>
                @endif
                <h1 class="mt-1">Booking #{{ $booking->booking_id }}</h1>
                <p>{{ $booking->unit->unit_type }} — {{ $booking->unit->house->name }}</p>
            </div>
            <div class="flex-center gap-1">
                @php
                    $statusBadge = match ($booking->status) {
                        \App\Support\BookingStatus::PENDING => 'badge-pending',
                        \App\Support\BookingStatus::ACCEPTED, \App\Support\BookingStatus::CONFIRMED => 'badge-accepted',
                        \App\Support\BookingStatus::REJECTED => 'badge-rejected',
                        default => \App\Support\BookingStatus::isTerminal($booking->status)
                            ? 'badge-completed'
                            : 'badge-cancelled',
                    };
                    $statusLabel = \App\Support\BookingStatus::canonical($booking->status);
                    $hasPaymentUnderReview = $booking->payments->contains('payment_status', \App\Support\PaymentStatus::UNDER_REVIEW);
                    $bookingTotalDue = $booking->amountDueAtCheckout();
                    $bookingBalance = $booking->outstandingBalance();
                    $verifiedBadge = $booking->status === \App\Support\BookingStatus::ACCEPTED
                        ? ($hasPaymentUnderReview ? 'badge-pending' : 'badge-unpaid')
                        : ($bookingBalance === 0.0
                            ? 'badge-paid'
                            : ($verifiedPaymentTotal > 0 ? 'badge-downpaid' : 'badge-unpaid'));
                    $paymentLabel = $booking->status === \App\Support\BookingStatus::ACCEPTED
                        ? ($hasPaymentUnderReview ? 'Downpayment under review' : 'Downpayment due')
                        : ($bookingBalance === 0.0
                            ? 'Paid'
                            : ($verifiedPaymentTotal > 0 ? 'Partially paid' : 'Due at checkout'));
                @endphp
                @include('bookings.partials.status-indicator', [
                    'booking' => $booking,
                    'statusBadge' => $statusBadge,
                    'statusLabel' => $statusLabel,
                ])
                <span class="badge {{ $verifiedBadge }}">{{ $paymentLabel }}</span>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success mb-3" role="status">{{ session('success') }}</div>
        @endif
        @if ($isPaymentOverdue)
            <div class="alert alert-warning mb-3" role="status">
                The payment deadline has passed without a qualifying downpayment. This booking remains on hold until Owner/Staff resolves it.
            </div>
        @endif
        @if ($booking->status === \App\Support\BookingStatus::REJECTED && $booking->rejection_reason)
            <div class="alert alert-danger mb-3" role="status">
                <strong>Rejection reason:</strong> {{ $booking->rejection_reason }}
            </div>
        @endif

        @php
            $nights = max(1, $booking->check_in->diffInDays($booking->check_out));
            $activeGuests = $booking->activeGuests;
            $payingGuestCount = $activeGuests->isNotEmpty()
                ? $activeGuests->where('is_free_child', false)->count()
                : null;
            $nightlyRate = (float) ($booking->nightly_rate ?? $booking->unit->price);
            $roomQuantity = ($booking->pricing_mode ?? 'Per Unit') === 'Per Head'
                ? ($payingGuestCount === null ? 'Paying guests as booked' : $payingGuestCount.' paying guests').' × '.$nights.' nights'
                : $nights.' nights';
            $roomPrice = '₱'.number_format($nightlyRate, 2)
                .(($booking->pricing_mode ?? 'Per Unit') === 'Per Head' ? ' / guest / night' : ' / unit / night');
            $approvedDiscountTotal = \App\Support\BookingFinancialContract::approvedDiscounts($booking);
        @endphp
        <div class="booking-details-layout">
            <section class="card booking-details-summary">
                <div class="card-header">Booking Summary</div>
                <div class="card-body">
                    <div class="fw-bold">{{ $booking->unit->unit_type }}</div>
                    <div class="text-muted fs-sm mb-3">{{ $booking->unit->house->name }} — {{ $booking->unit->house->city }}</div>
                    <div class="flex-between mb-2"><span>Check-in</span><strong>{{ $booking->check_in->format('M j, Y') }}</strong></div>
                    <div class="flex-between mb-2"><span>Check-out</span><strong>{{ $booking->check_out->format('M j, Y') }}</strong></div>
                    <div class="flex-between mb-2"><span>Nights</span><strong>{{ $nights }}</strong></div>
                    <div class="flex-between mb-2"><span>Guests</span><strong>{{ $booking->adult_count + $booking->child_count }}</strong></div>
                    <hr class="divider">
                    <div class="booking-price-line">
                        <span>Stay/room charge <small class="booking-price-detail">{{ $roomQuantity }} × {{ $roomPrice }}</small></span>
                        <strong class="booking-price-amount">₱{{ number_format(\App\Support\BookingFinancialContract::roomAmountBeforeDiscounts($booking), 2) }}</strong>
                    </div>
                    @if ((int) $booking->extra_guest_count > 0)
                        <div class="booking-price-line">
                            <span>Extra heads <small class="booking-price-detail">{{ $booking->extra_guest_count }} guests × {{ $nights }} nights × ₱{{ number_format((float) $booking->extra_guest_fee, 2) }} / guest / night</small></span>
                            <strong class="booking-price-amount">₱{{ number_format((float) $booking->extra_guest_total, 2) }}</strong>
                        </div>
                    @endif
                    @if ($approvedDiscountTotal > 0)
                        <div class="booking-price-line">
                            <span>Senior/PWD discount</span>
                            <strong class="booking-price-amount booking-price-amount--green">−₱{{ number_format($approvedDiscountTotal, 2) }}</strong>
                        </div>
                    @endif
                    <div class="booking-price-line booking-price-line--total">
                        <span>Stay total</span>
                        <strong class="booking-price-amount">₱{{ number_format($booking->amountDueBeforeCheckout(), 2) }}</strong>
                    </div>
                </div>
            </section>

            <section class="card booking-details-payments">
                <div class="card-header">Payments</div>
                <div class="card-body">
                    <div class="booking-price-line">
                        <span>Payments made <small class="booking-price-detail">Verified total / amount due at checkout</small></span>
                        <strong class="booking-price-amount booking-price-amount--green">₱{{ number_format((float) $verifiedPaymentTotal, 2) }} / ₱{{ number_format((float) $bookingTotalDue, 2) }}</strong>
                    </div>
                    <div class="booking-price-line booking-price-line--total">
                        <span>Balance to pay</span>
                        <strong class="booking-price-amount booking-price-amount--{{ $bookingBalance > 0 ? 'red' : 'green' }}">₱{{ number_format((float) $bookingBalance, 2) }}</strong>
                    </div>
                    @forelse ($booking->payments->sortByDesc('payment_date') as $payment)
                        @php
                            $paymentBadge = match ($payment->payment_status) {
                                \App\Support\PaymentStatus::VERIFIED => 'badge-verified',
                                \App\Support\PaymentStatus::UNDER_REVIEW => 'badge-pending',
                                default => 'badge-unpaid',
                            };
                        @endphp
                        <div class="card mb-2">
                            <div class="card-body">
                                <div class="flex-between" style="gap:10px">
                                    <strong>{{ $payment->payment_method }} · <span class="booking-price-amount--green">₱{{ number_format($payment->amount, 2) }}</span></strong>
                                    <span class="badge {{ $paymentBadge }}">{{ $payment->payment_status }}</span>
                                </div>
                                <div class="text-muted fs-sm mt-1">{{ $payment->payment_date->format('M j, Y g:i A') }}</div>
                                @if ($payment->reference_number)
                                    <div class="text-muted fs-sm">Reference: {{ $payment->reference_number }}</div>
                                @endif
                                @if ($payment->payment_receipt)
                                    <button type="button" class="btn btn-outline btn-sm mt-2" data-proof-viewer data-proof-url="{{ route('payments.proof', $payment->payment_id) }}" data-proof-download-url="{{ route('payments.proof', [$payment->payment_id, 'download' => 1]) }}" data-proof-type="{{ strtolower(pathinfo($payment->payment_receipt, PATHINFO_EXTENSION)) }}" data-proof-reference="{{ $payment->reference_number ?: '—' }}" data-proof-amount="₱{{ number_format((float) $payment->amount, 2) }}">View Receipt</button>
                                @endif
                                @if ($payment->notes)
                                    <p class="text-muted fs-sm mt-2 mb-0">{{ $payment->notes }}</p>
                                @endif

                                @if ($payment->payment_status === $underReviewStatus && in_array($booking->status, [\App\Support\BookingStatus::ACCEPTED, \App\Support\BookingStatus::CONFIRMED], true))
                                    <form method="POST" action="{{ route($dashboardPrefix.'.payments.review', $payment->payment_id) }}" class="mt-2">
                                        @csrf
                                        <div class="btn-group">
                                            <button type="submit" name="status" value="{{ \App\Support\PaymentStatus::VERIFIED }}" class="btn btn-success">Verify</button>
                                            <button type="button" class="btn btn-danger" data-show-payment-reason>Reject</button>
                                        </div>
                                        <div class="form-group mt-2" data-payment-reason-panel @if (! $errors->has('notes')) hidden @endif>
                                            <label class="required" for="payment-notes-{{ $payment->payment_id }}">Why can’t this payment be accepted?</label>
                                            <textarea id="payment-notes-{{ $payment->payment_id }}" name="notes" rows="2" maxlength="500" data-payment-review-note>{{ old('notes') }}</textarea>
                                            @error('notes')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                                            <div class="btn-group mt-2">
                                                <button type="submit" name="status" value="{{ \App\Support\PaymentStatus::INCORRECT }}" class="btn btn-danger">Reject payment</button>
                                                <button type="button" class="btn btn-outline" data-hide-payment-reason>Go back</button>
                                            </div>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No payments have been submitted.</p>
                    @endforelse
                </div>
            </section>

            <div class="booking-details-side">
                <section class="card booking-details-guest">
                    <div class="card-header">Guest Info</div>
                    <div class="card-body">
                        <div class="fw-bold">{{ $booking->displayGuestName() }}</div>
                        @if ($booking->displayGuestEmail())
                            <div class="text-muted fs-sm">{{ $booking->displayGuestEmail() }}</div>
                        @endif
                        @if ($booking->displayGuestPhone())
                            <div class="text-muted fs-sm">{{ $booking->displayGuestPhone() }}</div>
                        @endif
                    </div>
                </section>

                @unless ($booking->status === \App\Support\BookingStatus::CHECKED_IN)
                    <section class="card booking-details-actions">
                        <div class="card-header">Actions</div>
                        <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
                        @if ($booking->status === \App\Support\BookingStatus::PENDING)
                            @if ($booking->has_active_hold)
                                <p class="form-hint">Another booking has reserved overlapping dates.</p>
                            @elseif ($booking->queue_position > 1)
                                <p class="form-hint">This is queue position {{ $booking->queue_position }}. Accept the earliest overlapping request first.</p>
                            @else
                                <form method="POST" action="{{ route($dashboardPrefix.'.bookings.accept', $booking->booking_id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-block">Accept Booking Request</button>
                                </form>
                            @endif
                            <button type="button" class="btn btn-danger btn-block" data-modal-open="reject-booking-modal">Reject Booking Request</button>
                            <button type="button" class="btn btn-outline btn-block" data-modal-open="cancel-booking-modal">Cancel Booking</button>
                        @elseif (in_array($booking->status, [\App\Support\BookingStatus::ACCEPTED, \App\Support\BookingStatus::CONFIRMED], true))
                            <button type="button" class="btn btn-danger btn-block" data-modal-open="cancel-booking-modal">Cancel Booking</button>
                        @else
                            <p class="text-muted mb-0">No booking actions are available for this status.</p>
                        @endif
                        </div>
                    </section>
                @endunless
            </div>
        </div>

        @if ($booking->review || $booking->legacyFeedback->isNotEmpty())
            <section class="card mt-3">
                <div class="card-header">Guest Feedback</div>
                <div class="card-body">
                    @if ($booking->review)
                        <article>
                            <strong>Current review · {{ $booking->review->displayGuestName() }}</strong>
                            <div class="text-muted fs-sm">{{ $booking->review->created_at->format('M j, Y') }} · {{ $booking->review->rating }} out of 5</div>
                            @if ($booking->review->comment)
                                <p class="mb-0 mt-2">{{ $booking->review->comment }}</p>
                            @endif
                        </article>
                    @endif
                    @foreach ($booking->legacyFeedback as $feedback)
                        <article @if ($booking->review || ! $loop->first) class="mt-3" @endif>
                            <strong>Historical feedback · {{ trim(($feedback->user?->first_name ?? '').' '.($feedback->user?->last_name ?? '')) ?: $booking->displayGuestName() }}</strong>
                            <div class="text-muted fs-sm">{{ $feedback->created_at?->format('M j, Y') }} · {{ $feedback->rating }} out of 5 · {{ $feedback->status }}</div>
                            @if ($feedback->review)
                                <p class="mb-0 mt-2">{{ $feedback->review }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    @if ($booking->status === \App\Support\BookingStatus::PENDING)
        <div id="reject-booking-modal" class="modal-overlay" aria-hidden="true">
            <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="reject-modal-title">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)">
                    <strong id="reject-modal-title">Reject Booking Request</strong>
                    <button type="button" class="btn btn-outline btn-sm" data-modal-close aria-label="Close">Close</button>
                </div>
                <div style="padding:20px">
                    <form method="POST" action="{{ route($dashboardPrefix.'.bookings.reject', $booking->booking_id) }}">
                        @csrf
                        <input type="hidden" name="booking_action" value="reject">
                        <div class="form-group">
                            <label class="required" for="rejection-reason">Why are you rejecting this booking?</label>
                            <textarea id="rejection-reason" name="reason" rows="3" maxlength="500" required>{{ old('reason') }}</textarea>
                            @error('reason')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-danger">Reject Booking Request</button>
                            <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if (in_array($booking->status, [\App\Support\BookingStatus::PENDING, \App\Support\BookingStatus::ACCEPTED, \App\Support\BookingStatus::CONFIRMED], true))
        <div id="cancel-booking-modal" class="modal-overlay" aria-hidden="true">
            <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="cancel-modal-title">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)">
                    <strong id="cancel-modal-title">Cancel Booking</strong>
                    <button type="button" class="btn btn-outline btn-sm" data-modal-close aria-label="Close">Close</button>
                </div>
                <div style="padding:20px">
                    <div class="policy-box">The Guest will be notified and the booking hold will be released.</div>
                    <form method="POST" action="{{ route($dashboardPrefix.'.bookings.cancel', $booking->booking_id) }}">
                        @csrf
                        <input type="hidden" name="booking_action" value="cancel">
                        <div class="form-group">
                            <label class="required" for="cancellation-reason">Why are you cancelling this booking?</label>
                            <textarea id="cancellation-reason" name="reason" rows="3" maxlength="500" required>{{ old('reason') }}</textarea>
                            @error('reason')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-danger">Confirm Cancel</button>
                            <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.querySelectorAll('[data-modal-open]').forEach((button) => {
            button.addEventListener('click', () => {
                const modal = document.getElementById(button.dataset.modalOpen);
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
            });
        });
        document.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => {
                const modal = button.closest('.modal-overlay');
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
            });
        });
        document.querySelectorAll('.modal-overlay').forEach((modal) => {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.classList.remove('open');
                    modal.setAttribute('aria-hidden', 'true');
                }
            });
        });
        document.querySelectorAll('[data-payment-review-note]').forEach((notes) => {
            const form = notes.closest('form');
            const panel = form.querySelector('[data-payment-reason-panel]');
            const rejectButton = form.querySelector('[data-show-payment-reason]');
            const backButton = form.querySelector('[data-hide-payment-reason]');

            rejectButton.addEventListener('click', () => {
                panel.hidden = false;
                notes.required = true;
                notes.focus();
            });
            backButton.addEventListener('click', () => {
                panel.hidden = true;
                notes.required = false;
                notes.value = '';
            });
        });
        @if ($errors->has('reason'))
            const reasonModalId = @json(old('booking_action') === 'cancel' ? 'cancel-booking-modal' : 'reject-booking-modal');
            document.getElementById(reasonModalId)?.classList.add('open');
            document.getElementById(reasonModalId)?.setAttribute('aria-hidden', 'false');
        @endif
    </script>
    @include('partials.payment-proof-modal')
</x-shell>

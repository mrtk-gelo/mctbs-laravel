<x-shell title="Booking #{{ $booking->booking_id }} | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'bookings'])</x-slot:nav>

    <style>
        .guest-booking-cancel > summary {
            cursor: pointer;
            list-style: none;
        }
        .guest-booking-cancel > summary::-webkit-details-marker {
            display: none;
        }
        .booking-progress {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            max-width: 900px;
            margin: 20px auto 28px;
        }
        .booking-progress-step {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 7px;
            color: var(--text-muted);
            text-align: center;
            font-size: 12px;
        }
        .booking-progress-step:not(:last-child)::after {
            position: absolute;
            top: 15px;
            left: 50%;
            z-index: 0;
            width: 100%;
            height: 2px;
            background: var(--border);
            content: '';
        }
        .booking-progress-step.is-done:not(:last-child)::after {
            background: var(--success);
        }
        .booking-progress-marker {
            z-index: 1;
            display: grid;
            width: 32px;
            height: 32px;
            place-items: center;
            border: 2px solid var(--border);
            border-radius: 50%;
            background: var(--card);
            font-weight: 700;
        }
        .booking-progress-step.is-active { color: var(--accent); font-weight: 700; }
        .booking-progress-step.is-active .booking-progress-marker {
            border-color: var(--accent);
            background: var(--accent);
            color: #fff;
        }
        .booking-progress-step.is-done { color: var(--success); }
        .booking-progress-step.is-done .booking-progress-marker {
            border-color: var(--success);
            background: var(--success);
            color: #fff;
        }
        @media (max-width: 600px) {
            .booking-progress-step { font-size: 10px; }
            .booking-progress-marker { width: 28px; height: 28px; }
            .booking-progress-step:not(:last-child)::after { top: 13px; }
        }
    </style>

    <div class="container" style="margin-top:24px">


        <div class="page-header-row page-header">
            <div>
                <h1 class="mt-1">Booking #{{ $booking->booking_id }}</h1>
                <p>{{ $booking->unit->unit_type }} — {{ $booking->unit->house->name }}</p>
            </div>
            @php
                $checkoutTotal = $booking->amountDueAtCheckout();
                $checkoutBalance = $booking->outstandingBalance();
                $approvedDiscountTotal = \App\Support\BookingFinancialContract::approvedDiscounts($booking);
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
            @endphp
        </div>

        @if (session('info'))
            <div class="alert alert-info mb-3">{{ session('info') }}</div>
        @endif
        @if (session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif
        <livewire:guest.booking-status :booking="$booking" :key="'guest-booking-status-'.$booking->booking_id" />

        <div class="row">
            <div class="col-2">
                <div class="card mb-3">
                    <div class="card-header">Booking Details</div>
                    <div class="card-body" style="padding:0">
                        <div style="display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid var(--border)">
                            <div style="padding:12px 16px;border-right:1px solid var(--border)">
                                <div class="text-muted fs-sm">CHECK-IN</div>
                                <strong>{{ $booking->check_in->format('M j, Y') }}</strong>
                            </div>
                            <div style="padding:12px 16px">
                                <div class="text-muted fs-sm">CHECK-OUT</div>
                                <strong>{{ $booking->check_out->format('M j, Y') }}</strong>
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(2,1fr);border-bottom:1px solid var(--border);text-align:center">
                            <div style="padding:10px;border-right:1px solid var(--border)">
                                <div class="text-muted fs-sm">Nights</div>
                                <strong>{{ $nights }}</strong>
                            </div>
                            <div style="padding:10px">
                                <div class="text-muted fs-sm">Per night</div>
                                <strong>₱{{ number_format($nightlyRate, 2) }}</strong>
                            </div>
                        </div>
                        <div class="card-body">
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
                                    <span>Approved Senior/PWD discounts</span>
                                    <strong class="booking-price-amount booking-price-amount--green">−₱{{ number_format($approvedDiscountTotal, 2) }}</strong>
                                </div>
                            @endif
                            <div class="booking-price-line booking-price-line--total">
                                <span>Stay and extra guest total</span>
                                <strong class="booking-price-amount">₱{{ number_format($booking->amountDueBeforeCheckout(), 2) }}</strong>
                            </div>
                            @foreach ($booking->addons as $addon)
                                @if ((float) $addon->price * (int) $addon->quantity > 0)
                                    <div class="booking-price-line">
                                        <span>Added amenities · {{ $addon->name }} × {{ $addon->quantity }}</span>
                                        <strong class="booking-price-amount booking-price-amount--blue">₱{{ number_format((float) $addon->price * $addon->quantity, 2) }}</strong>
                                    </div>
                                @endif
                            @endforeach
                            @foreach ($booking->damages as $damage)
                                @if ((float) $damage->price * (int) $damage->quantity > 0)
                                    <div class="booking-price-line">
                                        <span>Damage/missing items · {{ $damage->name }} × {{ $damage->quantity }} <small>(not included in amount due)</small></span>
                                        <strong class="booking-price-amount booking-price-amount--red">₱{{ number_format((float) $damage->price * $damage->quantity, 2) }}</strong>
                                    </div>
                                @endif
                            @endforeach
                            @foreach ($booking->additionalCharges as $charge)
                                @if ((float) $charge->total_amount > 0)
                                    <div class="booking-price-line">
                                        <span>Damage/missing items · {{ $charge->description }} × {{ $charge->quantity }}</span>
                                        <strong class="booking-price-amount booking-price-amount--red">₱{{ number_format((float) $charge->total_amount, 2) }}</strong>
                                    </div>
                                @endif
                            @endforeach
                            @foreach ($booking->damageReports as $damage)
                                @if ((float) $damage->amount > 0)
                                    <div class="booking-price-line">
                                        <span>
                                            Damage/missing items · {{ $damage->description }}
                                            @if ((int) $damage->quantity > 1) × {{ $damage->quantity }} @endif
                                            ({{ $damage->isIncludedInFinalAmount() ? 'included in amount due' : 'not included in amount due' }})
                                        </span>
                                        <strong class="booking-price-amount booking-price-amount--red">₱{{ number_format((float) $damage->amount, 2) }}</strong>
                                    </div>
                                @endif
                            @endforeach
                            <div class="booking-price-line booking-price-line--total">
                                <span>{{ $approvedDiscountTotal > 0 ? 'Current total after approved discounts and checkout charges' : 'Estimated total before discount verification, including checkout charges' }}</span>
                                <strong class="booking-price-amount">₱{{ number_format((float) $checkoutTotal, 2) }}</strong>
                            </div>
                            @if ($verifiedPaymentTotal > 0)
                                <div class="booking-price-line">
                                    <span>Payments made</span>
                                    <strong class="booking-price-amount booking-price-amount--green">−₱{{ number_format((float) $verifiedPaymentTotal, 2) }}</strong>
                                </div>
                            @endif
                            <div class="booking-price-line booking-price-line--total">
                                <span>{{ $checkoutBalance > 0 ? 'Balance due at checkout' : 'Paid in full' }}</span>
                                <strong class="booking-price-amount booking-price-amount--{{ $checkoutBalance > 0 ? 'red' : 'green' }}">₱{{ number_format((float) $checkoutBalance, 2) }}</strong>
                            </div>
                            <p class="form-hint mt-2 mb-0">Verified payments count toward your balance. Show your Senior/PWD ID at checkout for the 20% discount.</p>
                            @if ($booking->discounts->isNotEmpty())
                                <hr class="divider">
                                <strong>Discount requests</strong>
                                @foreach ($booking->discounts as $discount)
                                    <div class="flex-between mt-1" style="gap:8px">
                                        <span>{{ $discount->discount_type }} discount for {{ $discount->guest->full_name }}
                                            @if ($discount->verification_status === \App\Support\PaymentStatus::VERIFIED)
                                                · ₱{{ number_format($discount->discount_amount, 2) }}
                                            @else
                                                · physical ID to be checked at checkout
                                            @endif
                                        </span>
                                        <span class="badge badge-pending">{{ $discount->verification_status }}</span>
                                        </div>
                                        @if (in_array($discount->verification_status, ['Invalid', 'Rejected'], true) && $discount->verification_note)
                                            <p class="form-hint text-danger mt-1 mb-0">Reason: {{ $discount->verification_note }}</p>
                                        @endif
                                @endforeach
                                <p class="form-hint mt-2 mb-0">Each verified ID gives 20% off that guest’s share. One discount per guest.</p>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($booking->edits->isNotEmpty())
                    <div class="card mb-3">
                        <div class="card-header">Booking Change History</div>
                        <div class="card-body">
                            @foreach ($booking->edits->sortByDesc('created_at') as $edit)
                                <div class="fw-bold">Updated by {{ $edit->editor->first_name }} {{ $edit->editor->last_name }}</div>
                                <div class="text-muted fs-sm">{{ $edit->created_at->format('M j, Y g:i A') }}</div>
                                <div class="fs-sm mt-1">{{ $edit->old_check_in->format('M j, Y') }} – {{ $edit->old_check_out->format('M j, Y') }} changed to {{ $edit->new_check_in->format('M j, Y') }} – {{ $edit->new_check_out->format('M j, Y') }}</div>
                                <div class="fs-sm">Total: ₱{{ number_format($edit->old_total_price, 2) }} → ₱{{ number_format($edit->new_total_price, 2) }}</div>
                                <p class="text-muted fs-sm mt-1 mb-0">{{ $edit->edit_reason }}</p>
                                @if (! $loop->last)
                                    <hr class="divider">
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">My Payment Submissions</div>
                    <div class="card-body">
                        @forelse ($booking->payments->sortByDesc('payment_date') as $payment)
                            @php
                                $submissionBadge = match ($payment->payment_status) {
                                    \App\Support\PaymentStatus::VERIFIED => 'badge-verified',
                                    \App\Support\PaymentStatus::UNDER_REVIEW => 'badge-pending',
                                    default => 'badge-unpaid',
                                };
                            @endphp
                            <div class="mb-3">
                                <div class="flex-between" style="gap:10px">
                                    <strong>{{ $payment->payment_method }} · <span class="booking-price-amount--green">₱{{ number_format($payment->amount, 2) }}</span></strong>
                                    <span class="badge {{ $submissionBadge }}">{{ $payment->payment_status }}</span>
                                </div>
                                <div class="text-muted fs-sm mt-1">{{ $payment->payment_date->format('M j, Y g:i A') }}</div>
                                @if ($payment->reference_number)
                                    <div class="text-muted fs-sm">Reference: {{ $payment->reference_number }}</div>
                                @endif
                                @if ($payment->notes)
                                    <p class="text-muted fs-sm mt-1 mb-0">{{ $payment->notes }}</p>
                                @endif
                                @if ($payment->payment_receipt)
                                    <button type="button" class="btn btn-outline btn-sm mt-1" data-proof-viewer data-proof-url="{{ route('payments.proof', $payment->payment_id) }}" data-proof-download-url="{{ route('payments.proof', [$payment->payment_id, 'download' => 1]) }}" data-proof-type="{{ strtolower(pathinfo($payment->payment_receipt, PATHINFO_EXTENSION)) }}" data-proof-reference="{{ $payment->reference_number ?: '—' }}" data-proof-amount="₱{{ number_format((float) $payment->amount, 2) }}">View submitted proof</button>
                                @endif
                            </div>
                            @if (! $loop->last)
                                <hr class="divider">
                            @endif
                        @empty
                            <p class="text-muted mb-0">You have not submitted a payment yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-fixed-300">
                @if (\App\Support\BookingStatus::isTerminal($booking->status) && $booking->receipt)
                    <div class="booking-breakdown-action mb-3">
                        <a href="{{ route('guest.bookings.receipt', $booking->booking_id) }}" class="btn btn-primary">View Booking Breakdown</a>
                    </div>
                @endif
                <div class="booking-details-actions mb-3" style="display:flex;flex-direction:column;gap:10px">
                    @if (in_array($booking->status, [\App\Support\BookingStatus::CONFIRMED, \App\Support\BookingStatus::CHECKED_IN], true))
                        <div data-booking-ticket-action>
                            <a href="{{ route('guest.bookings.ticket', $booking->booking_id) }}" class="btn btn-success btn-block">Download Ticket</a>
                            <p class="form-hint mt-1 mb-0">Download and show this ticket on check-in day.</p>
                        </div>
                    @endif
                    @if ($booking->status === \App\Support\BookingStatus::ACCEPTED && ! $hasUnderReviewPayment && $verifiedPaymentTotal < $checkoutTotal)
                        <div class="guest-payment-action">
                            <a href="{{ route('guest.bookings.payment', $booking->booking_id) }}" class="btn btn-primary btn-block">Pay Now</a>
                        </div>
                    @endif
                    @if ($booking->status === \App\Support\BookingStatus::PENDING || (in_array($booking->status, [\App\Support\BookingStatus::ACCEPTED, \App\Support\BookingStatus::CONFIRMED], true) && $booking->check_in->toDateString() > today()->toDateString()))
                        <details class="policy-box guest-booking-cancel" @if ($errors->has('reason')) open @endif>
                            <summary class="btn btn-danger btn-block">Cancel this booking</summary>
                            <form method="POST" action="{{ route('guest.bookings.cancel', $booking->booking_id) }}" class="mt-2">
                                @csrf
                                @if ($hasUnderReviewPayment || $verifiedPaymentTotal > 0)
                                    <p class="form-hint">Ask staff about refunds.</p>
                                @endif
                                <label class="required" for="guest-cancellation-reason">Why are you cancelling?</label>
                                <textarea id="guest-cancellation-reason" name="reason" rows="2" maxlength="500" required>{{ old('reason') }}</textarea>
                                @error('reason')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                                <button type="submit" class="btn btn-danger btn-block mt-2">Confirm cancellation</button>
                            </form>
                        </details>
                    @endif
                </div>

                @if ($booking->refunds->isNotEmpty())
                    <div class="card mb-3">
                        <div class="card-header">Refund Status</div>
                        <div class="card-body">
                            @foreach ($booking->refunds as $refund)
                                <div class="flex-between mb-2">
                                    <strong class="booking-price-amount--orange">₱{{ number_format($refund->amount, 2) }}</strong>
                                    <span class="badge {{ strtolower($refund->refund_status) === 'refunded' ? 'badge-verified' : 'badge-pending' }}">{{ $refund->refund_status }}</span>
                                </div>
                                <p class="text-muted fs-sm">{{ $refund->reason }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
    @include('partials.payment-proof-modal')
</x-shell>

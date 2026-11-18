<x-shell title="Booking Breakdown {{ $receipt->receipt_number }} | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'bookings'])</x-slot:nav>

    <div class="container-sm" style="margin-top:24px">
        <div class="flex-between mb-3">
            <a href="{{ route('guest.bookings.show', $booking->booking_id) }}" class="text-muted fs-sm"><i class="fa fa-arrow-left"></i> Back to booking</a>
            <a href="{{ route('guest.bookings.receipt.pdf', $booking->booking_id) }}" class="btn btn-outline btn-sm"><i class="fa fa-download"></i> Download PDF</a>
        </div>
        <article class="card receipt-print">
            <header style="background:var(--primary-lt);padding:28px;text-align:center;color:#fff">
                <h1 style="color:#fff">MCTBS</h1>
                <p>Motal and Conteros Transient Booking System</p>
            </header>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="text-muted fs-sm">Booking Breakdown</div>
                    <h2>{{ $receipt->receipt_number }}</h2>
                    <span class="badge badge-completed">{{ \App\Support\BookingStatus::canonical($booking->status) }}</span>
                </div>

                <div class="row mb-3">
                    <section class="col">
                        <h3 class="fs-sm text-muted mb-2">Guest information</h3>
                        <strong>{{ $booking->displayGuestName() }}</strong>
                        @if ($booking->displayGuestEmail())<div class="text-muted fs-sm">{{ $booking->displayGuestEmail() }}</div>@endif
                        @if ($booking->displayGuestPhone())<div class="text-muted fs-sm">{{ $booking->displayGuestPhone() }}</div>@endif
                    </section>
                    <section class="col">
                        <h3 class="fs-sm text-muted mb-2">Property</h3>
                        <strong>{{ $booking->unit->unit_type }}</strong>
                        <div>{{ $booking->unit->house->name }}</div>
                        <div class="text-muted fs-sm">{{ $booking->unit->house->address }}, {{ $booking->unit->house->city }}</div>
                    </section>
                </div>

                <hr class="divider">
                <div class="row mb-3">
                    <section class="col">
                        <h3 class="fs-sm text-muted mb-2">Stay details</h3>
                        <div class="flex-between mb-1"><span>Booking ID</span><strong>#{{ $booking->booking_id }}</strong></div>
                        <div class="flex-between mb-1"><span>Check-in</span><strong>{{ $booking->check_in->format('M j, Y') }}</strong></div>
                        <div class="flex-between mb-1"><span>Check-out</span><strong>{{ $booking->check_out->format('M j, Y') }}</strong></div>
                        <div class="flex-between mb-1"><span>Nights</span><strong>{{ $nights }}</strong></div>
                        <div class="flex-between mb-1"><span>Adult paying guests</span><strong>{{ $booking->adult_count }}</strong></div>
                        <div class="flex-between mb-1"><span>Free children</span><strong>{{ $booking->child_count }}</strong></div>
                    </section>
                    <section class="col">
                        <h3 class="fs-sm text-muted mb-2">Payment summary</h3>
                        @php
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
                        <div class="booking-price-line">
                            <span>Stay/room charge <small class="booking-price-detail">{{ $roomQuantity }} × {{ $roomPrice }}</small></span>
                            <strong class="booking-price-amount">₱{{ number_format($financialSummary['roomCharges'], 2) }}</strong>
                        </div>
                        @if ($financialSummary['extraGuestCharges'] > 0)
                            <div class="booking-price-line">
                                <span>Extra guest charges <small class="booking-price-detail">{{ $booking->extra_guest_count }} guests × {{ $nights }} nights × ₱{{ number_format((float) $booking->extra_guest_fee, 2) }} / guest / night · included in accommodation</small></span>
                                <strong class="booking-price-amount">₱{{ number_format($financialSummary['extraGuestCharges'], 2) }}</strong>
                            </div>
                        @endif
                        @if ($financialSummary['approvedDiscounts'] > 0)
                            <div class="booking-price-line">
                                <span>Eligible verified discounts</span>
                                <strong class="booking-price-amount booking-price-amount--green">−₱{{ number_format($financialSummary['approvedDiscounts'], 2) }}</strong>
                            </div>
                        @endif
                        <div class="booking-price-line booking-price-line--total">
                            <span>Accommodation after discounts</span>
                            <strong class="booking-price-amount">₱{{ number_format($financialSummary['accommodationAfterDiscounts'], 2) }}</strong>
                        </div>
                        @if ($financialSummary['amenities'] > 0)
                            <div class="booking-price-line">
                                <span>Approved amenities and add-ons</span>
                                <strong class="booking-price-amount booking-price-amount--blue">₱{{ number_format($financialSummary['amenities'], 2) }}</strong>
                            </div>
                        @endif
                        @if ($financialSummary['additionalCharges'] > 0)
                            <div class="booking-price-line">
                                <span>Additional charges included by the financial contract</span>
                                <strong class="booking-price-amount booking-price-amount--red">₱{{ number_format($financialSummary['additionalCharges'], 2) }}</strong>
                            </div>
                        @endif
                        @if ($financialSummary['approvedDamageCharges'] > 0)
                            <div class="booking-price-line">
                                <span>Damage charges</span>
                                <strong class="booking-price-amount booking-price-amount--red">₱{{ number_format($financialSummary['approvedDamageCharges'], 2) }}</strong>
                            </div>
                        @endif
                        <div class="booking-price-line booking-price-line--total">
                            <span>Final amount due</span>
                            <strong class="booking-price-amount">₱{{ number_format($financialSummary['finalAmountDue'], 2) }}</strong>
                        </div>
                        <div class="booking-price-line">
                            <span>Payments made (verified only)</span>
                            <strong class="booking-price-amount booking-price-amount--green">₱{{ number_format($financialSummary['verifiedPayments'], 2) }}</strong>
                        </div>
                        @if ($financialSummary['completedRefunds'] > 0)
                            <div class="booking-price-line">
                                <span>Completed refunds</span>
                                <strong class="booking-price-amount booking-price-amount--orange">₱{{ number_format($financialSummary['completedRefunds'], 2) }}</strong>
                            </div>
                        @endif
                        <div class="booking-price-line booking-price-line--total">
                            <span>{{ $financialSummary['outstandingBalance'] > 0 ? 'Outstanding balance' : 'Paid in full' }}</span>
                            <strong class="booking-price-amount booking-price-amount--{{ $financialSummary['outstandingBalance'] > 0 ? 'red' : 'green' }}">₱{{ number_format($financialSummary['outstandingBalance'], 2) }}</strong>
                        </div>
                        @if ($financialSummary['refundableOverpayment'] > 0)
                            <div class="booking-price-line booking-price-line--total">
                                <span>Refundable overpayment</span>
                                <strong class="booking-price-amount booking-price-amount--orange">₱{{ number_format($financialSummary['refundableOverpayment'], 2) }}</strong>
                            </div>
                        @endif
                    </section>
                </div>

                @if ($booking->refunds->isNotEmpty())
                    <hr class="divider">
                    <h3 class="fs-sm text-muted mb-2">Refund status</h3>
                    @foreach ($booking->refunds as $refund)
                        <div class="flex-between mb-2" style="gap:12px">
                            <span>
                                {{ $refund->reason }}
                                <small class="booking-price-detail">
                                    {{ $refund->processed_at?->format('M j, Y g:i A') ?? $refund->created_at?->format('M j, Y g:i A') ?? 'Date unavailable' }}
                                    @if ($refund->refund_reference) · Reference: {{ $refund->refund_reference }} @endif
                                    · Payment #{{ $refund->payment_id }}
                                    @if ($refund->payment?->reference_number) · Payment reference: {{ $refund->payment->reference_number }} @endif
                                </small>
                            </span>
                            <span>₱{{ number_format($refund->amount, 2) }} · {{ $refund->refund_status }}</span>
                        </div>
                    @endforeach
                @endif

                @if ($booking->discounts->isNotEmpty())
                    <hr class="divider">
                    <h3 class="fs-sm text-muted mb-2">Senior / PWD verification</h3>
                    @foreach ($booking->discounts as $discount)
                        <div class="flex-between mb-1">
                            <span>{{ $discount->guest->full_name }} · {{ $discount->discount_type }}{{ $discount->verification_status === 'Verified' ? ' · ₱'.number_format($discount->discount_amount, 2) : '' }}
                                @if ($discount->guest->roster_status !== \App\Models\BookingGuest::ROSTER_ACTIVE)
                                    <small class="booking-price-detail">Removed guest; discount excluded</small>
                                @elseif ($discount->verification_status !== 'Verified')
                                    <small class="booking-price-detail">Not applied to final amount due</small>
                                @endif
                            </span>
                            <span class="badge {{ $discount->verification_status === 'Verified' && $discount->guest->roster_status === \App\Models\BookingGuest::ROSTER_ACTIVE ? 'badge-verified' : 'badge-unpaid' }}">{{ $discount->verification_status }}</span>
                        </div>
                    @endforeach
                @endif

                <hr class="divider">
                @if ($booking->addons->isNotEmpty() || $booking->damages->isNotEmpty() || $booking->additionalCharges->isNotEmpty() || $booking->damageReports->isNotEmpty())
                    <h3 class="fs-sm text-muted mb-2">Additional / damage charges</h3>
                    @foreach ($booking->addons as $addon)
                        @if ((float) $addon->price * (int) $addon->quantity > 0)
                            <div class="booking-price-line">
                                <span>Added amenities · {{ $addon->name }} × {{ $addon->quantity }} <small>(included in final amount due)</small></span>
                                <strong class="booking-price-amount booking-price-amount--blue">₱{{ number_format((float) $addon->price * $addon->quantity, 2) }}</strong>
                            </div>
                        @endif
                    @endforeach
                    @foreach ($booking->damages as $damage)
                        @if ((float) $damage->price * (int) $damage->quantity > 0)
                            <div class="booking-price-line">
                                <span>Damage/missing items · {{ $damage->name }} · Qty {{ $damage->quantity }} · ₱{{ number_format((float) $damage->price, 2) }} / item <small>(not included in amount due)</small></span>
                                <strong class="booking-price-amount booking-price-amount--red">₱{{ number_format((float) $damage->price * $damage->quantity, 2) }}</strong>
                            </div>
                        @endif
                    @endforeach
                    @foreach ($booking->additionalCharges as $charge)
                        @if ((float) $charge->total_amount > 0)
                            <div class="booking-price-line">
                                <span>Additional charge · {{ $charge->description }} × {{ $charge->quantity }} <small>(included by financial contract)</small></span>
                                <strong class="booking-price-amount booking-price-amount--red">₱{{ number_format((float) $charge->total_amount, 2) }}</strong>
                            </div>
                        @endif
                    @endforeach
                    @foreach ($booking->damageReports as $damage)
                        @if ((float) $damage->amount > 0)
                            <div class="booking-price-line">
                                <span>
                                    Damage/missing items · {{ $damage->description }} · Qty {{ $damage->quantity }}
                                    @if ($damage->unit_price !== null)
                                        · ₱{{ number_format((float) $damage->unit_price, 2) }} / item
                                    @else
                                        · Unit price unavailable
                                    @endif
                                    ({{ $damage->isIncludedInFinalAmount() ? 'included in amount due' : 'not included in amount due' }})
                                </span>
                                <strong class="booking-price-amount booking-price-amount--red">₱{{ number_format($damage->calculatedChargeTotal(), 2) }}</strong>
                            </div>
                        @endif
                    @endforeach
                    <hr class="divider">
                @endif
                <h3 class="fs-sm text-muted mb-2">Payment transactions</h3>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Date</th><th>Method</th><th>Reference</th><th>Amount</th></tr></thead>
                        <tbody>
                            @forelse ($booking->payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date?->format('M j, Y g:i A') ?? 'Date unavailable' }}</td>
                                    <td>{{ $payment->payment_method }} · {{ $payment->payment_status }}</td>
                                    <td>{{ $payment->reference_number ?: '—' }}</td>
                                    <td>₱{{ number_format($payment->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted">No payments recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="text-center text-muted fs-sm mt-3">Booking Breakdown created {{ $receipt->issued_at->format('F j, Y g:i A') }}. Thank you for choosing MCTBS.</div>
                <p class="text-center text-muted fs-sm mt-3 mb-0">This is not an official receipt. An official receipt will be given by the staff at check-out.</p>
            </div>
        </article>
    </div>

    <style>
        @media print {
            .topnav, .site-footer, button, a.btn { display: none !important; }
            .page-wrapper { padding-top: 0 !important; }
            .receipt-print { box-shadow: none !important; border: 1px solid #ddd; }
        }
    </style>
</x-shell>

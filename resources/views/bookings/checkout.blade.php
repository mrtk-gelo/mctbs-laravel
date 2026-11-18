<x-shell title="Checkout #{{ $booking->booking_id }} | MCTBS">
    <x-slot:nav>
        @if ($dashboardPrefix === 'owner')
            @include('partials.owner-nav', ['activePage' => 'bookings'])
        @else
            @include('partials.staff-nav', ['activePage' => 'bookings'])
        @endif
    </x-slot:nav>

    <style>
        .checkout-dialog { position: fixed; inset: 0; margin: auto; width: min(680px, calc(100vw - 28px)); max-height: calc(100vh - 40px); overflow: auto; padding: 0; border: 1px solid var(--border); border-radius: 12px; color: var(--text); }
        .checkout-dialog::backdrop { background: rgba(15, 23, 42, .65); }
        .checkout-dialog-header, .checkout-dialog-body, .checkout-dialog-actions { padding: 16px 20px; }
        .checkout-dialog-header, .checkout-dialog-actions { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .checkout-dialog-header { border-bottom: 1px solid var(--border); }
        .checkout-dialog-actions { justify-content: flex-end; border-top: 1px solid var(--border); }
        .checkout-layout { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr) minmax(0, 1fr); gap: 18px; align-items: start; }
        .checkout-layout > *, .checkout-history > .card { min-width: 0; margin-bottom: 0; }
        .checkout-history { display: grid; gap: 18px; }
        .checkout-payment-history-row, .checkout-refund-history-row { align-items: flex-start; gap: 12px; }
        .damage-card-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px; }
        .damage-card { position: relative; display: block; padding: 14px; border: 1px solid var(--border); border-radius: 10px; cursor: pointer; transition: border-color .15s, box-shadow .15s; }
        .damage-card:has(input:checked) { border-color: var(--primary); box-shadow: 0 0 0 2px color-mix(in srgb, var(--primary) 20%, transparent); }
        .damage-card:focus-within { outline: 2px solid var(--primary); outline-offset: 2px; }
        .damage-card-selection { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
        .damage-card .form-group { display: grid; gap: 6px; }
        .damage-card input[type="number"]:disabled { opacity: .55; }
        @media (max-width: 900px) {
            .checkout-layout, .checkout-history { grid-template-columns: minmax(0, 1fr); }
            .checkout-breakdown, .checkout-payment, .checkout-history { grid-column: 1; }
        }
    </style>

    <div class="container" style="margin-top:24px;max-width:1440px">
        <div class="page-header-row page-header">
            <div>
                @if ($booking->status === \App\Support\BookingStatus::CHECKED_IN)
                    <a href="{{ route($dashboardPrefix.'.guests.check-in', $booking->booking_id) }}" class="text-muted fs-sm"><i class="fa fa-arrow-left"></i> Back to Guest Check-in</a>
                @else
                    <a href="{{ route($dashboardPrefix.'.bookings.show', $booking->booking_id) }}" class="text-muted fs-sm"><i class="fa fa-arrow-left"></i> Back to Booking Details</a>
                @endif
                <h1 class="mt-1">Checkout · Booking #{{ $booking->booking_id }}</h1>
                <p>{{ $booking->unit->unit_type }} · {{ $booking->unit->house->name }} · {{ $booking->displayGuestName() }}</p>
            </div>
            <span class="badge {{ $isReadOnly ? 'badge-completed' : 'badge-accepted' }}">{{ \App\Support\BookingStatus::canonical($booking->status) }}</span>
        </div>

        @if (session('success'))
            <div class="alert alert-success mb-3" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger mb-3" role="alert">{{ $errors->first() }}</div>
        @endif
        @if ($isEarlyCheckout)
            <div class="alert alert-warning mb-3" role="status">
                This stay is scheduled to end on {{ $booking->check_out->format('M j, Y') }}. If checkout is completed today, it will be recorded as an early checkout.
            </div>
        @endif

        <div class="checkout-layout">
        <section class="card checkout-breakdown">
            <div class="card-header">Checkout Breakdown</div>
            <div class="card-body">
                @php
                    $nights = max(1, $booking->check_in->diffInDays($booking->check_out));
                    $extraGuestCharge = $financialSummary['extraGuestCharges'];
                    $roomCharge = $financialSummary['roomCharges'];
                    $payingGuestCount = $booking->activeGuests->where('is_free_child', false)->count();
                    $nightlyRate = (float) ($booking->nightly_rate ?? $booking->unit->price);
                    $roomQuantity = ($booking->pricing_mode ?? 'Per Unit') === 'Per Head'
                        ? $payingGuestCount.' paying guests × '.$nights.' nights'
                        : $nights.' nights';
                    $roomPrice = '₱'.number_format($nightlyRate, 2)
                        .(($booking->pricing_mode ?? 'Per Unit') === 'Per Head' ? ' / guest / night' : ' / unit / night');
                @endphp
                <div class="booking-price-line">
                    <span>Stay/room charge <small class="booking-price-detail">{{ $roomQuantity }} × {{ $roomPrice }}</small></span>
                    <strong class="booking-price-amount">₱{{ number_format($roomCharge, 2) }}</strong>
                </div>
                @if ($extraGuestCharge > 0)
                    <div class="booking-price-line">
                        <span>Extra guest charges <small class="booking-price-detail">{{ $booking->extra_guest_count }} guests × {{ $nights }} nights × ₱{{ number_format((float) $booking->extra_guest_fee, 2) }} / guest / night · included in accommodation</small></span>
                        <strong class="booking-price-amount">₱{{ number_format($extraGuestCharge, 2) }}</strong>
                    </div>
                @endif

                @foreach ($booking->addons as $addon)
                    @if ((float) $addon->price * (int) $addon->quantity > 0)
                        <div class="booking-price-line">
                            <span>Added amenities · {{ $addon->name }} × {{ $addon->quantity }} <small>(included by financial contract)</small></span>
                            <strong class="booking-price-amount booking-price-amount--blue">₱{{ number_format((float) $addon->price * $addon->quantity, 2) }}</strong>
                        </div>
                    @endif
                @endforeach
                @foreach ($booking->additionalCharges as $charge)
                    @if ((float) $charge->total_amount > 0)
                        <div class="booking-price-line" data-checkout-charge="{{ $charge->additional_charge_id }}">
                            <span>Damage/missing items · {{ $charge->description }} × {{ $charge->quantity }} <small>(included by financial contract)</small></span>
                            <div class="booking-price-actions">
                                <strong class="booking-price-amount booking-price-amount--red">₱{{ number_format((float) $charge->total_amount, 2) }}</strong>
                                @unless ($isReadOnly)
                                    <div class="flex-center gap-1">
                                        <form method="POST" action="{{ route($dashboardPrefix.'.bookings.checkout.charges.quantity', [$booking->booking_id, $charge->additional_charge_id]) }}">
                                            @csrf
                                            <input type="hidden" name="change" value="-1">
                                            <button type="submit" class="btn btn-outline btn-sm" aria-label="Decrease {{ $charge->description }} quantity">−</button>
                                        </form>
                                        <form method="POST" action="{{ route($dashboardPrefix.'.bookings.checkout.charges.quantity', [$booking->booking_id, $charge->additional_charge_id]) }}">
                                            @csrf
                                            <input type="hidden" name="change" value="1">
                                            <button type="submit" class="btn btn-outline btn-sm" aria-label="Increase {{ $charge->description }} quantity">+</button>
                                        </form>
                                    </div>
                                @endunless
                            </div>
                        </div>
                    @endif
                @endforeach
                @foreach ($booking->damageReports as $damageReport)
                    @if ($damageReport->isIncludedInFinalAmount() && $damageReport->calculatedChargeTotal() > 0)
                        <div class="booking-price-line" data-damage-charge="{{ $damageReport->damage_id }}">
                            <span>
                                Damage/missing items · {{ $damageReport->description }}
                                @if ((int) $damageReport->quantity > 1) × {{ $damageReport->quantity }} @endif
                                <small>(included in amount due)</small>
                            </span>
                            <div class="booking-price-actions">
                                <strong class="booking-price-amount booking-price-amount--red">₱{{ number_format($damageReport->calculatedChargeTotal(), 2) }}</strong>
                                @unless ($isReadOnly)
                                    <div class="flex-center gap-1">
                                        <form method="POST" action="{{ route($dashboardPrefix.'.bookings.checkout.damages.quantity', [$booking->booking_id, $damageReport->damage_id]) }}">
                                            @csrf
                                            <input type="hidden" name="change" value="-1">
                                            <button type="submit" class="btn btn-outline btn-sm" aria-label="Decrease {{ $damageReport->description }} quantity">−</button>
                                        </form>
                                        <form method="POST" action="{{ route($dashboardPrefix.'.bookings.checkout.damages.quantity', [$booking->booking_id, $damageReport->damage_id]) }}">
                                            @csrf
                                            <input type="hidden" name="change" value="1">
                                            <button type="submit" class="btn btn-outline btn-sm" aria-label="Increase {{ $damageReport->description }} quantity">+</button>
                                        </form>
                                    </div>
                                @endunless
                            </div>
                        </div>
                    @endif
                @endforeach

                @if ($approvedDiscountTotal > 0)
                    <div class="booking-price-line">
                        <span>Eligible verified Senior/PWD discounts</span>
                        <strong class="booking-price-amount booking-price-amount--green">−₱{{ number_format((float) $approvedDiscountTotal, 2) }}</strong>
                    </div>
                @endif
                <div class="booking-price-line booking-price-line--total">
                    <span>Accommodation after discounts</span>
                    <strong class="booking-price-amount">₱{{ number_format($financialSummary['accommodationAfterDiscounts'], 2) }}</strong>
                </div>
                <div class="booking-price-line">
                    <span>Amenities and add-ons included by financial contract</span>
                    <strong class="booking-price-amount booking-price-amount--blue">₱{{ number_format($financialSummary['amenities'], 2) }}</strong>
                </div>
                <div class="booking-price-line">
                    <span>Additional charges included by financial contract</span>
                    <strong class="booking-price-amount booking-price-amount--red">₱{{ number_format($financialSummary['additionalCharges'], 2) }}</strong>
                </div>
                <div class="booking-price-line">
                    <span>Damage charges</span>
                    <strong class="booking-price-amount booking-price-amount--red">₱{{ number_format($financialSummary['approvedDamageCharges'], 2) }}</strong>
                </div>
                @if ($booking->discounts->isNotEmpty())
                    <div class="policy-box mt-2">
                        <strong>Discount status</strong>
                        @foreach ($booking->discounts as $discount)
                            <div class="flex-between fs-sm mt-1">
                                <span>
                                    {{ $discount->guest->full_name }} · {{ $discount->discount_type }} discount · {{ $discount->verification_status }}
                                    @if ($discount->guest->roster_status !== \App\Models\BookingGuest::ROSTER_ACTIVE)
                                        (removed guest; excluded)
                                    @elseif ($discount->verification_status !== 'Verified')
                                        (not applied)
                                    @else
                                        (applied once)
                                    @endif
                                </span>
                                @if ($discount->verification_status === 'Verified' && $discount->guest->roster_status === \App\Models\BookingGuest::ROSTER_ACTIVE)
                                    <strong>−₱{{ number_format($discount->discount_amount, 2) }}</strong>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="booking-price-line booking-price-line--total">
                    <span>Final amount due</span>
                    <strong class="booking-price-amount">₱{{ number_format($financialSummary['finalAmountDue'], 2) }}</strong>
                </div>
                @if ($refundedTotal > 0)
                    <div class="booking-price-line">
                        <span>Refunds</span>
                        <strong class="booking-price-amount booking-price-amount--orange">+₱{{ number_format((float) $refundedTotal, 2) }}</strong>
                    </div>
                @endif
                <div class="booking-price-line">
                    <span>Payments made</span>
                    <strong class="booking-price-amount booking-price-amount--green">−₱{{ number_format((float) $verifiedPaymentTotal, 2) }}</strong>
                </div>
                <hr class="divider">
                <div class="booking-price-line booking-price-line--total">
                    <span>Outstanding balance <small>(Balance to pay)</small></span>
                    <strong class="booking-price-amount booking-price-amount--{{ $balanceDue === 0.0 ? 'green' : 'red' }}">₱{{ number_format((float) $balanceDue, 2) }}</strong>
                </div>
                @if ($financialSummary['refundableOverpayment'] > 0)
                    <div class="booking-price-line booking-price-line--total">
                        <span>Refundable overpayment</span>
                        <strong class="booking-price-amount booking-price-amount--orange">₱{{ number_format($financialSummary['refundableOverpayment'], 2) }}</strong>
                    </div>
                @endif

                @unless ($isReadOnly)
                    @if ($isEarlyCheckout)
                        <div class="form-group mt-3">
                            <label class="required" for="early-checkout-reason">Reason for early checkout</label>
                            <textarea id="early-checkout-reason" name="early_checkout_reason" form="checkout-complete-form" rows="3" maxlength="1000" required>{{ old('early_checkout_reason') }}</textarea>
                            @error('early_checkout_reason')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                        </div>
                    @endif
                    <div class="flex-center gap-1 mt-3" style="flex-wrap:wrap;justify-content:flex-start">
                        <button type="button" class="btn btn-outline" data-open-checkout-dialog>Damage/Missing Items</button>
                        <form id="checkout-complete-form" method="POST" action="{{ route($dashboardPrefix.'.bookings.checkout.complete', $booking->booking_id) }}" onsubmit="return confirm('Complete checkout for this booking?')">
                            @csrf
                            <button type="submit" class="btn btn-primary" @disabled($balanceDue > 0 || $hasUnderReviewPayment)>
                                Complete Checkout
                            </button>
                        </form>
                    </div>
                    @if ($hasUnderReviewPayment)
                        <p class="alert alert-warning mt-3 mb-0">A submitted payment still needs review before checkout can be completed.</p>
                    @elseif ($balanceDue > 0)
                        <p class="form-hint mt-2 mb-0">Pay the remaining balance before completing checkout.</p>
                    @endif
                @else
                    <p class="text-muted mt-3 mb-0">Checkout is complete. This page is read-only.</p>
                @endunless
            </div>
        </section>

        <div class="checkout-history">
            <section class="card">
                <div class="card-header">Payment History</div>
                <div class="card-body">
                    @forelse ($booking->payments->sortByDesc('payment_date') as $payment)
                        <div class="flex-between checkout-payment-history-row mb-2">
                            <div>
                                <strong>{{ $payment->payment_method }} · ₱{{ number_format((float) $payment->amount, 2) }}</strong>
                                <div class="text-muted fs-sm">{{ $payment->payment_date->format('M j, Y g:i A') }} · {{ $payment->payment_status }}</div>
                                @if ($payment->reference_number)
                                    <div class="text-muted fs-sm">Reference: {{ $payment->reference_number }}</div>
                                @endif
                            </div>
                            @if ($payment->payment_receipt)
                                <a href="{{ route('payments.proof', $payment->payment_id) }}" class="btn btn-outline btn-sm">View receipt</a>
                            @endif
                        </div>
                        @if (! $loop->last)<hr class="divider">@endif
                    @empty
                        <p class="text-muted mb-0">No payments recorded.</p>
                    @endforelse
                </div>
            </section>

            <section class="card">
                <div class="card-header">Refund History</div>
                <div class="card-body">
                    @forelse ($booking->refunds->sortByDesc('created_at') as $refund)
                        <div class="flex-between checkout-refund-history-row">
                            <div>
                                <strong>₱{{ number_format((float) $refund->amount, 2) }} · {{ $refund->reason }}</strong>
                                <div class="text-muted fs-sm">
                                    {{ $refund->processed_at?->format('M j, Y g:i A') ?? $refund->created_at?->format('M j, Y g:i A') }}
                                    @if ($refund->processedBy)
                                        · {{ $refund->processedBy->first_name }} {{ $refund->processedBy->last_name }}
                                    @endif
                                    @if ($refund->refund_reference)
                                        · Reference: {{ $refund->refund_reference }}
                                    @endif
                                    · Payment #{{ $refund->payment_id }}
                                </div>
                            </div>
                            <span class="badge {{ $refund->refund_status === 'Refunded' ? 'badge-verified' : 'badge-pending' }}">{{ $refund->refund_status }}</span>
                        </div>
                        @if (! $loop->last)<hr class="divider">@endif
                    @empty
                        <p class="text-muted mb-0">No refunds have been recorded.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="card checkout-payment">
            <div class="card-header">Record Payment</div>
            <div class="card-body">
                @if ($isReadOnly)
                    <p class="text-muted mb-0">Checkout is complete. Payment records are read-only.</p>
                @elseif ($balanceDue > 0)
                    <p class="form-hint">Record a payment collected by the {{ $dashboardPrefix }}. It will be marked as verified.</p>
                    <form method="POST" action="{{ route($dashboardPrefix.'.bookings.checkout.payments', $booking->booking_id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="checkout-method">Payment method</label>
                            <select id="checkout-method" name="payment_method" required>
                                <option value="">Select a method</option>
                                <option>Cash</option>
                                <option>GCash</option>
                                <option>Bank Transfer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="checkout-amount">Amount (₱)</label>
                            <input id="checkout-amount" name="amount" type="number" min="0.01" max="{{ number_format($balanceDue, 2, '.', '') }}" step="0.01" value="{{ number_format($balanceDue, 2, '.', '') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="checkout-reference">Reference number</label>
                            <input id="checkout-reference" name="reference_number" type="text" maxlength="100" placeholder="Optional for in-person payments">
                        </div>
                        <div class="form-group">
                            <label for="checkout-proof">Receipt image or PDF</label>
                            <input id="checkout-proof" name="payment_receipt" type="file" accept="image/jpeg,image/png,application/pdf">
                        </div>
                        <div class="form-group">
                            <label for="checkout-notes">Notes</label>
                            <textarea id="checkout-notes" name="notes" rows="2" maxlength="1000"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Record Payment</button>
                    </form>
                @else
                    <p class="text-success mb-0">The balance is fully paid.</p>
                @endif
            </div>
        </section>
        </div>
    </div>

    @unless ($isReadOnly)
        <dialog class="checkout-dialog" id="damage-items-dialog" aria-labelledby="damage-items-dialog-title">
            <div class="checkout-dialog-header">
                <strong id="damage-items-dialog-title">Damage/Missing Items</strong>
            </div>
            <form method="POST" action="{{ route($dashboardPrefix.'.bookings.checkout.damages.store', $booking->booking_id) }}">
                @csrf
                <div class="checkout-dialog-body">
                    @if ($booking->unit->damageItems->isNotEmpty())
                        <p class="text-muted fs-sm">Select the damage or missing items and set each quantity. Charges are added immediately; use + or − on the bill to adjust a quantity, and − at zero removes it from the current charges.</p> <br>
                        <div class="damage-card-list">
                            @foreach ($booking->unit->damageItems as $index => $damageItem)
                                <label class="damage-card">
                                    <input class="damage-card-selection" type="checkbox" name="items[{{ $index }}][unit_damage_item_id]" value="{{ $damageItem->unit_damage_item_id }}" @checked((string) old('items.'.$index.'.unit_damage_item_id') === (string) $damageItem->unit_damage_item_id) data-damage-check>
                                    <span class="flex-between">
                                        <strong>{{ $damageItem->name }}</strong>
                                        <span>₱{{ number_format((float) $damageItem->price, 2) }}</span>
                                    </span>
                                    <span class="form-group mt-2 mb-0">
                                        <span class="text-muted fs-sm">Quantity</span>
                                        <input type="number" name="items[{{ $index }}][quantity]" value="{{ old('items.'.$index.'.quantity', 1) }}" min="1" max="100" @disabled((string) old('items.'.$index.'.unit_damage_item_id') !== (string) $damageItem->unit_damage_item_id) data-damage-quantity>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('items')
                            <p class="form-hint text-danger mt-2">{{ $message }}</p>
                        @enderror
                        @foreach ($errors->get('items.*') as $itemErrors)
                            @foreach ($itemErrors as $error)
                                <p class="form-hint text-danger mt-2">{{ $error }}</p>
                            @endforeach
                        @endforeach
                    @else
                        <p class="text-muted mb-0">This unit has no damage or missing item policies.</p>
                    @endif
                </div>
                <div class="checkout-dialog-actions">
                    <button type="button" class="btn btn-outline" data-close-checkout-dialog>Cancel</button>
                    <button type="submit" class="btn btn-primary" @disabled($booking->unit->damageItems->isEmpty())>Submit</button>
                </div>
            </form>
        </dialog>
    @endunless

    <script>
        (() => {
            document.addEventListener('click', (event) => {
                if (event.target.closest('[data-open-checkout-dialog]')) {
                    document.getElementById('damage-items-dialog')?.showModal();
                }

                if (event.target.closest('[data-close-checkout-dialog]')) {
                    event.target.closest('dialog')?.close();
                }
            });

            document.querySelectorAll('[data-damage-check]').forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    const quantity = checkbox.closest('.damage-card').querySelector('[data-damage-quantity]');
                    quantity.disabled = !checkbox.checked;
                });
            });

            @if ($errors->has('items') || $errors->hasAny(['description', 'quantity', 'unit_price']) || collect($errors->keys())->contains(fn ($key) => str_starts_with($key, 'items.')))
                document.getElementById('damage-items-dialog')?.showModal();
            @endif
        })();
    </script>
</x-shell>

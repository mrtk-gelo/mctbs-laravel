<x-shell title="Edit Booking #{{ $booking->booking_id }} | MCTBS">
    <x-slot:nav>
        @if ($dashboardPrefix === 'owner')
            @include('partials.owner-nav', ['activePage' => 'bookings'])
        @else
            @include('partials.staff-nav', ['activePage' => 'bookings'])
        @endif
    </x-slot:nav>

    <div class="container-sm" style="margin-top:24px">
        @if ($booking->status === \App\Support\BookingStatus::CHECKED_IN)
            <a href="{{ route($dashboardPrefix.'.guests.check-in', $booking->booking_id) }}" class="text-muted fs-sm"><i class="fa fa-arrow-left"></i> Back to Guest Check-in</a>
        @else
            <a href="{{ route($dashboardPrefix.'.bookings.show', $booking->booking_id) }}" class="text-muted fs-sm"><i class="fa fa-arrow-left"></i> Back to booking</a>
        @endif
        <div class="page-header-row page-header mt-2">
            <div><h1>Edit Booking #{{ $booking->booking_id }}</h1><p>{{ $booking->displayGuestName() }} · {{ $booking->unit->unit_type }}</p></div>
            <span class="badge badge-{{ strtolower($booking->status) }}">{{ $booking->status }}</span>
        </div>



        <div class="card">
            <div class="card-header">Update booking details</div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <div class="policy-box mb-3">
                    Owner/Staff can update upcoming dates after discussing the change with the guest.
                    Changes are saved with a timestamp and a before/after record. Any payment above the revised
                    total remains as credit toward checkout. Senior/PWD discounts are verified separately at checkout.
                </div>
                <form method="POST" action="{{ route($dashboardPrefix.'.bookings.update', $booking->booking_id) }}" id="booking-edit-form">
                    @csrf
                    @method('PUT')
                    <div class="form-row">
                        <div class="form-group">
                            <label for="check-in">Check-in</label>
                            @if ($booking->status === \App\Support\BookingStatus::CHECKED_IN)
                                <input id="check-in" type="date" value="{{ $booking->check_in->toDateString() }}" disabled aria-describedby="check-in-locked-hint">
                                <small id="check-in-locked-hint" class="form-hint">The recorded check-in date is fixed for an active stay.</small>
                            @else
                                <input id="check-in" type="date" name="check_in" value="{{ old('check_in', $booking->check_in->toDateString()) }}" min="{{ now()->addDays(2)->toDateString() }}" required>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="check-out">Check-out</label>
                            <input id="check-out" type="date" name="check_out" value="{{ old('check_out', $booking->check_out->toDateString()) }}" min="{{ $booking->status === \App\Support\BookingStatus::CHECKED_IN ? today()->addDay()->toDateString() : old('check_in', $booking->check_in->toDateString()) }}" required>
                        </div>
                    </div>
                    <div class="form-row mt-3">
                        <div class="form-group">
                            <label class="required" for="adult-count">Paying guests (counted guests)</label>
                            <input id="adult-count" type="number" name="adult_count" value="{{ old('adult_count', $booking->adult_count) }}" min="1" max="{{ $booking->unit->maximum_capacity ?? $booking->unit->capacity }}" required>
                        </div>
                        <div class="form-group">
                            <label class="required" for="child-count">Free children</label>
                            <input id="child-count" type="number" name="child_count" value="{{ old('child_count', $booking->child_count) }}" min="0" max="{{ $booking->unit->child_free_through_age === null ? 0 : 100 }}" required>
                        </div>
                    </div>
                    @if ($booking->status === \App\Support\BookingStatus::CHECKED_IN)
                        <p class="form-hint">The recorded check-in date is fixed for this active stay. Checkout can be adjusted, but guest-count reductions are not allowed after check-in and all counted guests must fit the unit’s maximum capacity.</p>
                    @else
                        <p class="form-hint">Paying guests and free children can be adjusted before check-in. All guests count toward the unit’s maximum occupancy.</p>
                    @endif

                    <div class="card mt-3">
                        <div class="card-header">Revised checkout estimate</div>
                        <div class="card-body">
                            <div class="booking-price-line booking-price-line--total">
                                <span>Current total before checkout discount</span>
                                <strong class="booking-price-amount">₱{{ number_format((float) $booking->amountDueBeforeCheckout(), 2) }}</strong>
                            </div>
                            @if ($currentCheckoutCharges > 0)
                                <div class="booking-price-line">
                                    <span>Existing checkout and damage charges</span>
                                    <strong class="booking-price-amount booking-price-amount--red">₱{{ number_format((float) $currentCheckoutCharges, 2) }}</strong>
                                </div>
                            @endif
                            <div class="booking-price-line">
                                <span>Payments made <small class="booking-price-detail">Verified payments to date</small></span>
                                <strong class="booking-price-amount booking-price-amount--green">₱{{ number_format((float) $verifiedPaymentTotal, 2) }}</strong>
                            </div>
                            <div class="booking-price-line booking-price-line--total">
                                <span>Revised total before checkout discount</span>
                                <strong id="revised-total" class="booking-price-amount">₱0.00</strong>
                            </div>
                            <div class="booking-price-line booking-price-line--total">
                                <span>Estimated remaining balance at checkout</span>
                                <strong id="checkout-credit" class="booking-price-amount booking-price-amount--green">₱0.00</strong>
                            </div>
                            @if ($booking->status === \App\Support\BookingStatus::ACCEPTED)
                                <p class="form-hint mt-2 mb-0">The downpayment is recalculated from the revised stay total. Submit the updated amount to confirm the booking.</p>
                            @elseif ($booking->status === \App\Support\BookingStatus::CONFIRMED)
                                <p class="form-hint mt-2 mb-0">Verified payments, including your downpayment, are credited toward the revised bill. Any remaining balance and eligible Senior Citizen/PWD discounts are settled at checkout.</p>
                            @else
                                <p class="form-hint mt-2 mb-0">No payment is due to submit a request. Any applicable downpayment is due only after the booking is accepted; the remaining balance is collected at checkout.</p>
                            @endif
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label for="edit-reason">Reason / change details</label>
                        <textarea id="edit-reason" name="reason" rows="3" maxlength="2000" required>{{ old('reason') }}</textarea>
                    </div>
                    <div class="flex-center gap-1">
                        <button type="submit" class="btn btn-primary">Save booking changes</button>
                        <a href="{{ $booking->status === \App\Support\BookingStatus::CHECKED_IN ? route($dashboardPrefix.'.guests.check-in', $booking->booking_id) : route($dashboardPrefix.'.bookings.show', $booking->booking_id) }}" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const checkIn = document.getElementById('check-in');
            const checkOut = document.getElementById('check-out');
            const totalOutput = document.getElementById('revised-total');
            const creditOutput = document.getElementById('checkout-credit');
            const adultCount = document.getElementById('adult-count');
            const verifiedPaid = Number(@json($verifiedPaymentTotal));
            const currentCheckoutCharges = Number(@json($currentCheckoutCharges));
            const nightlyRate = Number(@json((float) ($booking->nightly_rate ?? $booking->unit->price)));
            const extraGuestFee = Number(@json((float) ($booking->extra_guest_fee ?? 0)));
            const extraGuestCount = Number(@json((int) ($booking->extra_guest_count ?? 0)));
            const isCheckedIn = @json($booking->status === \App\Support\BookingStatus::CHECKED_IN);
            const toDate = (value) => {
                const [year, month, day] = value.split('-').map(Number);
                return new Date(year, month - 1, day);
            };
            const currency = (amount) => `₱${amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            const updateEstimate = () => {
                if (!checkIn.value || !checkOut.value || checkOut.value <= checkIn.value) {
                    totalOutput.textContent = 'Choose valid dates';
                    creditOutput.textContent = '—';
                    creditOutput.classList.remove('booking-price-amount--red', 'booking-price-amount--green');
                    creditOutput.classList.add('booking-price-amount--green');
                    return;
                }

                const nights = Math.round((toDate(checkOut.value) - toDate(checkIn.value)) / 86400000);
                const countedGuests = adultCount ? Number(adultCount.value) : null;
                const revisedExtraGuestCount = countedGuests === null
                    ? extraGuestCount
                    : Math.max(0, countedGuests - Number(@json((int) $booking->unit->capacity)));
                const revisedTotal = nights * (nightlyRate + extraGuestFee * revisedExtraGuestCount);
                totalOutput.textContent = currency(revisedTotal);
                const checkoutCredit = Math.max(0, revisedTotal + currentCheckoutCharges - verifiedPaid);
                creditOutput.textContent = currency(checkoutCredit);
                creditOutput.classList.remove('booking-price-amount--red', 'booking-price-amount--green');
                creditOutput.classList.add(checkoutCredit > 0 ? 'booking-price-amount--red' : 'booking-price-amount--green');
            };

            const updateCheckoutMinimum = () => {
                if (!checkIn.value) {
                    return;
                }
                const minimum = new Date(toDate(checkIn.value).getTime() + 86400000);
                const minimumCheckOut = isCheckedIn && minimum < toDate(@json(today()->addDay()->toDateString()))
                    ? toDate(@json(today()->addDay()->toDateString()))
                    : minimum;
                checkOut.min = `${minimumCheckOut.getFullYear()}-${String(minimumCheckOut.getMonth() + 1).padStart(2, '0')}-${String(minimumCheckOut.getDate()).padStart(2, '0')}`;
                if (checkOut.value && checkOut.value <= checkIn.value) {
                    checkOut.value = checkOut.min;
                }
                updateEstimate();
            };

            checkIn.addEventListener('change', updateCheckoutMinimum);
            checkOut.addEventListener('change', updateEstimate);
            adultCount?.addEventListener('input', updateEstimate);
            updateCheckoutMinimum();
        })();
    </script>
</x-shell>

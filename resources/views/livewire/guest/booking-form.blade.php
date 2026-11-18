<div class="container booking-form-page">
    <div class="steps mb-3">
        <div class="step active"><div class="step-circle">1</div><div class="step-label">Booking Details</div></div>
        <div class="step"><div class="step-circle">2</div><div class="step-label">Payment</div></div>
        <div class="step"><div class="step-circle">3</div><div class="step-label">Confirmation</div></div>
    </div>

    <div class="booking-form-layout">
        <section class="card">
            <div class="card-header">Booking Request — {{ $unit->unit_type }}</div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form wire:submit="submit">
                    <div class="booking-form-section">
                        <h3>Stay dates</h3>
                        <div class="booking-fields-grid booking-date-fields">
                            <div class="form-group">
                                <label class="required" for="booking-check-in">Check-in date</label>
                                <input id="booking-check-in" type="date" wire:model.live="checkIn" min="{{ today()->addDay()->toDateString() }}" required>
                                @error('checkIn')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="required" for="booking-check-out">Check-out date</label>
                                <input id="booking-check-out" type="date" wire:model.live="checkOut" min="{{ $checkIn ? \Carbon\Carbon::parse($checkIn)->addDay()->toDateString() : today()->addDays(2)->toDateString() }}" required>
                                @error('checkOut')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="booking-form-section">
                        <h3>Guests</h3>
                        <div class="booking-fields-grid booking-count-fields">
                            <div class="booking-count-inputs">
                                <div class="form-group">
                                    <label class="required" for="pax-count">Number of pax</label>
                                    <input id="pax-count" type="number" inputmode="numeric" wire:model.live.debounce.300ms="paxCount" min="1" max="{{ $unit->maximum_capacity ?? $unit->capacity }}">
                                    @error('paxCount')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                                </div>
                                @if ($unit->child_free_through_age !== null)
                                    <div class="form-group">
                                        <label for="free-child-count">Children age {{ $unit->child_free_through_age }} or younger</label>
                                        <input id="free-child-count" type="number" inputmode="numeric" wire:model.live.debounce.300ms="freeChildCount" min="0" max="100">
                                    </div>
                                @endif
                            </div>
                            <div class="booking-guest-summary">
                                <div class="booking-guest-summary-count booking-guest-summary-count--compact">
                                    <strong>Counted pax: {{ max(0, (int) $paxCount) + max(0, (int) $freeChildCount) }} / {{ $unit->maximum_capacity ?? $unit->capacity }} max</strong>
                                </div>
                                <p class="booking-section-note booking-guest-summary-paying">
                                    Paying: {{ max(0, (int) $paxCount) }} | Free children: {{ max(0, (int) $freeChildCount) }}
                                </p>
                                @if ($unit->child_free_through_age !== null)
                                    <p class="booking-section-note booking-guest-summary-guidance">Children {{ $unit->child_free_through_age }} years old or younger are free but still count toward the max.</p>
                                @endif
                                @error('freeChildCount')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                                <p class="booking-section-note booking-guest-summary-guidance">Standard: {{ $unit->capacity }} guests | Max: {{ $unit->maximum_capacity ?? $unit->capacity }} guests</p>
                                <p class="booking-section-note booking-guest-summary-guidance">
                                    @if (($unit->pricing_mode ?? 'Per Unit') === 'Per Unit')
                                        ₱{{ number_format((float) $unit->price, 2) }} / night | Extra guest: ₱{{ number_format((float) $unit->extra_guest_fee, 2) }} / night
                                    @else
                                        ₱{{ number_format((float) $unit->price, 2) }} / head / night
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="booking-fields-grid booking-id-fields">
                            <section class="booking-discount-group">
                                <h4>Senior Citizens</h4>
                                @foreach ($seniorIds as $index => $id)
                                    <div class="form-group" wire:key="senior-id-{{ $index }}">
                                        <label class="required" for="senior-id-{{ $index }}">Senior Citizen {{ $index + 1 }} reference ID</label>
                                        <div class="flex-between" style="gap:8px">
                                            <input id="senior-id-{{ $index }}" type="text" wire:model="seniorIds.{{ $index }}" maxlength="100">
                                            <button type="button" class="btn btn-outline btn-sm" wire:click="removeSenior({{ $index }})" aria-label="Remove Senior Citizen {{ $index + 1 }}">Remove</button>
                                        </div>
                                        @error("seniorIds.$index")<p class="form-hint text-danger">{{ $message }}</p>@enderror
                                    </div>
                                @endforeach
                                <button type="button" class="btn btn-outline btn-sm" wire:click="addSenior" @disabled($seniorCount + $pwdCount >= $paxCount)>{{ $seniorCount > 0 ? 'Add another senior' : 'Add a senior' }}</button>
                                @error('seniorCount')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                            </section>
                            <section class="booking-discount-group">
                                <h4>PWD guests</h4>
                                @foreach ($pwdIds as $index => $id)
                                    <div class="form-group" wire:key="pwd-id-{{ $index }}">
                                        <label class="required" for="pwd-id-{{ $index }}">PWD {{ $index + 1 }} reference ID</label>
                                        <div class="flex-between" style="gap:8px">
                                            <input id="pwd-id-{{ $index }}" type="text" wire:model="pwdIds.{{ $index }}" maxlength="100">
                                            <button type="button" class="btn btn-outline btn-sm" wire:click="removePwd({{ $index }})" aria-label="Remove PWD {{ $index + 1 }}">Remove</button>
                                        </div>
                                        @error("pwdIds.$index")<p class="form-hint text-danger">{{ $message }}</p>@enderror
                                    </div>
                                @endforeach
                                <button type="button" class="btn btn-outline btn-sm" wire:click="addPwd" @disabled($seniorCount + $pwdCount >= $paxCount)>{{ $pwdCount > 0 ? 'Add another PWD' : 'Add a PWD' }}</button>
                                @error('pwdCount')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                            </section>
                        </div>
                        <p class="form-hint booking-discount-note">Senior Citizen and PWD discounts are deducted at checkout after the ID is verified. Choose only one discount per person.</p>
                    </div>

                    <div class="policy-box">
                        <strong>Booking policy</strong>
                        <ul style="margin:8px 0 0 16px;font-size:12px;line-height:1.8">
                            <li>Your request will be reviewed by the property owner.</li>
                            <li>Check-in is at {{ \App\Support\UnitTimeFormatter::format((string) $unit->check_in_time) }}; check-out is at {{ \App\Support\UnitTimeFormatter::format((string) $unit->check_out_time) }}.</li>
                            <li>No payment is due to submit a request. If accepted, a {{ (int) (config('booking.downpayment_fraction') * 100) }}% downpayment is required to confirm the booking; the remaining balance is paid at checkout.</li>
                            <li>Present the original Senior Citizen/PWD ID at checkout. After verification, a 20% discount is applied to that guest’s share; only one discount may be used per guest.</li>
                            <li>Cancellation and refund policies are set by the property owner. Please read them carefully before submitting your request.</li>
                        </ul>
                    </div>

                    <div class="checkbox-group mb-3">
                        <input type="checkbox" wire:model="ackPolicy" id="ackPolicy">
                        <label for="ackPolicy" style="font-weight:400;cursor:pointer">
                            I have read and agree to the cancellation and payment policy.
                        </label>
                    </div>
                    @error('ackPolicy')<p class="form-hint text-danger">{{ $message }}</p>@enderror

                    <button type="submit" class="btn btn-primary btn-block btn-lg" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submit">Send booking request</span>
                        <span wire:loading wire:target="submit">Sending request…</span>
                    </button>
                </form>
            </div>
        </section>

        <aside class="card booking-price-card">
            <div class="card-header">Price summary</div>
            <div class="card-body">
                <div class="booking-price-property">{{ $unit->unit_type }} · {{ $unit->house->name }} · {{ $unit->house->city }}</div>

                @if ($this->summary)
                    @php
                        $countedGuests = max(1, (int) $paxCount);
                        $discountedGuests = (int) $seniorCount + (int) $pwdCount;
                        $guestShare = round((float) $this->summary['total'] / $countedGuests, 2);
                        $estimatedDiscount = round($guestShare * 0.2 * $discountedGuests, 2);
                        $pricingMode = $unit->pricing_mode ?? 'Per Unit';
                        $roomQuantity = $pricingMode === 'Per Head'
                            ? (int) $paxCount.' '.\Illuminate\Support\Str::plural('paying guest', (int) $paxCount).' × '.$this->summary['nights'].' nights'
                            : $this->summary['nights'].' nights';
                        $roomPrice = '₱'.number_format((float) $unit->price, 2)
                            .($pricingMode === 'Per Head' ? ' / guest / night' : ' / unit / night');
                    @endphp
                    <div class="booking-price-lines">
                        <div class="booking-price-line">
                            <span>Stay/room charge · {{ $pricingMode }} <small class="booking-price-detail">{{ $roomQuantity }} × {{ $roomPrice }}</small></span>
                            <strong class="booking-price-amount">₱{{ number_format((float) $this->summary['room_subtotal'], 2) }}</strong>
                        </div>
                        @if ($this->summary['extra_guest_count'] > 0)
                            <div class="booking-price-line">
                                <span>Extra heads <small class="booking-price-detail">{{ $this->summary['extra_guest_count'] }} extra guests × {{ $this->summary['nights'] }} nights × ₱{{ number_format((float) $this->summary['extra_guest_fee'], 2) }} / guest / night</small></span>
                                <strong class="booking-price-amount">₱{{ number_format((float) $this->summary['extra_guest_total'], 2) }}</strong>
                            </div>
                        @endif
                        <div class="booking-price-line booking-price-line--total">
                            <span>Stay total</span>
                            <strong class="booking-price-amount">₱{{ number_format((float) $this->summary['total'], 2) }}</strong>
                        </div>
                    </div>
                    @if ($discountedGuests > 0)
                        <div class="booking-price-line">
                            <span>Estimated Senior/PWD discount · {{ $discountedGuests }} {{ \Illuminate\Support\Str::plural('guest', $discountedGuests) }}</span>
                            <strong class="booking-price-amount booking-price-amount--green">−₱{{ number_format((float) $estimatedDiscount, 2) }}</strong>
                        </div>
                        <p class="form-hint booking-section-note booking-price-discount-note">Estimated per eligible guest: ₱{{ number_format(round($guestShare * 0.2, 2), 2) }}. Final discount is applied at checkout after ID verification.</p>
                    @endif
                    <div class="booking-price-line booking-price-line--total">
                        <span>Estimated total after discount</span>
                        <strong class="booking-price-amount">₱{{ number_format(max(0, (float) $this->summary['total'] - $estimatedDiscount), 2) }}</strong>
                    </div>
                    <p class="booking-price-note">No payment is due to submit a request. If accepted, a {{ (int) (config('booking.downpayment_fraction') * 100) }}% downpayment is required to confirm it. Any remaining balance and checkout charges are due at checkout.</p>
                @else
                    <div class="booking-price-empty fs-sm text-center">Select valid dates to see your price breakdown.</div>
                @endif
            </div>
        </aside>
    </div>

</div>

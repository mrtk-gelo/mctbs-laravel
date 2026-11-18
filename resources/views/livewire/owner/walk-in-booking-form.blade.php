<div class="container" style="margin-top:24px;max-width:1240px">
    <style>
        .walk-in-layout { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(280px, .85fr); gap: 20px; align-items: start; }
        .walk-in-layout > .card { min-width: 0; margin-bottom: 0; }
        .walk-in-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .walk-in-fields > .form-group { margin-bottom: 0; }
        .walk-in-count-summary { padding: 14px; border: 1px solid var(--border); border-radius: 10px; }
        .walk-in-discounts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .walk-in-discounts > section { min-width: 0; padding: 14px; border: 1px solid var(--border); border-radius: 10px; }
        .walk-in-price-line { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
        .walk-in-price-line strong { white-space: nowrap; }
        @media (max-width: 760px) {
            .walk-in-layout { grid-template-columns: minmax(0, 1fr); }
        }
        @media (max-width: 520px) {
            .walk-in-fields, .walk-in-discounts { grid-template-columns: minmax(0, 1fr); }
        }
    </style>

    <a href="{{ route($dashboardPrefix.'.bookings.index') }}" class="text-muted fs-sm">
        <i class="fa fa-arrow-left"></i> Back to bookings
    </a>
    <div class="page-header-row page-header mt-2">
        <div>
            <h1>Add Walk-in Booking</h1>
            <p>Save the booking and record the payment.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mb-3" role="alert">{{ $errors->first() }}</div>
    @endif

    @if ($units->isEmpty())
        <div class="empty-state">
            <i class="fa fa-bed"></i>
            <h3>No available units</h3>
            <p>There are no available units for your properties.</p>
        </div>
    @else
        <form wire:submit="submit">
            <div class="walk-in-layout">
                <section class="card">
                    <div class="card-header">Guest and Stay</div>
                    <div class="card-body">
                        <div class="walk-in-fields">
                            <div class="form-group" style="grid-column:1 / -1">
                                <label class="required" for="walk-in-unit">Unit</label>
                                <select id="walk-in-unit" wire:model.live="unitId" required>
                                    <option value="">Choose a unit</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->unit_id }}">{{ $unit->unit_type }} · {{ $unit->house->name }}</option>
                                    @endforeach
                                </select>
                                @error('unitId')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="required" for="walk-in-name">Guest name</label>
                                <input id="walk-in-name" type="text" wire:model.blur="walkInName" maxlength="200" required>
                                @error('walkInName')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="required" for="walk-in-phone">Contact number</label>
                                <input id="walk-in-phone" type="tel" inputmode="tel" wire:model.blur="walkInPhone" maxlength="30" required>
                                @error('walkInPhone')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group" style="grid-column:1 / -1">
                                <label for="walk-in-email">Email <span class="text-muted">(optional)</span></label>
                                <input id="walk-in-email" type="email" wire:model.blur="walkInEmail" maxlength="191">
                                @error('walkInEmail')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="required" for="walk-in-check-in">Check-in date</label>
                                <input id="walk-in-check-in" type="date" wire:model.live="checkIn" min="{{ today()->toDateString() }}" required>
                                @error('checkIn')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="required" for="walk-in-check-out">Check-out date</label>
                                <input id="walk-in-check-out" type="date" wire:model.live="checkOut" min="{{ $checkIn ? \Carbon\Carbon::parse($checkIn)->addDay()->toDateString() : today()->addDay()->toDateString() }}" required>
                                @error('checkOut')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        @if ($selectedUnit)
                            <div class="booking-form-section mt-3">
                                <h3>Guests</h3>
                                <div class="walk-in-fields">
                                    <div class="form-group">
                                        <label class="required" for="walk-in-adults">Paying guests</label>
                                        <input id="walk-in-adults" type="number" inputmode="numeric" wire:model.live.debounce.300ms="adultCount" min="1" max="{{ $selectedUnit->maximum_capacity ?? $selectedUnit->capacity }}" required>
                                        @error('adultCount')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                                    </div>
                                    @if ($selectedUnit->child_free_through_age !== null)
                                        <div class="form-group">
                                            <label for="walk-in-children">Children age {{ $selectedUnit->child_free_through_age }} or younger</label>
                                            <input id="walk-in-children" type="number" inputmode="numeric" wire:model.live.debounce.300ms="freeChildCount" min="0" max="100">
                                            @error('freeChildCount')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                                        </div>
                                    @endif
                                    <div class="walk-in-count-summary" style="grid-column:1 / -1">
                                        <strong>Counted pax: {{ max(0, (int) $adultCount) + max(0, (int) $freeChildCount) }} / {{ $selectedUnit->maximum_capacity ?? $selectedUnit->capacity }} max</strong>
                                        <p class="text-muted fs-sm mb-0 mt-1">Paying: {{ max(0, (int) $adultCount) }} | Free children: {{ max(0, (int) $freeChildCount) }}</p>
                                    </div>
                                </div>

                                <div class="walk-in-discounts mt-3">
                                    <section>
                                        <h4>Senior Citizens</h4>
                                        @foreach ($seniorIds as $index => $id)
                                            <div class="form-group" wire:key="walk-in-senior-{{ $index }}">
                                                <label class="required" for="walk-in-senior-id-{{ $index }}">ID reference</label>
                                                <div class="flex-between" style="gap:8px">
                                                    <input id="walk-in-senior-id-{{ $index }}" type="text" wire:model.blur="seniorIds.{{ $index }}" maxlength="100">
                                                    <button type="button" class="btn btn-outline btn-sm" wire:click="removeSenior({{ $index }})">Remove</button>
                                                </div>
                                                @error("seniorIds.$index")<p class="form-hint text-danger">{{ $message }}</p>@enderror
                                            </div>
                                        @endforeach
                                        <button type="button" class="btn btn-outline btn-sm" wire:click="addSenior" @disabled($seniorCount + $pwdCount >= (int) $adultCount)>Add Senior</button>
                                    </section>
                                    <section>
                                        <h4>PWD Guests</h4>
                                        @foreach ($pwdIds as $index => $id)
                                            <div class="form-group" wire:key="walk-in-pwd-{{ $index }}">
                                                <label class="required" for="walk-in-pwd-id-{{ $index }}">ID reference</label>
                                                <div class="flex-between" style="gap:8px">
                                                    <input id="walk-in-pwd-id-{{ $index }}" type="text" wire:model.blur="pwdIds.{{ $index }}" maxlength="100">
                                                    <button type="button" class="btn btn-outline btn-sm" wire:click="removePwd({{ $index }})">Remove</button>
                                                </div>
                                                @error("pwdIds.$index")<p class="form-hint text-danger">{{ $message }}</p>@enderror
                                            </div>
                                        @endforeach
                                        <button type="button" class="btn btn-outline btn-sm" wire:click="addPwd" @disabled($seniorCount + $pwdCount >= (int) $adultCount)>Add PWD</button>
                                    </section>
                                </div>
                                @error('seniorIds')<p class="form-hint text-danger mt-2">{{ $message }}</p>@enderror
                                @error('pwdIds')<p class="form-hint text-danger mt-2">{{ $message }}</p>@enderror
                            </div>

                            <div class="booking-form-section mt-3">
                                <h3>Payment</h3>
                                <div class="walk-in-fields">
                                    <div class="form-group">
                                        <label class="required" for="walk-in-payment-method">Method</label>
                                        <select id="walk-in-payment-method" wire:model="paymentMethod" required>
                                            <option value="Cash">Cash</option>
                                            <option value="GCash">GCash</option>
                                            <option value="Bank Transfer">Bank Transfer</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="required" for="walk-in-payment-amount">Amount received (₱)</label>
                                        <input id="walk-in-payment-amount" type="number" inputmode="decimal" wire:model.blur="paymentAmount" min="0.01" step="0.01" required>
                                        @error('paymentAmount')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                                    </div>
                                    <div class="form-group" style="grid-column:1 / -1">
                                        <label for="walk-in-payment-reference">Payment reference <span class="text-muted">(optional)</span></label>
                                        <input id="walk-in-payment-reference" type="text" wire:model.blur="paymentReference" maxlength="100">
                                        @error('paymentReference')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                                @if ($checkIn === today()->toDateString())
                                    <label class="flex-center gap-1 mt-3" style="justify-content:flex-start">
                                        <input type="checkbox" wire:model="checkInNow">
                                        Check-in now
                                    </label>
                                    @error('checkInNow')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                                @endif
                            </div>
                        @endif
                    </div>
                </section>

                <aside class="card">
                    <div class="card-header">Booking Summary</div>
                    <div class="card-body">
                        @if (! $selectedUnit)
                            <p class="text-muted mb-0">Choose a unit to see its capacity and price.</p>
                        @else
                            <p class="fw-bold mb-1">{{ $selectedUnit->unit_type }}</p>
                            <p class="text-muted fs-sm">{{ $selectedUnit->house->name }}</p>
                            <p class="text-muted fs-sm">Standard: {{ $selectedUnit->capacity }} guests | Max: {{ $selectedUnit->maximum_capacity ?? $selectedUnit->capacity }} guests</p>
                            @if ($summary)
                                <hr class="divider">
                                <div class="walk-in-price-line">
                                    <span>Stay/room charge</span>
                                    <strong>₱{{ number_format($summary['room_subtotal'], 2) }}</strong>
                                </div>
                                @if ($summary['extra_guest_total'] > 0)
                                    <div class="walk-in-price-line">
                                        <span>Extra heads</span>
                                        <strong>₱{{ number_format($summary['extra_guest_total'], 2) }}</strong>
                                    </div>
                                @endif
                                <div class="walk-in-price-line" style="font-weight:700">
                                    <span>Booking total</span>
                                    <strong>₱{{ number_format($summary['total'], 2) }}</strong>
                                </div>
                                @if ($summary['estimated_discount'] > 0)
                                    <div class="walk-in-price-line">
                                        <span>Estimated Senior/PWD discount</span>
                                        <strong class="text-success">−₱{{ number_format($summary['estimated_discount'], 2) }}</strong>
                                    </div>
                                    <p class="text-muted fs-sm">ID must be verified. The discount is refunded after payment.</p>
                                @endif
                            @else
                                <p class="text-muted fs-sm mb-0">Choose stay dates to see the price.</p>
                            @endif
                        @endif
                    </div>
                    <div class="card-body" style="padding-top:0">
                        <button type="submit" class="btn btn-primary btn-block" @disabled(! $selectedUnit)>Save Booking</button>
                    </div>
                </aside>
            </div>
        </form>
    @endif
</div>

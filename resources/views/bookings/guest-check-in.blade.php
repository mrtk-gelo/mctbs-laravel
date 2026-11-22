<x-shell title="Guest Check-in | MCTBS">
    <x-slot:nav>
        @if ($dashboardPrefix === 'staff')
            @include('partials.staff-nav', ['activePage' => 'dashboard'])
        @else
            @include('partials.owner-nav', ['activePage' => 'dashboard'])
        @endif
    </x-slot:nav>

    <style>
        .guest-checkin-dialog { position: fixed; inset: 0; margin: auto; width: min(680px, calc(100vw - 28px)); max-height: calc(100vh - 40px); overflow: auto; padding: 0; border: 1px solid var(--border); border-radius: 12px; color: var(--text); }
        .guest-checkin-dialog::backdrop { background: rgba(15, 23, 42, .65); }
        .guest-checkin-dialog-header, .guest-checkin-dialog-body, .guest-checkin-dialog-actions { padding: 16px 20px; }
        .guest-checkin-dialog-header, .guest-checkin-dialog-actions { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .guest-checkin-dialog-header { border-bottom: 1px solid var(--border); }
        .guest-checkin-dialog-actions { justify-content: flex-end; border-top: 1px solid var(--border); }
        .guest-checkin-layout { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); grid-template-areas: "stay actions" "verification addons" "refunds ."; gap: 20px; align-items: start; }
        .guest-checkin-layout > .card { margin-bottom: 0; min-width: 0; }
        .guest-checkin-stay { grid-area: stay; }
        .guest-checkin-actions { grid-area: actions; }
        .guest-checkin-verification { grid-area: verification; }
        .guest-checkin-addons { grid-area: addons; }
        .guest-checkin-refunds { grid-area: refunds; }
        .guest-checkin-layout > .card > .card-header { justify-content: flex-start; color: var(--heading-color); }
        .guest-checkin-layout > .guest-checkin-stay > .card-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .guest-checkin-stay .card-body { padding: 24px; }
        .guest-checkin-detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .guest-checkin-detail { display: grid; align-content: center; gap: 6px; min-width: 0; padding: 14px 16px; border: 1px solid #f1eee8; border-radius: 10px; background: #fff; }
        .guest-checkin-headcount-row { grid-column: 1 / -1; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .guest-checkin-headcount-row > .guest-checkin-detail-reference { grid-column: 1 / -1; }
        .guest-checkin-headcount-field { margin: 0; }
        .guest-checkin-headcount-field input { width: 100%; }
        .guest-checkin-reference { display: grid; justify-items: end; gap: 2px; text-align: right; }
        .guest-checkin-reference-label { color: #7c7b78; font-size: 11px; font-weight: 600; letter-spacing: .045em; line-height: 1.3; text-transform: uppercase; }
        .guest-checkin-reference-value { color: var(--heading-color); font-size: 13px; font-weight: 600; overflow-wrap: anywhere; }
        .guest-checkin-detail-date { border-color: #f3e8d0; background: #fffbf3; }
        .guest-checkin-detail-label { color: #7c7b78; font-size: 12px; font-weight: 600; letter-spacing: .045em; line-height: 1.3; text-transform: uppercase; }
        .guest-checkin-detail-value { color: #4b5563; font-size: 15px; font-weight: 600; line-height: 1.4; overflow-wrap: anywhere; }
        .guest-checkin-action-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .guest-checkin-action-grid > * { min-width: 0; }
        .guest-checkin-action-grid .btn { display: flex; align-items: center; justify-content: center; min-height: 42px; width: 100%; text-align: center; }
        @media (max-width: 700px) {
            .guest-checkin-layout { grid-template-columns: minmax(0, 1fr); gap: 14px; }
            .guest-checkin-layout { grid-template-areas: "stay" "actions" "verification" "addons" "refunds"; }
            .guest-checkin-stay > .card-header { align-items: flex-start; }
        }
        @media (max-width: 420px) {
            .guest-checkin-detail-grid, .guest-checkin-headcount-row, .guest-checkin-action-grid { grid-template-columns: minmax(0, 1fr); }
            .guest-checkin-reference { max-width: 58%; }
        }
        .amenity-card-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px; }
        .amenity-card { position: relative; display: block; padding: 14px; border: 1px solid var(--border); border-radius: 10px; cursor: pointer; transition: border-color .15s, box-shadow .15s; }
        .amenity-card:has(input:checked) { border-color: var(--primary); box-shadow: 0 0 0 2px color-mix(in srgb, var(--primary) 20%, transparent); }
        .amenity-card:focus-within { outline: 2px solid var(--primary); outline-offset: 2px; }
        .amenity-card-selection { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
        .amenity-card .form-group { display: grid; gap: 6px; }
        .amenity-card input[type="number"]:disabled { opacity: .55; }
    </style>

    <div class="container" style="margin-top:24px;max-width:1240px">
        <a href="{{ route($dashboardPrefix.'.dashboard') }}" class="text-muted fs-sm"><i class="fa fa-arrow-left"></i> Back to dashboard</a>
        <div class="page-header-row page-header mt-2">
            <div>
                <h1>Guest Check-in</h1>
                <p>{{ $booking->displayGuestName() }} · {{ $booking->unit->unit_type }}</p>
            </div>
            <span class="badge badge-accepted">{{ $booking->status }}</span>
        </div>

        @if (session('success'))
            <div class="alert alert-success mb-3" role="status">{{ session('success') }}</div>
        @endif
        @if (session('feedbackUrl'))
            <div class="alert alert-info mb-3" role="status">
                Feedback link:
                <a href="{{ session('feedbackUrl') }}" target="_blank" rel="noopener">{{ session('feedbackUrl') }}</a>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger mb-3" role="alert">{{ $errors->first() }}</div>
        @endif

        <div class="guest-checkin-layout">
            <section class="card guest-checkin-stay">
                <div class="card-header">
                    <span>Stay Details</span>
                    <span class="guest-checkin-reference">
                        <span class="guest-checkin-reference-label">Booking reference</span>
                        <strong class="guest-checkin-reference-value">{{ $booking->booking_reference ?? 'Booking #'.$booking->booking_id }}</strong>
                    </span>
                </div>
                <div class="card-body">
                    @if ($canUpdateCheckInHeadcount)
                        <form method="POST" action="{{ route($dashboardPrefix.'.guests.headcount.update', $booking->booking_id) }}">
                            @csrf
                            <div class="guest-checkin-detail-grid">
                    @else
                        <div class="guest-checkin-detail-grid">
                    @endif
                        <div class="guest-checkin-detail">
                            <span class="guest-checkin-detail-label">Guest</span>
                            <strong class="guest-checkin-detail-value">{{ $booking->displayGuestName() }}</strong>
                        </div>
                        <div class="guest-checkin-detail">
                            <span class="guest-checkin-detail-label">Unit</span>
                            <strong class="guest-checkin-detail-value">{{ $booking->unit->unit_type }} · {{ $booking->unit->house->name }}</strong>
                        </div>
                        <div class="guest-checkin-headcount-row">
                            @if ($canUpdateCheckInHeadcount)
                                <div class="form-group guest-checkin-headcount-field">
                                    <label for="adult-count">Adult Paying Guests</label>
                                    <input id="adult-count" name="adult_count" type="number" min="1" max="{{ $booking->unit->maximum_capacity ?? $booking->unit->capacity }}" step="1" value="{{ old('adult_count', $booking->adult_count) }}" required>
                                    @error('adult_count')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group guest-checkin-headcount-field">
                                    <label for="free-child-count">Free Children</label>
                                    <input id="free-child-count" name="child_count" type="number" min="0" max="{{ $booking->unit->child_free_through_age === null ? 0 : ($booking->unit->maximum_capacity ?? $booking->unit->capacity) }}" step="1" value="{{ old('child_count', $booking->child_count) }}" required>
                                    @error('child_count')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                                </div>
                                <div class="guest-checkin-detail guest-checkin-detail-reference">
                                    <span class="guest-checkin-detail-label">Guest Headcount</span>
                                    <span class="guest-checkin-detail-value">Compare the displayed counts with guests present. Save corrections before completing check-in.</span>
                                    <button type="submit" class="btn btn-outline">Save corrected headcount</button>
                                </div>
                            @else
                                <div class="guest-checkin-detail">
                                    <span class="guest-checkin-detail-label">Adult Paying Guests</span>
                                    <strong class="guest-checkin-detail-value">{{ $booking->adult_count }}</strong>
                                </div>
                                <div class="guest-checkin-detail">
                                    <span class="guest-checkin-detail-label">Free Children</span>
                                    <strong class="guest-checkin-detail-value">{{ $booking->child_count }}</strong>
                                </div>
                            @endif
                        </div>
                        <div class="guest-checkin-detail guest-checkin-detail-date">
                            <span class="guest-checkin-detail-label">Check-in</span>
                            <strong class="guest-checkin-detail-value">{{ $booking->check_in->format('M j, Y') }}</strong>
                        </div>
                        <div class="guest-checkin-detail guest-checkin-detail-date">
                            <span class="guest-checkin-detail-label">Check-out</span>
                            <strong class="guest-checkin-detail-value">{{ $booking->check_out->format('M j, Y') }}</strong>
                        </div>
                    @if ($canUpdateCheckInHeadcount)
                            </div>
                        </form>
                    @else
                        </div>
                    @endif
                </div>
            </section>

            <section class="card guest-checkin-actions">
                @if ($booking->status === \App\Support\BookingStatus::CHECKED_IN)
                    <div class="card-header">Actions</div>
                    <div class="card-body">
                        <div class="guest-checkin-action-grid">
                            <button type="button" class="btn btn-outline" data-open-checkin-dialog="refund-dialog">Refund</button>
                            <a href="{{ route($dashboardPrefix.'.bookings.edit', $booking->booking_id) }}" class="btn btn-outline">Edit Booking</a>
                            <button type="button" class="btn btn-outline" data-open-checkin-dialog="amenities-dialog">Add Amenities</button>
                            <a href="{{ route($dashboardPrefix.'.bookings.show', $booking->booking_id) }}" class="btn btn-outline">View Booking Details</a>
                            <form method="POST" action="{{ route($dashboardPrefix.'.guests.feedback.send', $booking->booking_id) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline">Send Feedback</button>
                            </form>
                            <a href="{{ route($dashboardPrefix.'.bookings.checkout', $booking->booking_id) }}" class="btn btn-primary">Check-out</a>
                        </div>
                    </div>
                @else
                    <div class="card-header">Complete Booking Check-in</div>
                    <div class="card-body">
                        <p class="text-muted">Verify the guest headcount and complete check-in when the declared counts are correct.</p> <br>
                        <form method="POST" action="{{ route($dashboardPrefix.'.bookings.check-in', $booking->booking_id) }}">
                            @csrf
                            <label class="form-group">
                                <span class="guest-checkin-detail-value">I verified the ticket’s booking reference and confirmed the paying guest and free-child counts match the guests present.</span>
                                <span class="flex-center gap-1">
                                    <input type="checkbox" name="guest_details_confirmed" value="1" required @checked(old('guest_details_confirmed'))>
                                    <span>Confirm booking details</span>
                                </span>
                            </label>
                            @error('guest_details_confirmed')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                            <button type="submit" class="btn btn-primary">Confirm Details &amp; Complete Check-in</button>
                        </form>
                    </div>
                @endif
            </section>

            @if ($booking->discounts->isNotEmpty())
            <section class="card guest-checkin-secondary guest-checkin-verification">
                <div class="card-header">Senior/PWD Verification</div>
                <div class="card-body">
                    @foreach ($booking->discounts as $discount)
                        <div class="policy-box mb-3">
                            <div class="flex-between">
                                <strong>{{ $discount->guest->full_name }} · {{ $discount->discount_type }}</strong>
                                <span class="badge {{ $discount->verification_status === 'Verified' ? 'badge-verified' : (in_array($discount->verification_status, ['Invalid', 'Rejected'], true) ? 'badge-unpaid' : 'badge-pending') }}">{{ $discount->verification_status }}</span>
                            </div>
                            <div class="text-muted fs-sm mt-1">ID number: {{ $discount->id_reference }}</div>
                            @if ($discount->verification_status === 'Verified')
                                <div class="flex-between fs-sm mt-2">
                                    <span>{{ $discount->discount_type }} discount for {{ $discount->guest->full_name }}</span>
                                    <strong>−₱{{ number_format($discount->discount_amount, 2) }}</strong>
                                </div>
                            @elseif ($discount->verification_status === 'Invalid')
                                <p class="alert alert-danger mt-2 mb-0">ID is invalid. Please tell the guest in person.</p>
                            @elseif ($discount->verification_status === \App\Support\BookingStatus::PENDING && $discount->guest->roster_status !== \App\Models\BookingGuest::ROSTER_ACTIVE)
                                <p class="form-hint mt-2">This guest was removed from the reservation, so the discount cannot be verified.</p>
                            @elseif ($discount->verification_status === \App\Support\BookingStatus::PENDING)
                                @if ($booking->status === \App\Support\BookingStatus::CONFIRMED)
                                    <p class="form-hint mt-2">Review this ID from the Upcoming Check-ins dashboard modal.</p>
                                    <a href="{{ route($dashboardPrefix.'.dashboard', ['review' => $booking->booking_id]) }}" class="btn btn-outline btn-sm mt-2">Open arrival review</a>
                                @else
                                    <p class="form-hint mt-2">This ID has not been reviewed.</p>
                                @endif
                            @endif
                            @if (in_array($discount->verification_status, ['Invalid', 'Rejected'], true) && $discount->verification_note)
                                <p class="alert alert-danger mt-2 mb-0">Reason: {{ $discount->verification_note }}</p>
                            @endif
                            @if ($discount->verifiedBy)
                                <div class="text-muted fs-sm mt-2">Reviewed by {{ $discount->verifiedBy->first_name }} {{ $discount->verifiedBy->last_name }} · {{ $discount->verified_at?->format('M j, Y g:i A') }}</div>
                            @endif
                        </div>
                    @endforeach
                    <p class="form-hint mb-0">Each eligible discount applies only to the guest named above, not to the whole booking. A verified discount is reflected in the checkout breakdown.</p>
                </div>
            </section>
            @endif

        @if ($booking->status === \App\Support\BookingStatus::CHECKED_IN)
        <section class="card guest-checkin-secondary guest-checkin-addons">
            <div class="card-header">Amenities Added</div>
            <div class="card-body">
                @php
                    $activeAddons = $booking->addons->filter(fn ($addon) => $addon->quantity > 0);
                @endphp
                @forelse ($activeAddons as $addon)
                    <div class="flex-between mb-2">
                        <span>{{ $addon->name }} · {{ $addon->quantity }} × ₱{{ number_format((float) $addon->price, 2) }}</span>
                        <span class="flex-center gap-1">
                            <strong>₱{{ number_format((float) $addon->price * $addon->quantity, 2) }}</strong>
                            <form method="POST" action="{{ route($dashboardPrefix.'.guests.addons.quantity', [$booking->booking_id, $addon->booking_addon_id]) }}">
                                @csrf
                                <input type="hidden" name="change" value="-1">
                                <button type="submit" class="btn btn-outline btn-sm" aria-label="Decrease {{ $addon->name }} quantity">−</button>
                            </form>
                            <form method="POST" action="{{ route($dashboardPrefix.'.guests.addons.quantity', [$booking->booking_id, $addon->booking_addon_id]) }}">
                                @csrf
                                <input type="hidden" name="change" value="1">
                                <button type="submit" class="btn btn-outline btn-sm" aria-label="Increase {{ $addon->name }} quantity">+</button>
                            </form>
                        </span>
                    </div>
                @empty
                    <p class="text-muted mb-0">No amenities have been added to this booking.</p>
                @endforelse
                @if ($activeAddons->isNotEmpty())
                    <hr class="divider">
                    <div class="flex-between"><strong>Amenities total</strong><strong>₱{{ number_format($activeAddons->sum(fn ($addon) => (float) $addon->price * $addon->quantity), 2) }}</strong></div>
                @endif
            </div>
        </section>

        <section class="card guest-checkin-secondary guest-checkin-refunds">
            <div class="card-header">Refund History</div>
            <div class="card-body">
                @forelse ($booking->refunds->sortByDesc('created_at') as $refund)
                    <div class="flex-between" style="gap:12px;align-items:flex-start">
                        <div>
                            <strong>₱{{ number_format((float) $refund->amount, 2) }} · {{ $refund->reason }}</strong>
                            <div class="text-muted fs-sm">{{ $refund->processed_at?->format('M j, Y g:i A') ?? $refund->created_at?->format('M j, Y g:i A') }} · {{ $refund->processedBy?->first_name }} {{ $refund->processedBy?->last_name }}</div>
                        </div>
                        <span class="badge {{ $refund->refund_status === 'Refunded' ? 'badge-verified' : 'badge-pending' }}">{{ $refund->refund_status }}</span>
                    </div>
                    @if (! $loop->last)<hr class="divider">@endif
                @empty
                    <p class="text-muted mb-0">No refunds have been recorded.</p>
                @endforelse
            </div>
        </section>
        @endif
        </div>
    </div>

    <dialog class="guest-checkin-dialog" id="refund-dialog" aria-labelledby="refund-dialog-title">
        <div class="guest-checkin-dialog-header">
            <strong id="refund-dialog-title">Record a Refund</strong>
        </div>
        <form method="POST" action="{{ route($dashboardPrefix.'.guests.refunds.store', $booking->booking_id) }}">
            @csrf
            <div class="guest-checkin-dialog-body">
                <p class="text-muted fs-sm">Available refundable overpayment after approved charges and completed refunds: ₱{{ number_format($refundAmountLimit, 2) }}.</p>
                <div class="form-group">
                    <label class="required" for="refund-amount">Refund amount (₱)</label>
                    <input id="refund-amount" name="amount" type="number" min="0.01" max="{{ number_format($refundAmountLimit, 2, '.', '') }}" step="0.01" value="{{ old('amount') }}" required>
                </div>
                <div class="form-group">
                    <label class="required" for="refund-reason">Reason</label>
                    <textarea id="refund-reason" name="reason" rows="3" maxlength="255" placeholder="Example: Guest needs to go home early" required>{{ old('reason') }}</textarea>
                </div>
                @if ($refundAmountLimit <= 0)
                    <p class="alert alert-warning mb-0">There is no verified payment balance available to refund.</p>
                @endif
            </div>
            <div class="guest-checkin-dialog-actions">
                <button type="button" class="btn btn-outline" data-close-checkin-dialog>Cancel</button>
                <button type="submit" class="btn btn-primary" @disabled($refundAmountLimit <= 0)>Refund</button>
            </div>
        </form>
    </dialog>

    <dialog class="guest-checkin-dialog" id="amenities-dialog" aria-labelledby="amenities-dialog-title">
        <div class="guest-checkin-dialog-header">
            <strong id="amenities-dialog-title">Add Amenities</strong>
        </div>
        <form method="POST" action="{{ route($dashboardPrefix.'.guests.addons.store', $booking->booking_id) }}">
            @csrf
            <div class="guest-checkin-dialog-body">
                @if ($booking->unit->addons->isNotEmpty())
                    <p class="text-muted fs-sm">Choose one or more amenities and set each quantity.</p> <br>
                    <div class="amenity-card-list">
                        @foreach ($booking->unit->addons as $index => $addon)
                            <label class="amenity-card">
                                <input class="amenity-card-selection" type="checkbox" name="addons[{{ $index }}][unit_addon_id]" value="{{ $addon->unit_addon_id }}" @checked((string) old('addons.'.$index.'.unit_addon_id') === (string) $addon->unit_addon_id) data-amenity-check>
                                <span class="flex-between">
                                    <strong>{{ $addon->name }}</strong>
                                    <span>₱{{ number_format((float) $addon->price, 2) }}</span>
                                </span>
                                <span class="form-group mt-2 mb-0">
                                    <span class="text-muted fs-sm">Quantity</span>
                                    <input type="number" name="addons[{{ $index }}][quantity]" value="{{ old('addons.'.$index.'.quantity', 1) }}" min="1" max="100" @disabled((string) old('addons.'.$index.'.unit_addon_id') !== (string) $addon->unit_addon_id) data-amenity-quantity>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('addons')
                        <p class="form-hint text-danger mt-2">{{ $message }}</p>
                    @enderror
                    @foreach ($errors->get('addons.*') as $addonErrors)
                        @foreach ($addonErrors as $error)
                            <p class="form-hint text-danger mt-2">{{ $error }}</p>
                        @endforeach
                    @endforeach
                @else
                    <p class="text-muted mb-0">This unit has no add-on amenities.</p>
                @endif
            </div>
            <div class="guest-checkin-dialog-actions">
                <button type="button" class="btn btn-outline" data-close-checkin-dialog>Cancel</button>
                <button type="submit" class="btn btn-primary" @disabled($booking->unit->addons->isEmpty())>Submit</button>
            </div>
        </form>
    </dialog>

    <script>
        (() => {
            document.addEventListener('click', (event) => {
                const openButton = event.target.closest('[data-open-checkin-dialog]');
                if (openButton) {
                    document.getElementById(openButton.dataset.openCheckinDialog)?.showModal();
                }

                if (event.target.closest('[data-close-checkin-dialog]')) {
                    event.target.closest('dialog')?.close();
                }
            });

            document.querySelectorAll('[data-amenity-check]').forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    const quantity = checkbox.closest('.amenity-card').querySelector('[data-amenity-quantity]');
                    quantity.disabled = !checkbox.checked;
                });
            });

            @if ($errors->has('amount') || $errors->has('reason'))
                document.getElementById('refund-dialog')?.showModal();
            @endif
            @if ($errors->has('addons') || collect($errors->keys())->contains(fn ($key) => str_starts_with($key, 'addons.')))
                document.getElementById('amenities-dialog')?.showModal();
            @endif
        })();
    </script>
</x-shell>

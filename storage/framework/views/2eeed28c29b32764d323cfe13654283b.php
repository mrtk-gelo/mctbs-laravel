<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Edit Booking #'.e($booking->booking_id).' | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Edit Booking #'.e($booking->booking_id).' | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> 
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dashboardPrefix === 'owner'): ?>
            <?php echo $__env->make('partials.owner-nav', ['activePage' => 'bookings'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('partials.staff-nav', ['activePage' => 'bookings'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="container-sm" style="margin-top:24px">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->status === \App\Support\BookingStatus::CHECKED_IN): ?>
            <a href="<?php echo e(route($dashboardPrefix.'.guests.check-in', $booking->booking_id)); ?>" class="text-muted fs-sm"><i class="fa fa-arrow-left"></i> Back to Guest Check-in</a>
        <?php else: ?>
            <a href="<?php echo e(route($dashboardPrefix.'.bookings.show', $booking->booking_id)); ?>" class="text-muted fs-sm"><i class="fa fa-arrow-left"></i> Back to booking</a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="page-header-row page-header mt-2">
            <div><h1>Edit Booking #<?php echo e($booking->booking_id); ?></h1><p><?php echo e($booking->displayGuestName()); ?> · <?php echo e($booking->unit->unit_type); ?></p></div>
            <span class="badge badge-<?php echo e(strtolower($booking->status)); ?>"><?php echo e($booking->status); ?></span>
        </div>



        <div class="card">
            <div class="card-header">Update booking details</div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                    <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="policy-box mb-3">
                    Owner/Staff can update upcoming dates after discussing the change with the guest.
                    Changes are saved with a timestamp and a before/after record. Any payment above the revised
                    total remains as credit toward checkout. Senior/PWD discounts are verified separately at checkout.
                </div>
                <form method="POST" action="<?php echo e(route($dashboardPrefix.'.bookings.update', $booking->booking_id)); ?>" id="booking-edit-form">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="check-in">Check-in</label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->status === \App\Support\BookingStatus::CHECKED_IN): ?>
                                <input id="check-in" type="date" value="<?php echo e($booking->check_in->toDateString()); ?>" disabled aria-describedby="check-in-locked-hint">
                                <small id="check-in-locked-hint" class="form-hint">The recorded check-in date is fixed for an active stay.</small>
                            <?php else: ?>
                                <input id="check-in" type="date" name="check_in" value="<?php echo e(old('check_in', $booking->check_in->toDateString())); ?>" min="<?php echo e(now()->addDays(2)->toDateString()); ?>" required>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="check-out">Check-out</label>
                            <input id="check-out" type="date" name="check_out" value="<?php echo e(old('check_out', $booking->check_out->toDateString())); ?>" min="<?php echo e($booking->status === \App\Support\BookingStatus::CHECKED_IN ? today()->addDay()->toDateString() : old('check_in', $booking->check_in->toDateString())); ?>" required>
                        </div>
                    </div>
                    <div class="form-row mt-3">
                        <div class="form-group">
                            <label class="required" for="adult-count">Paying guests (counted guests)</label>
                            <input id="adult-count" type="number" name="adult_count" value="<?php echo e(old('adult_count', $booking->adult_count)); ?>" min="1" max="<?php echo e($booking->unit->maximum_capacity ?? $booking->unit->capacity); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="required" for="child-count">Free children</label>
                            <input id="child-count" type="number" name="child_count" value="<?php echo e(old('child_count', $booking->child_count)); ?>" min="0" max="<?php echo e($booking->unit->child_free_through_age === null ? 0 : 100); ?>" required>
                        </div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->status === \App\Support\BookingStatus::CHECKED_IN): ?>
                        <p class="form-hint">The recorded check-in date is fixed for this active stay. Checkout can be adjusted, but guest-count reductions are not allowed after check-in and all counted guests must fit the unit’s maximum capacity.</p>
                    <?php else: ?>
                        <p class="form-hint">Paying guests and free children can be adjusted before check-in. All guests count toward the unit’s maximum occupancy.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="card mt-3">
                        <div class="card-header">Revised checkout estimate</div>
                        <div class="card-body">
                            <div class="booking-price-line booking-price-line--total">
                                <span>Current total before checkout discount</span>
                                <strong class="booking-price-amount">₱<?php echo e(number_format((float) $booking->amountDueBeforeCheckout(), 2)); ?></strong>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentCheckoutCharges > 0): ?>
                                <div class="booking-price-line">
                                    <span>Existing checkout and damage charges</span>
                                    <strong class="booking-price-amount booking-price-amount--red">₱<?php echo e(number_format((float) $currentCheckoutCharges, 2)); ?></strong>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="booking-price-line">
                                <span>Payments made <small class="booking-price-detail">Verified payments to date</small></span>
                                <strong class="booking-price-amount booking-price-amount--green">₱<?php echo e(number_format((float) $verifiedPaymentTotal, 2)); ?></strong>
                            </div>
                            <div class="booking-price-line booking-price-line--total">
                                <span>Revised total before checkout discount</span>
                                <strong id="revised-total" class="booking-price-amount">₱0.00</strong>
                            </div>
                            <div class="booking-price-line booking-price-line--total">
                                <span>Estimated remaining balance at checkout</span>
                                <strong id="checkout-credit" class="booking-price-amount booking-price-amount--green">₱0.00</strong>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->status === \App\Support\BookingStatus::ACCEPTED): ?>
                                <p class="form-hint mt-2 mb-0">The downpayment is recalculated from the revised stay total. Submit the updated amount to confirm the booking.</p>
                            <?php elseif($booking->status === \App\Support\BookingStatus::CONFIRMED): ?>
                                <p class="form-hint mt-2 mb-0">Verified payments, including your downpayment, are credited toward the revised bill. Any remaining balance and eligible Senior Citizen/PWD discounts are settled at checkout.</p>
                            <?php else: ?>
                                <p class="form-hint mt-2 mb-0">No payment is due to submit a request. Any applicable downpayment is due only after the booking is accepted; the remaining balance is collected at checkout.</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label for="edit-reason">Reason / change details</label>
                        <textarea id="edit-reason" name="reason" rows="3" maxlength="2000" required><?php echo e(old('reason')); ?></textarea>
                    </div>
                    <div class="flex-center gap-1">
                        <button type="submit" class="btn btn-primary">Save booking changes</button>
                        <a href="<?php echo e($booking->status === \App\Support\BookingStatus::CHECKED_IN ? route($dashboardPrefix.'.guests.check-in', $booking->booking_id) : route($dashboardPrefix.'.bookings.show', $booking->booking_id)); ?>" class="btn btn-outline">Cancel</a>
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
            const verifiedPaid = Number(<?php echo json_encode($verifiedPaymentTotal, 15, 512) ?>);
            const currentCheckoutCharges = Number(<?php echo json_encode($currentCheckoutCharges, 15, 512) ?>);
            const nightlyRate = Number(<?php echo json_encode((float) ($booking->nightly_rate ?? $booking->unit->price), 15, 512) ?>);
            const extraGuestFee = Number(<?php echo json_encode((float) ($booking->extra_guest_fee ?? 0), 15, 512) ?>);
            const extraGuestCount = Number(<?php echo json_encode((int) ($booking->extra_guest_count ?? 0), 15, 512) ?>);
            const isCheckedIn = <?php echo json_encode($booking->status === \App\Support\BookingStatus::CHECKED_IN, 15, 512) ?>;
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
                    : Math.max(0, countedGuests - Number(<?php echo json_encode((int) $booking->unit->capacity, 15, 512) ?>));
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
                const minimumCheckOut = isCheckedIn && minimum < toDate(<?php echo json_encode(today()->addDay()->toDateString(), 15, 512) ?>)
                    ? toDate(<?php echo json_encode(today()->addDay()->toDateString(), 15, 512) ?>)
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
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcffbe109f495178c1251f7f6dc1fe742)): ?>
<?php $attributes = $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742; ?>
<?php unset($__attributesOriginalcffbe109f495178c1251f7f6dc1fe742); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcffbe109f495178c1251f7f6dc1fe742)): ?>
<?php $component = $__componentOriginalcffbe109f495178c1251f7f6dc1fe742; ?>
<?php unset($__componentOriginalcffbe109f495178c1251f7f6dc1fe742); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/bookings/edit.blade.php ENDPATH**/ ?>
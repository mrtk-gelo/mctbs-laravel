<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Checkout #'.e($booking->booking_id).' | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Checkout #'.e($booking->booking_id).' | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> 
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dashboardPrefix === 'owner'): ?>
            <?php echo $__env->make('partials.owner-nav', ['activePage' => 'bookings'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('partials.staff-nav', ['activePage' => 'bookings'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php $__env->endSlot(); ?>

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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->status === \App\Support\BookingStatus::CHECKED_IN): ?>
                    <a href="<?php echo e(route($dashboardPrefix.'.guests.check-in', $booking->booking_id)); ?>" class="text-muted fs-sm"><i class="fa fa-arrow-left"></i> Back to Guest Check-in</a>
                <?php else: ?>
                    <a href="<?php echo e(route($dashboardPrefix.'.bookings.show', $booking->booking_id)); ?>" class="text-muted fs-sm"><i class="fa fa-arrow-left"></i> Back to Booking Details</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <h1 class="mt-1">Checkout · Booking #<?php echo e($booking->booking_id); ?></h1>
                <p><?php echo e($booking->unit->unit_type); ?> · <?php echo e($booking->unit->house->name); ?> · <?php echo e($booking->displayGuestName()); ?></p>
            </div>
            <span class="badge <?php echo e($isReadOnly ? 'badge-completed' : 'badge-accepted'); ?>"><?php echo e(\App\Support\BookingStatus::canonical($booking->status)); ?></span>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div class="alert alert-success mb-3" role="status"><?php echo e(session('success')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="alert alert-danger mb-3" role="alert"><?php echo e($errors->first()); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isEarlyCheckout): ?>
            <div class="alert alert-warning mb-3" role="status">
                This stay is scheduled to end on <?php echo e($booking->check_out->format('M j, Y')); ?>. If checkout is completed today, it will be recorded as an early checkout.
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="checkout-layout">
        <section class="card checkout-breakdown">
            <div class="card-header">Checkout Breakdown</div>
            <div class="card-body">
                <?php
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
                ?>
                <div class="booking-price-line">
                    <span>Stay/room charge <small class="booking-price-detail"><?php echo e($roomQuantity); ?> × <?php echo e($roomPrice); ?></small></span>
                    <strong class="booking-price-amount">₱<?php echo e(number_format($roomCharge, 2)); ?></strong>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($extraGuestCharge > 0): ?>
                    <div class="booking-price-line">
                        <span>Extra guest charges <small class="booking-price-detail"><?php echo e($booking->extra_guest_count); ?> guests × <?php echo e($nights); ?> nights × ₱<?php echo e(number_format((float) $booking->extra_guest_fee, 2)); ?> / guest / night · included in accommodation</small></span>
                        <strong class="booking-price-amount">₱<?php echo e(number_format($extraGuestCharge, 2)); ?></strong>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->addons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $addon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) $addon->price * (int) $addon->quantity > 0): ?>
                        <div class="booking-price-line">
                            <span>Added amenities · <?php echo e($addon->name); ?> × <?php echo e($addon->quantity); ?> <small>(included by financial contract)</small></span>
                            <strong class="booking-price-amount booking-price-amount--blue">₱<?php echo e(number_format((float) $addon->price * $addon->quantity, 2)); ?></strong>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->additionalCharges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $charge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) $charge->total_amount > 0): ?>
                        <div class="booking-price-line" data-checkout-charge="<?php echo e($charge->additional_charge_id); ?>">
                            <span>Damage/missing items · <?php echo e($charge->description); ?> × <?php echo e($charge->quantity); ?> <small>(included by financial contract)</small></span>
                            <div class="booking-price-actions">
                                <strong class="booking-price-amount booking-price-amount--red">₱<?php echo e(number_format((float) $charge->total_amount, 2)); ?></strong>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($isReadOnly)): ?>
                                    <div class="flex-center gap-1">
                                        <form method="POST" action="<?php echo e(route($dashboardPrefix.'.bookings.checkout.charges.quantity', [$booking->booking_id, $charge->additional_charge_id])); ?>">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="change" value="-1">
                                            <button type="submit" class="btn btn-outline btn-sm" aria-label="Decrease <?php echo e($charge->description); ?> quantity">−</button>
                                        </form>
                                        <form method="POST" action="<?php echo e(route($dashboardPrefix.'.bookings.checkout.charges.quantity', [$booking->booking_id, $charge->additional_charge_id])); ?>">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="change" value="1">
                                            <button type="submit" class="btn btn-outline btn-sm" aria-label="Increase <?php echo e($charge->description); ?> quantity">+</button>
                                        </form>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->damageReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $damageReport): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($damageReport->isIncludedInFinalAmount() && $damageReport->calculatedChargeTotal() > 0): ?>
                        <div class="booking-price-line" data-damage-charge="<?php echo e($damageReport->damage_id); ?>">
                            <span>
                                Damage/missing items · <?php echo e($damageReport->description); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((int) $damageReport->quantity > 1): ?> × <?php echo e($damageReport->quantity); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <small>(included in amount due)</small>
                            </span>
                            <div class="booking-price-actions">
                                <strong class="booking-price-amount booking-price-amount--red">₱<?php echo e(number_format($damageReport->calculatedChargeTotal(), 2)); ?></strong>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($isReadOnly)): ?>
                                    <div class="flex-center gap-1">
                                        <form method="POST" action="<?php echo e(route($dashboardPrefix.'.bookings.checkout.damages.quantity', [$booking->booking_id, $damageReport->damage_id])); ?>">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="change" value="-1">
                                            <button type="submit" class="btn btn-outline btn-sm" aria-label="Decrease <?php echo e($damageReport->description); ?> quantity">−</button>
                                        </form>
                                        <form method="POST" action="<?php echo e(route($dashboardPrefix.'.bookings.checkout.damages.quantity', [$booking->booking_id, $damageReport->damage_id])); ?>">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="change" value="1">
                                            <button type="submit" class="btn btn-outline btn-sm" aria-label="Increase <?php echo e($damageReport->description); ?> quantity">+</button>
                                        </form>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($approvedDiscountTotal > 0): ?>
                    <div class="booking-price-line">
                        <span>Eligible verified Senior/PWD discounts</span>
                        <strong class="booking-price-amount booking-price-amount--green">−₱<?php echo e(number_format((float) $approvedDiscountTotal, 2)); ?></strong>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="booking-price-line booking-price-line--total">
                    <span>Accommodation after discounts</span>
                    <strong class="booking-price-amount">₱<?php echo e(number_format($financialSummary['accommodationAfterDiscounts'], 2)); ?></strong>
                </div>
                <div class="booking-price-line">
                    <span>Amenities and add-ons included by financial contract</span>
                    <strong class="booking-price-amount booking-price-amount--blue">₱<?php echo e(number_format($financialSummary['amenities'], 2)); ?></strong>
                </div>
                <div class="booking-price-line">
                    <span>Additional charges included by financial contract</span>
                    <strong class="booking-price-amount booking-price-amount--red">₱<?php echo e(number_format($financialSummary['additionalCharges'], 2)); ?></strong>
                </div>
                <div class="booking-price-line">
                    <span>Damage charges</span>
                    <strong class="booking-price-amount booking-price-amount--red">₱<?php echo e(number_format($financialSummary['approvedDamageCharges'], 2)); ?></strong>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->discounts->isNotEmpty()): ?>
                    <div class="policy-box mt-2">
                        <strong>Discount status</strong>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->discounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $discount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="flex-between fs-sm mt-1">
                                <span>
                                    <?php echo e($discount->guest->full_name); ?> · <?php echo e($discount->discount_type); ?> discount · <?php echo e($discount->verification_status); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($discount->guest->roster_status !== \App\Models\BookingGuest::ROSTER_ACTIVE): ?>
                                        (removed guest; excluded)
                                    <?php elseif($discount->verification_status !== 'Verified'): ?>
                                        (not applied)
                                    <?php else: ?>
                                        (applied once)
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($discount->verification_status === 'Verified' && $discount->guest->roster_status === \App\Models\BookingGuest::ROSTER_ACTIVE): ?>
                                    <strong>−₱<?php echo e(number_format($discount->discount_amount, 2)); ?></strong>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="booking-price-line booking-price-line--total">
                    <span>Final amount due</span>
                    <strong class="booking-price-amount">₱<?php echo e(number_format($financialSummary['finalAmountDue'], 2)); ?></strong>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($refundedTotal > 0): ?>
                    <div class="booking-price-line">
                        <span>Refunds</span>
                        <strong class="booking-price-amount booking-price-amount--orange">+₱<?php echo e(number_format((float) $refundedTotal, 2)); ?></strong>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="booking-price-line">
                    <span>Payments made</span>
                    <strong class="booking-price-amount booking-price-amount--green">−₱<?php echo e(number_format((float) $verifiedPaymentTotal, 2)); ?></strong>
                </div>
                <hr class="divider">
                <div class="booking-price-line booking-price-line--total">
                    <span>Outstanding balance <small>(Balance to pay)</small></span>
                    <strong class="booking-price-amount booking-price-amount--<?php echo e($balanceDue === 0.0 ? 'green' : 'red'); ?>">₱<?php echo e(number_format((float) $balanceDue, 2)); ?></strong>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($financialSummary['refundableOverpayment'] > 0): ?>
                    <div class="booking-price-line booking-price-line--total">
                        <span>Refundable overpayment</span>
                        <strong class="booking-price-amount booking-price-amount--orange">₱<?php echo e(number_format($financialSummary['refundableOverpayment'], 2)); ?></strong>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($isReadOnly)): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isEarlyCheckout): ?>
                        <div class="form-group mt-3">
                            <label class="required" for="early-checkout-reason">Reason for early checkout</label>
                            <textarea id="early-checkout-reason" name="early_checkout_reason" form="checkout-complete-form" rows="3" maxlength="1000" required><?php echo e(old('early_checkout_reason')); ?></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['early_checkout_reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="flex-center gap-1 mt-3" style="flex-wrap:wrap;justify-content:flex-start">
                        <button type="button" class="btn btn-outline" data-open-checkout-dialog>Damage/Missing Items</button>
                        <form id="checkout-complete-form" method="POST" action="<?php echo e(route($dashboardPrefix.'.bookings.checkout.complete', $booking->booking_id)); ?>" onsubmit="return confirm('Complete checkout for this booking?')">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-primary" <?php if($balanceDue > 0 || $hasUnderReviewPayment): echo 'disabled'; endif; ?>>
                                Complete Checkout
                            </button>
                        </form>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasUnderReviewPayment): ?>
                        <p class="alert alert-warning mt-3 mb-0">A submitted payment still needs review before checkout can be completed.</p>
                    <?php elseif($balanceDue > 0): ?>
                        <p class="form-hint mt-2 mb-0">Pay the remaining balance before completing checkout.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php else: ?>
                    <p class="text-muted mt-3 mb-0">Checkout is complete. This page is read-only.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <div class="checkout-history">
            <section class="card">
                <div class="card-header">Payment History</div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $booking->payments->sortByDesc('payment_date'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="flex-between checkout-payment-history-row mb-2">
                            <div>
                                <strong><?php echo e($payment->payment_method); ?> · ₱<?php echo e(number_format((float) $payment->amount, 2)); ?></strong>
                                <div class="text-muted fs-sm"><?php echo e($payment->payment_date->format('M j, Y g:i A')); ?> · <?php echo e($payment->payment_status); ?></div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->reference_number): ?>
                                    <div class="text-muted fs-sm">Reference: <?php echo e($payment->reference_number); ?></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->payment_receipt): ?>
                                <a href="<?php echo e(route('payments.proof', $payment->payment_id)); ?>" class="btn btn-outline btn-sm">View receipt</a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $loop->last): ?><hr class="divider"><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="text-muted mb-0">No payments recorded.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>

            <section class="card">
                <div class="card-header">Refund History</div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $booking->refunds->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $refund): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="flex-between checkout-refund-history-row">
                            <div>
                                <strong>₱<?php echo e(number_format((float) $refund->amount, 2)); ?> · <?php echo e($refund->reason); ?></strong>
                                <div class="text-muted fs-sm">
                                    <?php echo e($refund->processed_at?->format('M j, Y g:i A') ?? $refund->created_at?->format('M j, Y g:i A')); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($refund->processedBy): ?>
                                        · <?php echo e($refund->processedBy->first_name); ?> <?php echo e($refund->processedBy->last_name); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($refund->refund_reference): ?>
                                        · Reference: <?php echo e($refund->refund_reference); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    · Payment #<?php echo e($refund->payment_id); ?>

                                </div>
                            </div>
                            <span class="badge <?php echo e($refund->refund_status === 'Refunded' ? 'badge-verified' : 'badge-pending'); ?>"><?php echo e($refund->refund_status); ?></span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $loop->last): ?><hr class="divider"><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="text-muted mb-0">No refunds have been recorded.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        </div>

        <section class="card checkout-payment">
            <div class="card-header">Record Payment</div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isReadOnly): ?>
                    <p class="text-muted mb-0">Checkout is complete. Payment records are read-only.</p>
                <?php elseif($balanceDue > 0): ?>
                    <p class="form-hint">Record a payment collected by the <?php echo e($dashboardPrefix); ?>. It will be marked as verified.</p>
                    <form method="POST" action="<?php echo e(route($dashboardPrefix.'.bookings.checkout.payments', $booking->booking_id)); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
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
                            <input id="checkout-amount" name="amount" type="number" min="0.01" max="<?php echo e(number_format($balanceDue, 2, '.', '')); ?>" step="0.01" value="<?php echo e(number_format($balanceDue, 2, '.', '')); ?>" required>
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
                <?php else: ?>
                    <p class="text-success mb-0">The balance is fully paid.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($isReadOnly)): ?>
        <dialog class="checkout-dialog" id="damage-items-dialog" aria-labelledby="damage-items-dialog-title">
            <div class="checkout-dialog-header">
                <strong id="damage-items-dialog-title">Damage/Missing Items</strong>
            </div>
            <form method="POST" action="<?php echo e(route($dashboardPrefix.'.bookings.checkout.damages.store', $booking->booking_id)); ?>">
                <?php echo csrf_field(); ?>
                <div class="checkout-dialog-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->unit->damageItems->isNotEmpty()): ?>
                        <p class="text-muted fs-sm">Select the damage or missing items and set each quantity. Charges are added immediately; use + or − on the bill to adjust a quantity, and − at zero removes it from the current charges.</p> <br>
                        <div class="damage-card-list">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->unit->damageItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $damageItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <label class="damage-card">
                                    <input class="damage-card-selection" type="checkbox" name="items[<?php echo e($index); ?>][unit_damage_item_id]" value="<?php echo e($damageItem->unit_damage_item_id); ?>" <?php if((string) old('items.'.$index.'.unit_damage_item_id') === (string) $damageItem->unit_damage_item_id): echo 'checked'; endif; ?> data-damage-check>
                                    <span class="flex-between">
                                        <strong><?php echo e($damageItem->name); ?></strong>
                                        <span>₱<?php echo e(number_format((float) $damageItem->price, 2)); ?></span>
                                    </span>
                                    <span class="form-group mt-2 mb-0">
                                        <span class="text-muted fs-sm">Quantity</span>
                                        <input type="number" name="items[<?php echo e($index); ?>][quantity]" value="<?php echo e(old('items.'.$index.'.quantity', 1)); ?>" min="1" max="100" <?php if((string) old('items.'.$index.'.unit_damage_item_id') !== (string) $damageItem->unit_damage_item_id): echo 'disabled'; endif; ?> data-damage-quantity>
                                    </span>
                                </label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['items'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="form-hint text-danger mt-2"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->get('items.*'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemErrors): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $itemErrors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <p class="form-hint text-danger mt-2"><?php echo e($error); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">This unit has no damage or missing item policies.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="checkout-dialog-actions">
                    <button type="button" class="btn btn-outline" data-close-checkout-dialog>Cancel</button>
                    <button type="submit" class="btn btn-primary" <?php if($booking->unit->damageItems->isEmpty()): echo 'disabled'; endif; ?>>Submit</button>
                </div>
            </form>
        </dialog>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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

            <?php if($errors->has('items') || $errors->hasAny(['description', 'quantity', 'unit_price']) || collect($errors->keys())->contains(fn ($key) => str_starts_with($key, 'items.'))): ?>
                document.getElementById('damage-items-dialog')?.showModal();
            <?php endif; ?>
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
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/bookings/checkout.blade.php ENDPATH**/ ?>
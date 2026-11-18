<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Booking #'.e($booking->booking_id).' | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Booking #'.e($booking->booking_id).' | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> <?php echo $__env->make('partials.nav', ['activePage' => 'bookings'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php $__env->endSlot(); ?>

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
                <h1 class="mt-1">Booking #<?php echo e($booking->booking_id); ?></h1>
                <p><?php echo e($booking->unit->unit_type); ?> — <?php echo e($booking->unit->house->name); ?></p>
            </div>
            <?php
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
            ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('info')): ?>
            <div class="alert alert-info mb-3"><?php echo e(session('info')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div class="alert alert-success mb-3"><?php echo e(session('success')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('guest.booking-status', ['booking' => $booking]);

$__keyOuter = $__key ?? null;

$__key = 'guest-booking-status-'.$booking->booking_id;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-927069465-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>

        <div class="row">
            <div class="col-2">
                <div class="card mb-3">
                    <div class="card-header">Booking Details</div>
                    <div class="card-body" style="padding:0">
                        <div style="display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid var(--border)">
                            <div style="padding:12px 16px;border-right:1px solid var(--border)">
                                <div class="text-muted fs-sm">CHECK-IN</div>
                                <strong><?php echo e($booking->check_in->format('M j, Y')); ?></strong>
                            </div>
                            <div style="padding:12px 16px">
                                <div class="text-muted fs-sm">CHECK-OUT</div>
                                <strong><?php echo e($booking->check_out->format('M j, Y')); ?></strong>
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(2,1fr);border-bottom:1px solid var(--border);text-align:center">
                            <div style="padding:10px;border-right:1px solid var(--border)">
                                <div class="text-muted fs-sm">Nights</div>
                                <strong><?php echo e($nights); ?></strong>
                            </div>
                            <div style="padding:10px">
                                <div class="text-muted fs-sm">Per night</div>
                                <strong>₱<?php echo e(number_format($nightlyRate, 2)); ?></strong>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="booking-price-line">
                                <span>Stay/room charge <small class="booking-price-detail"><?php echo e($roomQuantity); ?> × <?php echo e($roomPrice); ?></small></span>
                                <strong class="booking-price-amount">₱<?php echo e(number_format(\App\Support\BookingFinancialContract::roomAmountBeforeDiscounts($booking), 2)); ?></strong>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((int) $booking->extra_guest_count > 0): ?>
                                <div class="booking-price-line">
                                    <span>Extra heads <small class="booking-price-detail"><?php echo e($booking->extra_guest_count); ?> guests × <?php echo e($nights); ?> nights × ₱<?php echo e(number_format((float) $booking->extra_guest_fee, 2)); ?> / guest / night</small></span>
                                    <strong class="booking-price-amount">₱<?php echo e(number_format((float) $booking->extra_guest_total, 2)); ?></strong>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($approvedDiscountTotal > 0): ?>
                                <div class="booking-price-line">
                                    <span>Approved Senior/PWD discounts</span>
                                    <strong class="booking-price-amount booking-price-amount--green">−₱<?php echo e(number_format($approvedDiscountTotal, 2)); ?></strong>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="booking-price-line booking-price-line--total">
                                <span>Stay and extra guest total</span>
                                <strong class="booking-price-amount">₱<?php echo e(number_format($booking->amountDueBeforeCheckout(), 2)); ?></strong>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->addons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $addon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) $addon->price * (int) $addon->quantity > 0): ?>
                                    <div class="booking-price-line">
                                        <span>Added amenities · <?php echo e($addon->name); ?> × <?php echo e($addon->quantity); ?></span>
                                        <strong class="booking-price-amount booking-price-amount--blue">₱<?php echo e(number_format((float) $addon->price * $addon->quantity, 2)); ?></strong>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->damages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $damage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) $damage->price * (int) $damage->quantity > 0): ?>
                                    <div class="booking-price-line">
                                        <span>Damage/missing items · <?php echo e($damage->name); ?> × <?php echo e($damage->quantity); ?> <small>(not included in amount due)</small></span>
                                        <strong class="booking-price-amount booking-price-amount--red">₱<?php echo e(number_format((float) $damage->price * $damage->quantity, 2)); ?></strong>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->additionalCharges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $charge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) $charge->total_amount > 0): ?>
                                    <div class="booking-price-line">
                                        <span>Damage/missing items · <?php echo e($charge->description); ?> × <?php echo e($charge->quantity); ?></span>
                                        <strong class="booking-price-amount booking-price-amount--red">₱<?php echo e(number_format((float) $charge->total_amount, 2)); ?></strong>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->damageReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $damage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) $damage->amount > 0): ?>
                                    <div class="booking-price-line">
                                        <span>
                                            Damage/missing items · <?php echo e($damage->description); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((int) $damage->quantity > 1): ?> × <?php echo e($damage->quantity); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            (<?php echo e($damage->isIncludedInFinalAmount() ? 'included in amount due' : 'not included in amount due'); ?>)
                                        </span>
                                        <strong class="booking-price-amount booking-price-amount--red">₱<?php echo e(number_format((float) $damage->amount, 2)); ?></strong>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="booking-price-line booking-price-line--total">
                                <span><?php echo e($approvedDiscountTotal > 0 ? 'Current total after approved discounts and checkout charges' : 'Estimated total before discount verification, including checkout charges'); ?></span>
                                <strong class="booking-price-amount">₱<?php echo e(number_format((float) $checkoutTotal, 2)); ?></strong>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($verifiedPaymentTotal > 0): ?>
                                <div class="booking-price-line">
                                    <span>Payments made</span>
                                    <strong class="booking-price-amount booking-price-amount--green">−₱<?php echo e(number_format((float) $verifiedPaymentTotal, 2)); ?></strong>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="booking-price-line booking-price-line--total">
                                <span><?php echo e($checkoutBalance > 0 ? 'Balance due at checkout' : 'Paid in full'); ?></span>
                                <strong class="booking-price-amount booking-price-amount--<?php echo e($checkoutBalance > 0 ? 'red' : 'green'); ?>">₱<?php echo e(number_format((float) $checkoutBalance, 2)); ?></strong>
                            </div>
                            <p class="form-hint mt-2 mb-0">Verified payments count toward your balance. Show your Senior/PWD ID at checkout for the 20% discount.</p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->discounts->isNotEmpty()): ?>
                                <hr class="divider">
                                <strong>Discount requests</strong>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->discounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $discount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="flex-between mt-1" style="gap:8px">
                                        <span><?php echo e($discount->discount_type); ?> discount for <?php echo e($discount->guest->full_name); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($discount->verification_status === \App\Support\PaymentStatus::VERIFIED): ?>
                                                · ₱<?php echo e(number_format($discount->discount_amount, 2)); ?>

                                            <?php else: ?>
                                                · physical ID to be checked at checkout
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </span>
                                        <span class="badge badge-pending"><?php echo e($discount->verification_status); ?></span>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($discount->verification_status, ['Invalid', 'Rejected'], true) && $discount->verification_note): ?>
                                            <p class="form-hint text-danger mt-1 mb-0">Reason: <?php echo e($discount->verification_note); ?></p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <p class="form-hint mt-2 mb-0">Each verified ID gives 20% off that guest’s share. One discount per guest.</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->edits->isNotEmpty()): ?>
                    <div class="card mb-3">
                        <div class="card-header">Booking Change History</div>
                        <div class="card-body">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->edits->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $edit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="fw-bold">Updated by <?php echo e($edit->editor->first_name); ?> <?php echo e($edit->editor->last_name); ?></div>
                                <div class="text-muted fs-sm"><?php echo e($edit->created_at->format('M j, Y g:i A')); ?></div>
                                <div class="fs-sm mt-1"><?php echo e($edit->old_check_in->format('M j, Y')); ?> – <?php echo e($edit->old_check_out->format('M j, Y')); ?> changed to <?php echo e($edit->new_check_in->format('M j, Y')); ?> – <?php echo e($edit->new_check_out->format('M j, Y')); ?></div>
                                <div class="fs-sm">Total: ₱<?php echo e(number_format($edit->old_total_price, 2)); ?> → ₱<?php echo e(number_format($edit->new_total_price, 2)); ?></div>
                                <p class="text-muted fs-sm mt-1 mb-0"><?php echo e($edit->edit_reason); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $loop->last): ?>
                                    <hr class="divider">
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="card">
                    <div class="card-header">My Payment Submissions</div>
                    <div class="card-body">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $booking->payments->sortByDesc('payment_date'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $submissionBadge = match ($payment->payment_status) {
                                    \App\Support\PaymentStatus::VERIFIED => 'badge-verified',
                                    \App\Support\PaymentStatus::UNDER_REVIEW => 'badge-pending',
                                    default => 'badge-unpaid',
                                };
                            ?>
                            <div class="mb-3">
                                <div class="flex-between" style="gap:10px">
                                    <strong><?php echo e($payment->payment_method); ?> · <span class="booking-price-amount--green">₱<?php echo e(number_format($payment->amount, 2)); ?></span></strong>
                                    <span class="badge <?php echo e($submissionBadge); ?>"><?php echo e($payment->payment_status); ?></span>
                                </div>
                                <div class="text-muted fs-sm mt-1"><?php echo e($payment->payment_date->format('M j, Y g:i A')); ?></div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->reference_number): ?>
                                    <div class="text-muted fs-sm">Reference: <?php echo e($payment->reference_number); ?></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->notes): ?>
                                    <p class="text-muted fs-sm mt-1 mb-0"><?php echo e($payment->notes); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->payment_receipt): ?>
                                    <button type="button" class="btn btn-outline btn-sm mt-1" data-proof-viewer data-proof-url="<?php echo e(route('payments.proof', $payment->payment_id)); ?>" data-proof-download-url="<?php echo e(route('payments.proof', [$payment->payment_id, 'download' => 1])); ?>" data-proof-type="<?php echo e(strtolower(pathinfo($payment->payment_receipt, PATHINFO_EXTENSION))); ?>" data-proof-reference="<?php echo e($payment->reference_number ?: '—'); ?>" data-proof-amount="₱<?php echo e(number_format((float) $payment->amount, 2)); ?>">View submitted proof</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $loop->last): ?>
                                <hr class="divider">
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <p class="text-muted mb-0">You have not submitted a payment yet.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-fixed-300">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\App\Support\BookingStatus::isTerminal($booking->status) && $booking->receipt): ?>
                    <div class="booking-breakdown-action mb-3">
                        <a href="<?php echo e(route('guest.bookings.receipt', $booking->booking_id)); ?>" class="btn btn-primary">View Booking Breakdown</a>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="booking-details-actions mb-3" style="display:flex;flex-direction:column;gap:10px">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($booking->status, [\App\Support\BookingStatus::CONFIRMED, \App\Support\BookingStatus::CHECKED_IN], true)): ?>
                        <div data-booking-ticket-action>
                            <a href="<?php echo e(route('guest.bookings.ticket', $booking->booking_id)); ?>" class="btn btn-success btn-block">Download Ticket</a>
                            <p class="form-hint mt-1 mb-0">Download and show this ticket on check-in day.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->status === \App\Support\BookingStatus::ACCEPTED && ! $hasUnderReviewPayment && $verifiedPaymentTotal < $checkoutTotal): ?>
                        <div class="guest-payment-action">
                            <a href="<?php echo e(route('guest.bookings.payment', $booking->booking_id)); ?>" class="btn btn-primary btn-block">Pay Now</a>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->status === \App\Support\BookingStatus::PENDING || (in_array($booking->status, [\App\Support\BookingStatus::ACCEPTED, \App\Support\BookingStatus::CONFIRMED], true) && $booking->check_in->toDateString() > today()->toDateString())): ?>
                        <details class="policy-box guest-booking-cancel" <?php if($errors->has('reason')): ?> open <?php endif; ?>>
                            <summary class="btn btn-danger btn-block">Cancel this booking</summary>
                            <form method="POST" action="<?php echo e(route('guest.bookings.cancel', $booking->booking_id)); ?>" class="mt-2">
                                <?php echo csrf_field(); ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasUnderReviewPayment || $verifiedPaymentTotal > 0): ?>
                                    <p class="form-hint">Ask staff about refunds.</p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <label class="required" for="guest-cancellation-reason">Why are you cancelling?</label>
                                <textarea id="guest-cancellation-reason" name="reason" rows="2" maxlength="500" required><?php echo e(old('reason')); ?></textarea>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <button type="submit" class="btn btn-danger btn-block mt-2">Confirm cancellation</button>
                            </form>
                        </details>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->refunds->isNotEmpty()): ?>
                    <div class="card mb-3">
                        <div class="card-header">Refund Status</div>
                        <div class="card-body">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->refunds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $refund): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="flex-between mb-2">
                                    <strong class="booking-price-amount--orange">₱<?php echo e(number_format($refund->amount, 2)); ?></strong>
                                    <span class="badge <?php echo e(strtolower($refund->refund_status) === 'refunded' ? 'badge-verified' : 'badge-pending'); ?>"><?php echo e($refund->refund_status); ?></span>
                                </div>
                                <p class="text-muted fs-sm"><?php echo e($refund->reason); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            </div>
        </div>
    </div>
    <?php echo $__env->make('partials.payment-proof-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/guest/bookings/show.blade.php ENDPATH**/ ?>
<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Booking Breakdown '.e($receipt->receipt_number).' | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Booking Breakdown '.e($receipt->receipt_number).' | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> <?php echo $__env->make('partials.nav', ['activePage' => 'bookings'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php $__env->endSlot(); ?>

    <div class="container-sm" style="margin-top:24px">
        <div class="flex-between mb-3">
            <a href="<?php echo e(route('guest.bookings.show', $booking->booking_id)); ?>" class="text-muted fs-sm"><i class="fa fa-arrow-left"></i> Back to booking</a>
            <a href="<?php echo e(route('guest.bookings.receipt.pdf', $booking->booking_id)); ?>" class="btn btn-outline btn-sm"><i class="fa fa-download"></i> Download PDF</a>
        </div>
        <article class="card receipt-print">
            <header style="background:var(--primary-lt);padding:28px;text-align:center;color:#fff">
                <h1 style="color:#fff">MCTBS</h1>
                <p>Motal and Conteros Transient Booking System</p>
            </header>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="text-muted fs-sm">Booking Breakdown</div>
                    <h2><?php echo e($receipt->receipt_number); ?></h2>
                    <span class="badge badge-completed"><?php echo e(\App\Support\BookingStatus::canonical($booking->status)); ?></span>
                </div>

                <div class="row mb-3">
                    <section class="col">
                        <h3 class="fs-sm text-muted mb-2">Guest information</h3>
                        <strong><?php echo e($booking->displayGuestName()); ?></strong>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->displayGuestEmail()): ?><div class="text-muted fs-sm"><?php echo e($booking->displayGuestEmail()); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->displayGuestPhone()): ?><div class="text-muted fs-sm"><?php echo e($booking->displayGuestPhone()); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </section>
                    <section class="col">
                        <h3 class="fs-sm text-muted mb-2">Property</h3>
                        <strong><?php echo e($booking->unit->unit_type); ?></strong>
                        <div><?php echo e($booking->unit->house->name); ?></div>
                        <div class="text-muted fs-sm"><?php echo e($booking->unit->house->address); ?>, <?php echo e($booking->unit->house->city); ?></div>
                    </section>
                </div>

                <hr class="divider">
                <div class="row mb-3">
                    <section class="col">
                        <h3 class="fs-sm text-muted mb-2">Stay details</h3>
                        <div class="flex-between mb-1"><span>Booking ID</span><strong>#<?php echo e($booking->booking_id); ?></strong></div>
                        <div class="flex-between mb-1"><span>Check-in</span><strong><?php echo e($booking->check_in->format('M j, Y')); ?></strong></div>
                        <div class="flex-between mb-1"><span>Check-out</span><strong><?php echo e($booking->check_out->format('M j, Y')); ?></strong></div>
                        <div class="flex-between mb-1"><span>Nights</span><strong><?php echo e($nights); ?></strong></div>
                        <div class="flex-between mb-1"><span>Adult paying guests</span><strong><?php echo e($booking->adult_count); ?></strong></div>
                        <div class="flex-between mb-1"><span>Free children</span><strong><?php echo e($booking->child_count); ?></strong></div>
                    </section>
                    <section class="col">
                        <h3 class="fs-sm text-muted mb-2">Payment summary</h3>
                        <?php
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
                        <div class="booking-price-line">
                            <span>Stay/room charge <small class="booking-price-detail"><?php echo e($roomQuantity); ?> × <?php echo e($roomPrice); ?></small></span>
                            <strong class="booking-price-amount">₱<?php echo e(number_format($financialSummary['roomCharges'], 2)); ?></strong>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($financialSummary['extraGuestCharges'] > 0): ?>
                            <div class="booking-price-line">
                                <span>Extra guest charges <small class="booking-price-detail"><?php echo e($booking->extra_guest_count); ?> guests × <?php echo e($nights); ?> nights × ₱<?php echo e(number_format((float) $booking->extra_guest_fee, 2)); ?> / guest / night · included in accommodation</small></span>
                                <strong class="booking-price-amount">₱<?php echo e(number_format($financialSummary['extraGuestCharges'], 2)); ?></strong>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($financialSummary['approvedDiscounts'] > 0): ?>
                            <div class="booking-price-line">
                                <span>Eligible verified discounts</span>
                                <strong class="booking-price-amount booking-price-amount--green">−₱<?php echo e(number_format($financialSummary['approvedDiscounts'], 2)); ?></strong>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="booking-price-line booking-price-line--total">
                            <span>Accommodation after discounts</span>
                            <strong class="booking-price-amount">₱<?php echo e(number_format($financialSummary['accommodationAfterDiscounts'], 2)); ?></strong>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($financialSummary['amenities'] > 0): ?>
                            <div class="booking-price-line">
                                <span>Approved amenities and add-ons</span>
                                <strong class="booking-price-amount booking-price-amount--blue">₱<?php echo e(number_format($financialSummary['amenities'], 2)); ?></strong>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($financialSummary['additionalCharges'] > 0): ?>
                            <div class="booking-price-line">
                                <span>Additional charges included by the financial contract</span>
                                <strong class="booking-price-amount booking-price-amount--red">₱<?php echo e(number_format($financialSummary['additionalCharges'], 2)); ?></strong>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($financialSummary['approvedDamageCharges'] > 0): ?>
                            <div class="booking-price-line">
                                <span>Damage charges</span>
                                <strong class="booking-price-amount booking-price-amount--red">₱<?php echo e(number_format($financialSummary['approvedDamageCharges'], 2)); ?></strong>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="booking-price-line booking-price-line--total">
                            <span>Final amount due</span>
                            <strong class="booking-price-amount">₱<?php echo e(number_format($financialSummary['finalAmountDue'], 2)); ?></strong>
                        </div>
                        <div class="booking-price-line">
                            <span>Payments made (verified only)</span>
                            <strong class="booking-price-amount booking-price-amount--green">₱<?php echo e(number_format($financialSummary['verifiedPayments'], 2)); ?></strong>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($financialSummary['completedRefunds'] > 0): ?>
                            <div class="booking-price-line">
                                <span>Completed refunds</span>
                                <strong class="booking-price-amount booking-price-amount--orange">₱<?php echo e(number_format($financialSummary['completedRefunds'], 2)); ?></strong>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="booking-price-line booking-price-line--total">
                            <span><?php echo e($financialSummary['outstandingBalance'] > 0 ? 'Outstanding balance' : 'Paid in full'); ?></span>
                            <strong class="booking-price-amount booking-price-amount--<?php echo e($financialSummary['outstandingBalance'] > 0 ? 'red' : 'green'); ?>">₱<?php echo e(number_format($financialSummary['outstandingBalance'], 2)); ?></strong>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($financialSummary['refundableOverpayment'] > 0): ?>
                            <div class="booking-price-line booking-price-line--total">
                                <span>Refundable overpayment</span>
                                <strong class="booking-price-amount booking-price-amount--orange">₱<?php echo e(number_format($financialSummary['refundableOverpayment'], 2)); ?></strong>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </section>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->refunds->isNotEmpty()): ?>
                    <hr class="divider">
                    <h3 class="fs-sm text-muted mb-2">Refund status</h3>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->refunds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $refund): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="flex-between mb-2" style="gap:12px">
                            <span>
                                <?php echo e($refund->reason); ?>

                                <small class="booking-price-detail">
                                    <?php echo e($refund->processed_at?->format('M j, Y g:i A') ?? $refund->created_at?->format('M j, Y g:i A') ?? 'Date unavailable'); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($refund->refund_reference): ?> · Reference: <?php echo e($refund->refund_reference); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    · Payment #<?php echo e($refund->payment_id); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($refund->payment?->reference_number): ?> · Payment reference: <?php echo e($refund->payment->reference_number); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </small>
                            </span>
                            <span>₱<?php echo e(number_format($refund->amount, 2)); ?> · <?php echo e($refund->refund_status); ?></span>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->discounts->isNotEmpty()): ?>
                    <hr class="divider">
                    <h3 class="fs-sm text-muted mb-2">Senior / PWD verification</h3>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->discounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $discount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="flex-between mb-1">
                            <span><?php echo e($discount->guest->full_name); ?> · <?php echo e($discount->discount_type); ?><?php echo e($discount->verification_status === 'Verified' ? ' · ₱'.number_format($discount->discount_amount, 2) : ''); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($discount->guest->roster_status !== \App\Models\BookingGuest::ROSTER_ACTIVE): ?>
                                    <small class="booking-price-detail">Removed guest; discount excluded</small>
                                <?php elseif($discount->verification_status !== 'Verified'): ?>
                                    <small class="booking-price-detail">Not applied to final amount due</small>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>
                            <span class="badge <?php echo e($discount->verification_status === 'Verified' && $discount->guest->roster_status === \App\Models\BookingGuest::ROSTER_ACTIVE ? 'badge-verified' : 'badge-unpaid'); ?>"><?php echo e($discount->verification_status); ?></span>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <hr class="divider">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->addons->isNotEmpty() || $booking->damages->isNotEmpty() || $booking->additionalCharges->isNotEmpty() || $booking->damageReports->isNotEmpty()): ?>
                    <h3 class="fs-sm text-muted mb-2">Additional / damage charges</h3>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->addons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $addon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) $addon->price * (int) $addon->quantity > 0): ?>
                            <div class="booking-price-line">
                                <span>Added amenities · <?php echo e($addon->name); ?> × <?php echo e($addon->quantity); ?> <small>(included in final amount due)</small></span>
                                <strong class="booking-price-amount booking-price-amount--blue">₱<?php echo e(number_format((float) $addon->price * $addon->quantity, 2)); ?></strong>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->damages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $damage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) $damage->price * (int) $damage->quantity > 0): ?>
                            <div class="booking-price-line">
                                <span>Damage/missing items · <?php echo e($damage->name); ?> · Qty <?php echo e($damage->quantity); ?> · ₱<?php echo e(number_format((float) $damage->price, 2)); ?> / item <small>(not included in amount due)</small></span>
                                <strong class="booking-price-amount booking-price-amount--red">₱<?php echo e(number_format((float) $damage->price * $damage->quantity, 2)); ?></strong>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->additionalCharges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $charge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) $charge->total_amount > 0): ?>
                            <div class="booking-price-line">
                                <span>Additional charge · <?php echo e($charge->description); ?> × <?php echo e($charge->quantity); ?> <small>(included by financial contract)</small></span>
                                <strong class="booking-price-amount booking-price-amount--red">₱<?php echo e(number_format((float) $charge->total_amount, 2)); ?></strong>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->damageReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $damage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) $damage->amount > 0): ?>
                            <div class="booking-price-line">
                                <span>
                                    Damage/missing items · <?php echo e($damage->description); ?> · Qty <?php echo e($damage->quantity); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($damage->unit_price !== null): ?>
                                        · ₱<?php echo e(number_format((float) $damage->unit_price, 2)); ?> / item
                                    <?php else: ?>
                                        · Unit price unavailable
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    (<?php echo e($damage->isIncludedInFinalAmount() ? 'included in amount due' : 'not included in amount due'); ?>)
                                </span>
                                <strong class="booking-price-amount booking-price-amount--red">₱<?php echo e(number_format($damage->calculatedChargeTotal(), 2)); ?></strong>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <hr class="divider">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <h3 class="fs-sm text-muted mb-2">Payment transactions</h3>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Date</th><th>Method</th><th>Reference</th><th>Amount</th></tr></thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $booking->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td><?php echo e($payment->payment_date?->format('M j, Y g:i A') ?? 'Date unavailable'); ?></td>
                                    <td><?php echo e($payment->payment_method); ?> · <?php echo e($payment->payment_status); ?></td>
                                    <td><?php echo e($payment->reference_number ?: '—'); ?></td>
                                    <td>₱<?php echo e(number_format($payment->amount, 2)); ?></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="4" class="text-muted">No payments recorded.</td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center text-muted fs-sm mt-3">Booking Breakdown created <?php echo e($receipt->issued_at->format('F j, Y g:i A')); ?>. Thank you for choosing MCTBS.</div>
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
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/guest/bookings/receipt.blade.php ENDPATH**/ ?>
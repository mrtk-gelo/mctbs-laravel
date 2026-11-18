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

     <?php $__env->slot('nav', null, []); ?> 
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dashboardPrefix === 'owner'): ?>
            <?php echo $__env->make('partials.owner-nav', ['activePage' => 'bookings'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('partials.staff-nav', ['activePage' => 'bookings'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="container" style="margin-top:24px">
        <div class="page-header-row page-header">
            <div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->status === \App\Support\BookingStatus::CHECKED_IN): ?>
                    <a href="<?php echo e(route($dashboardPrefix.'.guests.check-in', $booking->booking_id)); ?>" class="text-muted fs-sm"><i class="fa fa-arrow-left"></i> Back to Guest Check-in</a>
                <?php else: ?>
                    <a href="<?php echo e(route($dashboardPrefix.'.bookings.index')); ?>" class="text-muted fs-sm"><i class="fa fa-arrow-left"></i> Back to bookings</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <h1 class="mt-1">Booking #<?php echo e($booking->booking_id); ?></h1>
                <p><?php echo e($booking->unit->unit_type); ?> — <?php echo e($booking->unit->house->name); ?></p>
            </div>
            <div class="flex-center gap-1">
                <?php
                    $statusBadge = match ($booking->status) {
                        \App\Support\BookingStatus::PENDING => 'badge-pending',
                        \App\Support\BookingStatus::ACCEPTED, \App\Support\BookingStatus::CONFIRMED => 'badge-accepted',
                        \App\Support\BookingStatus::REJECTED => 'badge-rejected',
                        default => \App\Support\BookingStatus::isTerminal($booking->status)
                            ? 'badge-completed'
                            : 'badge-cancelled',
                    };
                    $statusLabel = \App\Support\BookingStatus::canonical($booking->status);
                    $hasPaymentUnderReview = $booking->payments->contains('payment_status', \App\Support\PaymentStatus::UNDER_REVIEW);
                    $bookingTotalDue = $booking->amountDueAtCheckout();
                    $bookingBalance = $booking->outstandingBalance();
                    $verifiedBadge = $booking->status === \App\Support\BookingStatus::ACCEPTED
                        ? ($hasPaymentUnderReview ? 'badge-pending' : 'badge-unpaid')
                        : ($bookingBalance === 0.0
                            ? 'badge-paid'
                            : ($verifiedPaymentTotal > 0 ? 'badge-downpaid' : 'badge-unpaid'));
                    $paymentLabel = $booking->status === \App\Support\BookingStatus::ACCEPTED
                        ? ($hasPaymentUnderReview ? 'Downpayment under review' : 'Downpayment due')
                        : ($bookingBalance === 0.0
                            ? 'Paid'
                            : ($verifiedPaymentTotal > 0 ? 'Partially paid' : 'Due at checkout'));
                ?>
                <?php echo $__env->make('bookings.partials.status-indicator', [
                    'booking' => $booking,
                    'statusBadge' => $statusBadge,
                    'statusLabel' => $statusLabel,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <span class="badge <?php echo e($verifiedBadge); ?>"><?php echo e($paymentLabel); ?></span>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div class="alert alert-success mb-3" role="status"><?php echo e(session('success')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isPaymentOverdue): ?>
            <div class="alert alert-warning mb-3" role="status">
                The payment deadline has passed without a qualifying downpayment. This booking remains on hold until Owner/Staff resolves it.
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->status === \App\Support\BookingStatus::REJECTED && $booking->rejection_reason): ?>
            <div class="alert alert-danger mb-3" role="status">
                <strong>Rejection reason:</strong> <?php echo e($booking->rejection_reason); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php
            $nights = max(1, $booking->check_in->diffInDays($booking->check_out));
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
            $approvedDiscountTotal = \App\Support\BookingFinancialContract::approvedDiscounts($booking);
        ?>
        <div class="booking-details-layout">
            <section class="card booking-details-summary">
                <div class="card-header">Booking Summary</div>
                <div class="card-body">
                    <div class="fw-bold"><?php echo e($booking->unit->unit_type); ?></div>
                    <div class="text-muted fs-sm mb-3"><?php echo e($booking->unit->house->name); ?> — <?php echo e($booking->unit->house->city); ?></div>
                    <div class="flex-between mb-2"><span>Check-in</span><strong><?php echo e($booking->check_in->format('M j, Y')); ?></strong></div>
                    <div class="flex-between mb-2"><span>Check-out</span><strong><?php echo e($booking->check_out->format('M j, Y')); ?></strong></div>
                    <div class="flex-between mb-2"><span>Nights</span><strong><?php echo e($nights); ?></strong></div>
                    <div class="flex-between mb-2"><span>Guests</span><strong><?php echo e($booking->adult_count + $booking->child_count); ?></strong></div>
                    <hr class="divider">
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
                            <span>Senior/PWD discount</span>
                            <strong class="booking-price-amount booking-price-amount--green">−₱<?php echo e(number_format($approvedDiscountTotal, 2)); ?></strong>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="booking-price-line booking-price-line--total">
                        <span>Stay total</span>
                        <strong class="booking-price-amount">₱<?php echo e(number_format($booking->amountDueBeforeCheckout(), 2)); ?></strong>
                    </div>
                </div>
            </section>

            <section class="card booking-details-payments">
                <div class="card-header">Payments</div>
                <div class="card-body">
                    <div class="booking-price-line">
                        <span>Payments made <small class="booking-price-detail">Verified total / amount due at checkout</small></span>
                        <strong class="booking-price-amount booking-price-amount--green">₱<?php echo e(number_format((float) $verifiedPaymentTotal, 2)); ?> / ₱<?php echo e(number_format((float) $bookingTotalDue, 2)); ?></strong>
                    </div>
                    <div class="booking-price-line booking-price-line--total">
                        <span>Balance to pay</span>
                        <strong class="booking-price-amount booking-price-amount--<?php echo e($bookingBalance > 0 ? 'red' : 'green'); ?>">₱<?php echo e(number_format((float) $bookingBalance, 2)); ?></strong>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $booking->payments->sortByDesc('payment_date'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $paymentBadge = match ($payment->payment_status) {
                                \App\Support\PaymentStatus::VERIFIED => 'badge-verified',
                                \App\Support\PaymentStatus::UNDER_REVIEW => 'badge-pending',
                                default => 'badge-unpaid',
                            };
                        ?>
                        <div class="card mb-2">
                            <div class="card-body">
                                <div class="flex-between" style="gap:10px">
                                    <strong><?php echo e($payment->payment_method); ?> · <span class="booking-price-amount--green">₱<?php echo e(number_format($payment->amount, 2)); ?></span></strong>
                                    <span class="badge <?php echo e($paymentBadge); ?>"><?php echo e($payment->payment_status); ?></span>
                                </div>
                                <div class="text-muted fs-sm mt-1"><?php echo e($payment->payment_date->format('M j, Y g:i A')); ?></div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->reference_number): ?>
                                    <div class="text-muted fs-sm">Reference: <?php echo e($payment->reference_number); ?></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->payment_receipt): ?>
                                    <button type="button" class="btn btn-outline btn-sm mt-2" data-proof-viewer data-proof-url="<?php echo e(route('payments.proof', $payment->payment_id)); ?>" data-proof-download-url="<?php echo e(route('payments.proof', [$payment->payment_id, 'download' => 1])); ?>" data-proof-type="<?php echo e(strtolower(pathinfo($payment->payment_receipt, PATHINFO_EXTENSION))); ?>" data-proof-reference="<?php echo e($payment->reference_number ?: '—'); ?>" data-proof-amount="₱<?php echo e(number_format((float) $payment->amount, 2)); ?>">View Receipt</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->notes): ?>
                                    <p class="text-muted fs-sm mt-2 mb-0"><?php echo e($payment->notes); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->payment_status === $underReviewStatus && in_array($booking->status, [\App\Support\BookingStatus::ACCEPTED, \App\Support\BookingStatus::CONFIRMED], true)): ?>
                                    <form method="POST" action="<?php echo e(route($dashboardPrefix.'.payments.review', $payment->payment_id)); ?>" class="mt-2">
                                        <?php echo csrf_field(); ?>
                                        <div class="btn-group">
                                            <button type="submit" name="status" value="<?php echo e(\App\Support\PaymentStatus::VERIFIED); ?>" class="btn btn-success">Verify</button>
                                            <button type="button" class="btn btn-danger" data-show-payment-reason>Reject</button>
                                        </div>
                                        <div class="form-group mt-2" data-payment-reason-panel <?php if(! $errors->has('notes')): ?> hidden <?php endif; ?>>
                                            <label class="required" for="payment-notes-<?php echo e($payment->payment_id); ?>">Why can’t this payment be accepted?</label>
                                            <textarea id="payment-notes-<?php echo e($payment->payment_id); ?>" name="notes" rows="2" maxlength="500" data-payment-review-note><?php echo e(old('notes')); ?></textarea>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <div class="btn-group mt-2">
                                                <button type="submit" name="status" value="<?php echo e(\App\Support\PaymentStatus::INCORRECT); ?>" class="btn btn-danger">Reject payment</button>
                                                <button type="button" class="btn btn-outline" data-hide-payment-reason>Go back</button>
                                            </div>
                                        </div>
                                    </form>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="text-muted mb-0">No payments have been submitted.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>

            <div class="booking-details-side">
                <section class="card booking-details-guest">
                    <div class="card-header">Guest Info</div>
                    <div class="card-body">
                        <div class="fw-bold"><?php echo e($booking->displayGuestName()); ?></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->displayGuestEmail()): ?>
                            <div class="text-muted fs-sm"><?php echo e($booking->displayGuestEmail()); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->displayGuestPhone()): ?>
                            <div class="text-muted fs-sm"><?php echo e($booking->displayGuestPhone()); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </section>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($booking->status === \App\Support\BookingStatus::CHECKED_IN)): ?>
                    <section class="card booking-details-actions">
                        <div class="card-header">Actions</div>
                        <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->status === \App\Support\BookingStatus::PENDING): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->has_active_hold): ?>
                                <p class="form-hint">Another booking has reserved overlapping dates.</p>
                            <?php elseif($booking->queue_position > 1): ?>
                                <p class="form-hint">This is queue position <?php echo e($booking->queue_position); ?>. Accept the earliest overlapping request first.</p>
                            <?php else: ?>
                                <form method="POST" action="<?php echo e(route($dashboardPrefix.'.bookings.accept', $booking->booking_id)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-success btn-block">Accept Booking Request</button>
                                </form>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <button type="button" class="btn btn-danger btn-block" data-modal-open="reject-booking-modal">Reject Booking Request</button>
                            <button type="button" class="btn btn-outline btn-block" data-modal-open="cancel-booking-modal">Cancel Booking</button>
                        <?php elseif(in_array($booking->status, [\App\Support\BookingStatus::ACCEPTED, \App\Support\BookingStatus::CONFIRMED], true)): ?>
                            <button type="button" class="btn btn-danger btn-block" data-modal-open="cancel-booking-modal">Cancel Booking</button>
                        <?php else: ?>
                            <p class="text-muted mb-0">No booking actions are available for this status.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->review || $booking->legacyFeedback->isNotEmpty()): ?>
            <section class="card mt-3">
                <div class="card-header">Guest Feedback</div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->review): ?>
                        <article>
                            <strong>Current review · <?php echo e($booking->review->displayGuestName()); ?></strong>
                            <div class="text-muted fs-sm"><?php echo e($booking->review->created_at->format('M j, Y')); ?> · <?php echo e($booking->review->rating); ?> out of 5</div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->review->comment): ?>
                                <p class="mb-0 mt-2"><?php echo e($booking->review->comment); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </article>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->legacyFeedback; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feedback): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article <?php if($booking->review || ! $loop->first): ?> class="mt-3" <?php endif; ?>>
                            <strong>Historical feedback · <?php echo e(trim(($feedback->user?->first_name ?? '').' '.($feedback->user?->last_name ?? '')) ?: $booking->displayGuestName()); ?></strong>
                            <div class="text-muted fs-sm"><?php echo e($feedback->created_at?->format('M j, Y')); ?> · <?php echo e($feedback->rating); ?> out of 5 · <?php echo e($feedback->status); ?></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($feedback->review): ?>
                                <p class="mb-0 mt-2"><?php echo e($feedback->review); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->status === \App\Support\BookingStatus::PENDING): ?>
        <div id="reject-booking-modal" class="modal-overlay" aria-hidden="true">
            <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="reject-modal-title">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)">
                    <strong id="reject-modal-title">Reject Booking Request</strong>
                    <button type="button" class="btn btn-outline btn-sm" data-modal-close aria-label="Close">Close</button>
                </div>
                <div style="padding:20px">
                    <form method="POST" action="<?php echo e(route($dashboardPrefix.'.bookings.reject', $booking->booking_id)); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="booking_action" value="reject">
                        <div class="form-group">
                            <label class="required" for="rejection-reason">Why are you rejecting this booking?</label>
                            <textarea id="rejection-reason" name="reason" rows="3" maxlength="500" required><?php echo e(old('reason')); ?></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-danger">Reject Booking Request</button>
                            <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($booking->status, [\App\Support\BookingStatus::PENDING, \App\Support\BookingStatus::ACCEPTED, \App\Support\BookingStatus::CONFIRMED], true)): ?>
        <div id="cancel-booking-modal" class="modal-overlay" aria-hidden="true">
            <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="cancel-modal-title">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)">
                    <strong id="cancel-modal-title">Cancel Booking</strong>
                    <button type="button" class="btn btn-outline btn-sm" data-modal-close aria-label="Close">Close</button>
                </div>
                <div style="padding:20px">
                    <div class="policy-box">The Guest will be notified and the booking hold will be released.</div>
                    <form method="POST" action="<?php echo e(route($dashboardPrefix.'.bookings.cancel', $booking->booking_id)); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="booking_action" value="cancel">
                        <div class="form-group">
                            <label class="required" for="cancellation-reason">Why are you cancelling this booking?</label>
                            <textarea id="cancellation-reason" name="reason" rows="3" maxlength="500" required><?php echo e(old('reason')); ?></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-danger">Confirm Cancel</button>
                            <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <script>
        document.querySelectorAll('[data-modal-open]').forEach((button) => {
            button.addEventListener('click', () => {
                const modal = document.getElementById(button.dataset.modalOpen);
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
            });
        });
        document.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => {
                const modal = button.closest('.modal-overlay');
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
            });
        });
        document.querySelectorAll('.modal-overlay').forEach((modal) => {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.classList.remove('open');
                    modal.setAttribute('aria-hidden', 'true');
                }
            });
        });
        document.querySelectorAll('[data-payment-review-note]').forEach((notes) => {
            const form = notes.closest('form');
            const panel = form.querySelector('[data-payment-reason-panel]');
            const rejectButton = form.querySelector('[data-show-payment-reason]');
            const backButton = form.querySelector('[data-hide-payment-reason]');

            rejectButton.addEventListener('click', () => {
                panel.hidden = false;
                notes.required = true;
                notes.focus();
            });
            backButton.addEventListener('click', () => {
                panel.hidden = true;
                notes.required = false;
                notes.value = '';
            });
        });
        <?php if($errors->has('reason')): ?>
            const reasonModalId = <?php echo json_encode(old('booking_action') === 'cancel' ? 'cancel-booking-modal' : 'reject-booking-modal', 15, 512) ?>;
            document.getElementById(reasonModalId)?.classList.add('open');
            document.getElementById(reasonModalId)?.setAttribute('aria-hidden', 'false');
        <?php endif; ?>
    </script>
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
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/bookings/show.blade.php ENDPATH**/ ?>
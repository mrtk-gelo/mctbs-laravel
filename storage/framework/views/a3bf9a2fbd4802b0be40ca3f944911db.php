<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Guest Check-in | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Guest Check-in | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> 
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dashboardPrefix === 'staff'): ?>
            <?php echo $__env->make('partials.staff-nav', ['activePage' => 'dashboard'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('partials.owner-nav', ['activePage' => 'dashboard'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php $__env->endSlot(); ?>

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
        <a href="<?php echo e(route($dashboardPrefix.'.dashboard')); ?>" class="text-muted fs-sm"><i class="fa fa-arrow-left"></i> Back to dashboard</a>
        <div class="page-header-row page-header mt-2">
            <div>
                <h1>Guest Check-in</h1>
                <p><?php echo e($booking->displayGuestName()); ?> · <?php echo e($booking->unit->unit_type); ?></p>
            </div>
            <span class="badge badge-accepted"><?php echo e($booking->status); ?></span>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div class="alert alert-success mb-3" role="status"><?php echo e(session('success')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('feedbackUrl')): ?>
            <div class="alert alert-info mb-3" role="status">
                Feedback link:
                <a href="<?php echo e(session('feedbackUrl')); ?>" target="_blank" rel="noopener"><?php echo e(session('feedbackUrl')); ?></a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="alert alert-danger mb-3" role="alert"><?php echo e($errors->first()); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="guest-checkin-layout">
            <section class="card guest-checkin-stay">
                <div class="card-header">
                    <span>Stay Details</span>
                    <span class="guest-checkin-reference">
                        <span class="guest-checkin-reference-label">Booking reference</span>
                        <strong class="guest-checkin-reference-value"><?php echo e($booking->booking_reference ?? 'Booking #'.$booking->booking_id); ?></strong>
                    </span>
                </div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canUpdateCheckInHeadcount): ?>
                        <form method="POST" action="<?php echo e(route($dashboardPrefix.'.guests.headcount.update', $booking->booking_id)); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="guest-checkin-detail-grid">
                    <?php else: ?>
                        <div class="guest-checkin-detail-grid">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="guest-checkin-detail">
                            <span class="guest-checkin-detail-label">Guest</span>
                            <strong class="guest-checkin-detail-value"><?php echo e($booking->displayGuestName()); ?></strong>
                        </div>
                        <div class="guest-checkin-detail">
                            <span class="guest-checkin-detail-label">Unit</span>
                            <strong class="guest-checkin-detail-value"><?php echo e($booking->unit->unit_type); ?> · <?php echo e($booking->unit->house->name); ?></strong>
                        </div>
                        <div class="guest-checkin-headcount-row">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canUpdateCheckInHeadcount): ?>
                                <div class="form-group guest-checkin-headcount-field">
                                    <label for="adult-count">Adult Paying Guests</label>
                                    <input id="adult-count" name="adult_count" type="number" min="1" max="<?php echo e($booking->unit->maximum_capacity ?? $booking->unit->capacity); ?>" step="1" value="<?php echo e(old('adult_count', $booking->adult_count)); ?>" required>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['adult_count'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="form-group guest-checkin-headcount-field">
                                    <label for="free-child-count">Free Children</label>
                                    <input id="free-child-count" name="child_count" type="number" min="0" max="<?php echo e($booking->unit->child_free_through_age === null ? 0 : ($booking->unit->maximum_capacity ?? $booking->unit->capacity)); ?>" step="1" value="<?php echo e(old('child_count', $booking->child_count)); ?>" required>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['child_count'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="guest-checkin-detail guest-checkin-detail-reference">
                                    <span class="guest-checkin-detail-label">Guest Headcount</span>
                                    <span class="guest-checkin-detail-value">Compare the displayed counts with guests present. Save corrections before completing check-in.</span>
                                    <button type="submit" class="btn btn-outline">Save corrected headcount</button>
                                </div>
                            <?php else: ?>
                                <div class="guest-checkin-detail">
                                    <span class="guest-checkin-detail-label">Adult Paying Guests</span>
                                    <strong class="guest-checkin-detail-value"><?php echo e($booking->adult_count); ?></strong>
                                </div>
                                <div class="guest-checkin-detail">
                                    <span class="guest-checkin-detail-label">Free Children</span>
                                    <strong class="guest-checkin-detail-value"><?php echo e($booking->child_count); ?></strong>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="guest-checkin-detail guest-checkin-detail-date">
                            <span class="guest-checkin-detail-label">Check-in</span>
                            <strong class="guest-checkin-detail-value"><?php echo e($booking->check_in->format('M j, Y')); ?></strong>
                        </div>
                        <div class="guest-checkin-detail guest-checkin-detail-date">
                            <span class="guest-checkin-detail-label">Check-out</span>
                            <strong class="guest-checkin-detail-value"><?php echo e($booking->check_out->format('M j, Y')); ?></strong>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canUpdateCheckInHeadcount): ?>
                            </div>
                        </form>
                    <?php else: ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>

            <section class="card guest-checkin-actions">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->status === \App\Support\BookingStatus::CHECKED_IN): ?>
                    <div class="card-header">Actions</div>
                    <div class="card-body">
                        <div class="guest-checkin-action-grid">
                            <button type="button" class="btn btn-outline" data-open-checkin-dialog="refund-dialog">Refund</button>
                            <a href="<?php echo e(route($dashboardPrefix.'.bookings.edit', $booking->booking_id)); ?>" class="btn btn-outline">Edit Booking</a>
                            <button type="button" class="btn btn-outline" data-open-checkin-dialog="amenities-dialog">Add Amenities</button>
                            <a href="<?php echo e(route($dashboardPrefix.'.bookings.show', $booking->booking_id)); ?>" class="btn btn-outline">View Booking Details</a>
                            <form method="POST" action="<?php echo e(route($dashboardPrefix.'.guests.feedback.send', $booking->booking_id)); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-outline">Send Feedback</button>
                            </form>
                            <a href="<?php echo e(route($dashboardPrefix.'.bookings.checkout', $booking->booking_id)); ?>" class="btn btn-primary">Check-out</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card-header">Complete Booking Check-in</div>
                    <div class="card-body">
                        <p class="text-muted">Verify the guest headcount and complete check-in when the declared counts are correct.</p> <br>
                        <form method="POST" action="<?php echo e(route($dashboardPrefix.'.bookings.check-in', $booking->booking_id)); ?>">
                            <?php echo csrf_field(); ?>
                            <label class="form-group">
                                <span class="guest-checkin-detail-value">I verified the ticket’s booking reference and confirmed the paying guest and free-child counts match the guests present.</span>
                                <span class="flex-center gap-1">
                                    <input type="checkbox" name="guest_details_confirmed" value="1" required <?php if(old('guest_details_confirmed')): echo 'checked'; endif; ?>>
                                    <span>Confirm booking details</span>
                                </span>
                            </label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['guest_details_confirmed'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <button type="submit" class="btn btn-primary">Confirm Details &amp; Complete Check-in</button>
                        </form>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->discounts->isNotEmpty()): ?>
            <section class="card guest-checkin-secondary guest-checkin-verification">
                <div class="card-header">Senior/PWD Verification</div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->discounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $discount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="policy-box mb-3">
                            <div class="flex-between">
                                <strong><?php echo e($discount->guest->full_name); ?> · <?php echo e($discount->discount_type); ?></strong>
                                <span class="badge <?php echo e($discount->verification_status === 'Verified' ? 'badge-verified' : (in_array($discount->verification_status, ['Invalid', 'Rejected'], true) ? 'badge-unpaid' : 'badge-pending')); ?>"><?php echo e($discount->verification_status); ?></span>
                            </div>
                            <div class="text-muted fs-sm mt-1">ID number: <?php echo e($discount->id_reference); ?></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($discount->verification_status === 'Invalid'): ?>
                                <p class="alert alert-danger mt-2 mb-0">ID is invalid. Please tell the guest in person.</p>
                            <?php elseif($discount->verification_status === 'Verified'): ?>
                                <div class="flex-between fs-sm mt-2">
                                    <span><?php echo e($discount->discount_type); ?> discount for <?php echo e($discount->guest->full_name); ?></span>
                                    <strong>−₱<?php echo e(number_format($discount->discount_amount, 2)); ?></strong>
                                </div>
                            <?php elseif($discount->verification_status === \App\Support\BookingStatus::PENDING && $discount->guest->roster_status !== \App\Models\BookingGuest::ROSTER_ACTIVE): ?>
                                <p class="form-hint mt-2">This guest was removed from the reservation, so the discount cannot be verified.</p>
                            <?php elseif($discount->verification_status === \App\Support\BookingStatus::PENDING): ?>
                                <p class="form-hint mt-2">Compare this number with the guest’s physical ID before recording the result.</p>
                                <form method="POST" action="<?php echo e(route($dashboardPrefix.'.discounts.review', $discount->discount_id)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <div class="flex-center gap-1 mt-2">
                                        <button type="submit" name="status" value="Verified" class="btn btn-success">Verify</button>
                                        <button type="button" class="btn btn-danger" data-show-discount-reason>Reject ID</button>
                                    </div>
                                    <div class="form-group mt-2" data-discount-reason-panel <?php if(! $errors->has('verification_note')): ?> hidden <?php endif; ?>>
                                        <label class="required" for="discount-review-note-<?php echo e($discount->discount_id); ?>">Why can’t this ID be accepted?</label>
                                        <textarea id="discount-review-note-<?php echo e($discount->discount_id); ?>" name="verification_note" rows="2" maxlength="500" data-discount-review-note><?php echo e(old('verification_note')); ?></textarea>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['verification_note'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <div class="flex-center gap-1 mt-2">
                                            <button type="submit" name="status" value="Invalid" class="btn btn-danger">Reject ID</button>
                                            <button type="button" class="btn btn-outline" data-hide-discount-reason>Go back</button>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($discount->verification_status, ['Invalid', 'Rejected'], true) && $discount->verification_note): ?>
                                <p class="alert alert-danger mt-2 mb-0">Reason: <?php echo e($discount->verification_note); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($discount->verifiedBy): ?>
                                <div class="text-muted fs-sm mt-2">Reviewed by <?php echo e($discount->verifiedBy->first_name); ?> <?php echo e($discount->verifiedBy->last_name); ?> · <?php echo e($discount->verified_at?->format('M j, Y g:i A')); ?></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="form-hint mb-0">Each eligible discount applies only to the guest named above, not to the whole booking. A verified discount is reflected in the checkout breakdown.</p>
                </div>
            </section>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->status === \App\Support\BookingStatus::CHECKED_IN): ?>
        <section class="card guest-checkin-secondary guest-checkin-addons">
            <div class="card-header">Amenities Added</div>
            <div class="card-body">
                <?php
                    $activeAddons = $booking->addons->filter(fn ($addon) => $addon->quantity > 0);
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $activeAddons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $addon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="flex-between mb-2">
                        <span><?php echo e($addon->name); ?> · <?php echo e($addon->quantity); ?> × ₱<?php echo e(number_format((float) $addon->price, 2)); ?></span>
                        <span class="flex-center gap-1">
                            <strong>₱<?php echo e(number_format((float) $addon->price * $addon->quantity, 2)); ?></strong>
                            <form method="POST" action="<?php echo e(route($dashboardPrefix.'.guests.addons.quantity', [$booking->booking_id, $addon->booking_addon_id])); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="change" value="-1">
                                <button type="submit" class="btn btn-outline btn-sm" aria-label="Decrease <?php echo e($addon->name); ?> quantity">−</button>
                            </form>
                            <form method="POST" action="<?php echo e(route($dashboardPrefix.'.guests.addons.quantity', [$booking->booking_id, $addon->booking_addon_id])); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="change" value="1">
                                <button type="submit" class="btn btn-outline btn-sm" aria-label="Increase <?php echo e($addon->name); ?> quantity">+</button>
                            </form>
                        </span>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="text-muted mb-0">No amenities have been added to this booking.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeAddons->isNotEmpty()): ?>
                    <hr class="divider">
                    <div class="flex-between"><strong>Amenities total</strong><strong>₱<?php echo e(number_format($activeAddons->sum(fn ($addon) => (float) $addon->price * $addon->quantity), 2)); ?></strong></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <section class="card guest-checkin-secondary guest-checkin-refunds">
            <div class="card-header">Refund History</div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $booking->refunds->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $refund): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="flex-between" style="gap:12px;align-items:flex-start">
                        <div>
                            <strong>₱<?php echo e(number_format((float) $refund->amount, 2)); ?> · <?php echo e($refund->reason); ?></strong>
                            <div class="text-muted fs-sm"><?php echo e($refund->processed_at?->format('M j, Y g:i A') ?? $refund->created_at?->format('M j, Y g:i A')); ?> · <?php echo e($refund->processedBy?->first_name); ?> <?php echo e($refund->processedBy?->last_name); ?></div>
                        </div>
                        <span class="badge <?php echo e($refund->refund_status === 'Refunded' ? 'badge-verified' : 'badge-pending'); ?>"><?php echo e($refund->refund_status); ?></span>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $loop->last): ?><hr class="divider"><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="text-muted mb-0">No refunds have been recorded.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <dialog class="guest-checkin-dialog" id="refund-dialog" aria-labelledby="refund-dialog-title">
        <div class="guest-checkin-dialog-header">
            <strong id="refund-dialog-title">Record a Refund</strong>
        </div>
        <form method="POST" action="<?php echo e(route($dashboardPrefix.'.guests.refunds.store', $booking->booking_id)); ?>">
            <?php echo csrf_field(); ?>
            <div class="guest-checkin-dialog-body">
                <p class="text-muted fs-sm">Available refundable overpayment after approved charges and completed refunds: ₱<?php echo e(number_format($refundAmountLimit, 2)); ?>.</p>
                <div class="form-group">
                    <label class="required" for="refund-amount">Refund amount (₱)</label>
                    <input id="refund-amount" name="amount" type="number" min="0.01" max="<?php echo e(number_format($refundAmountLimit, 2, '.', '')); ?>" step="0.01" value="<?php echo e(old('amount')); ?>" required>
                </div>
                <div class="form-group">
                    <label class="required" for="refund-reason">Reason</label>
                    <textarea id="refund-reason" name="reason" rows="3" maxlength="255" placeholder="Example: Guest needs to go home early" required><?php echo e(old('reason')); ?></textarea>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($refundAmountLimit <= 0): ?>
                    <p class="alert alert-warning mb-0">There is no verified payment balance available to refund.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="guest-checkin-dialog-actions">
                <button type="button" class="btn btn-outline" data-close-checkin-dialog>Cancel</button>
                <button type="submit" class="btn btn-primary" <?php if($refundAmountLimit <= 0): echo 'disabled'; endif; ?>>Refund</button>
            </div>
        </form>
    </dialog>

    <dialog class="guest-checkin-dialog" id="amenities-dialog" aria-labelledby="amenities-dialog-title">
        <div class="guest-checkin-dialog-header">
            <strong id="amenities-dialog-title">Add Amenities</strong>
        </div>
        <form method="POST" action="<?php echo e(route($dashboardPrefix.'.guests.addons.store', $booking->booking_id)); ?>">
            <?php echo csrf_field(); ?>
            <div class="guest-checkin-dialog-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->unit->addons->isNotEmpty()): ?>
                    <p class="text-muted fs-sm">Choose one or more amenities and set each quantity.</p> <br>
                    <div class="amenity-card-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->unit->addons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $addon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <label class="amenity-card">
                                <input class="amenity-card-selection" type="checkbox" name="addons[<?php echo e($index); ?>][unit_addon_id]" value="<?php echo e($addon->unit_addon_id); ?>" <?php if((string) old('addons.'.$index.'.unit_addon_id') === (string) $addon->unit_addon_id): echo 'checked'; endif; ?> data-amenity-check>
                                <span class="flex-between">
                                    <strong><?php echo e($addon->name); ?></strong>
                                    <span>₱<?php echo e(number_format((float) $addon->price, 2)); ?></span>
                                </span>
                                <span class="form-group mt-2 mb-0">
                                    <span class="text-muted fs-sm">Quantity</span>
                                    <input type="number" name="addons[<?php echo e($index); ?>][quantity]" value="<?php echo e(old('addons.'.$index.'.quantity', 1)); ?>" min="1" max="100" <?php if((string) old('addons.'.$index.'.unit_addon_id') !== (string) $addon->unit_addon_id): echo 'disabled'; endif; ?> data-amenity-quantity>
                                </span>
                            </label>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['addons'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="form-hint text-danger mt-2"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->get('addons.*'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $addonErrors): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $addonErrors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <p class="form-hint text-danger mt-2"><?php echo e($error); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">This unit has no add-on amenities.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="guest-checkin-dialog-actions">
                <button type="button" class="btn btn-outline" data-close-checkin-dialog>Cancel</button>
                <button type="submit" class="btn btn-primary" <?php if($booking->unit->addons->isEmpty()): echo 'disabled'; endif; ?>>Submit</button>
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

            document.querySelectorAll('[data-discount-review-note]').forEach((notes) => {
                const form = notes.closest('form');
                const panel = form.querySelector('[data-discount-reason-panel]');
                const rejectButton = form.querySelector('[data-show-discount-reason]');
                const backButton = form.querySelector('[data-hide-discount-reason]');

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

            <?php if($errors->has('amount') || $errors->has('reason')): ?>
                document.getElementById('refund-dialog')?.showModal();
            <?php endif; ?>
            <?php if($errors->has('addons') || collect($errors->keys())->contains(fn ($key) => str_starts_with($key, 'addons.'))): ?>
                document.getElementById('amenities-dialog')?.showModal();
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
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/bookings/guest-check-in.blade.php ENDPATH**/ ?>
<div class="container workspace-page staff-dashboard-page">
    <div class="page-header-row page-header">
        <div>
            <h1>Work Queue</h1>
            <p>Start with booking requests and payments that need your attention.</p>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->has('check_in')): ?>
        <div class="alert alert-danger mt-3" role="alert"><?php echo e($errors->first('check_in')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success mt-3" role="status"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="staff-dashboard-queues mt-3">
        <section class="card">
            <div class="card-header">
                <span><i class="fa fa-right-to-bracket"></i> Upcoming Check-ins</span>
                <span class="badge badge-pending"><?php echo e($upcomingCheckIns->count()); ?></span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Guest / Unit</th><th>Check-in date</th><th>Booking reference</th><th>Guests</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $upcomingCheckIns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($booking->displayGuestName()); ?></strong>
                                    <div class="text-muted fs-sm"><?php echo e($booking->unit->unit_type); ?></div>
                                </td>
                                <td><?php echo e($booking->check_in->format('M j, Y')); ?>                                </td>
                                <td><?php echo e($booking->booking_reference ?? 'Booking #'.$booking->booking_id); ?></td>
                                <td><?php echo e($booking->adult_count + $booking->child_count); ?></td>
                                <td><a href="<?php echo e(route($dashboardPrefix.'.guests.check-in', $booking->booking_id)); ?>" class="btn btn-primary btn-sm">Manage Check-in</a></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="5" class="text-muted">No upcoming guests expected.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <span><i class="fa fa-person-circle-check"></i> Current Guests</span>
                <span class="badge badge-pending"><?php echo e($currentGuests->count()); ?></span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Guest / Unit</th><th>Booking reference</th><th>Check-out</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $currentGuests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($booking->displayGuestName()); ?></strong>
                                    <div class="text-muted fs-sm"><?php echo e($booking->unit->unit_type); ?></div>
                                </td>
                                <td><?php echo e($booking->booking_reference ?? 'Booking #'.$booking->booking_id); ?></td>
                                <td><?php echo e($booking->check_out->format('M j, Y')); ?></td>
                                <td><a href="<?php echo e(route($dashboardPrefix.'.guests.check-in', $booking->booking_id)); ?>" class="btn btn-outline btn-sm">Manage Guest</a></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="4" class="text-muted">No current guests.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($houses->isEmpty()): ?>
        <div class="page-header-row page-header mt-3">
            <div>
                <h2 class="section-page-title">Transient Houses</h2>
            </div>
        </div>
        <div class="empty-state">
            <i class="fa fa-building"></i>
            <h3>No assigned properties</h3>
            <p>Booking requests and payment reviews will appear here when a property is assigned.</p>
        </div>
    <?php else: ?>
        <div class="page-header-row page-header mt-3">
            <div>
                <h2 class="section-page-title">Transient Houses</h2>
            </div>
        </div>
        <div class="staff-dashboard-queues">
            <section class="card">
                <div class="card-header">
                    <span><i class="fa fa-calendar-check"></i> New Booking Requests</span>
                    <span class="badge badge-pending"><?php echo e($pendingBookings->count()); ?></span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Guest / Property</th><th>Stay</th><th>Requested</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pendingBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo e($booking->booking_id); ?> · <?php echo e($booking->displayGuestName()); ?></strong>
                                        <div class="text-muted fs-sm"><?php echo e($booking->unit->unit_type); ?> · <?php echo e($booking->unit->house->name); ?></div>
                                    </td>
                                    <td><?php echo e($booking->check_in->format('M j')); ?>–<?php echo e($booking->check_out->format('M j, Y')); ?></td>
                                    <td><?php echo e($booking->created_at->diffForHumans()); ?></td>
                                    <td><a href="<?php echo e(route($dashboardPrefix.'.bookings.show', $booking->booking_id)); ?>" class="btn btn-outline btn-sm">Review</a></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="4" class="text-muted">No booking requests need review.</td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="card">
                <div class="card-header">
                    <span><i class="fa fa-receipt"></i> Payments to Verify</span>
                    <span class="badge badge-pending"><?php echo e($paymentReviews->sum(fn ($booking) => max(1, $booking->payments->count()))); ?></span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Booking / Guest</th><th>Submission / Deadline</th><th>Amount / Minimum</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $paymentReviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo e($booking->booking_id); ?> · <?php echo e($booking->displayGuestName()); ?></strong>
                                            <div class="text-muted fs-sm"><?php echo e($payment->payment_method); ?> · <?php echo e($booking->unit->house->name); ?></div>
                                        </td>
                                        <td>
                                            Submitted <?php echo e($payment->payment_date->format('M j, g:i A')); ?>

                                            <div class="text-muted fs-sm">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->payment_overdue): ?>
                                                    Deadline passed; booking hold remains until Owner/Staff resolves it.
                                                <?php else: ?>
                                                    Deadline <?php echo e($booking->payment_deadline?->format('M j, g:i A')); ?> · hold active while payment is reviewed.
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </td>
                                        <td>₱<?php echo e(number_format($payment->amount, 2)); ?></td>
                                        <td><a href="<?php echo e(route($dashboardPrefix.'.bookings.show', $booking->booking_id)); ?>" class="btn btn-outline btn-sm">Verify</a></td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->payments->isEmpty()): ?>
                                    <tr>
                                        <td><strong>#<?php echo e($booking->booking_id); ?> · <?php echo e($booking->displayGuestName()); ?></strong><div class="text-muted fs-sm"><?php echo e($booking->unit->house->name); ?></div></td>
                                        <td>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->payment_overdue): ?>
                                                Overdue since <?php echo e($booking->payment_deadline?->format('M j, g:i A')); ?>

                                            <?php else: ?>
                                                Due <?php echo e($booking->payment_deadline?->format('M j, g:i A')); ?>

                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td>₱<?php echo e(number_format($booking->downpaymentAmount(), 2)); ?> min.</td>
                                        <td><a href="<?php echo e(route($dashboardPrefix.'.bookings.show', $booking->booking_id)); ?>" class="btn btn-outline btn-sm">Follow up</a></td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="4" class="text-muted">No payment submissions need review.</td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </div>

        <div class="flex-center gap-1 mt-3 text-muted fs-sm" aria-label="Assigned properties">
            <strong>Properties:</strong>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $houses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $house): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <span><?php echo e($house->name); ?> · <?php echo e($house->city); ?></span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/partials/booking-work-queues.blade.php ENDPATH**/ ?>
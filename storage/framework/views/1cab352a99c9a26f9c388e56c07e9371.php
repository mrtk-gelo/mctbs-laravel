<?php
    $selectedTab = request('tab');
    $pendingIsActive = ! in_array($selectedTab, ['accepted', 'history', 'archive'], true);
    $acceptedIsActive = $selectedTab === 'accepted';
    $historyIsActive = $selectedTab === 'history';
    $archiveIsActive = $selectedTab === 'archive';
    $archivableStatuses = [
        ...\App\Support\BookingStatus::completedStatuses(),
        \App\Support\BookingStatus::CANCELLED,
        \App\Support\BookingStatus::REJECTED,
    ];
    $hasArchivableBookings = $historyBookings->contains(
        fn ($booking): bool => ! $booking->archive && in_array($booking->status, $archivableStatuses, true)
    );
?>

<div id="booking-results">
    <div class="tabs" role="tablist">
        <button type="button" class="tab-btn <?php echo e($pendingIsActive ? 'active' : ''); ?>" data-tab="pending-bookings">
            Pending <span class="badge badge-pending"><?php echo e($pendingBookings->count()); ?></span>
        </button>
        <button type="button" class="tab-btn <?php echo e($acceptedIsActive ? 'active' : ''); ?>" data-tab="accepted-bookings">
            Accepted <span class="badge badge-accepted"><?php echo e($acceptedBookings->count()); ?></span>
        </button>
        <button type="button" class="tab-btn <?php echo e($historyIsActive ? 'active' : ''); ?>" data-tab="history-bookings">
            History <span class="badge badge-completed"><?php echo e($historyBookings->count()); ?></span>
        </button>
        <button type="button" class="tab-btn <?php echo e($archiveIsActive ? 'active' : ''); ?>" data-tab="archive-bookings">
            Archive <span class="badge badge-cancelled"><?php echo e($archivedBookings->count()); ?></span>
        </button>
    </div>

    <div class="tab-pane <?php echo e($pendingIsActive ? 'active' : ''); ?>" id="pending-bookings" data-booking-filter-panel>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendingBookings->isEmpty()): ?>
            <div class="empty-state" data-filter-empty><i class="fa fa-clock"></i><h3>No pending requests</h3><p>New booking requests will appear here.</p></div>
        <?php else: ?>
            <div class="card" data-booking-list>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Booking</th><th>Guest</th><th>Unit</th><th>Check-in</th><th>Check-out</th><th>Guests</th><th>Total</th><th>Requested</th><th>Action</th></tr></thead>
                        <tbody data-booking-items>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pendingBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr data-booking-item data-created-at="<?php echo e($booking->created_at?->timestamp ?? 0); ?>" data-check-in="<?php echo e($booking->check_in->toDateString()); ?>" data-check-out="<?php echo e($booking->check_out->toDateString()); ?>">
                                    <td><strong>#<?php echo e($booking->booking_id); ?></strong><br><span class="badge badge-pending">Pending</span></td>
                                    <td><?php echo e($booking->displayGuestName()); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->displayGuestEmail()): ?><br><span class="text-muted fs-sm"><?php echo e($booking->displayGuestEmail()); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></td>
                                    <td><?php echo e($booking->unit->unit_type); ?><br><span class="text-muted fs-sm"><?php echo e($booking->unit->house->name); ?></span></td>
                                    <td><?php echo e($booking->check_in->format('M j, Y')); ?></td>
                                    <td><?php echo e($booking->check_out->format('M j, Y')); ?></td>
                                    <td><?php echo e($booking->adult_count + $booking->child_count); ?></td>
                                    <td>₱<?php echo e(number_format($booking->amountDueBeforeCheckout(), 2)); ?></td>
                                    <td><?php echo e($booking->created_at->format('M j, Y')); ?></td>
                                    <td><a href="<?php echo e(route($dashboardPrefix.'.bookings.show', $booking->booking_id)); ?>" class="btn btn-outline btn-sm">View</a></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="empty-state" data-filter-empty hidden><i class="fa fa-filter"></i><h3>No bookings match these filters</h3></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="tab-pane <?php echo e($acceptedIsActive ? 'active' : ''); ?>" id="accepted-bookings" data-booking-filter-panel>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($acceptedBookings->isEmpty()): ?>
            <div class="empty-state" data-filter-empty><i class="fa fa-check-circle"></i><h3>No accepted bookings</h3></div>
        <?php else: ?>
            <div class="card" data-booking-list>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Booking</th><th>Guest</th><th>Unit</th><th>Check-in</th><th>Check-out</th><th>Total</th><th>Payment</th><th>Deadline</th><th>Action</th></tr></thead>
                        <tbody data-booking-items>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $acceptedBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $latestPayment = $booking->payments->sortByDesc('payment_date')->first();
                                    $paymentBadge = match ($latestPayment?->payment_status) {
                                        \App\Support\PaymentStatus::VERIFIED => 'badge-verified',
                                        \App\Support\PaymentStatus::UNDER_REVIEW => 'badge-pending',
                                        default => 'badge-unpaid',
                                    };
                                    $acceptedBadge = $booking->status === \App\Support\BookingStatus::CONFIRMED ? 'badge-completed' : 'badge-accepted';
                                ?>
                                <tr data-booking-item data-created-at="<?php echo e($booking->created_at?->timestamp ?? 0); ?>" data-check-in="<?php echo e($booking->check_in->toDateString()); ?>" data-check-out="<?php echo e($booking->check_out->toDateString()); ?>">
                                    <td><strong>#<?php echo e($booking->booking_id); ?></strong><br><span class="badge <?php echo e($acceptedBadge); ?>"><?php echo e(\App\Support\BookingStatus::canonical($booking->status)); ?></span></td>
                                    <td><?php echo e($booking->displayGuestName()); ?></td>
                                    <td><?php echo e($booking->unit->unit_type); ?><br><span class="text-muted fs-sm"><?php echo e($booking->unit->house->name); ?></span></td>
                                    <td><?php echo e($booking->check_in->format('M j, Y')); ?></td>
                                    <td><?php echo e($booking->check_out->format('M j, Y')); ?></td>
                                    <td>₱<?php echo e(number_format($booking->amountDueBeforeCheckout(), 2)); ?></td>
                                    <td><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($latestPayment): ?><span class="badge <?php echo e($paymentBadge); ?>"><?php echo e($latestPayment->payment_status); ?></span><?php else: ?><span class="badge badge-unpaid">Unpaid</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></td>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->status === \App\Support\BookingStatus::ACCEPTED): ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->payment_overdue): ?>
                                                Overdue since <?php echo e($booking->payment_deadline?->format('M j, Y g:i A')); ?>

                                            <?php else: ?>
                                                <?php echo e($booking->payment_deadline?->format('M j, Y g:i A')); ?>

                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td><a href="<?php echo e(route($dashboardPrefix.'.bookings.show', $booking->booking_id)); ?>" class="btn btn-outline btn-sm">View</a></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="empty-state" data-filter-empty hidden><i class="fa fa-filter"></i><h3>No bookings match these filters</h3></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="tab-pane <?php echo e($historyIsActive ? 'active' : ''); ?>" id="history-bookings" data-booking-filter-panel>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($historyBookings->isEmpty()): ?>
            <div class="empty-state" data-filter-empty><i class="fa fa-history"></i><h3>No booking history</h3></div>
        <?php else: ?>
            <form method="POST" action="<?php echo e(route($dashboardPrefix.'.bookings.archive-selected')); ?>" data-history-archive-form>
                <?php echo csrf_field(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasArchivableBookings): ?>
                    <div class="booking-archive-toolbar">
                        <label class="booking-archive-select-all">
                            <input type="checkbox" data-archive-select-all>
                            <span>Select all</span>
                        </label>
                        <span class="booking-archive-count" data-archive-count aria-live="polite">0 selected</span>
                        <button type="submit" class="btn btn-outline" data-archive-submit disabled>Archive selected</button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="card" data-booking-list>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th><span class="sr-only">Select</span></th><th>Booking</th><th>Guest</th><th>Unit</th><th>Check-in</th><th>Check-out</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody data-booking-items>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $historyBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <?php
                                        $canonicalStatus = \App\Support\BookingStatus::canonical($booking->status);
                                        $historyBadge = match ($canonicalStatus) {
                                            \App\Support\BookingStatus::COMPLETED => 'badge-completed',
                                            \App\Support\BookingStatus::REJECTED => 'badge-rejected',
                                            \App\Support\BookingStatus::CANCELLED => 'badge-cancelled',
                                            \App\Support\BookingStatus::PENDING => 'badge-pending',
                                            default => 'badge-accepted',
                                        };
                                        $canArchive = ! $booking->archive && in_array($booking->status, $archivableStatuses, true);
                                    ?>
                                    <tr data-booking-item data-created-at="<?php echo e($booking->created_at?->timestamp ?? 0); ?>" data-check-in="<?php echo e($booking->check_in->toDateString()); ?>" data-check-out="<?php echo e($booking->check_out->toDateString()); ?>">
                                        <td>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canArchive): ?>
                                                <input type="checkbox" name="booking_ids[]" value="<?php echo e($booking->booking_id); ?>" aria-label="Select booking #<?php echo e($booking->booking_id); ?>" data-archive-booking>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td>
                                            <strong>#<?php echo e($booking->booking_id); ?></strong>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->archive): ?>
                                                <br><span class="badge badge-cancelled">Archived</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td><?php echo e($booking->displayGuestName()); ?></td>
                                        <td><?php echo e($booking->unit->unit_type); ?><br><span class="text-muted fs-sm"><?php echo e($booking->unit->house->name); ?></span></td>
                                        <td><?php echo e($booking->check_in->format('M j, Y')); ?></td>
                                        <td><?php echo e($booking->check_out->format('M j, Y')); ?></td>
                                        <td>₱<?php echo e(number_format($booking->amountDueBeforeCheckout(), 2)); ?></td>
                                        <td>
                                            <?php echo $__env->make('bookings.partials.status-indicator', [
                                                'booking' => $booking,
                                                'statusBadge' => $historyBadge,
                                                'statusLabel' => $canonicalStatus,
                                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </td>
                                        <td><a href="<?php echo e(route($dashboardPrefix.'.bookings.show', $booking->booking_id)); ?>" class="btn btn-outline btn-sm">View</a></td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="empty-state" data-filter-empty hidden><i class="fa fa-filter"></i><h3>No bookings match these filters</h3></div>
            </form>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="tab-pane <?php echo e($archiveIsActive ? 'active' : ''); ?>" id="archive-bookings" data-booking-filter-panel>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($archivedBookings->isEmpty()): ?>
            <div class="empty-state" data-filter-empty><i class="fa fa-archive"></i><h3>No archived bookings</h3></div>
        <?php else: ?>
            <div class="card" data-booking-list>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Booking</th><th>Guest</th><th>Unit</th><th>Check-in</th><th>Check-out</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody data-booking-items>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $archivedBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $canonicalStatus = \App\Support\BookingStatus::canonical($booking->status);
                                    $historyBadge = match ($canonicalStatus) {
                                        \App\Support\BookingStatus::COMPLETED => 'badge-completed',
                                        \App\Support\BookingStatus::REJECTED => 'badge-rejected',
                                        \App\Support\BookingStatus::CANCELLED => 'badge-cancelled',
                                        \App\Support\BookingStatus::PENDING => 'badge-pending',
                                        default => 'badge-accepted',
                                    };
                                ?>
                                <tr data-booking-item data-created-at="<?php echo e($booking->created_at?->timestamp ?? 0); ?>" data-check-in="<?php echo e($booking->check_in->toDateString()); ?>" data-check-out="<?php echo e($booking->check_out->toDateString()); ?>">
                                    <td><strong>#<?php echo e($booking->booking_id); ?></strong></td>
                                    <td><?php echo e($booking->displayGuestName()); ?></td>
                                    <td><?php echo e($booking->unit->unit_type); ?><br><span class="text-muted fs-sm"><?php echo e($booking->unit->house->name); ?></span></td>
                                    <td><?php echo e($booking->check_in->format('M j, Y')); ?></td>
                                    <td><?php echo e($booking->check_out->format('M j, Y')); ?></td>
                                    <td>₱<?php echo e(number_format($booking->amountDueBeforeCheckout(), 2)); ?></td>
                                    <td>
                                        <?php echo $__env->make('bookings.partials.status-indicator', [
                                            'booking' => $booking,
                                            'statusBadge' => $historyBadge,
                                            'statusLabel' => $canonicalStatus,
                                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    </td>
                                    <td><a href="<?php echo e(route($dashboardPrefix.'.bookings.show', $booking->booking_id)); ?>" class="btn btn-outline btn-sm">View</a></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="empty-state" data-filter-empty hidden><i class="fa fa-filter"></i><h3>No bookings match these filters</h3></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/bookings/partials/manage-results.blade.php ENDPATH**/ ?>
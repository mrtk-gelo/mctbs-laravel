<?php
    $canonicalStatus = \App\Support\BookingStatus::canonical($booking->status);
    $statusBadge = match ($canonicalStatus) {
        \App\Support\BookingStatus::PENDING => 'badge-pending',
        \App\Support\BookingStatus::ACCEPTED, \App\Support\BookingStatus::CONFIRMED => 'badge-accepted',
        \App\Support\BookingStatus::COMPLETED => 'badge-completed',
        \App\Support\BookingStatus::REJECTED => 'badge-rejected',
        default => 'badge-cancelled',
    };
    $paymentBadge = $canonicalStatus === \App\Support\BookingStatus::ACCEPTED
        ? ($hasUnderReviewPayment ? 'badge-pending' : 'badge-unpaid')
        : ($verifiedPaymentTotal >= $checkoutTotal
            ? 'badge-paid'
            : ($verifiedPaymentTotal > 0 ? 'badge-downpaid' : 'badge-pending'));
    $paymentLabel = $canonicalStatus === \App\Support\BookingStatus::ACCEPTED
        ? ($hasUnderReviewPayment ? 'Downpayment under review' : 'Downpayment due')
        : ($verifiedPaymentTotal >= $checkoutTotal
            ? 'Paid'
            : ($verifiedPaymentTotal > 0 ? 'Balance due at checkout' : 'Due at checkout'));
    $progressStep = match ($canonicalStatus) {
        \App\Support\BookingStatus::PENDING => 2,
        \App\Support\BookingStatus::ACCEPTED => 3,
        \App\Support\BookingStatus::CONFIRMED => 4,
        \App\Support\BookingStatus::COMPLETED => 5,
        default => 2,
    };
    $isStayComplete = \App\Support\BookingStatus::isTerminal($booking->status);
?>

<div wire:poll.15s.visible="refreshStatus">
    <div class="page-header-row mb-3">
        <div class="flex-center gap-1">
            <?php echo $__env->make('bookings.partials.status-indicator', [
                'booking' => $booking,
                'statusBadge' => $statusBadge,
                'statusLabel' => $canonicalStatus,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <span class="badge <?php echo e($paymentBadge); ?>"><?php echo e($paymentLabel); ?></span>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->status === \App\Support\BookingStatus::ACCEPTED): ?>
        <div class="alert alert-info mb-3" role="status">
            <i class="fa fa-clock"></i>
            <span>Booking accepted. Pay ₱<?php echo e(number_format($downpaymentAmount, 2)); ?> to confirm.</span>
        </div>
    <?php elseif(in_array($booking->status, [\App\Support\BookingStatus::CONFIRMED, \App\Support\BookingStatus::CHECKED_IN], true)): ?>
        <div class="alert alert-success mb-3" role="status">
            <i class="fa fa-check-circle"></i>
            <span>Downpayment verified. Pay any remaining balance at checkout.</span>
        </div>
    <?php elseif($booking->status === \App\Support\BookingStatus::REJECTED && $booking->rejection_reason): ?>
        <div class="alert alert-danger mb-3" role="alert">
            <strong>Rejection reason:</strong> <?php echo e($booking->rejection_reason); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <section class="booking-progress" role="progressbar" aria-label="Booking progress" aria-valuemin="1" aria-valuemax="5" aria-valuenow="<?php echo e($progressStep); ?>">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['Request sent', 'Owner review', 'Downpayment', 'Booking confirmed', 'Stay completed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stepLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php
                $stepNumber = $index + 1;
                $stepDone = $stepNumber < $progressStep || $isStayComplete;
                $stepActive = $stepNumber === $progressStep && ! $isStayComplete;
            ?>
            <div class="booking-progress-step <?php echo e($stepDone ? 'is-done' : ''); ?> <?php echo e($stepActive ? 'is-active' : ''); ?>">
                <span class="booking-progress-marker">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stepDone): ?>
                        <i class="fa fa-check" aria-hidden="true"></i>
                    <?php else: ?>
                        <?php echo e($stepNumber); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </span>
                <span><?php echo e($stepLabel); ?></span>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </section>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->status === \App\Support\BookingStatus::PENDING): ?>
        <div class="mb-3">
            <p class="text-muted mb-0">Waiting for review.</p>
        </div>
    <?php elseif($verifiedPaymentTotal >= $checkoutTotal): ?>
        <div class="mb-3">
            <p class="text-muted mb-0">Payment complete.</p>
        </div>
    <?php elseif($hasUnderReviewPayment): ?>
        <div class="mb-3">
            <p class="alert alert-info mb-0" role="status">Payment under review.</p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/livewire/guest/booking-status.blade.php ENDPATH**/ ?>
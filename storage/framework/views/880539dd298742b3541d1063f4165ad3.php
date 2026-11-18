<?php
    $indicatorMessage = null;

    if ($booking->checkedOutEarly() && filled($booking->early_checkout_reason)) {
        $indicatorMessage = 'Early Check-out: '.$booking->early_checkout_reason;
    } elseif ($booking->status === \App\Support\BookingStatus::CANCELLED && filled($booking->cancellation_reason)) {
        $indicatorMessage = 'Cancellation reason: '.$booking->cancellation_reason;
    } elseif ($booking->status === \App\Support\BookingStatus::REJECTED && filled($booking->rejection_reason)) {
        $indicatorMessage = 'Rejection reason: '.$booking->rejection_reason;
    }
?>

<span
    class="booking-status-indicator"
    <?php if($indicatorMessage): ?>
        data-status-tooltip="<?php echo e($indicatorMessage); ?>"
        aria-label="<?php echo e($statusLabel); ?>. <?php echo e($indicatorMessage); ?>"
        aria-describedby="booking-status-tooltip"
        tabindex="0"
    <?php endif; ?>
>
    <span class="badge <?php echo e($statusBadge); ?>"><?php echo e($statusLabel); ?></span>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($indicatorMessage): ?>
        <span class="booking-status-indicator__dot" aria-hidden="true"></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</span>
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/bookings/partials/status-indicator.blade.php ENDPATH**/ ?>
Hello <?php echo e($guestName); ?>,

Your booking request has been accepted and the dates are reserved while you submit the downpayment.

Booking: #<?php echo e($booking->booking_id); ?>

Property: <?php echo e($booking->unit->house->name); ?>

Unit: <?php echo e($booking->unit->unit_type); ?>

Check-in: <?php echo e($booking->check_in->format('M j, Y')); ?> at <?php echo e(\App\Support\UnitTimeFormatter::format((string) $booking->unit->check_in_time)); ?>

Check-out: <?php echo e($booking->check_out->format('M j, Y')); ?> at <?php echo e(\App\Support\UnitTimeFormatter::format((string) $booking->unit->check_out_time)); ?>


Submit the downpayment of ₱<?php echo e(number_format($downpaymentAmount, 2)); ?> to confirm your reservation. Your downpayment will be credited to your final bill; any remaining balance, including booking changes and checkout charges, is due at checkout.

Submit your downpayment:
<?php echo e($paymentUrl); ?>


If you requested a Senior Citizen or PWD discount, bring the original valid ID; the discount is applied after it is verified at checkout.
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/emails/booking-accepted.blade.php ENDPATH**/ ?>
Hello <?php echo e($guestName); ?>,

Thank you for staying at <?php echo e($booking->unit->house->name); ?>. We would appreciate your rating and feedback about your stay.

Share your feedback using this secure link:
<?php echo $feedbackUrl; ?>


This link expires in 7 days and can be used to submit one review for booking <?php echo e($booking->booking_reference); ?>.
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/emails/booking-feedback-requested.blade.php ENDPATH**/ ?>
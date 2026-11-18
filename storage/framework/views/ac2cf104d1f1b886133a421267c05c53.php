New inquiry about <?php echo e($propertyName); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unitName): ?> — <?php echo e($unitName); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

From: <?php echo e($senderName); ?>

Reply email: <?php echo e($senderEmail); ?>


Message:
<?php echo e($inquiryMessage); ?>

<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/emails/property-inquiry.blade.php ENDPATH**/ ?>
<?php
    $activePage = $activePage ?? 'dashboard';
?>
<?php echo $__env->make('partials.nav', ['activePage' => $activePage], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/partials/staff-nav.blade.php ENDPATH**/ ?>
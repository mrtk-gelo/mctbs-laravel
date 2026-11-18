<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>

<body>
    <?php echo $__env->make('partials.nav', ['activePage' => 'bookings'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="page-wrapper">
        <?php echo e($slot); ?>

    </div>

    <?php echo $__env->make('partials.system-interface', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>
</html><?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/layouts/app.blade.php ENDPATH**/ ?>
<?php
    $navUser = auth()->user();
    $roleName = $navUser?->role?->name;
?>
<nav class="topnav">
    <div class="topnav-brand">
        <a href="<?php echo e($navUser ? $navUser->dashboardPath() : route('home')); ?>" aria-label="MCTBS dashboard">
            <span class="brand-icon"></span>
        </a>
    </div>

    <div class="topnav-center">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($navUser): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($roleName):
                case ('System Admin'): ?>
                    <a href="<?php echo e(route('sysadmin.dashboard')); ?>" class="<?php echo e(($activePage ?? '') === 'dashboard' ? 'active' : ''); ?>"><i class="fa fa-gauge"></i> Dashboard</a>
                    <a href="<?php echo e(route('sysadmin.owners.index')); ?>" class="<?php echo e(($activePage ?? '') === 'owners' ? 'active' : ''); ?>"><i class="fa fa-users"></i> Owners</a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php break; ?>

                <?php case ('Owner'): ?>
                    <a href="<?php echo e(route('owner.dashboard')); ?>" class="<?php echo e(($activePage ?? '') === 'dashboard' ? 'active' : ''); ?>"><i class="fa fa-gauge"></i> Dashboard</a>
                    <a href="<?php echo e(route('owner.houses.index')); ?>" class="<?php echo e(in_array($activePage ?? '', ['houses', 'units'], true) ? 'active' : ''); ?>"><i class="fa fa-building"></i> Properties</a>
                    <a href="<?php echo e(route('owner.bookings.index')); ?>" class="<?php echo e(($activePage ?? '') === 'bookings' ? 'active' : ''); ?>"><i class="fa fa-calendar-check"></i> Bookings</a>
                    <a href="<?php echo e(route('owner.bookings.index', ['section' => 'calendar'])); ?>" class="<?php echo e(($activePage ?? '') === 'calendar' ? 'active' : ''); ?>"><i class="fa fa-calendar"></i> Calendar</a>
                    <a href="<?php echo e(route('owner.bookings.index', ['section' => 'reports'])); ?>" class="<?php echo e(($activePage ?? '') === 'reports' ? 'active' : ''); ?>"><i class="fa fa-chart-bar"></i> Reports</a>
                    <a href="<?php echo e(route('owner.admins.index')); ?>" class="<?php echo e(($activePage ?? '') === 'staff' ? 'active' : ''); ?>"><i class="fa fa-user-shield"></i> Staff</a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php break; ?>

                <?php case ('Staff'): ?>
                    <a href="<?php echo e(route('staff.dashboard')); ?>" class="<?php echo e(($activePage ?? '') === 'dashboard' ? 'active' : ''); ?>"><i class="fa fa-gauge"></i> Dashboard</a>
                    <a href="<?php echo e(route('staff.houses.index')); ?>" class="<?php echo e(in_array($activePage ?? '', ['houses', 'units'], true) ? 'active' : ''); ?>"><i class="fa fa-building"></i> Properties</a>
                    <a href="<?php echo e(route('staff.bookings.index')); ?>" class="<?php echo e(($activePage ?? '') === 'bookings' ? 'active' : ''); ?>"><i class="fa fa-calendar-check"></i> Bookings</a>
                    <a href="<?php echo e(route('staff.bookings.index', ['section' => 'calendar'])); ?>" class="<?php echo e(($activePage ?? '') === 'calendar' ? 'active' : ''); ?>"><i class="fa fa-calendar"></i> Calendar</a>
                    <a href="<?php echo e(route('staff.bookings.index', ['section' => 'reports'])); ?>" class="<?php echo e(($activePage ?? '') === 'reports' ? 'active' : ''); ?>"><i class="fa fa-chart-bar"></i> Reports</a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php break; ?>

                <?php case ('Guest'): ?>
                    <a href="<?php echo e(request()->routeIs('home') ? '#home' : route('home').'#home'); ?>" data-scroll-nav="home" class="<?php echo e(($activePage ?? '') === 'home' ? 'active' : ''); ?>"><i class="fa fa-home"></i> Home</a>
                    <a href="<?php echo e(route('home').'#transients'); ?>" data-scroll-nav="transients" class="<?php echo e(($activePage ?? '') === 'transients' ? 'active' : ''); ?>"><i class="fa fa-search"></i> Transients</a>
                    <a href="<?php echo e(route('guest.dashboard')); ?>" class="<?php echo e(($activePage ?? '') === 'dashboard' ? 'active' : ''); ?>"><i class="fa fa-calendar-check"></i> Bookings</a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php break; ?>
            <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php else: ?>
            <a href="<?php echo e(request()->routeIs('home') ? '#home' : route('home').'#home'); ?>" data-scroll-nav="home" class="<?php echo e(($activePage ?? '') === 'home' ? 'active' : ''); ?>"><i class="fa fa-home"></i> Home</a>
            <a href="<?php echo e(route('home').'#transients'); ?>" data-scroll-nav="transients" class="<?php echo e(($activePage ?? '') === 'transients' ? 'active' : ''); ?>"><i class="fa fa-search"></i> Transients</a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="topnav-right">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($navUser): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($roleName, ['System Admin', 'Owner', 'Staff', 'Guest'], true)): ?>
                <?php echo $__env->make('partials.notification-bell', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php echo $__env->make('partials.profile-menu', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <a href="<?php echo e(route('login')); ?>" class="btn-outline-sm">Log in</a>
            <a href="<?php echo e(route('register')); ?>" class="btn-primary-sm">Register</a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</nav><?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/partials/nav.blade.php ENDPATH**/ ?>
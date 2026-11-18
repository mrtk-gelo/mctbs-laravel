<div class="nav-dropdown profile-dropdown">
    <button
        class="profile-btn"
        type="button"
        aria-controls="profileMenu"
        aria-expanded="false"
        onclick="const isOpen = this.closest('.nav-dropdown').classList.toggle('open'); this.setAttribute('aria-expanded', isOpen)"
    >
        <span class="avatar-initials"><?php echo e($navUser->initials()); ?></span>
        <span><?php echo e($navUser->first_name); ?></span>
        <i class="fa fa-chevron-down fa-xs"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-right" id="profileMenu">
        <div class="profile-menu-header">
            <div class="profile-menu-name"><?php echo e($navUser->first_name); ?> <?php echo e($navUser->last_name); ?></div>
            <div class="profile-menu-role"><?php echo e($navUser->role?->displayName() ?? 'Member'); ?></div>
        </div>
        <a href="<?php echo e(route('profile.edit')); ?>"><i class="fa fa-user-pen"></i> Profile</a>
        <a href="<?php echo e(route('profile.security')); ?>"><i class="fa fa-shield-halved"></i> Session &amp; account</a>
        <hr>
        <form method="POST" action="<?php echo e(route('logout')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit" class="text-danger"><i class="fa fa-right-from-bracket"></i> Logout</button>
        </form>
    </div>
</div>
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/partials/profile-menu.blade.php ENDPATH**/ ?>
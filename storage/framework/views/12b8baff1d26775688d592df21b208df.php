<div class="notif-wrapper" wire:poll.15s.visible>
    <button
        class="notif-btn"
        type="button"
        data-notification-toggle
        aria-label="Notifications"
        aria-expanded="false"
    >
        <i class="fa fa-bell"></i>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unreadNotificationCount > 0): ?>
            <span class="notif-badge"><?php echo e($unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </button>
    <div class="notif-dropdown">
        <div class="notif-header">
            <span>Notifications</span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unreadNotificationCount > 0): ?>
                <button type="button" class="mark-all-read" wire:click="markAllRead">Mark all read</button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="notif-list">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentNotifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <a
                    class="notif-item <?php echo e($notification->is_read ? '' : 'unread'); ?>"
                    href="<?php echo e(route('notifications.open', $notification->notification_id)); ?>"
                >
                    <div class="notif-title"><?php echo e($notification->title); ?></div>
                    <div class="notif-msg"><?php echo e($notification->message); ?></div>
                    <div class="notif-time"><?php echo e($notification->created_at?->diffForHumans()); ?></div>
                </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="notif-loading">You have no notifications.</div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/livewire/notification-bell.blade.php ENDPATH**/ ?>
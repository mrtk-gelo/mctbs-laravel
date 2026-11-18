<style>
    .system-message-modal {
        position: fixed;
        inset: 0;
        max-height: calc(100vh - 32px);
        overflow: auto;
        width: min(92vw, 440px);
        margin: auto;
        padding: 0;
        border: 1px solid var(--border, #d1d5db);
        border-radius: 16px;
        color: var(--text, #111827);
        background: var(--card, #fff);
        box-shadow: 0 20px 60px rgb(0 0 0 / 24%);
    }
    .system-message-modal::backdrop { background: rgb(15 23 42 / 55%); backdrop-filter: blur(2px); }
    .system-message-content { display: grid; justify-items: center; gap: 12px; padding: 28px; text-align: center; }
    .system-message-icon { color: var(--success, #22c55e); font-size: 38px; }
    .system-message-icon.is-error { color: var(--danger, #ef4444); }
    .system-message-content h2, .system-message-content p { margin: 0; }
    .system-message-content .btn {
        padding: 9px 18px;
        border: 0;
        border-radius: 8px;
        background: var(--accent, #d97706);
        color: #fff;
        cursor: pointer;
    }
    .system-message-toast {
        position: fixed;
        z-index: 1100;
        top: 20px;
        left: 50%;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        width: min(92vw, 560px);
        padding: 14px 16px;
        transform: translateX(-50%);
        border: 1px solid var(--border, #d1d5db);
        border-radius: 10px;
        background: var(--card, #fff);
        color: var(--text, #111827);
        box-shadow: 0 8px 28px rgb(15 23 42 / 16%);
    }
    .system-message-toast.is-error { border-color: var(--danger, #ef4444); }
    .system-message-toast p { flex: 1; margin: 0; }
    .system-message-toast button {
        border: 0;
        background: transparent;
        color: inherit;
        cursor: pointer;
        font-size: 18px;
        line-height: 1;
    }
</style>

<?php
    $noticeType = collect(['success', 'error', 'warning', 'info', 'status'])
        ->first(fn (string $type): bool => session()->has($type));
    $noticeMessage = $noticeType ? session($noticeType) : ($errors->any() ? $errors->first() : null);
    $modalNotice = session('modal');
    $modalMessage = is_array($modalNotice) ? ($modalNotice['message'] ?? null) : $modalNotice;
    $modalTitle = is_array($modalNotice) ? ($modalNotice['title'] ?? 'Update') : 'Update';
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($modalMessage): ?>
    <dialog class="system-message-modal" data-auto-open-dialog aria-labelledby="system-message-title">
        <div class="system-message-content">
            <span class="system-message-icon is-success" aria-hidden="true"><i class="fa fa-circle-check"></i></span>
            <h2 id="system-message-title"><?php echo e($modalTitle); ?></h2>
            <p><?php echo e($modalMessage); ?></p>
            <form method="dialog">
                <button type="submit" class="btn btn-primary">OK</button>
            </form>
        </div>
    </dialog>
<?php elseif($noticeMessage): ?>
    <div class="system-message-toast <?php echo e($noticeType === 'error' || $errors->any() ? 'is-error' : ''); ?>" role="<?php echo e($noticeType === 'error' || $errors->any() ? 'alert' : 'status'); ?>">
        <p><?php echo e($noticeMessage); ?></p>
        <button type="button" data-dismiss-notice aria-label="Dismiss notification">&times;</button>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/partials/system-interface.blade.php ENDPATH**/ ?>
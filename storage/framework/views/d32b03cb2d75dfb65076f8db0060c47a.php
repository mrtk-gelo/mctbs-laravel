<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Session &amp; Account | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Session &amp; Account | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> 
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($user->role->name):
            case ('System Admin'): ?>
                <?php echo $__env->make('partials.sysadmin-nav', ['activePage' => 'profile'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php break; ?>
            <?php case ('Owner'): ?>
                <?php echo $__env->make('partials.owner-nav', ['activePage' => 'profile'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php break; ?>
            <?php case ('Staff'): ?>
                <?php echo $__env->make('partials.staff-nav', ['activePage' => 'profile'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php break; ?>
            <?php default: ?>
                <?php echo $__env->make('partials.nav', ['activePage' => 'profile'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="container workspace-page profile-page">
        <div class="page-header">
            <h1>Session &amp; account</h1>
            <p>Change your password independently from your profile details.</p>
        </div>



        <section class="card profile-settings-card">
            <form method="POST" action="<?php echo e(route('profile.password.update')); ?>" class="profile-settings-form">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="card-header"><span><i class="fa fa-shield-halved"></i> Password</span></div>
                <div class="card-body">
                    <p class="profile-section-intro">Use a strong password you have not used elsewhere.</p>
                    <div class="form-group">
                        <label for="current_password">Current password <span class="field-requirement">Required</span></label>
                        <input id="current_password" type="password" name="current_password" autocomplete="current-password" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="flex:1">
                            <label for="password">New password <span class="field-requirement">Required</span></label>
                            <input id="password" type="password" name="password" autocomplete="new-password" required>
                            <small class="field-hint">At least 8 characters, with uppercase and lowercase letters, a number, and a symbol.</small>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label for="password_confirmation">Confirm new password <span class="field-requirement">Required</span></label>
                            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                        </div>
                    </div>
                </div>
                <div class="profile-save-bar">
                    <a class="btn btn-outline" href="<?php echo e(route('profile.edit')); ?>">Back to profile</a>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-key"></i> Update password</button>
                </div>
            </form>
        </section>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcffbe109f495178c1251f7f6dc1fe742)): ?>
<?php $attributes = $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742; ?>
<?php unset($__attributesOriginalcffbe109f495178c1251f7f6dc1fe742); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcffbe109f495178c1251f7f6dc1fe742)): ?>
<?php $component = $__componentOriginalcffbe109f495178c1251f7f6dc1fe742; ?>
<?php unset($__componentOriginalcffbe109f495178c1251f7f6dc1fe742); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/auth/security.blade.php ENDPATH**/ ?>
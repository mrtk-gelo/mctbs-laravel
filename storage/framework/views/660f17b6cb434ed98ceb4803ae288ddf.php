<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Edit Profile | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Edit Profile | MCTBS']); ?>
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
            <h1>Profile</h1>
            <p>Manage your personal information and contact details.</p>
        </div>



        <section class="card profile-settings-card" id="profile-information">
            <form method="POST" action="<?php echo e(route('profile.update')); ?>" class="profile-settings-form">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="card-header"><span><i class="fa fa-user-pen"></i> Profile information</span></div>
                <div class="card-body">
                    <p class="profile-section-intro">These details are shared with guests and teammates when you manage a booking or property.</p>
                    <div class="profile-identity">
                        <span class="avatar-initials" aria-hidden="true"><?php echo e($user->initials()); ?></span>
                        <span><strong><?php echo e($user->first_name); ?> <?php echo e($user->last_name); ?></strong><small>Your initials are used throughout MCTBS.</small></span>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="flex:1">
                            <label class="required" for="first_name">First Name <span class="field-requirement">Required</span></label>
                            <input id="first_name" name="first_name" value="<?php echo e(old('first_name', $user->first_name)); ?>" maxlength="100" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="required" for="last_name">Last Name <span class="field-requirement">Required</span></label>
                            <input id="last_name" name="last_name" value="<?php echo e(old('last_name', $user->last_name)); ?>" maxlength="100" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address <span class="field-requirement">Required</span></label>
                        <input id="email" type="email" value="<?php echo e($user->email); ?>" readonly>
                        <small class="text-muted">Email changes are disabled so account verification remains valid.</small>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone <span class="field-requirement">Optional</span></label>
                        <input id="phone" name="phone" value="<?php echo e(old('phone', $user->phone)); ?>" maxlength="30">
                    </div>
                </div>
                <div class="profile-save-bar">
                    <span class="text-muted fs-sm">Your password is not required to update profile details.</span>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk"></i> Save profile</button>
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
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/auth/profile.blade.php ENDPATH**/ ?>
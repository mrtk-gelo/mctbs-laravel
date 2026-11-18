<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Staff Management | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Staff Management | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> <?php echo $__env->make('partials.owner-nav', ['activePage' => 'staff'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php $__env->endSlot(); ?>

    <div class="container workspace-page staff-management-page">
        <div class="page-header-row page-header">
            <div>
                <h1>Staff &amp; access</h1>
                <p>Invite trusted teammates and manage which property they can help you operate.</p>
            </div>
            <a href="#invite-staff" class="btn btn-primary"><i class="fa fa-user-plus"></i> Invite staff</a>
        </div>



        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('invite_link')): ?>
            <div class="alert alert-info invitation-link-alert mb-3">
                <strong>[Dev Mode] Invitation link (no mail sending yet):</strong>
                <a href="<?php echo e(session('invite_link')); ?>"><?php echo e(session('invite_link')); ?></a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="card mb-3 staff-management-card" id="invite-staff">
            <div class="card-header"><span><i class="fa fa-paper-plane"></i> Invite a staff member</span></div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($houses->isEmpty()): ?>
                    <p class="text-muted">You need a transient house before inviting staff.</p>
                <?php else: ?>
                    <form method="POST" action="<?php echo e(route('owner.admins.invite')); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="form-row">
                            <div class="form-group" style="flex:2">
                                <label class="required">Staff's email address</label>
                                <input type="email" name="email" value="<?php echo e(old('email')); ?>" required>
                            </div>
                            <div class="form-group" style="flex:1">
                                <label class="required">Assign to house</label>
                                <select name="house_id" required>
                                    <option value="">— Select a house —</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $houses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $house): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($house->house_id); ?>" <?php if(old('house_id') == $house->house_id): echo 'selected'; endif; ?>><?php echo e($house->name); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Invite</button>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="card staff-management-card">
            <div class="card-header"><span><i class="fa fa-users"></i> Your staff</span></div>
            <div class="card-body">
                <div class="staff-list">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $admins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="staff-card">
                        <div class="staff-card-avatar" aria-hidden="true">
                            <?php echo e($entry->invitee?->initials() ?? 'ST'); ?>

                        </div>
                        <div class="staff-card-info">
                            <strong><?php echo e(trim(($entry->invitee?->first_name ?? '').' '.($entry->invitee?->last_name ?? '')) ?: 'Staff member'); ?></strong>
                            <span><?php echo e($entry->email); ?></span>
                        </div>
                        <div class="staff-card-house">
                            <i class="fa fa-building"></i>
                            <?php echo e($entry->house->name ?? 'No house assigned'); ?>

                        </div>
                        <form class="staff-card-actions" method="POST" action="<?php echo e(route('owner.admins.remove', $entry)); ?>" onsubmit="return confirm('Remove this staff member?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-user-minus"></i> Remove</button>
                        </form>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="text-muted">No staff yet.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
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
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/owner/admins.blade.php ENDPATH**/ ?>
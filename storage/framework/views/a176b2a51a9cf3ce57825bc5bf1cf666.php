<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Owner Management | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Owner Management | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> <?php echo $__env->make('partials.sysadmin-nav', ['activePage' => 'owners'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php $__env->endSlot(); ?>

    <div class="container workspace-page">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div class="alert alert-success" role="status"><?php echo e(session('success')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
            <div class="alert alert-danger" role="alert"><?php echo e(session('error')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo e($errors->first()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="page-header-row page-header">
            <div>
                <h1>Owner Management</h1>
                <p>Manage owner accounts and invitations. Account Managers do not manage property operations.</p>
            </div>
            <a class="btn btn-primary" href="#invite-owner"><i class="fa fa-user-plus"></i> Invite an Owner</a>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('invite_link')): ?>
            <div class="alert alert-info invitation-link-alert mb-3">
                <div><strong>Invitation link ready</strong><span>Development mode: share this link with the invited owner.</span></div>
                <a href="<?php echo e(session('invite_link')); ?>"><?php echo e(session('invite_link')); ?></a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <section class="card invite-owner-card mb-3" id="invite-owner">
            <div class="card-header"><span><i class="fa fa-paper-plane"></i> Invite an owner</span></div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('sysadmin.owners.invite')); ?>" class="owner-invite-form">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label class="required" for="owner-email">Email address</label>
                        <input id="owner-email" type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="owner@example.com" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Create invitation</button>
                </form>
            </div>
        </section>

        <section class="card personnel-table-card mb-3">
            <div class="card-header">
                <span>Registered owners</span>
            </div>
            <div class="personnel-table-toolbar">
                <div class="personnel-owner-actions">
                    <form method="GET" action="<?php echo e(route('sysadmin.owners.index')); ?>" class="personnel-search" role="search" data-ajax-search data-results="#owner-results">
                        <i class="fa fa-search"></i>
                        <input type="text" name="q" value="<?php echo e($search); ?>" maxlength="100" placeholder="Search owners" aria-label="Search owners">
                        <p class="sr-only" data-search-status aria-live="polite"></p>
                        <p class="alert alert-danger mt-2" data-search-error hidden></p>
                    </form>
                    <form id="bulk-owner-delete-form" method="POST" action="<?php echo e(route('sysadmin.owners.bulk-destroy')); ?>" onsubmit="return confirm('Delete the selected owner accounts? Accounts linked to protected records cannot be deleted.')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button class="btn btn-danger btn-sm" type="submit"><i class="fa fa-trash"></i> Delete selected</button>
                    </form>
                </div>
            </div>
            <?php echo $__env->make('sysadmin.partials.owner-results', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </section>

        <section class="card personnel-table-card" id="owner-invitations">
            <div class="card-header">
                <span>Pending owner invitations</span>
                <span class="text-muted fs-sm"><?php echo e($invitations->count()); ?> awaiting registration</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Email</th><th>Expires</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $invitations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invitation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td><strong><?php echo e($invitation->email); ?></strong></td>
                                <td><?php echo e($invitation->expires_at->format('M j, Y g:i A')); ?></td>
                                <td><span class="badge badge-pending">Awaiting registration</span></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="3" class="empty-table-cell">No pending invitations.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
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
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/sysadmin/owners.blade.php ENDPATH**/ ?>
<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Transient Houses | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Transient Houses | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> 
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role->name === 'Staff'): ?>
            <?php echo $__env->make('partials.staff-nav', ['activePage' => 'houses'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('partials.owner-nav', ['activePage' => 'houses'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="container" style="margin-top:24px">
        <div class="page-header-row page-header mb-3">
            <div>
                <h1>Transient Houses</h1>
                <p>Manage your transient houses and units.</p>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role->name === 'Owner'): ?>
                <a href="<?php echo e(route('owner.houses.create')); ?>" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Add Transient House
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <h2 class="management-section-title"><?php echo e(auth()->user()->role->name === 'Owner' ? 'Your Houses' : 'Assigned Houses'); ?></h2>
        <div class="management-property-list">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $houses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $house): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="management-property-card unit-card">
                    <div class="management-property-image">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($house->cover_photo): ?>
                            <img class="unit-card-img" src="<?php echo e(asset('storage/' . $house->cover_photo)); ?>" alt="<?php echo e($house->name); ?> cover photo">
                        <?php else: ?>
                            <div class="unit-card-img-placeholder" aria-label="No cover photo">
                                <i class="fa fa-building"></i>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="management-property-content unit-card-body house-card-body">
                        <div class="management-property-details">
                            <div>
                                <span class="management-property-eyebrow">Transient House</span>
                                <h2><?php echo e($house->name); ?></h2>
                                <p class="management-property-location">
                                    <i class="fa fa-location-dot"></i>
                                    <?php echo e($house->address); ?><?php echo e($house->barangay ? ', '.$house->barangay : ''); ?>, <?php echo e($house->city); ?>

                                </p>
                            </div>
                            <form method="POST" action="<?php echo e(route(auth()->user()->role->name === 'Staff' ? 'staff.houses.availability' : 'owner.houses.availability', $house)); ?>" class="availability-control">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <input type="hidden" name="is_active" value="0">
                                <label class="availability-switch">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        aria-label="<?php echo e($house->is_active ? 'Hide house from public site' : 'Show house on public site'); ?>"
                                        <?php if($house->is_active): echo 'checked'; endif; ?>
                                        onchange="this.form.requestSubmit()"
                                    >
                                    <span class="availability-slider"></span>
                                </label>
                                <span class="availability-label"><?php echo e($house->is_active ? 'Public' : 'Hidden'); ?></span>
                            </form>
                        </div>
                        <div class="management-property-footer house-card-meta">
                            <span class="management-property-meta">
                                <i class="fa fa-door-open"></i>
                                <?php echo e($house->units_count); ?> unit<?php echo e($house->units_count === 1 ? '' : 's'); ?>

                            </span>
                            <div class="management-property-actions">
                                <a href="<?php echo e(route(auth()->user()->role->name === 'Staff' ? 'staff.houses.edit' : 'owner.houses.edit', $house)); ?>" class="btn btn-outline btn-sm">
                                    <i class="fa fa-pen"></i> Edit
                                </a>
                                <a href="<?php echo e(route(auth()->user()->role->name === 'Staff' ? 'staff.houses.units' : 'owner.houses.units', $house)); ?>" class="btn btn-primary btn-sm">
                                    Manage Units <i class="fa fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-0">No transient houses found.</p>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/owner/houses/index.blade.php ENDPATH**/ ?>
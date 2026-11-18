<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Manage Units | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Manage Units | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> 
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role->name === 'Staff'): ?>
            <?php echo $__env->make('partials.staff-nav', ['activePage' => 'units'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('partials.owner-nav', ['activePage' => 'units'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="container" style="margin-top:24px">
        <div class="flex-between mb-3">
            <div>
                <h1 class="mb-1"><?php echo e($house->name); ?></h1>
                <p class="text-muted"><?php echo e($house->address); ?><?php echo e($house->barangay ? ', '.$house->barangay : ''); ?>, <?php echo e($house->city); ?></p>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role->name === 'Owner'): ?>
                <a href="<?php echo e(route('owner.houses.units.create', $house)); ?>" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Add Unit
                </a>
            <?php else: ?>
                <a href="<?php echo e(route('staff.houses.units.create', $house)); ?>" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Add Unit
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <h2 class="management-section-title">Units</h2>
        <div class="management-property-list">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="management-property-card unit-card">
                    <div class="management-property-image">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unit->photos->isNotEmpty()): ?>
                            <img class="unit-card-img" src="<?php echo e(asset('storage/' . $unit->photos->first()->photo_path)); ?>" alt="<?php echo e($unit->unit_type); ?> photo">
                        <?php elseif($house->cover_photo): ?>
                            <img class="unit-card-img" src="<?php echo e(asset('storage/' . $house->cover_photo)); ?>" alt="<?php echo e($unit->unit_type); ?> at <?php echo e($house->name); ?>">
                        <?php else: ?>
                            <div class="unit-card-img-placeholder" aria-label="No house cover photo">
                                <i class="fa fa-door-open"></i>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="management-property-content unit-card-body house-card-body">
                        <div class="management-property-details">
                            <div>
                                <span class="management-property-eyebrow">Unit at <?php echo e($house->name); ?></span>
                                <h2><?php echo e($unit->unit_type); ?></h2>
                                <p class="management-property-location">
                                    <i class="fa fa-users"></i> <?php echo e($unit->capacity); ?> standard guests; max <?php echo e($unit->maximum_capacity ?? $unit->capacity); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((int) ($unit->maximum_capacity ?? $unit->capacity) > (int) $unit->capacity): ?>
                                        · extra <?php echo e(number_format((float) $unit->extra_guest_fee, 2)); ?>/guest/night
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unit->description): ?>
                                    <p class="management-property-description"><?php echo e($unit->description); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($unit->status, ['Available', 'Unavailable'], true)): ?>
                                <form method="POST" action="<?php echo e(route(auth()->user()->role->name === 'Staff' ? 'staff.houses.units.availability' : 'owner.houses.units.availability', [$house, $unit])); ?>" class="availability-control">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <input type="hidden" name="status" value="Unavailable">
                                    <label class="availability-switch">
                                        <input
                                            type="checkbox"
                                            name="status"
                                            value="Available"
                                            aria-label="<?php echo e($unit->status === 'Available' ? 'Hide unit from public site' : 'Show unit on public site'); ?>"
                                            <?php if($unit->status === 'Available'): echo 'checked'; endif; ?>
                                            onchange="this.form.requestSubmit()"
                                        >
                                        <span class="availability-slider"></span>
                                    </label>
                                    <span class="availability-label"><?php echo e($unit->status === 'Available' ? 'Public' : 'Hidden'); ?></span>
                                </form>
                            <?php else: ?>
                                <span class="badge badge-booked">Booked</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="management-property-footer house-card-meta">
                            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                                <span class="management-property-price">₱<?php echo e(number_format($unit->price, 2)); ?> <small>/ night</small></span>
                                <span class="management-property-meta"><i class="fa fa-images"></i> <?php echo e($unit->photos->count()); ?> photo<?php echo e($unit->photos->count() === 1 ? '' : 's'); ?></span>
                            </div>
                            <a href="<?php echo e(route(auth()->user()->role->name === 'Staff' ? 'staff.houses.units.edit' : 'owner.houses.units.edit', [$house, $unit])); ?>" class="btn btn-outline btn-sm">
                                <i class="fa fa-pen"></i> Edit Unit
                            </a>
                        </div>
                    </div>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-0">No units have been added to this house yet.</p>
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
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/owner/houses/units.blade.php ENDPATH**/ ?>
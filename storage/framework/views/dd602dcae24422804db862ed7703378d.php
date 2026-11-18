<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Add Unit | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Add Unit | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> 
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role->name === 'Staff'): ?>
            <?php echo $__env->make('partials.staff-nav', ['activePage' => 'units'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('partials.owner-nav', ['activePage' => 'units'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="container" style="margin-top:24px;max-width:900px">
        <div class="page-header">
            <h1>Add Unit</h1>
            <p><?php echo e($house->name); ?> · <?php echo e($house->city); ?></p>
        </div>



        <div class="card">
            <div class="card-header">Unit Details</div>
            <div class="card-body">
                <?php
                    $routeName = auth()->user()->role->name === 'Staff'
                        ? 'staff.houses.units.store'
                        : 'owner.houses.units.store';
                ?>
                <form method="POST" action="<?php echo e(route($routeName, $house)); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="form-row">
                        <div class="form-group" style="flex:2">
                            <label class="required">Unit type</label>
                            <input type="text" name="unit_type" value="<?php echo e(old('unit_type')); ?>" maxlength="100" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="required">Status</label>
                            <select name="status" required>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['Available', 'Unavailable', 'Booked']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($status); ?>" <?php if(old('status', 'Available') === $status): echo 'selected'; endif; ?>><?php echo e($status); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="flex:1">
                            <label class="required">Capacity</label>
                            <input type="number" name="capacity" value="<?php echo e(old('capacity', 1)); ?>" min="1" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="required" for="maximum_capacity">Maximum counted guests</label>
                            <input id="maximum_capacity" type="number" name="maximum_capacity" value="<?php echo e(old('maximum_capacity', old('capacity', 1))); ?>" min="<?php echo e(old('capacity', 1)); ?>" required>
                            <p class="form-hint">The maximum total guests, including free children.</p>
                        </div>
                    </div>
                    <div class="form-row unit-time-fields">
                        <div class="form-group" style="flex:1">
                            <label class="required" for="check_in_time">Check-in time</label>
                            <input id="check_in_time" type="time" name="check_in_time" value="<?php echo e(old('check_in_time', '14:00')); ?>" required>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['check_in_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="required" for="check_out_time">Check-out time</label>
                            <input id="check_out_time" type="time" name="check_out_time" value="<?php echo e(old('check_out_time', '12:00')); ?>" required>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['check_out_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <?php echo $__env->make('owner.houses.partials.unit-pricing-fields', [
                        'pricingMode' => old('pricing_mode', 'Per Unit'),
                        'price' => old('price'),
                        'extraGuestFee' => old('extra_guest_fee', 0),
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="form-group">
                        <label for="child_free_through_age">Free child age limit <span class="text-muted fs-sm">(optional)</span></label>
                        <input id="child_free_through_age" type="number" name="child_free_through_age" value="<?php echo e(old('child_free_through_age')); ?>" min="0" max="17" placeholder="Leave blank if no free-child age">
                        <p class="form-hint">Children through this age are free but still count toward maximum capacity. For example, enter 3 for ages 0–3.</p>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4"><?php echo e(old('description')); ?></textarea>
                    </div>
                    <?php echo $__env->make('owner.houses.partials.unit-extra-items', [
                        'addons' => old('addons', []),
                        'damageItems' => old('damage_items', []),
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="form-group">
                        <label for="photos">Unit Photos</label>
                        <input id="photos" type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple>
                        <p class="form-hint">Add up to 10 JPG, PNG, or WebP photos, maximum 5 MB each. The first photo is the main photo guests see.</p>
                    </div>
                    <div style="display:flex;gap:10px">
                        <button type="submit" class="btn btn-primary">Save Unit</button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role->name === 'Staff'): ?>
                            <a href="<?php echo e(route('staff.houses.units', $house)); ?>" class="btn btn-outline">Cancel</a>
                        <?php else: ?>
                            <a href="<?php echo e(route('owner.houses.units', $house)); ?>" class="btn btn-outline">Cancel</a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </form>
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
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/owner/houses/create-unit.blade.php ENDPATH**/ ?>
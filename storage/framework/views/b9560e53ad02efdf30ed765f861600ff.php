<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Edit Unit | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Edit Unit | MCTBS']); ?>
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
            <h1>Edit Unit</h1>
            <p><?php echo e($house->name); ?> · <?php echo e($house->address); ?><?php echo e($house->barangay ? ', '.$house->barangay : ''); ?>, <?php echo e($house->city); ?></p>
        </div>



        <div class="card">
            <div class="card-header">Unit Details</div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route(auth()->user()->role->name === 'Staff' ? 'staff.houses.units.update' : 'owner.houses.units.update', [$house, $unit])); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="form-row">
                        <div class="form-group" style="flex:2">
                            <label class="required" for="unit_type">Unit type</label>
                            <input id="unit_type" type="text" name="unit_type" value="<?php echo e(old('unit_type', $unit->unit_type)); ?>" maxlength="100" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="required" for="status">Public availability</label>
                            <select id="status" name="status" required>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['Available', 'Unavailable', 'Booked']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($status); ?>" <?php if(old('status', $unit->status) === $status): echo 'selected'; endif; ?>>
                                        <?php echo e($status === 'Available' ? 'Available to the public' : ($status === 'Unavailable' ? 'Hidden from the public' : $status)); ?>

                                    </option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="flex:1">
                            <label class="required" for="capacity">Capacity</label>
                            <input id="capacity" type="number" name="capacity" value="<?php echo e(old('capacity', $unit->capacity)); ?>" min="1" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="required" for="maximum_capacity">Maximum counted guests</label>
                            <input id="maximum_capacity" type="number" name="maximum_capacity" value="<?php echo e(old('maximum_capacity', $unit->maximum_capacity ?? $unit->capacity)); ?>" min="<?php echo e(old('capacity', $unit->capacity)); ?>" required>
                            <p class="form-hint">The maximum total guests, including free children.</p>
                        </div>
                    </div>
                    <div class="form-row unit-time-fields">
                        <div class="form-group" style="flex:1">
                            <label class="required" for="check_in_time">Check-in time</label>
                            <input id="check_in_time" type="time" name="check_in_time" value="<?php echo e(old('check_in_time', substr((string) $unit->check_in_time, 0, 5))); ?>" required>
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
                            <input id="check_out_time" type="time" name="check_out_time" value="<?php echo e(old('check_out_time', substr((string) $unit->check_out_time, 0, 5))); ?>" required>
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
                        'pricingMode' => old('pricing_mode', $unit->pricing_mode),
                        'price' => old('price', $unit->price),
                        'extraGuestFee' => old('extra_guest_fee', $unit->extra_guest_fee ?? 0),
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="form-group">
                        <label for="child_free_through_age">Free child age limit <span class="text-muted fs-sm">(optional)</span></label>
                        <input id="child_free_through_age" type="number" name="child_free_through_age" value="<?php echo e(old('child_free_through_age', $unit->child_free_through_age)); ?>" min="0" max="17" placeholder="Leave blank if no free-child age">
                        <p class="form-hint">Children through this age are free but still count toward maximum capacity. For example, enter 3 for ages 0–3.</p>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?php echo e(old('description', $unit->description)); ?></textarea>
                    </div>
                    <?php echo $__env->make('owner.houses.partials.unit-extra-items', [
                        'addons' => old('addons', $unit->addons->map(fn ($addon) => ['name' => $addon->name, 'price' => $addon->price])->all()),
                        'damageItems' => old('damage_items', $unit->damageItems->map(fn ($item) => ['name' => $item->name, 'price' => $item->price])->all()),
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="form-group">
                        <label>Current Unit Photos</label>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unit->photos->isNotEmpty()): ?>
                            <div class="unit-photo-management-grid">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $unit->photos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <label class="unit-photo-management-item">
                                        <img src="<?php echo e(asset('storage/' . $photo->photo_path)); ?>" alt="Unit photo <?php echo e($loop->iteration); ?>">
                                        <span class="unit-photo-management-caption">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($photo->is_cover): ?>
                                                <strong>Main photo</strong>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <span><input type="checkbox" name="remove_photos[]" value="<?php echo e($photo->photo_id); ?>"> Remove</span>
                                        </span>
                                    </label>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No unit photos uploaded yet.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="photos">Add More Photos</label>
                        <input id="photos" type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple>
                        <p class="form-hint">The unit can have up to 10 photos total. JPG, PNG, or WebP; maximum 5 MB each.</p>
                    </div>
                    <div style="display:flex;gap:10px">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk"></i> Save Changes</button>
                        <a href="<?php echo e(route(auth()->user()->role->name === 'Staff' ? 'staff.houses.units' : 'owner.houses.units', $house)); ?>" class="btn btn-outline">Cancel</a>
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
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/owner/houses/edit-unit.blade.php ENDPATH**/ ?>
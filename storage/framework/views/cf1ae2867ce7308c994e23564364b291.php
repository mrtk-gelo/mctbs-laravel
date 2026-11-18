<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Edit Transient House | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Edit Transient House | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> 
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role->name === 'Staff'): ?>
            <?php echo $__env->make('partials.staff-nav', ['activePage' => 'houses'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('partials.owner-nav', ['activePage' => 'houses'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="container" style="margin-top:24px;max-width:900px">
        <div class="page-header">
            <h1>Edit Transient House</h1>
            <p>Update the details for <?php echo e($house->name); ?>.</p>
        </div>



        <div class="card">
            <div class="card-header">House Details</div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route(auth()->user()->role->name === 'Staff' ? 'staff.houses.update' : 'owner.houses.update', $house)); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="form-row">
                        <div class="form-group" style="flex:1">
                            <label class="required" for="name">House name</label>
                            <input id="name" type="text" name="name" value="<?php echo e(old('name', $house->name)); ?>" maxlength="255" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="required" for="city">City</label>
                            <input id="city" type="text" name="city" value="<?php echo e(old('city', $house->city)); ?>" maxlength="100" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="flex:2">
                            <label class="required" for="address">Address</label>
                            <input id="address" type="text" name="address" value="<?php echo e(old('address', $house->address)); ?>" maxlength="500" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label for="barangay">Barangay</label>
                            <input id="barangay" type="text" name="barangay" value="<?php echo e(old('barangay', $house->barangay)); ?>" maxlength="100">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="required" for="contact_number">Contact number</label>
                        <input id="contact_number" type="text" name="contact_number" value="<?php echo e(old('contact_number', $house->contact_number)); ?>" maxlength="30" required>
                    </div>
                    <?php echo $__env->make('owner.houses.partials.social-links-fields', ['socialLinks' => $house->social_links ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?php echo e(old('description', $house->description)); ?></textarea>
                    </div>
                    <?php echo $__env->make('owner.houses.partials.policy-fields', ['policies' => $house->policies], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="form-group">
                        <label for="cover_photo">Cover photo</label>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($house->cover_photo && ! old('remove_cover_photo')): ?>
                            <div class="mb-2">
                                <img src="<?php echo e(asset('storage/' . $house->cover_photo)); ?>" alt="<?php echo e($house->name); ?> cover photo" style="width:180px;height:120px;object-fit:cover;border-radius:8px">
                            </div>
                            <label class="checkbox-group">
                                <input type="checkbox" name="remove_cover_photo" value="1" <?php if(old('remove_cover_photo')): echo 'checked'; endif; ?>>
                                <span>Remove current cover photo</span>
                            </label>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <input id="cover_photo" type="file" name="cover_photo" accept="image/jpeg,image/png,image/webp">
                        <p class="form-hint">Upload a new JPG, PNG, or WebP image to replace the current one; maximum 5 MB.</p>
                    </div>
                    <div style="display:flex;gap:10px">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk"></i> Save Changes</button>
                        <a href="<?php echo e(route(auth()->user()->role->name === 'Staff' ? 'staff.houses.index' : 'owner.houses.index')); ?>" class="btn btn-outline">Cancel</a>
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
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/owner/houses/edit.blade.php ENDPATH**/ ?>
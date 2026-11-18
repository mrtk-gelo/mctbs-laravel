<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Add Transient House | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Add Transient House | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> <?php echo $__env->make('partials.owner-nav', ['activePage' => 'houses'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php $__env->endSlot(); ?>

    <div class="container" style="margin-top:24px;max-width:900px">
        <div class="page-header">
            <h1>Add Transient House</h1>
            <p>Enter the details for your new transient house.</p>
        </div>



        <div class="card">
            <div class="card-header">House Details</div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('owner.houses.store')); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="form-row">
                        <div class="form-group" style="flex:1">
                            <label class="required">House name</label>
                            <input type="text" name="name" value="<?php echo e(old('name')); ?>" maxlength="255" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="required">City</label>
                            <input type="text" name="city" value="<?php echo e(old('city')); ?>" maxlength="100" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="flex:2">
                            <label class="required">Address</label>
                            <input type="text" name="address" value="<?php echo e(old('address')); ?>" maxlength="500" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label>Barangay</label>
                            <input type="text" name="barangay" value="<?php echo e(old('barangay')); ?>" maxlength="100">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="required">Contact number</label>
                        <input type="text" name="contact_number" value="<?php echo e(old('contact_number')); ?>" maxlength="30" required>
                    </div>
                    <?php echo $__env->make('owner.houses.partials.social-links-fields', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4"><?php echo e(old('description')); ?></textarea>
                    </div>
                    <?php echo $__env->make('owner.houses.partials.policy-fields', ['policies' => []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="form-group">
                        <label>Cover photo</label>
                        <input type="file" name="cover_photo" accept="image/jpeg,image/png,image/webp">
                        <p class="form-hint">JPG, PNG, or WebP image; maximum 5 MB.</p>
                    </div>
                    <div style="display:flex;gap:10px">
                        <button type="submit" class="btn btn-primary">Save House</button>
                        <a href="<?php echo e(route('owner.houses.index')); ?>" class="btn btn-outline">Cancel</a>
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
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/owner/houses/create.blade.php ENDPATH**/ ?>
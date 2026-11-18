<?php
    $socialLinks = old('social_links', $socialLinks ?? []);
    $socialLinks = $socialLinks ?: [['name' => '', 'url' => '']];
?>

<div class="form-group social-links-fields">
    <label>Social media contacts</label>
    <p class="form-hint">Add the social media name and its profile or contact link. Only links you provide will appear to guests.</p>
    <div data-social-links>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $socialLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $socialLink): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="form-row social-link-row" data-social-link-row>
                <div class="form-group" style="flex:1">
                    <label for="social-name-<?php echo e($index); ?>">Social media name</label>
                    <input id="social-name-<?php echo e($index); ?>" type="text" name="social_links[<?php echo e($index); ?>][name]" value="<?php echo e($socialLink['name'] ?? ''); ?>" maxlength="50" placeholder="Facebook">
                </div>
                <div class="form-group" style="flex:2">
                    <label for="social-url-<?php echo e($index); ?>">Social media link</label>
                    <input id="social-url-<?php echo e($index); ?>" class="social-link-url" type="url" name="social_links[<?php echo e($index); ?>][url]" value="<?php echo e($socialLink['url'] ?? ''); ?>" maxlength="2048" placeholder="https://facebook.com/your-page">
                </div>
                <button type="button" class="btn btn-outline btn-sm" data-remove-social-link aria-label="Remove social media link">Remove</button>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['social_links'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="form-hint text-danger"><?php echo e($message); ?></p>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <button type="button" class="btn btn-outline btn-sm" data-add-social-link>Add social media link</button>
    <template data-social-link-template>
        <div class="form-row social-link-row" data-social-link-row>
            <div class="form-group" style="flex:1">
                <label>Social media name</label>
                <input type="text" name="social_links[__INDEX__][name]" maxlength="50" placeholder="Instagram">
            </div>
            <div class="form-group" style="flex:2">
                <label>Social media link</label>
                <input class="social-link-url" type="url" name="social_links[__INDEX__][url]" maxlength="2048" placeholder="https://instagram.com/your-page">
            </div>
            <button type="button" class="btn btn-outline btn-sm" data-remove-social-link aria-label="Remove social media link">Remove</button>
        </div>
    </template>
</div>
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/owner/houses/partials/social-links-fields.blade.php ENDPATH**/ ?>
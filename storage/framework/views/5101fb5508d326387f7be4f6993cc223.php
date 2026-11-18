<div class="form-row">
    <div class="form-group" style="flex:1">
        <label class="required" for="pricing_mode">Pricing mode</label>
        <select id="pricing_mode" name="pricing_mode" required>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['Per Unit', 'Per Head']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <option value="<?php echo e($mode); ?>" <?php if($pricingMode === $mode): echo 'selected'; endif; ?>><?php echo e($mode); ?></option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </select>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['pricing_mode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <div class="form-group" style="flex:1">
        <label class="required" for="price" data-pricing-label="Per Unit">Price per unit per night (₱)</label>
        <label class="required" for="price" data-pricing-label="Per Head" hidden>Price per paying head per night (₱)</label>
        <input id="price" type="number" name="price" value="<?php echo e($price); ?>" min="0" step="0.01" required>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<div class="form-group" id="extra-guest-fee-group" data-pricing-mode="Per Unit">
    <label class="required" for="extra_guest_fee">Extra guest fee per paying guest per night (₱)</label>
    <input id="extra_guest_fee" type="number" name="extra_guest_fee" value="<?php echo e($extraGuestFee); ?>" min="0" step="0.01" required>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['extra_guest_fee'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<script>
    const pricingModeSelect = document.getElementById('pricing_mode');
    const extraGuestFeeGroup = document.getElementById('extra-guest-fee-group');
    const extraGuestFeeInput = document.getElementById('extra_guest_fee');
    const pricingLabels = document.querySelectorAll('[data-pricing-label]');

    function updatePricingFields() {
        const isPerUnit = pricingModeSelect.value === 'Per Unit';
        extraGuestFeeGroup.hidden = !isPerUnit;
        extraGuestFeeGroup.style.display = isPerUnit ? '' : 'none';
        extraGuestFeeInput.disabled = !isPerUnit;
        pricingLabels.forEach((label) => {
            const isVisible = label.dataset.pricingLabel === pricingModeSelect.value;
            label.hidden = !isVisible;
            label.style.display = isVisible ? '' : 'none';
        });
    }

    pricingModeSelect.addEventListener('change', updatePricingFields);
    updatePricingFields();
</script>
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/owner/houses/partials/unit-pricing-fields.blade.php ENDPATH**/ ?>
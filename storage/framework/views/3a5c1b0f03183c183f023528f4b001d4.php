<div class="downpayment-livewire-form">
    <form id="downpayment-form" class="downpayment-form" wire:submit="submit">
        <section class="card">
            <div class="card-header">Choose payment method</div>
            <div class="card-body">
                <p class="form-hint">Select how you paid.</p>
                <div class="downpayment-method-tabs">
                    <input class="downpayment-method-input" id="downpayment-gcash" type="radio" wire:model="paymentMethod" value="GCash">
                    <label class="downpayment-method-tab" for="downpayment-gcash">
                        <i class="fa fa-mobile-alt" aria-hidden="true"></i> GCash
                    </label>
                    <input class="downpayment-method-input" id="downpayment-bank" type="radio" wire:model="paymentMethod" value="Bank Transfer">
                    <label class="downpayment-method-tab" for="downpayment-bank">
                        <i class="fa fa-university" aria-hidden="true"></i> Bank Transfer
                    </label>
                    <div class="policy-box downpayment-method-instructions downpayment-method-instructions-gcash">
                        Use the GCash details from the property. Enter the reference and attach proof.
                    </div>
                    <div class="policy-box downpayment-method-instructions downpayment-method-instructions-bank">
                        Use the bank details from the property. Enter the reference and attach proof.
                    </div>
                    <div class="policy-box downpayment-method-instructions downpayment-method-instructions-default">
                        Choose GCash or Bank Transfer for instructions.
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['paymentMethod'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <section class="card">
            <div class="card-header">Submit payment details</div>
            <div class="card-body">
                <div class="flex-between fw-bold fs-lg mb-2">
                    <span>Downpayment due</span>
                    <strong>₱<?php echo e(number_format($downpaymentAmount, 2)); ?></strong>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <li><?php echo e($error); ?></li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="form-group">
                    <label for="downpayment-amount">Amount (₱)</label>
                    <input id="downpayment-amount" type="number" inputmode="decimal" wire:model="amount" min="<?php echo e(number_format($downpaymentAmount, 2, '.', '')); ?>" max="<?php echo e(number_format($remainingBalance, 2, '.', '')); ?>" step="0.01" required>
                    <small class="form-hint">Minimum downpayment: ₱<?php echo e(number_format($downpaymentAmount, 2)); ?>.</small>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="downpayment-reference">Reference number</label>
                    <input id="downpayment-reference" type="text" wire:model.blur="referenceNumber" maxlength="100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['referenceNumber'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="downpayment-proof">Payment proof (JPG, PNG, or PDF)</label>
                    <input id="downpayment-proof" class="downpayment-file-input" type="file" wire:model="paymentReceipt" accept="image/jpeg,image/png,application/pdf" required>
                    <small class="form-hint">Up to 5 MB.</small>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['paymentReceipt'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div wire:loading wire:target="paymentReceipt" class="form-hint">Uploading file…</div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paymentReceipt): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(str_starts_with($paymentReceipt->getMimeType(), 'image/')): ?>
                            <img class="downpayment-proof-preview" src="<?php echo e($paymentReceipt->temporaryUrl()); ?>" alt="Selected payment proof preview">
                        <?php else: ?>
                            <p class="form-hint mb-0"><?php echo e($paymentReceipt->getClientOriginalName()); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="downpayment-notes">Notes</label>
                    <textarea id="downpayment-notes" wire:model="notes" rows="2" maxlength="1000"></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Submit Downpayment</span>
                    <span wire:loading wire:target="submit">Submitting…</span>
                </button>
            </div>
        </section>
    </form>
</div>
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/livewire/guest/downpayment-form.blade.php ENDPATH**/ ?>
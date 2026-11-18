<div class="container" style="margin-top:24px;max-width:1240px">
    <style>
        .walk-in-layout { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(280px, .85fr); gap: 20px; align-items: start; }
        .walk-in-layout > .card { min-width: 0; margin-bottom: 0; }
        .walk-in-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .walk-in-fields > .form-group { margin-bottom: 0; }
        .walk-in-count-summary { padding: 14px; border: 1px solid var(--border); border-radius: 10px; }
        .walk-in-discounts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .walk-in-discounts > section { min-width: 0; padding: 14px; border: 1px solid var(--border); border-radius: 10px; }
        .walk-in-price-line { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
        .walk-in-price-line strong { white-space: nowrap; }
        @media (max-width: 760px) {
            .walk-in-layout { grid-template-columns: minmax(0, 1fr); }
        }
        @media (max-width: 520px) {
            .walk-in-fields, .walk-in-discounts { grid-template-columns: minmax(0, 1fr); }
        }
    </style>

    <a href="<?php echo e(route($dashboardPrefix.'.bookings.index')); ?>" class="text-muted fs-sm">
        <i class="fa fa-arrow-left"></i> Back to bookings
    </a>
    <div class="page-header-row page-header mt-2">
        <div>
            <h1>Add Walk-in Booking</h1>
            <p>Save the booking and record the payment.</p>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="alert alert-danger mb-3" role="alert"><?php echo e($errors->first()); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($units->isEmpty()): ?>
        <div class="empty-state">
            <i class="fa fa-bed"></i>
            <h3>No available units</h3>
            <p>There are no available units for your properties.</p>
        </div>
    <?php else: ?>
        <form wire:submit="submit">
            <div class="walk-in-layout">
                <section class="card">
                    <div class="card-header">Guest and Stay</div>
                    <div class="card-body">
                        <div class="walk-in-fields">
                            <div class="form-group" style="grid-column:1 / -1">
                                <label class="required" for="walk-in-unit">Unit</label>
                                <select id="walk-in-unit" wire:model.live="unitId" required>
                                    <option value="">Choose a unit</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($unit->unit_id); ?>"><?php echo e($unit->unit_type); ?> · <?php echo e($unit->house->name); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['unitId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="required" for="walk-in-name">Guest name</label>
                                <input id="walk-in-name" type="text" wire:model.blur="walkInName" maxlength="200" required>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['walkInName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="required" for="walk-in-phone">Contact number</label>
                                <input id="walk-in-phone" type="tel" inputmode="tel" wire:model.blur="walkInPhone" maxlength="30" required>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['walkInPhone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="form-group" style="grid-column:1 / -1">
                                <label for="walk-in-email">Email <span class="text-muted">(optional)</span></label>
                                <input id="walk-in-email" type="email" wire:model.blur="walkInEmail" maxlength="191">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['walkInEmail'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="required" for="walk-in-check-in">Check-in date</label>
                                <input id="walk-in-check-in" type="date" wire:model.live="checkIn" min="<?php echo e(today()->toDateString()); ?>" required>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['checkIn'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="required" for="walk-in-check-out">Check-out date</label>
                                <input id="walk-in-check-out" type="date" wire:model.live="checkOut" min="<?php echo e($checkIn ? \Carbon\Carbon::parse($checkIn)->addDay()->toDateString() : today()->addDay()->toDateString()); ?>" required>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['checkOut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedUnit): ?>
                            <div class="booking-form-section mt-3">
                                <h3>Guests</h3>
                                <div class="walk-in-fields">
                                    <div class="form-group">
                                        <label class="required" for="walk-in-adults">Paying guests</label>
                                        <input id="walk-in-adults" type="number" inputmode="numeric" wire:model.live.debounce.300ms="adultCount" min="1" max="<?php echo e($selectedUnit->maximum_capacity ?? $selectedUnit->capacity); ?>" required>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['adultCount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedUnit->child_free_through_age !== null): ?>
                                        <div class="form-group">
                                            <label for="walk-in-children">Children age <?php echo e($selectedUnit->child_free_through_age); ?> or younger</label>
                                            <input id="walk-in-children" type="number" inputmode="numeric" wire:model.live.debounce.300ms="freeChildCount" min="0" max="100">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['freeChildCount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <div class="walk-in-count-summary" style="grid-column:1 / -1">
                                        <strong>Counted pax: <?php echo e(max(0, (int) $adultCount) + max(0, (int) $freeChildCount)); ?> / <?php echo e($selectedUnit->maximum_capacity ?? $selectedUnit->capacity); ?> max</strong>
                                        <p class="text-muted fs-sm mb-0 mt-1">Paying: <?php echo e(max(0, (int) $adultCount)); ?> | Free children: <?php echo e(max(0, (int) $freeChildCount)); ?></p>
                                    </div>
                                </div>

                                <div class="walk-in-discounts mt-3">
                                    <section>
                                        <h4>Senior Citizens</h4>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $seniorIds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $id): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <div class="form-group" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'walk-in-senior-'.e($index).''; ?>wire:key="walk-in-senior-<?php echo e($index); ?>">
                                                <label class="required" for="walk-in-senior-id-<?php echo e($index); ?>">ID reference</label>
                                                <div class="flex-between" style="gap:8px">
                                                    <input id="walk-in-senior-id-<?php echo e($index); ?>" type="text" wire:model.blur="seniorIds.<?php echo e($index); ?>" maxlength="100">
                                                    <button type="button" class="btn btn-outline btn-sm" wire:click="removeSenior(<?php echo e($index); ?>)">Remove</button>
                                                </div>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["seniorIds.$index"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <button type="button" class="btn btn-outline btn-sm" wire:click="addSenior" <?php if($seniorCount + $pwdCount >= (int) $adultCount): echo 'disabled'; endif; ?>>Add Senior</button>
                                    </section>
                                    <section>
                                        <h4>PWD Guests</h4>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pwdIds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $id): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <div class="form-group" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'walk-in-pwd-'.e($index).''; ?>wire:key="walk-in-pwd-<?php echo e($index); ?>">
                                                <label class="required" for="walk-in-pwd-id-<?php echo e($index); ?>">ID reference</label>
                                                <div class="flex-between" style="gap:8px">
                                                    <input id="walk-in-pwd-id-<?php echo e($index); ?>" type="text" wire:model.blur="pwdIds.<?php echo e($index); ?>" maxlength="100">
                                                    <button type="button" class="btn btn-outline btn-sm" wire:click="removePwd(<?php echo e($index); ?>)">Remove</button>
                                                </div>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["pwdIds.$index"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <button type="button" class="btn btn-outline btn-sm" wire:click="addPwd" <?php if($seniorCount + $pwdCount >= (int) $adultCount): echo 'disabled'; endif; ?>>Add PWD</button>
                                    </section>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['seniorIds'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger mt-2"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['pwdIds'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger mt-2"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div class="booking-form-section mt-3">
                                <h3>Payment</h3>
                                <div class="walk-in-fields">
                                    <div class="form-group">
                                        <label class="required" for="walk-in-payment-method">Method</label>
                                        <select id="walk-in-payment-method" wire:model="paymentMethod" required>
                                            <option value="Cash">Cash</option>
                                            <option value="GCash">GCash</option>
                                            <option value="Bank Transfer">Bank Transfer</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="required" for="walk-in-payment-amount">Amount received (₱)</label>
                                        <input id="walk-in-payment-amount" type="number" inputmode="decimal" wire:model.blur="paymentAmount" min="0.01" step="0.01" required>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['paymentAmount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="form-group" style="grid-column:1 / -1">
                                        <label for="walk-in-payment-reference">Payment reference <span class="text-muted">(optional)</span></label>
                                        <input id="walk-in-payment-reference" type="text" wire:model.blur="paymentReference" maxlength="100">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['paymentReference'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($checkIn === today()->toDateString()): ?>
                                    <label class="flex-center gap-1 mt-3" style="justify-content:flex-start">
                                        <input type="checkbox" wire:model="checkInNow">
                                        Check-in now
                                    </label>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['checkInNow'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </section>

                <aside class="card">
                    <div class="card-header">Booking Summary</div>
                    <div class="card-body">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $selectedUnit): ?>
                            <p class="text-muted mb-0">Choose a unit to see its capacity and price.</p>
                        <?php else: ?>
                            <p class="fw-bold mb-1"><?php echo e($selectedUnit->unit_type); ?></p>
                            <p class="text-muted fs-sm"><?php echo e($selectedUnit->house->name); ?></p>
                            <p class="text-muted fs-sm">Standard: <?php echo e($selectedUnit->capacity); ?> guests | Max: <?php echo e($selectedUnit->maximum_capacity ?? $selectedUnit->capacity); ?> guests</p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($summary): ?>
                                <hr class="divider">
                                <div class="walk-in-price-line">
                                    <span>Stay/room charge</span>
                                    <strong>₱<?php echo e(number_format($summary['room_subtotal'], 2)); ?></strong>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($summary['extra_guest_total'] > 0): ?>
                                    <div class="walk-in-price-line">
                                        <span>Extra heads</span>
                                        <strong>₱<?php echo e(number_format($summary['extra_guest_total'], 2)); ?></strong>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <div class="walk-in-price-line" style="font-weight:700">
                                    <span>Booking total</span>
                                    <strong>₱<?php echo e(number_format($summary['total'], 2)); ?></strong>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($summary['estimated_discount'] > 0): ?>
                                    <div class="walk-in-price-line">
                                        <span>Estimated Senior/PWD discount</span>
                                        <strong class="text-success">−₱<?php echo e(number_format($summary['estimated_discount'], 2)); ?></strong>
                                    </div>
                                    <p class="text-muted fs-sm">ID must be verified. The discount is refunded after payment.</p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?>
                                <p class="text-muted fs-sm mb-0">Choose stay dates to see the price.</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="card-body" style="padding-top:0">
                        <button type="submit" class="btn btn-primary btn-block" <?php if(! $selectedUnit): echo 'disabled'; endif; ?>>Save Booking</button>
                    </div>
                </aside>
            </div>
        </form>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/livewire/owner/walk-in-booking-form.blade.php ENDPATH**/ ?>
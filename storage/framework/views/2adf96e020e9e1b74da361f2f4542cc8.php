<div class="container booking-form-page">
    <div class="steps mb-3">
        <div class="step active"><div class="step-circle">1</div><div class="step-label">Booking Details</div></div>
        <div class="step"><div class="step-circle">2</div><div class="step-label">Payment</div></div>
        <div class="step"><div class="step-circle">3</div><div class="step-label">Confirmation</div></div>
    </div>

    <div class="booking-form-layout">
        <section class="card">
            <div class="card-header">Booking Request — <?php echo e($unit->unit_type); ?></div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                    <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <form wire:submit="submit">
                    <div class="booking-form-section">
                        <h3>Stay dates</h3>
                        <div class="booking-fields-grid booking-date-fields">
                            <div class="form-group">
                                <label class="required" for="booking-check-in">Check-in date</label>
                                <input id="booking-check-in" type="date" wire:model.live="checkIn" min="<?php echo e(today()->addDay()->toDateString()); ?>" required>
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
                                <label class="required" for="booking-check-out">Check-out date</label>
                                <input id="booking-check-out" type="date" wire:model.live="checkOut" min="<?php echo e($checkIn ? \Carbon\Carbon::parse($checkIn)->addDay()->toDateString() : today()->addDays(2)->toDateString()); ?>" required>
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
                    </div>

                    <div class="booking-form-section">
                        <h3>Guests</h3>
                        <div class="booking-fields-grid booking-count-fields">
                            <div class="booking-count-inputs">
                                <div class="form-group">
                                    <label class="required" for="pax-count">Number of pax</label>
                                    <input id="pax-count" type="number" inputmode="numeric" wire:model.live.debounce.300ms="paxCount" min="1" max="<?php echo e($unit->maximum_capacity ?? $unit->capacity); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['paxCount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unit->child_free_through_age !== null): ?>
                                    <div class="form-group">
                                        <label for="free-child-count">Children age <?php echo e($unit->child_free_through_age); ?> or younger</label>
                                        <input id="free-child-count" type="number" inputmode="numeric" wire:model.live.debounce.300ms="freeChildCount" min="0" max="100">
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="booking-guest-summary">
                                <div class="booking-guest-summary-count booking-guest-summary-count--compact">
                                    <strong>Counted pax: <?php echo e(max(0, (int) $paxCount) + max(0, (int) $freeChildCount)); ?> / <?php echo e($unit->maximum_capacity ?? $unit->capacity); ?> max</strong>
                                </div>
                                <p class="booking-section-note booking-guest-summary-paying">
                                    Paying: <?php echo e(max(0, (int) $paxCount)); ?> | Free children: <?php echo e(max(0, (int) $freeChildCount)); ?>

                                </p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unit->child_free_through_age !== null): ?>
                                    <p class="booking-section-note booking-guest-summary-guidance">Children <?php echo e($unit->child_free_through_age); ?> years old or younger are free but still count toward the max.</p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['freeChildCount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <p class="booking-section-note booking-guest-summary-guidance">Standard: <?php echo e($unit->capacity); ?> guests | Max: <?php echo e($unit->maximum_capacity ?? $unit->capacity); ?> guests</p>
                                <p class="booking-section-note booking-guest-summary-guidance">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($unit->pricing_mode ?? 'Per Unit') === 'Per Unit'): ?>
                                        ₱<?php echo e(number_format((float) $unit->price, 2)); ?> / night | Extra guest: ₱<?php echo e(number_format((float) $unit->extra_guest_fee, 2)); ?> / night
                                    <?php else: ?>
                                        ₱<?php echo e(number_format((float) $unit->price, 2)); ?> / head / night
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="booking-fields-grid booking-id-fields">
                            <section class="booking-discount-group">
                                <h4>Senior Citizens</h4>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $seniorIds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $id): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="form-group" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'senior-id-'.e($index).''; ?>wire:key="senior-id-<?php echo e($index); ?>">
                                        <label class="required" for="senior-id-<?php echo e($index); ?>">Senior Citizen <?php echo e($index + 1); ?> reference ID</label>
                                        <div class="flex-between" style="gap:8px">
                                            <input id="senior-id-<?php echo e($index); ?>" type="text" wire:model="seniorIds.<?php echo e($index); ?>" maxlength="100">
                                            <button type="button" class="btn btn-outline btn-sm" wire:click="removeSenior(<?php echo e($index); ?>)" aria-label="Remove Senior Citizen <?php echo e($index + 1); ?>">Remove</button>
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
                                <button type="button" class="btn btn-outline btn-sm" wire:click="addSenior" <?php if($seniorCount + $pwdCount >= $paxCount): echo 'disabled'; endif; ?>><?php echo e($seniorCount > 0 ? 'Add another senior' : 'Add a senior'); ?></button>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['seniorCount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </section>
                            <section class="booking-discount-group">
                                <h4>PWD guests</h4>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pwdIds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $id): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="form-group" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'pwd-id-'.e($index).''; ?>wire:key="pwd-id-<?php echo e($index); ?>">
                                        <label class="required" for="pwd-id-<?php echo e($index); ?>">PWD <?php echo e($index + 1); ?> reference ID</label>
                                        <div class="flex-between" style="gap:8px">
                                            <input id="pwd-id-<?php echo e($index); ?>" type="text" wire:model="pwdIds.<?php echo e($index); ?>" maxlength="100">
                                            <button type="button" class="btn btn-outline btn-sm" wire:click="removePwd(<?php echo e($index); ?>)" aria-label="Remove PWD <?php echo e($index + 1); ?>">Remove</button>
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
                                <button type="button" class="btn btn-outline btn-sm" wire:click="addPwd" <?php if($seniorCount + $pwdCount >= $paxCount): echo 'disabled'; endif; ?>><?php echo e($pwdCount > 0 ? 'Add another PWD' : 'Add a PWD'); ?></button>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['pwdCount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </section>
                        </div>
                        <p class="form-hint booking-discount-note">Senior Citizen and PWD discounts are deducted at checkout after the ID is verified. Choose only one discount per person.</p>
                    </div>

                    <div class="policy-box">
                        <strong>Booking policy</strong>
                        <ul style="margin:8px 0 0 16px;font-size:12px;line-height:1.8">
                            <li>Your request will be reviewed by the property owner.</li>
                            <li>Check-in is at <?php echo e(\App\Support\UnitTimeFormatter::format((string) $unit->check_in_time)); ?>; check-out is at <?php echo e(\App\Support\UnitTimeFormatter::format((string) $unit->check_out_time)); ?>.</li>
                            <li>No payment is due to submit a request. If accepted, a <?php echo e((int) (config('booking.downpayment_fraction') * 100)); ?>% downpayment is required to confirm the booking; the remaining balance is paid at checkout.</li>
                            <li>Present the original Senior Citizen/PWD ID at checkout. After verification, a 20% discount is applied to that guest’s share; only one discount may be used per guest.</li>
                            <li>Cancellation and refund policies are set by the property owner. Please read them carefully before submitting your request.</li>
                        </ul>
                    </div>

                    <div class="checkbox-group mb-3">
                        <input type="checkbox" wire:model="ackPolicy" id="ackPolicy">
                        <label for="ackPolicy" style="font-weight:400;cursor:pointer">
                            I have read and agree to the cancellation and payment policy.
                        </label>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['ackPolicy'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-hint text-danger"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <button type="submit" class="btn btn-primary btn-block btn-lg" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submit">Send booking request</span>
                        <span wire:loading wire:target="submit">Sending request…</span>
                    </button>
                </form>
            </div>
        </section>

        <aside class="card booking-price-card">
            <div class="card-header">Price summary</div>
            <div class="card-body">
                <div class="booking-price-property"><?php echo e($unit->unit_type); ?> · <?php echo e($unit->house->name); ?> · <?php echo e($unit->house->city); ?></div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->summary): ?>
                    <?php
                        $countedGuests = max(1, (int) $paxCount);
                        $discountedGuests = (int) $seniorCount + (int) $pwdCount;
                        $guestShare = round((float) $this->summary['total'] / $countedGuests, 2);
                        $estimatedDiscount = round($guestShare * 0.2 * $discountedGuests, 2);
                        $pricingMode = $unit->pricing_mode ?? 'Per Unit';
                        $roomQuantity = $pricingMode === 'Per Head'
                            ? (int) $paxCount.' '.\Illuminate\Support\Str::plural('paying guest', (int) $paxCount).' × '.$this->summary['nights'].' nights'
                            : $this->summary['nights'].' nights';
                        $roomPrice = '₱'.number_format((float) $unit->price, 2)
                            .($pricingMode === 'Per Head' ? ' / guest / night' : ' / unit / night');
                    ?>
                    <div class="booking-price-lines">
                        <div class="booking-price-line">
                            <span>Stay/room charge · <?php echo e($pricingMode); ?> <small class="booking-price-detail"><?php echo e($roomQuantity); ?> × <?php echo e($roomPrice); ?></small></span>
                            <strong class="booking-price-amount">₱<?php echo e(number_format((float) $this->summary['room_subtotal'], 2)); ?></strong>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->summary['extra_guest_count'] > 0): ?>
                            <div class="booking-price-line">
                                <span>Extra heads <small class="booking-price-detail"><?php echo e($this->summary['extra_guest_count']); ?> extra guests × <?php echo e($this->summary['nights']); ?> nights × ₱<?php echo e(number_format((float) $this->summary['extra_guest_fee'], 2)); ?> / guest / night</small></span>
                                <strong class="booking-price-amount">₱<?php echo e(number_format((float) $this->summary['extra_guest_total'], 2)); ?></strong>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="booking-price-line booking-price-line--total">
                            <span>Stay total</span>
                            <strong class="booking-price-amount">₱<?php echo e(number_format((float) $this->summary['total'], 2)); ?></strong>
                        </div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($discountedGuests > 0): ?>
                        <div class="booking-price-line">
                            <span>Estimated Senior/PWD discount · <?php echo e($discountedGuests); ?> <?php echo e(\Illuminate\Support\Str::plural('guest', $discountedGuests)); ?></span>
                            <strong class="booking-price-amount booking-price-amount--green">−₱<?php echo e(number_format((float) $estimatedDiscount, 2)); ?></strong>
                        </div>
                        <p class="form-hint booking-section-note booking-price-discount-note">Estimated per eligible guest: ₱<?php echo e(number_format(round($guestShare * 0.2, 2), 2)); ?>. Final discount is applied at checkout after ID verification.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="booking-price-line booking-price-line--total">
                        <span>Estimated total after discount</span>
                        <strong class="booking-price-amount">₱<?php echo e(number_format(max(0, (float) $this->summary['total'] - $estimatedDiscount), 2)); ?></strong>
                    </div>
                    <p class="booking-price-note">No payment is due to submit a request. If accepted, a <?php echo e((int) (config('booking.downpayment_fraction') * 100)); ?>% downpayment is required to confirm it. Any remaining balance and checkout charges are due at checkout.</p>
                <?php else: ?>
                    <div class="booking-price-empty fs-sm text-center">Select valid dates to see your price breakdown.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </aside>
    </div>

</div>
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/livewire/guest/booking-form.blade.php ENDPATH**/ ?>
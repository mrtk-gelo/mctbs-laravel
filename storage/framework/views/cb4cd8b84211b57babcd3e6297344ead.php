<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Downpayment · Booking #'.e($booking->booking_id).' | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Downpayment · Booking #'.e($booking->booking_id).' | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> <?php echo $__env->make('partials.nav', ['activePage' => 'bookings'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php $__env->endSlot(); ?>

    <style>
        .downpayment-layout {
            display: grid;
            grid-template-columns: minmax(210px, .85fr) minmax(320px, 1.35fr) minmax(250px, 1fr);
            gap: 20px;
            align-items: start;
        }
        .downpayment-method-input {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            clip-path: inset(50%);
        }
        .downpayment-method-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 16px;
        }
        .downpayment-method-tab {
            display: flex;
            min-height: 48px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--card);
            cursor: pointer;
            font-weight: 600;
            text-align: center;
        }
        .downpayment-method-input:checked + .downpayment-method-tab {
            border-color: var(--accent);
            color: var(--accent);
            box-shadow: 0 0 0 1px var(--accent);
        }
        .downpayment-method-input:focus-visible + .downpayment-method-tab {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }
        .downpayment-method-instructions { display: none; }
        .downpayment-method-instructions-default { display: block; }
        #downpayment-gcash:checked ~ .downpayment-method-instructions-gcash,
        #downpayment-bank:checked ~ .downpayment-method-instructions-bank { display: block; }
        #downpayment-gcash:checked ~ .downpayment-method-instructions-default,
        #downpayment-bank:checked ~ .downpayment-method-instructions-default { display: none; }
        .downpayment-breakdown-total {
            border-top: 1px solid var(--border);
            padding-top: 12px;
        }
        .downpayment-livewire-form,
        .downpayment-form {
            display: contents;
        }
        .downpayment-file-input { font-size: 12px; }
        .downpayment-file-input::file-selector-button {
            margin-right: 10px;
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: var(--surface, var(--card));
            color: var(--text);
            cursor: pointer;
            font: inherit;
            font-weight: 600;
        }
        .downpayment-proof-preview {
            display: block;
            max-width: 180px;
            max-height: 180px;
            margin-top: 10px;
            border: 1px solid var(--border);
            border-radius: 8px;
            object-fit: contain;
        }
        .downpayment-pending-column { grid-column: 1 / 3; }
        @media (max-width: 900px) {
            .downpayment-layout { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .downpayment-breakdown-column { grid-column: 1 / -1; }
        }
        @media (max-width: 640px) {
            .downpayment-layout { grid-template-columns: minmax(0, 1fr); }
            .downpayment-breakdown-column { grid-column: auto; }
        }
    </style>

    <div class="container" style="margin-top:24px">
        <div class="page-header">
            <a href="<?php echo e(route('guest.bookings.show', $booking->booking_id)); ?>" class="text-muted fs-sm">
                <i class="fa fa-arrow-left"></i> Back to booking
            </a>
            <h1 class="mt-1">Downpayment · Booking #<?php echo e($booking->booking_id); ?></h1>
            <p><?php echo e($booking->unit->unit_type); ?> · <?php echo e($booking->unit->house->name); ?></p>
        </div>

        <?php
            $nights = max(1, $booking->check_in->diffInDays($booking->check_out));
            $approvedDiscountTotal = \App\Support\BookingFinancialContract::approvedDiscounts($booking);
            $currentCharges = $booking->amountDueAtCheckout();
            $verifiedPaymentTotal = \App\Support\BookingFinancialContract::verifiedPayments($booking);
            $estimatedCheckoutBalance = max(0, $remainingBalance - $downpaymentAmount);
            $activeGuests = $booking->activeGuests;
            $payingGuestCount = $activeGuests->isNotEmpty()
                ? $activeGuests->where('is_free_child', false)->count()
                : null;
            $nightlyRate = (float) ($booking->nightly_rate ?? $booking->unit->price);
            $roomQuantity = ($booking->pricing_mode ?? 'Per Unit') === 'Per Head'
                ? ($payingGuestCount === null ? 'Paying guests as booked' : $payingGuestCount.' paying guests').' × '.$nights.' nights'
                : $nights.' nights';
            $roomPrice = '₱'.number_format($nightlyRate, 2)
                .(($booking->pricing_mode ?? 'Per Unit') === 'Per Head' ? ' / guest / night' : ' / unit / night');
        ?>

        <div class="downpayment-layout">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasUnderReviewPayment): ?>
                <div class="downpayment-pending-column">
                    <section class="card">
                        <div class="card-header">Submit payment details</div>
                        <div class="card-body">
                            <div class="alert alert-info mb-0">Your downpayment is being reviewed.</div>
                        </div>
                    </section>
                </div>
            <?php else: ?>
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('guest.downpayment-form', ['booking' => $booking,'downpayment-amount' => $downpaymentAmount,'remaining-balance' => $remainingBalance]);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3226707009-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="downpayment-breakdown-column">
                <section class="card">
                    <div class="card-header">Bill breakdown</div>
                    <div class="card-body">
                        <div class="booking-price-line">
                            <span>Stay/room charge <small class="booking-price-detail"><?php echo e($roomQuantity); ?> × <?php echo e($roomPrice); ?></small></span>
                            <strong class="booking-price-amount">₱<?php echo e(number_format(\App\Support\BookingFinancialContract::roomAmountBeforeDiscounts($booking), 2)); ?></strong>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((int) $booking->extra_guest_count > 0): ?>
                            <div class="booking-price-line">
                                <span>Extra heads <small class="booking-price-detail"><?php echo e($booking->extra_guest_count); ?> guests × <?php echo e($nights); ?> nights × ₱<?php echo e(number_format((float) $booking->extra_guest_fee, 2)); ?> / guest / night</small></span>
                                <strong class="booking-price-amount">₱<?php echo e(number_format((float) $booking->extra_guest_total, 2)); ?></strong>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($approvedDiscountTotal > 0): ?>
                            <div class="booking-price-line">
                                <span>Approved Senior/PWD discounts</span>
                                <strong class="booking-price-amount booking-price-amount--green">−₱<?php echo e(number_format($approvedDiscountTotal, 2)); ?></strong>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="booking-price-line booking-price-line--total">
                            <span>Booking amount</span>
                            <strong class="booking-price-amount">₱<?php echo e(number_format($booking->amountDueBeforeCheckout(), 2)); ?></strong>
                        </div>
                        <div class="booking-price-line booking-price-line--total">
                            <span>Current amount due</span>
                            <strong class="booking-price-amount<?php echo e($currentCharges > 0 ? ' booking-price-amount--red' : ''); ?>">₱<?php echo e(number_format((float) $currentCharges, 2)); ?></strong>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($verifiedPaymentTotal > 0): ?>
                            <div class="booking-price-line">
                                <span>Payments made</span>
                                <strong class="booking-price-amount booking-price-amount--green">−₱<?php echo e(number_format($verifiedPaymentTotal, 2)); ?></strong>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="booking-price-line booking-price-line--total">
                            <span>Downpayment due now</span>
                            <strong class="booking-price-amount">₱<?php echo e(number_format((float) $downpaymentAmount, 2)); ?></strong>
                        </div>
                        <div class="booking-price-line booking-price-line--total">
                            <span>Estimated balance after downpayment</span>
                            <strong class="booking-price-amount booking-price-amount--<?php echo e($estimatedCheckoutBalance > 0 ? 'red' : 'green'); ?>">₱<?php echo e(number_format((float) $estimatedCheckoutBalance, 2)); ?></strong>
                        </div>
                        <p class="form-hint mt-2 mb-0">Paying more now lowers your checkout balance.</p>
                    </div>
                </section>
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
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/guest/bookings/payment.blade.php ENDPATH**/ ?>
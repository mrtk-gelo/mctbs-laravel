<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { margin: 28pt; }
        body { margin: 0; color: #263238; font-family: DejaVu Sans, sans-serif; font-size: 9pt; }
        h1 { margin: 0; font-size: 19pt; }
        h2 { margin: 0 0 6pt; color: #506070; font-size: 11pt; }
        p { margin: 4pt 0; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .header { margin-bottom: 18pt; padding: 14pt; background: #2f5f7f; color: #fff; text-align: center; }
        .header p { margin-top: 5pt; }
        .metadata { margin-bottom: 16pt; }
        .metadata td { width: 50%; padding: 5pt 8pt; vertical-align: top; }
        .muted { color: #697783; }
        .breakdown th, .breakdown td, .transactions th, .transactions td { padding: 6pt 8pt; border-bottom: 1px solid #d9e0e5; text-align: left; vertical-align: top; }
        .breakdown th, .transactions th { background: #f1f4f6; font-weight: bold; }
        .amount { text-align: right !important; white-space: nowrap; }
        .blue { color: #1769aa; }
        .red { color: #bd3030; }
        .green { color: #237a45; }
        .orange { color: #c66a18; }
        .total td { font-weight: bold; }
        .section { margin-top: 16pt; }
        .footer { margin-top: 18pt; color: #697783; font-size: 8pt; text-align: center; }
    </style>
</head>
<body>
    <?php
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

    <header class="header">
        <h1>MCTBS</h1>
        <p>Motal and Conteros Transient Booking System</p>
        <strong>Booking Breakdown · <?php echo e($receipt->receipt_number); ?></strong>
    </header>

    <table class="metadata">
        <tr>
            <td>
                <h2>Guest</h2>
                <strong><?php echo e($booking->displayGuestName()); ?></strong>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->displayGuestEmail()): ?><p><?php echo e($booking->displayGuestEmail()); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->displayGuestPhone()): ?><p><?php echo e($booking->displayGuestPhone()); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </td>
            <td>
                <h2>Property</h2>
                <strong><?php echo e($booking->unit->unit_type); ?></strong>
                <p><?php echo e($booking->unit->house->name); ?></p>
                <p><?php echo e($booking->unit->house->address); ?>, <?php echo e($booking->unit->house->city); ?></p>
            </td>
        </tr>
        <tr>
            <td>
                <h2>Stay details</h2>
                <p>Booking ID: <strong>#<?php echo e($booking->booking_id); ?></strong></p>
                <p>Check-in: <strong><?php echo e($booking->check_in->format('M j, Y')); ?></strong></p>
                <p>Check-out: <strong><?php echo e($booking->check_out->format('M j, Y')); ?></strong></p>
                <p>Nights: <strong><?php echo e($nights); ?></strong></p>
                <p>Adult paying guests: <strong><?php echo e($booking->adult_count); ?></strong></p>
                <p>Free children: <strong><?php echo e($booking->child_count); ?></strong></p>
            </td>
            <td>
                <h2>Payment summary</h2>
                <p>Status: <strong><?php echo e(\App\Support\BookingStatus::canonical($booking->status)); ?></strong></p>
                <p>Final amount due: <strong>₱<?php echo e(number_format($financialSummary['finalAmountDue'], 2)); ?></strong></p>
                <p>Payments made (verified only): <strong class="green">₱<?php echo e(number_format($financialSummary['verifiedPayments'], 2)); ?></strong></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($financialSummary['completedRefunds'] > 0): ?>
                    <p>Completed refunds: <strong class="orange">₱<?php echo e(number_format($financialSummary['completedRefunds'], 2)); ?></strong></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p>Outstanding balance: <strong class="<?php echo e($financialSummary['outstandingBalance'] > 0 ? 'red' : 'green'); ?>">₱<?php echo e(number_format($financialSummary['outstandingBalance'], 2)); ?></strong></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($financialSummary['refundableOverpayment'] > 0): ?>
                    <p>Refundable overpayment: <strong class="orange">₱<?php echo e(number_format($financialSummary['refundableOverpayment'], 2)); ?></strong></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </td>
        </tr>
    </table>

    <table class="breakdown">
        <thead><tr><th>Booking breakdown</th><th class="amount">Amount</th></tr></thead>
        <tbody>
            <tr>
                <td>Stay/room charge · <?php echo e($roomQuantity); ?> × <?php echo e($roomPrice); ?></td>
                <td class="amount">₱<?php echo e(number_format($financialSummary['roomCharges'], 2)); ?></td>
            </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($financialSummary['extraGuestCharges'] > 0): ?>
                <tr>
                    <td>Extra guest charges · <?php echo e($booking->extra_guest_count); ?> × <?php echo e($nights); ?> nights × ₱<?php echo e(number_format((float) $booking->extra_guest_fee, 2)); ?> / guest / night · included in accommodation</td>
                    <td class="amount">₱<?php echo e(number_format($financialSummary['extraGuestCharges'], 2)); ?></td>
                </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($financialSummary['approvedDiscounts'] > 0): ?>
                <tr><td>Eligible verified discounts</td><td class="amount green">−₱<?php echo e(number_format($financialSummary['approvedDiscounts'], 2)); ?></td></tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <tr class="total"><td>Accommodation after discounts</td><td class="amount">₱<?php echo e(number_format($financialSummary['accommodationAfterDiscounts'], 2)); ?></td></tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->addons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $addon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) $addon->price * (int) $addon->quantity > 0): ?>
                    <tr><td>Amenity/add-on · <?php echo e($addon->name); ?> × <?php echo e($addon->quantity); ?> (included by financial contract)</td><td class="amount blue">₱<?php echo e(number_format((float) $addon->price * $addon->quantity, 2)); ?></td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->damages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $damage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) $damage->price * (int) $damage->quantity > 0): ?>
                    <tr><td>Historical damage/missing item · <?php echo e($damage->name); ?> · Qty <?php echo e($damage->quantity); ?> · ₱<?php echo e(number_format((float) $damage->price, 2)); ?> / item (not included; no approval status)</td><td class="amount red">₱<?php echo e(number_format((float) $damage->price * $damage->quantity, 2)); ?></td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->additionalCharges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $charge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) $charge->total_amount > 0): ?>
                    <tr><td>Additional charge · <?php echo e($charge->description); ?> × <?php echo e($charge->quantity); ?> (included by financial contract)</td><td class="amount red">₱<?php echo e(number_format((float) $charge->total_amount, 2)); ?></td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->damageReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $damage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) $damage->amount > 0): ?>
                    <tr>
                        <td>
                            Damage/missing items · <?php echo e($damage->description); ?> · Qty <?php echo e($damage->quantity); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($damage->unit_price !== null): ?>
                                · ₱<?php echo e(number_format((float) $damage->unit_price, 2)); ?> / item
                            <?php else: ?>
                                · Unit price unavailable
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            (<?php echo e($damage->isIncludedInFinalAmount() ? 'included in amount due' : 'not included in amount due'); ?>)
                        </td>
                        <td class="amount red">₱<?php echo e(number_format($damage->calculatedChargeTotal(), 2)); ?></td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <tr class="total"><td>Final amount due</td><td class="amount">₱<?php echo e(number_format($financialSummary['finalAmountDue'], 2)); ?></td></tr>
        </tbody>
    </table>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->discounts->isNotEmpty()): ?>
        <section class="section">
            <h2>Discount verification</h2>
            <table class="transactions">
                <thead><tr><th>Guest</th><th>Discount</th><th>Status</th><th>Bill treatment</th><th class="amount">Amount</th></tr></thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $booking->discounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $discount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td><?php echo e($discount->guest->full_name); ?></td>
                            <td><?php echo e($discount->discount_type); ?></td>
                            <td><?php echo e($discount->verification_status); ?></td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($discount->guest->roster_status !== \App\Models\BookingGuest::ROSTER_ACTIVE): ?>
                                    Removed guest; excluded
                                <?php elseif($discount->verification_status === 'Verified'): ?>
                                    Applied once to accommodation
                                <?php else: ?>
                                    Not applied
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="amount"><?php echo e($discount->verification_status === 'Verified' && $discount->guest->roster_status === \App\Models\BookingGuest::ROSTER_ACTIVE ? '−₱'.number_format($discount->discount_amount, 2) : '—'); ?></td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <section class="section">
        <h2>Payment transactions</h2>
        <table class="transactions">
            <thead><tr><th>Date</th><th>Method</th><th>Reference</th><th>Status</th><th class="amount">Amount</th></tr></thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $booking->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td><?php echo e($payment->payment_date?->format('M j, Y g:i A') ?? 'Date unavailable'); ?></td>
                        <td><?php echo e($payment->payment_method); ?></td>
                        <td><?php echo e($payment->reference_number ?: '—'); ?></td>
                        <td><?php echo e($payment->payment_status); ?></td>
                        <td class="amount <?php echo e($payment->payment_status === \App\Support\PaymentStatus::VERIFIED ? 'green' : ''); ?>">₱<?php echo e(number_format($payment->amount, 2)); ?></td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr><td colspan="5" class="muted">No payments recorded.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </section>

    <section class="section">
        <h2>Refund transactions</h2>
        <table class="transactions">
            <thead><tr><th>Date</th><th>Reason</th><th>Reference / payment</th><th>Status</th><th class="amount">Amount</th></tr></thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $booking->refunds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $refund): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td><?php echo e($refund->processed_at?->format('M j, Y g:i A') ?? $refund->created_at?->format('M j, Y g:i A') ?? 'Date unavailable'); ?></td>
                        <td><?php echo e($refund->reason); ?></td>
                        <td><?php echo e($refund->refund_reference ?: '—'); ?> · Payment #<?php echo e($refund->payment_id); ?></td>
                        <td><?php echo e($refund->refund_status); ?></td>
                        <td class="amount <?php echo e($refund->refund_status === 'Refunded' ? 'orange' : ''); ?>">₱<?php echo e(number_format($refund->amount, 2)); ?></td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr><td colspan="5" class="muted">No refunds recorded.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </section>

    <p class="footer">Created <?php echo e($receipt->issued_at->format('F j, Y g:i A')); ?> · Not an official receipt.</p>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/guest/bookings/receipt-pdf.blade.php ENDPATH**/ ?>
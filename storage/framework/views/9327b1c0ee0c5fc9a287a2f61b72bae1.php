<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MCTBS Booking Report</title>
    <style>
        @page { margin: 24px; }
        body { color: #252525; font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        h1 { margin: 0 0 5px; color: #6b3a14; font-size: 17px; }
        h2 { margin: 15px 0 5px; color: #6b3a14; font-size: 12px; }
        p { margin: 3px 0; }
        table { width: 100%; margin-bottom: 11px; border-collapse: collapse; }
        th, td { padding: 5px; border: 1px solid #ddd; text-align: left; }
        th { background: #6b3a14; color: #fff; font-weight: bold; }
        .report-subtitle { margin-bottom: 10px; color: #666; font-size: 8px; font-style: italic; }
        .summary { width: auto; margin: 0 0 9px; border-collapse: separate; }
        .summary td { padding: 4px 9px; border: 0; background: #faf6f1; }
        .money { text-align: right; white-space: nowrap; }
        .refund { color: #c00000; }
        .total td { border-top: 2px solid #6b3a14; font-weight: bold; }
        .section { page-break-inside: avoid; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <h1>MCTBS Booking Report | <?php echo e($selectedHouseName); ?> | <?php echo e($fromDate); ?> to <?php echo e($toDate); ?> | <?php echo e($selectedQuarter); ?></h1>
    <p class="report-subtitle">
        Period: <?php echo e(ucfirst($period)); ?> · Year: <?php echo e($selectedYear); ?> · Quarter: <?php echo e($selectedQuarter); ?> ·
        House: <?php echo e($selectedHouseName); ?> · Generated: <?php echo e($generatedAt); ?>

    </p>

    <table class="summary">
        <tbody>
            <tr>
                <td><strong>Net Revenue (after refunds)</strong></td>
                <td class="money"><strong>₱<?php echo e(number_format($totalRevenue, 2)); ?></strong></td>
            </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalRefunded > 0): ?>
                <tr>
                    <td>Refunds</td>
                    <td class="money refund">-₱<?php echo e(number_format($totalRefunded, 2)); ?></td>
                </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>

    <section class="section">
        <h2>Revenue by <?php echo e($period === 'annually' ? 'year' : ($period === 'weekly' ? 'week' : 'month')); ?></h2>
        <table>
            <thead><tr><th><?php echo e(ucfirst($period)); ?></th><th>Payments received</th><th>Refunds</th><th>Net Revenue (after refunds)</th></tr></thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $revenueByPeriodRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td><?php echo e($row['period']); ?></td>
                        <td class="money">₱<?php echo e(number_format($row['payments'], 2)); ?></td>
                        <td class="money refund"><?php echo e($row['refunds'] > 0 ? '-₱'.number_format($row['refunds'], 2) : '₱0.00'); ?></td>
                        <td class="money">₱<?php echo e(number_format($row['revenue'], 2)); ?></td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr class="total">
                    <td>Total</td>
                    <td class="money">₱<?php echo e(number_format($totalReceived, 2)); ?></td>
                    <td class="money refund"><?php echo e($totalRefunded > 0 ? '-₱'.number_format($totalRefunded, 2) : '₱0.00'); ?></td>
                    <td class="money">₱<?php echo e(number_format($totalRevenue, 2)); ?></td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="section">
        <h2>Revenue per House</h2>
        <table>
            <thead><tr><th>House</th><th>Payments received</th><th>Refunds</th><th>Net Revenue (after refunds)</th></tr></thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $revenueByHouse; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td><?php echo e($row['house']); ?></td>
                        <td class="money">₱<?php echo e(number_format($row['payments'], 2)); ?></td>
                        <td class="money refund"><?php echo e($row['refunds'] > 0 ? '-₱'.number_format($row['refunds'], 2) : '₱0.00'); ?></td>
                        <td class="money">₱<?php echo e(number_format($row['revenue'], 2)); ?></td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr class="total">
                    <td>Total</td>
                    <td class="money">₱<?php echo e(number_format($totalReceived, 2)); ?></td>
                    <td class="money refund"><?php echo e($totalRefunded > 0 ? '-₱'.number_format($totalRefunded, 2) : '₱0.00'); ?></td>
                    <td class="money">₱<?php echo e(number_format($totalRevenue, 2)); ?></td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="section">
        <h2>Revenue per Unit</h2>
        <table>
            <thead><tr><th>Unit</th><th>House</th><th>Payments received</th><th>Refunds</th><th>Net Revenue (after refunds)</th></tr></thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $revenueByUnit; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td><?php echo e($row['unit']); ?></td>
                        <td><?php echo e($row['house']); ?></td>
                        <td class="money">₱<?php echo e(number_format($row['payments'], 2)); ?></td>
                        <td class="money refund"><?php echo e($row['refunds'] > 0 ? '-₱'.number_format($row['refunds'], 2) : '₱0.00'); ?></td>
                        <td class="money">₱<?php echo e(number_format($row['revenue'], 2)); ?></td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr class="total">
                    <td colspan="2">Total</td>
                    <td class="money">₱<?php echo e(number_format($totalReceived, 2)); ?></td>
                    <td class="money refund"><?php echo e($totalRefunded > 0 ? '-₱'.number_format($totalRefunded, 2) : '₱0.00'); ?></td>
                    <td class="money">₱<?php echo e(number_format($totalRevenue, 2)); ?></td>
                </tr>
            </tbody>
        </table>
    </section>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($period === 'annually'): ?>
        <section class="section">
            <h2>Revenue per Quarter — <?php echo e($selectedYear); ?></h2>
            <table>
                <thead><tr><th>Quarter</th><th>Payments received</th><th>Refunds</th><th>Net Revenue (after refunds)</th></tr></thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $quarterRevenueRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td><?php echo e($row['quarter']); ?></td>
                            <td class="money">₱<?php echo e(number_format($row['payments'], 2)); ?></td>
                            <td class="money refund"><?php echo e($row['refunds'] > 0 ? '-₱'.number_format($row['refunds'], 2) : '₱0.00'); ?></td>
                            <td class="money">₱<?php echo e(number_format($row['revenue'], 2)); ?></td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr class="total">
                        <td>Total</td>
                        <td class="money">₱<?php echo e(number_format($totalReceived, 2)); ?></td>
                        <td class="money refund"><?php echo e($totalRefunded > 0 ? '-₱'.number_format($totalRefunded, 2) : '₱0.00'); ?></td>
                        <td class="money">₱<?php echo e(number_format($totalRevenue, 2)); ?></td>
                    </tr>
                </tbody>
            </table>
        </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <section class="section">
        <h2>Booking Activity</h2>
        <table>
            <thead><tr><th>House</th><th>Month with most check-ins</th><th>Check-ins that month</th><th>Top booked dates</th></tr></thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $activityRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $topDates = collect($row['top_dates'])->map(fn (array $date): string => $date['date'].' ('.$date['bookings'].')')->implode(', ');
                    ?>
                    <tr>
                        <td><?php echo e($row['house']); ?></td>
                        <td><?php echo e($row['most_booked_month']); ?></td>
                        <td><?php echo e($row['month_bookings']); ?></td>
                        <td><?php echo e($topDates ?: 'No booked dates'); ?></td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    </section>
</body>
</html>
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/bookings/reports-export.blade.php ENDPATH**/ ?>
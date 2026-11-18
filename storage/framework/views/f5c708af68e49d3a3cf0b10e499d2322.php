<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Reports | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Reports | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> 
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dashboardPrefix === 'owner'): ?>
            <?php echo $__env->make('partials.owner-nav', ['activePage' => 'reports'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('partials.staff-nav', ['activePage' => 'reports'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="container workspace-page">
        <div class="page-header-row page-header report-page-header">
            <div>
                <h1>Reports</h1>
                <p><?php echo e($periodLabel); ?></p>
            </div>
            <div class="report-header-actions">
                <a class="btn btn-outline" href="<?php echo e(route($dashboardPrefix.'.reports.excel', $reportQuery)); ?>">
                    <i class="fa fa-file-excel"></i> Download Excel
                </a>
                <a class="btn btn-outline" href="<?php echo e(route($dashboardPrefix.'.reports.pdf', $reportQuery)); ?>">
                    <i class="fa fa-file-pdf"></i> Download PDF
                </a>
            </div>
        </div>

        <div class="report-toolbar">
            <div class="report-period-tabs" role="tablist" aria-label="Report period">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['weekly' => 'Weekly', 'monthly' => 'Monthly', 'annually' => 'Annually']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $periodKey => $periodTitle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <a
                        class="report-period-tab <?php echo e($period === $periodKey ? 'active' : ''); ?>"
                        href="<?php echo e(route($dashboardPrefix.'.bookings.index', array_merge($reportQuery, ['period' => $periodKey]))); ?>"
                        <?php if($period === $periodKey): ?> aria-current="page" <?php endif; ?>
                    ><?php echo e($periodTitle); ?></a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>

        <form class="report-range-filter" method="GET" action="<?php echo e(route($dashboardPrefix.'.bookings.index')); ?>" id="report-filter-form">
            <input type="hidden" name="section" value="reports">
            <input type="hidden" name="period" value="<?php echo e($period); ?>">
            <div class="report-range-field">
                <label for="report-year">Year</label>
                <select id="report-year" name="year">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = range(2000, now()->year); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($yearOption); ?>" <?php if($selectedYear === $yearOption): echo 'selected'; endif; ?>><?php echo e($yearOption); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <div class="report-range-field">
                <label for="report-quarter">Quarter</label>
                <select id="report-quarter" name="quarter">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['All', 'Q1', 'Q2', 'Q3', 'Q4', 'Custom']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quarterOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($quarterOption); ?>" <?php if($selectedQuarter === $quarterOption): echo 'selected'; endif; ?>><?php echo e($quarterOption); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <div class="report-range-field">
                <label for="report-house">House</label>
                <select id="report-house" name="house_id">
                    <option value="">All my houses</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $houses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $house): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($house->house_id); ?>" <?php if($selectedHouseId === $house->house_id): echo 'selected'; endif; ?>><?php echo e($house->name); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <div class="report-range-field">
                <label for="report-from-date">From date</label>
                <input id="report-from-date" type="date" name="from_date" value="<?php echo e(old('from_date', $fromDate)); ?>" required>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['from_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="form-error"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="report-range-field">
                <label for="report-to-date">To date</label>
                <input id="report-to-date" type="date" name="to_date" value="<?php echo e(old('to_date', $toDate)); ?>" required>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['to_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="form-error"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="report-range-actions">
                <button class="btn btn-primary" type="submit">Apply filters</button>
                <a class="btn btn-outline" href="<?php echo e(route($dashboardPrefix.'.bookings.index', ['section' => 'reports'])); ?>">Reset</a>
            </div>
        </form>

        <div class="stats-grid report-summary">
            <div class="stat-card">
                <span class="stat-icon green"><i class="fa fa-peso-sign"></i></span>
                <div>
                    <div class="stat-value">₱<?php echo e(number_format($totalRevenue, 2)); ?></div>
                    <div class="stat-label">Net Revenue (after refunds)</div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalRefunded > 0): ?>
                        <small class="text-muted">Refunds: <span style="color:#c0392b">-₱<?php echo e(number_format($totalRefunded, 2)); ?></span></small>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <div class="stat-card">
                <span class="stat-icon blue"><i class="fa fa-calendar-check"></i></span>
                <div><div class="stat-value"><?php echo e($totalBookings); ?></div><div class="stat-label">Bookings in range</div></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon orange"><i class="fa fa-circle-check"></i></span>
                <div><div class="stat-value"><?php echo e($completedBookings); ?></div><div class="stat-label">Completed bookings</div></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon red"><i class="fa fa-clock"></i></span>
                <div><div class="stat-value"><?php echo e($pendingBookings); ?></div><div class="stat-label">Pending requests</div></div>
            </div>
        </div>

        <div class="report-content-grid">
            <section class="card report-card">
                <div class="card-header">
                    <span><i class="fa fa-chart-column"></i> Net Revenue (after refunds)</span>
                    <span class="report-period-caption"><?php echo e($periodLabel); ?></span>
                </div>
                <div class="report-bars">
                    <?php
                        $maximumRevenue = max((float) $chartRows->max('revenue'), 1);
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $chartRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="report-bar-row">
                            <span class="report-bar-label"><?php echo e($row['label']); ?></span>
                            <div class="report-bar-track" role="img" aria-label="<?php echo e($row['label']); ?>: ₱<?php echo e(number_format($row['revenue'], 2)); ?>">
                                <span class="report-bar-fill" style="width: <?php echo e(min(100, max(0, (float) $row['revenue'] / $maximumRevenue * 100))); ?>%"></span>
                            </div>
                            <strong>₱<?php echo e(number_format($row['revenue'], 2)); ?></strong>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="empty-state compact-empty"><i class="fa fa-chart-line"></i><h3>No revenue data</h3><p>Verified payments will appear here.</p></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>

            <section class="card report-card">
                <div class="card-header"><span><i class="fa fa-wallet"></i> Verified payment methods</span></div>
                <div class="payment-breakdown">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $paymentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method => $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="payment-breakdown-row">
                            <span><i class="fa fa-credit-card"></i> <?php echo e(ucwords(str_replace('_', ' ', $method))); ?></span>
                            <strong>₱<?php echo e(number_format($amount, 2)); ?></strong>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="empty-state compact-empty"><i class="fa fa-receipt"></i><h3>No payments yet</h3><p>There are no verified payments in this range.</p></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        </div>

        <section class="card report-top-units">
            <div class="card-header"><span>Revenue by period — <?php echo e(ucfirst($period)); ?></span></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th><?php echo e(ucfirst($period)); ?></th><th>Payments received</th><th>Refunds</th><th>Net Revenue (after refunds)</th></tr></thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $revenueByPeriodRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td><?php echo e($row['period']); ?></td>
                                <td>₱<?php echo e(number_format($row['payments'], 2)); ?></td>
                                <td style="color:#c0392b"><?php echo e($row['refunds'] > 0 ? '-₱'.number_format($row['refunds'], 2) : '₱0.00'); ?></td>
                                <td><strong>₱<?php echo e(number_format($row['revenue'], 2)); ?></strong></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="4" class="empty-table-cell">No revenue for these filters.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card report-top-units">
            <div class="card-header"><span>Revenue per House</span></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>House</th><th>Payments received</th><th>Refunds</th><th>Net Revenue (after refunds)</th></tr></thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $revenueByHouse; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr><td><?php echo e($row['house']); ?></td><td>₱<?php echo e(number_format($row['payments'], 2)); ?></td><td style="color:#c0392b"><?php echo e($row['refunds'] > 0 ? '-₱'.number_format($row['refunds'], 2) : '₱0.00'); ?></td><td><strong>₱<?php echo e(number_format($row['revenue'], 2)); ?></strong></td></tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="4" class="empty-table-cell">No houses found.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card report-top-units">
            <div class="card-header"><span>Revenue per Unit</span></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Unit</th><th>House</th><th>Payments received</th><th>Refunds</th><th>Net Revenue (after refunds)</th></tr></thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $revenueByUnit; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr><td><?php echo e($row['unit']); ?></td><td><?php echo e($row['house']); ?></td><td>₱<?php echo e(number_format($row['payments'], 2)); ?></td><td style="color:#c0392b"><?php echo e($row['refunds'] > 0 ? '-₱'.number_format($row['refunds'], 2) : '₱0.00'); ?></td><td><strong>₱<?php echo e(number_format($row['revenue'], 2)); ?></strong></td></tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="5" class="empty-table-cell">No units found.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($period === 'annually'): ?>
            <section class="card report-top-units">
                <div class="card-header"><span>Revenue per Quarter — <?php echo e($selectedYear); ?></span></div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Quarter</th><th>Payments received</th><th>Refunds</th><th>Net Revenue (after refunds)</th></tr></thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $quarterRevenueRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr><td><?php echo e($row['quarter']); ?></td><td>₱<?php echo e(number_format($row['payments'], 2)); ?></td><td style="color:#c0392b"><?php echo e($row['refunds'] > 0 ? '-₱'.number_format($row['refunds'], 2) : '₱0.00'); ?></td><td><strong>₱<?php echo e(number_format($row['revenue'], 2)); ?></strong></td></tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <section class="card report-top-units">
            <div class="card-header"><span>Booking Activity per House</span></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>House</th><th>Month with most check-ins</th><th>Check-ins that month</th><th>Top 5 booked dates</th></tr></thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $activityRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td><?php echo e($row['house']); ?></td>
                                <td><?php echo e($row['most_booked_month']); ?></td>
                                <td><?php echo e($row['month_bookings']); ?></td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = $row['top_dates']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <span><?php echo e(\Illuminate\Support\Carbon::parse($date['date'])->format('M j, Y')); ?> (<?php echo e($date['bookings']); ?>)</span><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $loop->last): ?>, <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        No booked dates
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="4" class="empty-table-cell">No houses found.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        const reportYear = document.getElementById('report-year');
        const reportQuarter = document.getElementById('report-quarter');
        const reportFrom = document.getElementById('report-from-date');
        const reportTo = document.getElementById('report-to-date');

        function fillQuarterRange() {
            const year = Number(reportYear.value);
            const quarter = reportQuarter.value;
            const startMonth = quarter === 'All' ? 1 : (Number(quarter.slice(1)) - 1) * 3 + 1;
            const endMonth = quarter === 'All' ? 12 : startMonth + 2;
            const start = new Date(year, startMonth - 1, 1);
            const end = new Date(year, endMonth, 0);
            reportFrom.value = `${year}-${String(start.getMonth() + 1).padStart(2, '0')}-${String(start.getDate()).padStart(2, '0')}`;
            reportTo.value = `${year}-${String(end.getMonth() + 1).padStart(2, '0')}-${String(end.getDate()).padStart(2, '0')}`;
        }

        reportQuarter.addEventListener('change', () => {
            if (reportQuarter.value !== 'Custom') {
                fillQuarterRange();
            }
        });
        reportYear.addEventListener('change', () => {
            if (reportQuarter.value !== 'Custom') {
                fillQuarterRange();
            }
        });
        [reportFrom, reportTo].forEach((field) => {
            field.addEventListener('input', () => {
                reportQuarter.value = 'Custom';
            });
        });
    </script>
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
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/bookings/reports.blade.php ENDPATH**/ ?>
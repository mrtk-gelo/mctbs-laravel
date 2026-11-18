<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Bookings | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Bookings | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> <?php echo $__env->make('partials.nav', ['activePage' => 'bookings'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php $__env->endSlot(); ?>

    <div class="container" data-booking-filter-root>
        <div class="page-header mt-3">
            <h1>Bookings</h1>
            <p>Track and manage all your reservations</p>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div class="alert alert-success mb-3" role="status"><?php echo e(session('success')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="mb-3">
            <a href="<?php echo e(route('public.transients')); ?>" class="btn btn-primary">
                <i class="fa fa-search"></i> Browse transient houses
            </a>
        </div>

        <?php echo $__env->make('bookings.partials.list-filters', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="tabs">
            <button type="button" class="tab-btn active" data-tab="tab-pending">Pending (<?php echo e($pending->count()); ?>)</button>
            <button type="button" class="tab-btn" data-tab="tab-accepted">Accepted (<?php echo e($accepted->count()); ?>)</button>
            <button type="button" class="tab-btn" data-tab="tab-completed">History (<?php echo e($completed->count()); ?>)</button>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['tab-pending' => $pending, 'tab-accepted' => $accepted, 'tab-completed' => $completed]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tabId => $list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="tab-pane <?php if($loop->first): ?> active <?php endif; ?>" id="<?php echo e($tabId); ?>" data-booking-filter-panel>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($list->isEmpty()): ?>
                    <div class="empty-state" data-filter-empty><i class="fa fa-calendar"></i><h3>No bookings here</h3></div>
                <?php else: ?>
                    <div data-booking-list>
                        <div data-booking-items>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $canonicalStatus = \App\Support\BookingStatus::canonical($b->status);
                                ?>
                                <div class="card mb-3" data-booking-item data-created-at="<?php echo e($b->created_at?->timestamp ?? 0); ?>" data-check-in="<?php echo e(\Illuminate\Support\Carbon::parse($b->check_in)->toDateString()); ?>" data-check-out="<?php echo e(\Illuminate\Support\Carbon::parse($b->check_out)->toDateString()); ?>">
                                    <div class="card-body flex-between" style="flex-wrap:wrap;gap:12px">
                                        <div>
                                            <div class="fw-bold" style="font-size:16px"><?php echo e($b->unit->unit_type); ?></div>
                                            <div class="text-muted fs-sm"><?php echo e($b->unit->house->name); ?> — <?php echo e($b->unit->house->city); ?></div>
                                            <div class="fs-sm mt-1">
                                                <i class="fa fa-calendar-alt"></i> <?php echo e(\Illuminate\Support\Carbon::parse($b->check_in)->format('M j, Y')); ?> → <?php echo e(\Illuminate\Support\Carbon::parse($b->check_out)->format('M j, Y')); ?>

                                            </div>
                                            <a href="<?php echo e(route('guest.bookings.show', $b->booking_id)); ?>" class="btn btn-outline-primary btn-sm mt-2">View booking</a>
                                        </div>
                                        <div style="text-align:right">
                                            <div class="fw-bold" style="font-size:18px;color:var(--accent)">₱<?php echo e(number_format($b->amountDueBeforeCheckout(), 2)); ?></div>
                                            <?php echo $__env->make('bookings.partials.status-indicator', [
                                                'booking' => $b,
                                                'statusBadge' => 'badge-'.strtolower($canonicalStatus),
                                                'statusLabel' => $canonicalStatus,
                                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            <div class="fs-sm text-muted mt-1">Booking #<?php echo e($b->booking_id); ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                    <div class="empty-state" data-filter-empty hidden><i class="fa fa-filter"></i><h3>No bookings match these filters</h3></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

    <script>
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById(btn.dataset.tab).classList.add('active');
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
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/guest/dashboard.blade.php ENDPATH**/ ?>
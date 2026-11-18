<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Calendar | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Calendar | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> 
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dashboardPrefix === 'owner'): ?>
            <?php echo $__env->make('partials.owner-nav', ['activePage' => 'calendar'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('partials.staff-nav', ['activePage' => 'calendar'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="container workspace-page">
    <div class="page-header-row page-header">
        <div>
            <h1>Unit Calendar</h1>
            <p>View confirmed stays and date-specific availability for your properties.</p>
            </div>
        </div>

        <div class="calendar-layout">
            <aside class="calendar-sidebar">
                <div class="card">
                    <div class="card-header"><span><i class="fa fa-door-open"></i> Select a unit</span></div>
                    <div class="calendar-unit-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $calendarUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <a
                                class="calendar-unit-option <?php echo e($selectedUnit?->unit_id === $unit->unit_id ? 'active' : ''); ?>"
                                href="<?php echo e(route($dashboardPrefix.'.bookings.index', ['section' => 'calendar', 'unit_id' => $unit->unit_id, 'month' => $calendarMonth->month, 'year' => $calendarMonth->year])); ?>"
                            >
                                <span class="calendar-unit-icon"><i class="fa fa-bed"></i></span>
                                <span><strong><?php echo e($unit->unit_type); ?></strong><small><?php echo e($unit->house->name); ?></small></span>
                                <i class="fa fa-chevron-right unit-option-arrow"></i>
                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="empty-state compact-empty">
                                <i class="fa fa-door-closed"></i>
                                <h3>No units available</h3>
                                <p>Add or assign a unit before using the calendar.</p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedUnit): ?>
                    <section class="card calendar-block-card">
                        <div class="card-body">
                            <p class="calendar-block-help">Select available dates in the calendar to block them, then add a reason.</p>
                            <form method="POST" action="<?php echo e(route($dashboardPrefix.'.bookings.calendar-blocks.store', ['section' => 'calendar', 'unit_id' => $selectedUnit->unit_id, 'month' => $calendarMonth->month, 'year' => $calendarMonth->year])); ?>" class="calendar-block-form" data-calendar-block-form>
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="unit_id" value="<?php echo e($selectedUnit->unit_id); ?>">
                                <div data-calendar-selected-inputs></div>
                                <p class="calendar-selection-count" data-calendar-selection-count aria-live="polite">No dates selected</p>
                                <div class="form-group">
                                    <label for="calendar-block-note">Reason <span class="text-muted fs-sm">(optional)</span></label>
                                    <input id="calendar-block-note" type="text" name="note" maxlength="255" value="<?php echo e(old('note')); ?>" placeholder="Maintenance or personal use">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['note'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="form-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['dates'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="form-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['dates.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="form-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <button type="submit" class="btn btn-primary" data-calendar-block-submit disabled>Block date(s)</button>
                            </form>
                        </div>
                    </section>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </aside>

            <section class="card calendar-main">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedUnit): ?>
                    <div class="calendar-heading">
                        <div>
                            <h2><?php echo e($calendarMonth->format('F Y')); ?></h2>
                            <p><?php echo e($selectedUnit->house->name); ?> <span aria-hidden="true">·</span> <?php echo e($selectedUnit->unit_type); ?></p>
                        </div>
                        <div class="calendar-month-nav">
                            <a
                                class="btn btn-outline btn-sm"
                                aria-label="Previous month"
                                href="<?php echo e(route($dashboardPrefix.'.bookings.index', ['section' => 'calendar', 'unit_id' => $selectedUnit->unit_id, 'month' => $previousMonth->month, 'year' => $previousMonth->year])); ?>"
                            ><i class="fa fa-chevron-left"></i></a>
                            <a
                                class="btn btn-outline btn-sm"
                                aria-label="Next month"
                                href="<?php echo e(route($dashboardPrefix.'.bookings.index', ['section' => 'calendar', 'unit_id' => $selectedUnit->unit_id, 'month' => $nextMonth->month, 'year' => $nextMonth->year])); ?>"
                            ><i class="fa fa-chevron-right"></i></a>
                        </div>
                    </div>

                    <div class="calendar-weekdays" aria-hidden="true">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $weekday): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <span><?php echo e($weekday); ?></span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                    <div class="availability-calendar-grid">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($blank = 0; $blank < $weekdayOffset; $blank++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <span class="calendar-day calendar-day-empty" aria-hidden="true"></span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $calendarCells; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $booking = $cell['booking'];
                                $dayLabel = $cell['date']->format('F j, Y');
                                $isPastWithoutBooking = $cell['isPast'] && ! $booking;
                                $statusClass = $isPastWithoutBooking ? 'past' : strtolower($cell['status']);
                                if ($cell['checkoutTurnover']) {
                                    $statusClass .= ' checkout-turnover';
                                }
                                $turnoverDetails = 'Check-out at '.\App\Support\UnitTimeFormatter::format((string) $selectedUnit->check_out_time)
                                    .' · check-in available at '.\App\Support\UnitTimeFormatter::format((string) $selectedUnit->check_in_time);
                                $cellTitle = $booking
                                    ? $booking->displayGuestName().' · Booking #'.$booking->booking_id.' · '.$booking->check_in->format('M j').'–'.$booking->check_out->format('M j')
                                        .($cell['checkoutTurnover'] ? ' · '.$turnoverDetails : '')
                                    : ($isPastWithoutBooking ? $dayLabel : ($cell['note'] ?: $cell['status'].' · '.$dayLabel));
                                $isSelectable = $cell['status'] === 'Available' && $cell['date']->gte($calendarToday);
                            ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking): ?>
                                <a
                                    class="calendar-day <?php echo e($statusClass); ?> <?php echo e($cell['isToday'] ? 'today' : ''); ?>"
                                    href="<?php echo e(route($dashboardPrefix.'.bookings.show', $booking->booking_id)); ?>"
                                    aria-label="<?php echo e($dayLabel); ?>: <?php echo e($cellTitle); ?>. Guest <?php echo e($booking->displayGuestName()); ?>. Contact <?php echo e($booking->displayGuestPhone() ?: 'not provided'); ?>. Open booking details."
                                    data-calendar-booking
                                    data-calendar-date="<?php echo e($dayLabel); ?>"
                                    data-calendar-booking-id="<?php echo e($booking->booking_id); ?>"
                                    data-calendar-guest="<?php echo e($booking->displayGuestName()); ?>"
                                    data-calendar-phone="<?php echo e($booking->displayGuestPhone() ?: 'Not provided'); ?>"
                                    data-calendar-status="<?php echo e($cell['checkoutTurnover'] ? \App\Support\BookingStatus::canonical($booking->status).' · '.$turnoverDetails : \App\Support\BookingStatus::canonical($booking->status)); ?>"
                                >
                                    <span class="calendar-day-number"><?php echo e($cell['date']->day); ?></span>
                                    <span class="calendar-day-status"><?php echo e($cell['checkoutTurnover'] ? 'Check-out / available' : $cell['status']); ?></span>
                                    <span class="calendar-day-detail"><?php echo e($cell['checkoutTurnover'] ? $turnoverDetails : '#'.$booking->booking_id); ?></span>
                                </a>
                            <?php else: ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSelectable): ?>
                                    <button
                                        type="button"
                                        class="calendar-day <?php echo e($statusClass); ?> calendar-day-selectable <?php echo e($cell['isToday'] ? 'today' : ''); ?>"
                                        title="<?php echo e($cellTitle); ?>"
                                        aria-label="<?php echo e($dayLabel); ?>: <?php echo e($cellTitle); ?>. Select date to block."
                                        aria-pressed="false"
                                        data-calendar-select-date="<?php echo e($cell['date']->toDateString()); ?>"
                                    >
                                <?php else: ?>
                                <div
                                    class="calendar-day <?php echo e($statusClass); ?> <?php echo e($cell['isToday'] ? 'today' : ''); ?>"
                                    title="<?php echo e($cellTitle); ?>"
                                    aria-label="<?php echo e($dayLabel); ?>: <?php echo e($cellTitle); ?>"
                                >
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <span class="calendar-day-number"><?php echo e($cell['date']->day); ?></span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($isPastWithoutBooking)): ?>
                                        <span class="calendar-day-status"><?php echo e($cell['status']); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cell['note'] && ! $isPastWithoutBooking): ?>
                                        <span class="calendar-day-detail"><?php echo e($cell['note']); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $isPastWithoutBooking && $cell['status'] === 'Unavailable' && $availabilityByDate->has($cell['date']->toDateString())): ?>
                                        <form method="POST" action="<?php echo e(route($dashboardPrefix.'.bookings.calendar-blocks.destroy', ['availabilityId' => $availabilityByDate->get($cell['date']->toDateString())->availability_id, 'section' => 'calendar', 'unit_id' => $selectedUnit->unit_id, 'month' => $calendarMonth->month, 'year' => $calendarMonth->year])); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-outline btn-sm" aria-label="Remove block for <?php echo e($dayLabel); ?>">Unblock</button>
                                        </form>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSelectable): ?>
                                    </button>
                                <?php else: ?>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                    <p class="calendar-readonly-note"><i class="fa fa-circle-info"></i> Hover over or focus a booked date to see guest contact details. Select it to open the booking. Date blocks prevent new requests and can be removed from unavailable dates.</p>
                    <div id="calendar-booking-popover" class="calendar-booking-popover" role="status" aria-live="polite" hidden>
                        <div class="calendar-booking-popover-heading">
                            <span class="calendar-booking-popover-icon"><i class="fa fa-user"></i></span>
                            <span><strong data-calendar-popover-guest></strong><small data-calendar-popover-date></small></span>
                        </div>
                        <div class="calendar-booking-popover-detail"><i class="fa fa-phone"></i><span data-calendar-popover-phone></span></div>
                        <div class="calendar-booking-popover-detail"><i class="fa fa-calendar-check"></i><span data-calendar-popover-status></span></div>
                        <div class="calendar-booking-popover-detail"><i class="fa fa-hashtag"></i><span data-calendar-popover-id></span></div>
                    </div>
                    <script>
                        (() => {
                            const blockForm = document.querySelector('[data-calendar-block-form]');
                            if (blockForm) {
                                const selectedInputs = blockForm.querySelector('[data-calendar-selected-inputs]');
                                const selectionCount = blockForm.querySelector('[data-calendar-selection-count]');
                                const submitButton = blockForm.querySelector('[data-calendar-block-submit]');
                                const selectedDates = new Set();

                                const updateSelection = () => {
                                    selectedInputs.replaceChildren(...Array.from(selectedDates, (date) => {
                                        const input = document.createElement('input');
                                        input.type = 'hidden';
                                        input.name = 'dates[]';
                                        input.value = date;
                                        return input;
                                    }));
                                    selectionCount.textContent = selectedDates.size
                                        ? `${selectedDates.size} date${selectedDates.size === 1 ? '' : 's'} selected`
                                        : 'No dates selected';
                                    submitButton.disabled = selectedDates.size === 0;
                                };

                                document.querySelectorAll('[data-calendar-select-date]').forEach((tile) => {
                                    tile.addEventListener('click', () => {
                                        const date = tile.dataset.calendarSelectDate;
                                        if (selectedDates.has(date)) {
                                            selectedDates.delete(date);
                                        } else {
                                            selectedDates.add(date);
                                        }

                                        tile.classList.toggle('selected', selectedDates.has(date));
                                        tile.setAttribute('aria-pressed', String(selectedDates.has(date)));
                                        updateSelection();
                                    });
                                });
                            }

                            const popover = document.getElementById('calendar-booking-popover');
                            if (!popover) {
                                return;
                            }

                            const guest = popover.querySelector('[data-calendar-popover-guest]');
                            const date = popover.querySelector('[data-calendar-popover-date]');
                            const phone = popover.querySelector('[data-calendar-popover-phone]');
                            const status = popover.querySelector('[data-calendar-popover-status]');
                            const bookingId = popover.querySelector('[data-calendar-popover-id]');
                            let activeCell = null;

                            const hidePopover = () => {
                                popover.hidden = true;
                                activeCell = null;
                            };

                            const showPopover = (cell) => {
                                activeCell = cell;
                                guest.textContent = cell.dataset.calendarGuest;
                                date.textContent = cell.dataset.calendarDate;
                                phone.textContent = cell.dataset.calendarPhone;
                                status.textContent = cell.dataset.calendarStatus;
                                bookingId.textContent = `Booking #${cell.dataset.calendarBookingId}`;
                                popover.hidden = false;

                                const bounds = cell.getBoundingClientRect();
                                const popoverBounds = popover.getBoundingClientRect();
                                const margin = 12;
                                const left = Math.min(
                                    Math.max(margin, bounds.left + (bounds.width - popoverBounds.width) / 2),
                                    window.innerWidth - popoverBounds.width - margin
                                );
                                const top = bounds.bottom + popoverBounds.height + margin <= window.innerHeight
                                    ? bounds.bottom + margin
                                    : Math.max(margin, bounds.top - popoverBounds.height - margin);

                                popover.style.left = `${left}px`;
                                popover.style.top = `${top}px`;
                            };

                            document.querySelectorAll('[data-calendar-booking]').forEach((cell) => {
                                cell.addEventListener('mouseenter', () => showPopover(cell));
                                cell.addEventListener('mouseleave', () => {
                                    if (document.activeElement !== cell) {
                                        hidePopover();
                                    }
                                });
                                cell.addEventListener('focus', () => showPopover(cell));
                                cell.addEventListener('blur', hidePopover);
                            });

                            window.addEventListener('resize', () => {
                                if (activeCell) {
                                    showPopover(activeCell);
                                }
                            });
                            window.addEventListener('scroll', hidePopover, true);
                            document.addEventListener('keydown', (event) => {
                                if (event.key === 'Escape') {
                                    hidePopover();
                                }
                            });
                        })();
                    </script>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa fa-calendar"></i>
                        <h3>Select a unit</h3>
                        <p>Choose a unit from the list to see its bookings and availability.</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>
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
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/bookings/calendar.blade.php ENDPATH**/ ?>
<x-shell title="Calendar | MCTBS">
    <x-slot:nav>
        @if ($dashboardPrefix === 'owner')
            @include('partials.owner-nav', ['activePage' => 'calendar'])
        @else
            @include('partials.staff-nav', ['activePage' => 'calendar'])
        @endif
    </x-slot:nav>

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
                        @forelse ($calendarUnits as $unit)
                            <a
                                class="calendar-unit-option {{ $selectedUnit?->unit_id === $unit->unit_id ? 'active' : '' }}"
                                href="{{ route($dashboardPrefix.'.bookings.index', ['section' => 'calendar', 'unit_id' => $unit->unit_id, 'month' => $calendarMonth->month, 'year' => $calendarMonth->year]) }}"
                            >
                                <span class="calendar-unit-icon"><i class="fa fa-bed"></i></span>
                                <span><strong>{{ $unit->unit_type }}</strong><small>{{ $unit->house->name }}</small></span>
                                <i class="fa fa-chevron-right unit-option-arrow"></i>
                            </a>
                        @empty
                            <div class="empty-state compact-empty">
                                <i class="fa fa-door-closed"></i>
                                <h3>No units available</h3>
                                <p>Add or assign a unit before using the calendar.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                @if ($selectedUnit)
                    <section class="card calendar-block-card">
                        <div class="card-body">
                            <p class="calendar-block-help">Select available dates in the calendar to block them, then add a reason.</p>
                            <form method="POST" action="{{ route($dashboardPrefix.'.bookings.calendar-blocks.store', ['section' => 'calendar', 'unit_id' => $selectedUnit->unit_id, 'month' => $calendarMonth->month, 'year' => $calendarMonth->year]) }}" class="calendar-block-form" data-calendar-block-form>
                                @csrf
                                <input type="hidden" name="unit_id" value="{{ $selectedUnit->unit_id }}">
                                <div data-calendar-selected-inputs></div>
                                <p class="calendar-selection-count" data-calendar-selection-count aria-live="polite">No dates selected</p>
                                <div class="form-group">
                                    <label for="calendar-block-note">Reason <span class="text-muted fs-sm">(optional)</span></label>
                                    <input id="calendar-block-note" type="text" name="note" maxlength="255" value="{{ old('note') }}" placeholder="Maintenance or personal use">
                                    @error('note')<span class="form-error">{{ $message }}</span>@enderror
                                </div>
                                @error('dates')<span class="form-error">{{ $message }}</span>@enderror
                                @error('dates.*')<span class="form-error">{{ $message }}</span>@enderror
                                <button type="submit" class="btn btn-primary" data-calendar-block-submit disabled>Block date(s)</button>
                            </form>
                        </div>
                    </section>
                @endif
            </aside>

            <section class="card calendar-main">
                @if ($selectedUnit)
                    <div class="calendar-heading">
                        <div>
                            <h2>{{ $calendarMonth->format('F Y') }}</h2>
                            <p>{{ $selectedUnit->house->name }} <span aria-hidden="true">·</span> {{ $selectedUnit->unit_type }}</p>
                        </div>
                        <div class="calendar-month-nav">
                            <a
                                class="btn btn-outline btn-sm"
                                aria-label="Previous month"
                                href="{{ route($dashboardPrefix.'.bookings.index', ['section' => 'calendar', 'unit_id' => $selectedUnit->unit_id, 'month' => $previousMonth->month, 'year' => $previousMonth->year]) }}"
                            ><i class="fa fa-chevron-left"></i></a>
                            <a
                                class="btn btn-outline btn-sm"
                                aria-label="Next month"
                                href="{{ route($dashboardPrefix.'.bookings.index', ['section' => 'calendar', 'unit_id' => $selectedUnit->unit_id, 'month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
                            ><i class="fa fa-chevron-right"></i></a>
                        </div>
                    </div>

                    <div class="calendar-weekdays" aria-hidden="true">
                        @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)
                            <span>{{ $weekday }}</span>
                        @endforeach
                    </div>
                    <div class="availability-calendar-grid">
                        @for ($blank = 0; $blank < $weekdayOffset; $blank++)
                            <span class="calendar-day calendar-day-empty" aria-hidden="true"></span>
                        @endfor

                        @foreach ($calendarCells as $cell)
                            @php
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
                            @endphp
                            @if ($booking)
                                <a
                                    class="calendar-day {{ $statusClass }} {{ $cell['isToday'] ? 'today' : '' }}"
                                    href="{{ route($dashboardPrefix.'.bookings.show', $booking->booking_id) }}"
                                    aria-label="{{ $dayLabel }}: {{ $cellTitle }}. Guest {{ $booking->displayGuestName() }}. Contact {{ $booking->displayGuestPhone() ?: 'not provided' }}. Open booking details."
                                    data-calendar-booking
                                    data-calendar-date="{{ $dayLabel }}"
                                    data-calendar-booking-id="{{ $booking->booking_id }}"
                                    data-calendar-guest="{{ $booking->displayGuestName() }}"
                                    data-calendar-phone="{{ $booking->displayGuestPhone() ?: 'Not provided' }}"
                                    data-calendar-status="{{ $cell['checkoutTurnover'] ? \App\Support\BookingStatus::canonical($booking->status).' · '.$turnoverDetails : \App\Support\BookingStatus::canonical($booking->status) }}"
                                >
                                    <span class="calendar-day-number">{{ $cell['date']->day }}</span>
                                    <span class="calendar-day-status">{{ $cell['checkoutTurnover'] ? 'Check-out / available' : $cell['status'] }}</span>
                                    <span class="calendar-day-detail">{{ $cell['checkoutTurnover'] ? $turnoverDetails : '#'.$booking->booking_id }}</span>
                                </a>
                            @else
                                @if ($isSelectable)
                                    <button
                                        type="button"
                                        class="calendar-day {{ $statusClass }} calendar-day-selectable {{ $cell['isToday'] ? 'today' : '' }}"
                                        title="{{ $cellTitle }}"
                                        aria-label="{{ $dayLabel }}: {{ $cellTitle }}. Select date to block."
                                        aria-pressed="false"
                                        data-calendar-select-date="{{ $cell['date']->toDateString() }}"
                                    >
                                @else
                                <div
                                    class="calendar-day {{ $statusClass }} {{ $cell['isToday'] ? 'today' : '' }}"
                                    title="{{ $cellTitle }}"
                                    aria-label="{{ $dayLabel }}: {{ $cellTitle }}"
                                >
                                @endif
                                    <span class="calendar-day-number">{{ $cell['date']->day }}</span>
                                    @unless ($isPastWithoutBooking)
                                        <span class="calendar-day-status">{{ $cell['status'] }}</span>
                                    @endunless
                                    @if ($cell['note'] && ! $isPastWithoutBooking)
                                        <span class="calendar-day-detail">{{ $cell['note'] }}</span>
                                    @endif
                                    @if (! $isPastWithoutBooking && $cell['status'] === 'Unavailable' && $availabilityByDate->has($cell['date']->toDateString()))
                                        <form method="POST" action="{{ route($dashboardPrefix.'.bookings.calendar-blocks.destroy', ['availabilityId' => $availabilityByDate->get($cell['date']->toDateString())->availability_id, 'section' => 'calendar', 'unit_id' => $selectedUnit->unit_id, 'month' => $calendarMonth->month, 'year' => $calendarMonth->year]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline btn-sm" aria-label="Remove block for {{ $dayLabel }}">Unblock</button>
                                        </form>
                                    @endif
                                @if ($isSelectable)
                                    </button>
                                @else
                                </div>
                                @endif
                            @endif
                        @endforeach
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
                @else
                    <div class="empty-state">
                        <i class="fa fa-calendar"></i>
                        <h3>Select a unit</h3>
                        <p>Choose a unit from the list to see its bookings and availability.</p>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-shell>

<?php

use App\Models\Booking;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\UnitCalendar;
use App\Models\User;
use App\Services\BookingService;
use App\Services\BookingWorkflowService;
use App\Support\BookingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function makeCalendarBlockScenario(): array
{
    $owner = User::create([
        'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Calendar',
        'last_name' => 'Owner',
        'email' => 'calendar-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Calendar House',
        'address' => '10 Calendar Road',
        'city' => 'Baguio',
        'contact_number' => '5553000',
        'is_active' => true,
    ]);
    $unit = Unit::create([
        'house_id' => $house->house_id,
        'unit_type' => 'Garden Room',
        'capacity' => 4,
        'price' => 1800,
        'status' => 'Available',
    ]);

    return compact('owner', 'house', 'unit');
}

test('owners and staff see the same calendar block controls without temporary hold dates', function () {
    ['owner' => $owner, 'house' => $house, 'unit' => $unit] = makeCalendarBlockScenario();
    $staff = User::create([
        'role_id' => Role::where('name', 'Staff')->firstOrFail()->role_id,
        'first_name' => 'Calendar',
        'last_name' => 'Staff',
        'email' => 'calendar-staff@example.com',
        'password' => 'StaffPass123!',
        'is_active' => true,
    ]);
    Invitation::create([
        'invited_by' => $owner->user_id,
        'house_id' => $house->house_id,
        'role_id' => Role::where('name', 'Staff')->firstOrFail()->role_id,
        'email' => $staff->email,
        'token' => 'calendar-staff-invite',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);

    foreach ([
        [$owner, 'owner.bookings.index'],
        [$staff, 'staff.bookings.index'],
    ] as [$user, $route]) {
        $this->actingAs($user)
            ->get(route($route, ['section' => 'calendar', 'unit_id' => $unit->unit_id]))
            ->assertOk()
            ->assertSee('data-calendar-block-form', false)
            ->assertSee('Block date(s)')
            ->assertSee('Unit Calendar')
            ->assertDontSee('Temporary hold dates')
            ->assertDontSee('Block from')
            ->assertDontSee('Through');
    }
});

test('an owner can block multiple selected dates with a shared reason', function () {
    ['owner' => $owner, 'unit' => $unit] = makeCalendarBlockScenario();
    $dates = [now()->addDays(3)->toDateString(), now()->addDays(5)->toDateString()];

    $this->actingAs($owner)
        ->post(route('owner.bookings.calendar-blocks.store', [
            'section' => 'calendar',
            'unit_id' => $unit->unit_id,
        ]), [
            'unit_id' => $unit->unit_id,
            'dates' => $dates,
            'note' => 'Scheduled maintenance',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    foreach ($dates as $date) {
        $availability = UnitCalendar::query()
            ->where('unit_id', $unit->unit_id)
            ->whereDate('date', $date)
            ->firstOrFail();

        expect($availability->status)->toBe('Unavailable')
            ->and($availability->note)->toBe('Scheduled maintenance');
    }

    expect(UnitCalendar::where('unit_id', $unit->unit_id)->count())->toBe(2);
});

test('blocking dates preserves pending requests and prevents their acceptance', function () {
    ['owner' => $owner, 'unit' => $unit] = makeCalendarBlockScenario();
    $guest = User::create([
        'role_id' => Role::where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Calendar',
        'last_name' => 'Guest',
        'email' => 'calendar-pending-guest@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);
    $checkIn = now()->addDays(5);
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => $checkIn->toDateString(),
        'check_out' => $checkIn->copy()->addDays(2)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Pending Guest']],
    ]);
    $blockedDate = $checkIn->copy()->addDay()->toDateString();

    $this->actingAs($owner)
        ->post(route('owner.bookings.calendar-blocks.store', [
            'section' => 'calendar',
            'unit_id' => $unit->unit_id,
        ]), [
            'unit_id' => $unit->unit_id,
            'dates' => [$blockedDate],
            'note' => 'Scheduled maintenance',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($booking->fresh()->status)->toBe(BookingStatus::PENDING);
    expect($unit->availability()
        ->whereDate('date', $blockedDate)
        ->where('status', 'Unavailable')
        ->value('note'))->toBe('Scheduled maintenance');
    expect(fn () => app(BookingWorkflowService::class)->accept($owner, $booking->booking_id))
        ->toThrow(ValidationException::class);
    expect($booking->fresh()->status)->toBe(BookingStatus::PENDING);
});

test('blocking a pending booking checkout date prevents acceptance without cancelling the request', function () {
    ['owner' => $owner, 'unit' => $unit] = makeCalendarBlockScenario();
    $guest = User::create([
        'role_id' => Role::where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Checkout',
        'last_name' => 'Guest',
        'email' => 'calendar-checkout-pending@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);
    $checkIn = now()->addDays(5);
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => $checkIn->toDateString(),
        'check_out' => $checkIn->copy()->addDays(2)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Pending Guest']],
    ]);
    $checkoutDate = $booking->check_out->toDateString();

    $this->actingAs($owner)
        ->post(route('owner.bookings.calendar-blocks.store', [
            'section' => 'calendar',
            'unit_id' => $unit->unit_id,
        ]), [
            'unit_id' => $unit->unit_id,
            'dates' => [$checkoutDate],
            'note' => 'Checkout-day maintenance',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($booking->fresh()->status)->toBe(BookingStatus::PENDING);
    expect(fn () => app(BookingWorkflowService::class)->accept($owner, $booking->booking_id))
        ->toThrow(ValidationException::class);
    expect($booking->fresh()->status)->toBe(BookingStatus::PENDING);
});

test('selected dates overlapping an accepted booking cannot be blocked', function () {
    ['owner' => $owner, 'unit' => $unit] = makeCalendarBlockScenario();
    $guest = User::create([
        'role_id' => Role::where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Calendar',
        'last_name' => 'Guest',
        'email' => 'calendar-guest@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);
    $blockedDate = now()->addDays(3)->toDateString();
    Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $blockedDate.' 14:00:00',
        'check_out' => now()->addDays(4)->toDateString().' 12:00:00',
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'status' => BookingStatus::ACCEPTED,
    ]);

    $this->actingAs($owner)
        ->from(route('owner.bookings.index', ['section' => 'calendar', 'unit_id' => $unit->unit_id]))
        ->post(route('owner.bookings.calendar-blocks.store', [
            'section' => 'calendar',
            'unit_id' => $unit->unit_id,
        ]), [
            'unit_id' => $unit->unit_id,
            'dates' => [$blockedDate],
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('dates');

    expect(UnitCalendar::query()
        ->where('unit_id', $unit->unit_id)
        ->whereDate('date', $blockedDate)
        ->exists())->toBeFalse();
});

test('owner calendar grays past dates without bookings and preserves booking and turnover details', function () {
    $this->travelTo(now()->startOfMonth()->addDays(10));

    ['owner' => $owner, 'unit' => $unit] = makeCalendarBlockScenario();
    $unit->update([
        'check_in_time' => '15:30',
        'check_out_time' => '11:00',
    ]);
    $guest = User::create([
        'role_id' => Role::where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Calendar',
        'last_name' => 'Guest',
        'email' => 'calendar-turnover-guest@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);
    $pastBlockDate = today()->subDays(2);
    UnitCalendar::create([
        'unit_id' => $unit->unit_id,
        'date' => $pastBlockDate,
        'status' => 'Unavailable',
        'note' => 'Old maintenance block',
    ]);
    $pastBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->subDays(6)->setTime(14, 0),
        'check_out' => today()->subDays(4)->setTime(12, 0),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::COMPLETED,
    ]);

    $firstCheckout = today()->addDays(4);
    $sameDayCheckIn = today()->addDays(4);
    $otherCheckout = today()->addDays(8);
    $firstBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->addDays(2)->setTime(15, 30),
        'check_out' => $firstCheckout->setTime(11, 0),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    $sameDayBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $sameDayCheckIn->setTime(15, 30),
        'check_out' => today()->addDays(6)->setTime(11, 0),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    $turnoverBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->addDays(7)->setTime(15, 30),
        'check_out' => $otherCheckout->setTime(11, 0),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    $cancelledBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->addDays(10)->setTime(15, 30),
        'check_out' => today()->addDays(12)->setTime(11, 0),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CANCELLED,
    ]);
    $rejectedBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->addDays(14)->setTime(15, 30),
        'check_out' => today()->addDays(16)->setTime(11, 0),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::REJECTED,
    ]);

    $response = $this->actingAs($owner)
        ->get(route('owner.bookings.index', [
            'section' => 'calendar',
            'unit_id' => $unit->unit_id,
            'month' => today()->month,
            'year' => today()->year,
        ]))
        ->assertOk()
        ->assertSee('class="calendar-day past ', false)
        ->assertDontSee('class="calendar-day past unavailable"', false)
        ->assertDontSee('Old maintenance block')
        ->assertSee('class="calendar-day booked', false)
        ->assertSee('data-calendar-booking-id="'.$pastBooking->booking_id.'"', false)
        ->assertSee('class="calendar-day available checkout-turnover', false)
        ->assertSee('Check-out at 11:00 AM · check-in available at 3:30 PM')
        ->assertSee('data-calendar-booking-id="'.$firstBooking->booking_id.'"', false)
        ->assertSee('data-calendar-booking-id="'.$sameDayBooking->booking_id.'"', false)
        ->assertSee('data-calendar-booking-id="'.$turnoverBooking->booking_id.'"', false);

    expect($response->getContent())
        ->toContain('data-calendar-date="'.today()->addDays(4)->format('F j, Y').'"')
        ->toContain('data-calendar-date="'.$pastBooking->check_out->format('F j, Y').'"')
        ->not->toContain('class="calendar-day booked checkout-turnover"');

    $calendarHtml = $response->getContent();
    expect(preg_match(
        '/class="calendar-day booked [^"]*"[^>]*data-calendar-date="'.preg_quote($firstCheckout->format('F j, Y'), '/').'"[^>]*data-calendar-booking-id="'.$sameDayBooking->booking_id.'"/',
        $calendarHtml
    ))->toBe(1);
    expect(preg_match(
        '/class="calendar-day available checkout-turnover[^"]*"[^>]*data-calendar-date="'.preg_quote($otherCheckout->format('F j, Y'), '/').'"[^>]*data-calendar-booking-id="'.$turnoverBooking->booking_id.'"/',
        $calendarHtml
    ))->toBe(1);
    expect(preg_match(
        '/class="calendar-day booked [^"]*"[^>]*data-calendar-date="'.preg_quote($pastBooking->check_out->format('F j, Y'), '/').'"[^>]*data-calendar-booking-id="'.$pastBooking->booking_id.'"/',
        $calendarHtml
    ))->toBe(1);

    foreach ([$cancelledBooking, $rejectedBooking] as $releasedBooking) {
        $this->actingAs($owner)
            ->get(route('owner.bookings.index', [
                'section' => 'calendar',
                'unit_id' => $unit->unit_id,
                'month' => $releasedBooking->check_out->month,
                'year' => $releasedBooking->check_out->year,
            ]))
            ->assertOk()
            ->assertSee('data-calendar-select-date="'.$releasedBooking->check_out->toDateString().'"', false);
    }
});

test('public calendar shows unit-specific turnover and excludes cancelled booking dates', function () {
    $this->travelTo(now()->startOfMonth()->addDays(10));

    ['unit' => $unit] = makeCalendarBlockScenario();
    $guest = User::create([
        'role_id' => Role::where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Calendar',
        'last_name' => 'Guest',
        'email' => 'public-calendar-guest@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);
    $unit->update([
        'check_in_time' => '15:30',
        'check_out_time' => '11:00',
    ]);
    $checkoutDate = today()->addDays(5);
    $sameDayCheckIn = today()->addDays(5);
    Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->addDays(3)->setTime(15, 30),
        'check_out' => $checkoutDate->setTime(11, 0),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $sameDayCheckIn->setTime(15, 30),
        'check_out' => today()->addDays(7)->setTime(11, 0),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    $cancelledCheckOut = today()->addDays(9);
    Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->addDays(7)->setTime(15, 30),
        'check_out' => $cancelledCheckOut->setTime(11, 0),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CANCELLED,
    ]);

    $response = $this->get(route('public.unit', $unit))
        ->assertOk()
        ->assertSee('Check-out / new check-in')
        ->assertSee("Check-out at ' + checkOutTime + '; new check-in available at ' + checkInTime + '.", false)
        ->assertSee('checkoutDates[dateStr] && !checkInDates[dateStr]', false);

    expect($response->getContent())
        ->toContain('"'.$checkoutDate->toDateString().'":true')
        ->toContain('"'.$sameDayCheckIn->toDateString().'":true')
        ->not->toContain('"'.$cancelledCheckOut->toDateString().'":true')
        ->not->toContain('cal-legend-item"><div class="cal-legend-dot" style="background:linear-gradient');
});

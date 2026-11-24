<?php

use App\Livewire\Owner\WalkInBookingForm;
use App\Mail\BookingFeedbackRequested;
use App\Models\Booking;
use App\Models\Invitation;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\UnitCalendar;
use App\Models\User;
use App\Services\BookingReportService;
use App\Support\BookingStatus;
use App\Support\PaymentStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makeWalkInBookingScenario(
    int $capacity = 2,
    int $maximumCapacity = 4,
    string $pricingMode = 'Per Unit',
    float $price = 1800,
    float $extraGuestFee = 300
): array {
    $owner = User::create([
        'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Walk-in',
        'last_name' => 'Owner',
        'email' => 'walk-in-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Walk-in House',
        'address' => '10 Test Road',
        'city' => 'Baguio',
        'contact_number' => '5552000',
        'is_active' => true,
    ]);
    $unit = Unit::create([
        'house_id' => $house->house_id,
        'unit_type' => 'Walk-in Room',
        'capacity' => $capacity,
        'maximum_capacity' => $maximumCapacity,
        'child_free_through_age' => 2,
        'pricing_mode' => $pricingMode,
        'price' => $price,
        'extra_guest_fee' => $extraGuestFee,
        'status' => 'Available',
    ]);

    return compact('owner', 'house', 'unit');
}

function walkInStayDates(int $startOffset = 5, int $nights = 2): array
{
    $checkIn = now('Asia/Manila')->addDays($startOffset)->toDateString();
    $checkOut = Carbon::parse($checkIn, 'Asia/Manila')->addDays($nights)->toDateString();

    return [$checkIn, $checkOut];
}

test('owner creates a per-head walk-in with free children and a Senior discount', function () {
    ['owner' => $owner, 'house' => $house, 'unit' => $unit] = makeWalkInBookingScenario(
        capacity: 2,
        maximumCapacity: 3,
        pricingMode: 'Per Head',
        price: 1000
    );
    [$checkIn, $checkOut] = walkInStayDates(startOffset: 0);
    $this->actingAs($owner);
    $this->get(route('owner.bookings.create'))
        ->assertOk()
        ->assertSee('Add Walk-in Booking');

    Livewire::test(WalkInBookingForm::class)
        ->set('unitId', $unit->unit_id)
        ->set('walkInName', 'Walk-in Guest')
        ->set('walkInPhone', '09170000000')
        ->set('walkInEmail', 'walk-in@example.com')
        ->set('checkIn', $checkIn)
        ->set('checkOut', $checkOut)
        ->set('adultCount', '2')
        ->set('freeChildCount', '1')
        ->call('addSenior')
        ->set('seniorIds.0', 'SC-100')
        ->set('paymentAmount', '4000')
        ->set('paymentMethod', 'Cash')
        ->set('paymentReference', 'WALKIN-PERHEAD-001')
        ->set('checkInNow', true)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect(route('owner.guests.check-in', 1));

    $booking = Booking::query()->firstOrFail();
    expect($booking->user_id)->toBeNull()
        ->and($booking->walk_in_phone)->toBe('09170000000')
        ->and($booking->walk_in_email)->toBe('walk-in@example.com')
        ->and($booking->status)->toBe(BookingStatus::CHECKED_IN)
        ->and($booking->pricing_mode)->toBe('Per Head')
        ->and($booking->total_price)->toBe('4000.00')
        ->and($booking->displayGuestName())->toBe('Walk-in Guest');

    $this->assertDatabaseHas('booking_guests', [
        'booking_id' => $booking->booking_id,
        'full_name' => 'Walk-in Guest',
        'is_primary_guest' => true,
        'share_amount' => '2000.00',
    ]);
    $this->assertDatabaseHas('booking_guests', [
        'booking_id' => $booking->booking_id,
        'guest_type' => 'child',
        'is_free_child' => true,
        'share_amount' => '0.00',
    ]);
    $this->assertDatabaseHas('booking_discounts', [
        'booking_id' => $booking->booking_id,
        'discount_type' => 'Senior Citizen',
        'eligible_amount' => '2000.00',
        'discount_amount' => '400.00',
        'verification_status' => BookingStatus::PENDING,
    ]);
    $this->assertDatabaseHas('payments', [
        'booking_id' => $booking->booking_id,
        'amount' => '4000.00',
        'payment_method' => 'Cash',
        'payment_status' => PaymentStatus::VERIFIED,
        'reference_number' => 'WALKIN-PERHEAD-001',
    ]);

    $this->get(route('owner.bookings.index'))
        ->assertOk()
        ->assertSee('Walk-in Guest');
    $this->get(route('owner.dashboard'))
        ->assertOk()
        ->assertSee('Walk-in Guest');
    $this->get(route('owner.bookings.show', $booking->booking_id))
        ->assertOk()
        ->assertSee('Walk-in Guest')
        ->assertSee('walk-in@example.com')
        ->assertSee('09170000000');
    $this->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertOk()
        ->assertSee('Walk-in Guest');
    $this->get(route('owner.bookings.index', [
        'section' => 'calendar',
        'unit_id' => $unit->unit_id,
        'month' => Carbon::parse($checkIn)->month,
        'year' => Carbon::parse($checkIn)->year,
    ]))
        ->assertOk()
        ->assertSee('Walk-in Guest');
    $this->get(route('owner.bookings.index', [
        'section' => 'reports',
        'period' => 'monthly',
        'year' => Carbon::parse($checkIn)->year,
        'from_date' => $checkIn,
        'to_date' => $checkOut,
    ]))->assertOk()
        ->assertSee('Reports & Analytics')
        ->assertSee('data-report-tab="overview"', false)
        ->assertSee('Transaction Report')
        ->assertSee('Booking Activity by Property');

    $report = app(BookingReportService::class)->generate(collect([$house->house_id]), [
        'period' => 'monthly',
        'year' => Carbon::parse($checkIn)->year,
        'quarter' => 'All',
        'house_id' => null,
        'from_date' => $checkIn,
        'to_date' => $checkOut,
    ]);
    expect($report['periodBookings']->pluck('booking_id')->all())->toContain($booking->booking_id);
});

test('assigned staff can create a walk-in booking without a guest account', function () {
    ['owner' => $owner, 'house' => $house, 'unit' => $unit] = makeWalkInBookingScenario();
    $staff = User::create([
        'role_id' => Role::where('name', 'Staff')->firstOrFail()->role_id,
        'first_name' => 'Assigned',
        'last_name' => 'Staff',
        'email' => 'walk-in-staff@example.com',
        'password' => 'StaffPass123!',
        'is_active' => true,
    ]);
    Invitation::create([
        'invited_by' => $owner->user_id,
        'house_id' => $house->house_id,
        'role_id' => Role::where('name', 'Staff')->firstOrFail()->role_id,
        'email' => $staff->email,
        'token' => 'walk-in-staff-assignment',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);
    [$checkIn, $checkOut] = walkInStayDates();
    $this->actingAs($staff)
        ->get(route('staff.bookings.create'))
        ->assertOk()
        ->assertSee('Add Walk-in Booking')
        ->assertSee('Walk-in Room');

    Livewire::test(WalkInBookingForm::class)
        ->set('unitId', $unit->unit_id)
        ->set('walkInName', 'Staff Walk-in')
        ->set('walkInPhone', '09174445555')
        ->set('checkIn', $checkIn)
        ->set('checkOut', $checkOut)
        ->set('adultCount', '1')
        ->set('paymentAmount', '3600')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect(route('staff.bookings.show', 1));

    expect(Booking::query()->firstOrFail()->user_id)->toBeNull();
    $this->assertDatabaseHas('payments', [
        'booking_id' => 1,
        'payment_status' => PaymentStatus::VERIFIED,
    ]);
});

test('walk-in booking edits recalculate shares through the shared pricing workflow', function () {
    ['owner' => $owner, 'unit' => $unit] = makeWalkInBookingScenario(
        capacity: 1,
        maximumCapacity: 3,
        pricingMode: 'Per Unit',
        price: 1800
    );
    [$checkIn, $checkOut] = walkInStayDates();
    $this->actingAs($owner);

    Livewire::test(WalkInBookingForm::class)
        ->set('unitId', $unit->unit_id)
        ->set('walkInName', 'Walk-in Edit Guest')
        ->set('walkInPhone', '09170000000')
        ->set('checkIn', $checkIn)
        ->set('checkOut', $checkOut)
        ->set('adultCount', '1')
        ->set('paymentAmount', '3600')
        ->set('paymentMethod', 'Cash')
        ->call('submit')
        ->assertHasNoErrors();

    $booking = Booking::query()->firstOrFail();
    $this->put(route('owner.bookings.update', $booking->booking_id), [
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'adult_count' => 2,
        'child_count' => 0,
        'reason' => 'The walk-in guest updated the party size.',
    ])->assertRedirect(route('owner.bookings.show', $booking->booking_id));

    $activePayingGuests = $booking->activeGuests()->where('is_free_child', false)->get();
    expect($booking->fresh()->total_price)->toBe('4200.00')
        ->and($activePayingGuests->pluck('share_amount')->all())->toBe(['2100.00', '2100.00'])
        ->and($activePayingGuests->sum(fn ($guest): float => (float) $guest->share_amount))->toBe(4200.0);
});

test('Per Unit walk-ins charge extra paying heads but not free children up to max capacity', function () {
    ['owner' => $owner, 'unit' => $unit] = makeWalkInBookingScenario(
        capacity: 2,
        maximumCapacity: 4,
        pricingMode: 'Per Unit',
        price: 2000,
        extraGuestFee: 300
    );
    [$checkIn, $checkOut] = walkInStayDates();
    $this->actingAs($owner);

    Livewire::test(WalkInBookingForm::class)
        ->set('unitId', $unit->unit_id)
        ->set('walkInName', 'Room Guest')
        ->set('walkInPhone', '09171112222')
        ->set('checkIn', $checkIn)
        ->set('checkOut', $checkOut)
        ->set('adultCount', '3')
        ->set('freeChildCount', '1')
        ->set('paymentAmount', '4600')
        ->call('submit')
        ->assertHasNoErrors();

    $booking = Booking::query()->firstOrFail();
    expect($booking->status)->toBe(BookingStatus::CONFIRMED)
        ->and($booking->pricing_mode)->toBe('Per Unit')
        ->and($booking->extra_guest_count)->toBe(1)
        ->and($booking->extra_guest_total)->toBe('600.00')
        ->and($booking->total_price)->toBe('4600.00')
        ->and($booking->guests->where('is_free_child', false)->pluck('share_amount')->all())->toBe(['1533.33', '1533.33', '1533.34'])
        ->and($booking->guests->where('is_free_child', false)->sum(fn ($bookingGuest): float => (float) $bookingGuest->share_amount))->toBe(4600.0);
    $this->assertDatabaseHas('booking_guests', [
        'booking_id' => $booking->booking_id,
        'guest_type' => 'child',
        'is_free_child' => true,
        'share_amount' => '0.00',
    ]);
});

test('walk-in discount estimates use the allocated individual shares', function () {
    ['owner' => $owner, 'unit' => $unit] = makeWalkInBookingScenario(
        capacity: 3,
        maximumCapacity: 3,
        pricingMode: 'Per Unit',
        price: 100,
        extraGuestFee: 0
    );
    [$checkIn, $checkOut] = walkInStayDates(nights: 1);
    $this->actingAs($owner);

    Livewire::test(WalkInBookingForm::class)
        ->set('unitId', $unit->unit_id)
        ->set('checkIn', $checkIn)
        ->set('checkOut', $checkOut)
        ->set('adultCount', '3')
        ->call('addSenior')
        ->set('seniorIds.0', 'SC-WALKIN-1')
        ->call('addSenior')
        ->set('seniorIds.1', 'SC-WALKIN-2')
        ->call('addPwd')
        ->set('pwdIds.0', 'PWD-WALKIN-1')
        ->assertSee('−₱20.01');
});

test('walk-in capacity counts free children and rejects entries above maximum', function () {
    ['owner' => $owner, 'unit' => $unit] = makeWalkInBookingScenario(maximumCapacity: 3);
    [$checkIn, $checkOut] = walkInStayDates();
    $this->actingAs($owner);

    Livewire::test(WalkInBookingForm::class)
        ->set('unitId', $unit->unit_id)
        ->set('walkInName', 'Over Capacity Guest')
        ->set('walkInPhone', '09172223333')
        ->set('checkIn', $checkIn)
        ->set('checkOut', $checkOut)
        ->set('adultCount', '2')
        ->set('freeChildCount', '2')
        ->set('paymentAmount', '1800')
        ->call('submit')
        ->assertHasErrors('freeChildCount')
        ->assertSee('Maximum is 3 guests.')
        ->assertSet('freeChildCount', '2');

    $this->assertDatabaseCount('bookings', 0);
    $this->assertDatabaseCount('payments', 0);
});

test('walk-in form reports unavailable dates instantly and submission rechecks an active overlap', function () {
    ['owner' => $owner, 'unit' => $unit] = makeWalkInBookingScenario();
    $blockedDate = now('Asia/Manila')->addDays(4)->toDateString();
    UnitCalendar::create([
        'unit_id' => $unit->unit_id,
        'date' => $blockedDate,
        'status' => 'Unavailable',
        'note' => 'Maintenance',
    ]);
    $this->actingAs($owner);

    Livewire::test(WalkInBookingForm::class)
        ->set('unitId', $unit->unit_id)
        ->set('checkIn', $blockedDate)
        ->assertHasErrors('checkIn')
        ->assertSee('This check-in date is unavailable or booked.');

    [$laterCheckIn] = walkInStayDates(12);
    [$earlierCheckOut] = walkInStayDates(10);
    Livewire::test(WalkInBookingForm::class)
        ->set('unitId', $unit->unit_id)
        ->set('checkIn', walkInStayDates(8)[0])
        ->set('checkOut', $earlierCheckOut)
        ->set('checkIn', $laterCheckIn)
        ->assertSet('checkOut', Carbon::parse($laterCheckIn)->addDay()->toDateString())
        ->assertHasNoErrors(['checkIn', 'checkOut']);

    [$checkIn, $checkOut] = walkInStayDates(8);
    $existingBooking = Booking::create([
        'user_id' => null,
        'walk_in_phone' => '09173334444',
        'unit_id' => $unit->unit_id,
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'status' => BookingStatus::CONFIRMED,
    ]);

    Livewire::test(WalkInBookingForm::class)
        ->set('unitId', $unit->unit_id)
        ->set('walkInName', 'Overlapping Guest')
        ->set('walkInPhone', '09175556666')
        ->set('checkIn', $checkIn)
        ->set('checkOut', $checkOut)
        ->set('adultCount', '1')
        ->set('paymentAmount', '1800')
        ->call('submit')
        ->assertHasErrors('checkIn');

    expect(Booking::query()->count())->toBe(1)
        ->and($existingBooking->fresh()->status)->toBe(BookingStatus::CONFIRMED);
    $this->assertDatabaseCount('payments', 0);
});

test('walk-in form accepts a checkout two days after the selected check-in date', function () {
    ['owner' => $owner, 'unit' => $unit] = makeWalkInBookingScenario();
    $this->actingAs($owner);

    Livewire::test(WalkInBookingForm::class)
        ->set('unitId', $unit->unit_id)
        ->set('checkIn', '2026-11-21')
        ->set('checkOut', '2026-11-20')
        ->assertHasErrors('checkOut')
        ->set('checkOut', '2026-11-23')
        ->assertSet('checkIn', '2026-11-21')
        ->assertSet('checkOut', '2026-11-23')
        ->assertHasNoErrors(['checkIn', 'checkOut']);
});

test('walk-in booking accepts only a later date-only checkout', function (
    string $checkIn,
    string $checkOut,
    bool $isValid,
    string $expectedTotal,
    string $paymentAmount
) {
    $this->travelTo('2026-12-01 10:00:00');
    Mail::fake();
    ['owner' => $owner, 'unit' => $unit] = makeWalkInBookingScenario();
    $unit->update([
        'check_in_time' => '15:30',
        'check_out_time' => '11:00',
    ]);
    $this->actingAs($owner);

    $component = Livewire::test(WalkInBookingForm::class)
        ->set('unitId', $unit->unit_id)
        ->set('walkInName', 'Date Validation Guest')
        ->set('walkInPhone', '09170000000')
        ->set('checkIn', $checkIn)
        ->set('checkOut', $checkOut)
        ->set('adultCount', '1')
        ->set('paymentAmount', $paymentAmount)
        ->set('paymentMethod', 'Cash');

    if (! $isValid) {
        $component->call('submit')
            ->assertHasErrors('checkOut')
            ->assertSee('Check-out must be after check-in.');

        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('payments', 0);

        return;
    }

    $component->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect(route('owner.bookings.show', 1));

    $booking = Booking::query()->firstOrFail();
    expect($booking->check_in->toDateString())->toBe($checkIn)
        ->and($booking->check_out->toDateString())->toBe($checkOut)
        ->and($booking->total_price)->toBe($expectedTotal);
})->with([
    'January 1 to January 5' => ['2027-01-01', '2027-01-05', true, '7200.00', '3600'],
    'January 1 to January 2' => ['2027-01-01', '2027-01-02', true, '1800.00', '900'],
    'same-day checkout' => ['2027-01-01', '2027-01-01', false, '0.00', '900'],
    'checkout before check-in' => ['2027-01-05', '2027-01-01', false, '0.00', '900'],
]);

test('walk-in booking can check in on the previous booking checkout date', function () {
    ['owner' => $owner, 'unit' => $unit] = makeWalkInBookingScenario();
    $unit->update([
        'check_in_time' => '15:30',
        'check_out_time' => '11:00',
    ]);
    $previousCheckIn = now()->addDays(5);
    Booking::create([
        'user_id' => null,
        'walk_in_phone' => '09171112222',
        'unit_id' => $unit->unit_id,
        'check_in' => $previousCheckIn->toDateString(),
        'check_out' => $previousCheckIn->copy()->addDays(2)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    $this->actingAs($owner);

    Livewire::test(WalkInBookingForm::class)
        ->set('unitId', $unit->unit_id)
        ->set('walkInName', 'Turnover Guest')
        ->set('walkInPhone', '09173334444')
        ->set('checkIn', $previousCheckIn->copy()->addDays(2)->toDateString())
        ->set('checkOut', $previousCheckIn->copy()->addDays(4)->toDateString())
        ->set('adultCount', '1')
        ->set('paymentAmount', '1800')
        ->set('paymentMethod', 'Cash')
        ->call('submit')
        ->assertHasNoErrors();

    expect(Booking::query()->count())->toBe(2)
        ->and(Booking::query()->latest('booking_id')->firstOrFail()->check_in->toDateString())
        ->toBe($previousCheckIn->copy()->addDays(2)->toDateString());
});

test('walk-in payment reference is checked again before the booking is saved', function () {
    ['owner' => $owner, 'unit' => $unit] = makeWalkInBookingScenario();
    [$existingCheckIn, $existingCheckOut] = walkInStayDates();
    $existingBooking = Booking::create([
        'user_id' => null,
        'walk_in_phone' => '09176667777',
        'unit_id' => $unit->unit_id,
        'check_in' => $existingCheckIn,
        'check_out' => $existingCheckOut,
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'status' => BookingStatus::CONFIRMED,
    ]);
    Payment::create([
        'booking_id' => $existingBooking->booking_id,
        'payment_method' => 'Cash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
        'reference_number' => 'DUPLICATE-WALKIN-REF',
    ]);
    [$checkIn, $checkOut] = walkInStayDates(9);
    $this->actingAs($owner);

    Livewire::test(WalkInBookingForm::class)
        ->set('unitId', $unit->unit_id)
        ->set('walkInName', 'Another Guest')
        ->set('walkInPhone', '09178889999')
        ->set('checkIn', $checkIn)
        ->set('checkOut', $checkOut)
        ->set('adultCount', '1')
        ->set('paymentAmount', '1800')
        ->set('paymentReference', 'duplicate-walkin-ref')
        ->call('submit')
        ->assertHasErrors('paymentReference')
        ->assertSee('Reference number already used.');

    $this->assertDatabaseCount('bookings', 1);
    $this->assertDatabaseCount('payments', 1);
});

test('walk-in can check in today and submit one review from the feedback token without login', function () {
    Mail::fake();
    ['owner' => $owner, 'unit' => $unit] = makeWalkInBookingScenario(price: 1800);
    $checkIn = now('Asia/Manila')->toDateString();
    $checkOut = now('Asia/Manila')->addDay()->toDateString();
    $this->actingAs($owner);

    Livewire::test(WalkInBookingForm::class)
        ->set('unitId', $unit->unit_id)
        ->set('walkInName', 'Check-in Guest')
        ->set('walkInPhone', '09179990000')
        ->set('checkIn', $checkIn)
        ->set('checkOut', $checkOut)
        ->set('adultCount', '1')
        ->set('paymentAmount', '1800')
        ->set('checkInNow', true)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect(route('owner.guests.check-in', 1));

    $booking = Booking::query()->firstOrFail();
    expect($booking->user_id)->toBeNull()
        ->and($booking->status)->toBe(BookingStatus::CHECKED_IN);
    $this->get(route('owner.dashboard'))
        ->assertOk()
        ->assertSee('Check-in Guest');

    $sendFeedback = $this->post(route('owner.guests.feedback.send', $booking->booking_id));
    $sendFeedback->assertRedirect(route('owner.guests.check-in', $booking->booking_id))
        ->assertSessionHas('feedbackUrl');
    Mail::assertNothingSent();

    $feedbackUrl = $sendFeedback->getSession()->get('feedbackUrl');
    $this->get(route('owner.guests.check-in', $booking->booking_id))
        ->assertOk()
        ->assertSee($feedbackUrl);

    auth()->logout();
    $this->get($feedbackUrl)
        ->assertOk()
        ->assertSee('How was your stay?')
        ->assertSee('Walk-in Room')
        ->assertDontSee('Check-in Guest');
    $feedbackRoute = route('guest.feedback.store', [
        'booking' => $booking->booking_id,
        'token' => basename($feedbackUrl),
    ]);
    $this->from($feedbackUrl)
        ->post($feedbackRoute, ['rating' => 5, 'comment' => 'Good stay.'])
        ->assertRedirect($feedbackUrl)
        ->assertSessionHas('success');

    $this->assertDatabaseHas('reviews', [
        'booking_id' => $booking->booking_id,
        'user_id' => null,
        'rating' => 5,
        'comment' => 'Good stay.',
    ]);
    $this->from($feedbackUrl)
        ->post($feedbackRoute, ['rating' => 4])
        ->assertSessionHasErrors('feedback');
    expect(Review::query()->where('booking_id', $booking->booking_id)->count())->toBe(1);
    $this->get(route('guest.feedback.show', ['booking' => $booking->booking_id, 'token' => 'invalid']))
        ->assertOk()
        ->assertSee('This feedback link is invalid or has expired.')
        ->assertDontSee('Check-in Guest')
        ->assertDontSee('Walk-in Room');
});

test('walk-in feedback is emailed to the saved contact when an email is provided', function () {
    Mail::fake();
    ['owner' => $owner, 'unit' => $unit] = makeWalkInBookingScenario(price: 1800);
    $checkIn = now('Asia/Manila')->toDateString();
    $checkOut = now('Asia/Manila')->addDay()->toDateString();
    $this->actingAs($owner);

    Livewire::test(WalkInBookingForm::class)
        ->set('unitId', $unit->unit_id)
        ->set('walkInName', 'Email Guest')
        ->set('walkInPhone', '09170001111')
        ->set('walkInEmail', 'email-guest@example.com')
        ->set('checkIn', $checkIn)
        ->set('checkOut', $checkOut)
        ->set('adultCount', '1')
        ->set('paymentAmount', '1800')
        ->set('checkInNow', true)
        ->call('submit')
        ->assertHasNoErrors();

    $booking = Booking::query()->firstOrFail();
    $this->post(route('owner.guests.feedback.send', $booking->booking_id))
        ->assertRedirect(route('owner.guests.check-in', $booking->booking_id));

    Mail::assertSent(BookingFeedbackRequested::class, fn (BookingFeedbackRequested $mail): bool => $mail->hasTo('email-guest@example.com'));
});

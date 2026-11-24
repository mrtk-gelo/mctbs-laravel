<?php

use App\Exports\BookingReportExport;
use App\Livewire\Guest\BookingForm;
use App\Livewire\Guest\DownpaymentForm;
use App\Livewire\NotificationBell;
use App\Mail\BookingAccepted;
use App\Mail\BookingFeedbackRequested;
use App\Mail\BookingTicket;
use App\Models\AdditionalCharge;
use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\BookingDamage;
use App\Models\BookingDiscount;
use App\Models\BookingGuest;
use App\Models\DamageReport;
use App\Models\Feedback;
use App\Models\Invitation;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Review;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\UnitAddon;
use App\Models\UnitCalendar;
use App\Models\UnitDamageItem;
use App\Models\User;
use App\Services\BookingReportService;
use App\Services\BookingService;
use App\Services\BookingTicketDeliveryService;
use App\Services\BookingWorkflowService;
use App\Support\BookingStatus;
use App\Support\PaymentStatus;
use App\Support\UnitTimeFormatter;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Maatwebsite\Excel\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

uses(RefreshDatabase::class);

function makeGuestBookingScenario(int $capacity = 4): array
{
    $owner = User::create([
        'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'House',
        'last_name' => 'Owner',
        'email' => 'booking-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Booking House',
        'address' => '10 Test Road',
        'city' => 'Baguio',
        'contact_number' => '5552000',
        'is_active' => true,
    ]);
    $unit = Unit::create([
        'house_id' => $house->house_id,
        'unit_type' => 'Family Room',
        'capacity' => $capacity,
        'price' => 1800,
        'status' => 'Available',
    ]);
    $guest = User::create([
        'role_id' => Role::where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Booking',
        'last_name' => 'Guest',
        'email' => 'booking-guest@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);

    return compact('owner', 'house', 'unit', 'guest');
}

test('guest booking status and actions update while the page remains open', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => '2028-12-01',
        'check_out' => '2028-12-02',
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'status' => BookingStatus::PENDING,
    ]);

    $this->actingAs($guest);

    $component = Livewire::test(App\Livewire\Guest\BookingStatus::class, ['booking' => $booking])
        ->assertSee('Pending')
        ->assertSee('Waiting for review.');

    $booking->update(['status' => BookingStatus::ACCEPTED]);
    $component
        ->call('refreshStatus')
        ->assertSee('Accepted')
        ->assertDontSee('Pay Now');
    $this->get(route('guest.bookings.show', $booking->booking_id))
        ->assertSee('guest-payment-action', false)
        ->assertSee('Pay Now')
        ->assertSee(route('guest.bookings.payment', $booking->booking_id));

    $booking->update([
        'status' => BookingStatus::REJECTED,
        'rejection_reason' => 'The requested dates are unavailable.',
    ]);
    $component
        ->call('refreshStatus')
        ->assertSee('Rejected')
        ->assertSee('The requested dates are unavailable.');

    $booking->update(['status' => BookingStatus::CONFIRMED]);
    $component
        ->call('refreshStatus')
        ->assertSee('Confirmed')
        ->assertDontSee('Download Ticket');
    $this->get(route('guest.bookings.show', $booking->booking_id))
        ->assertSeeInOrder([
            'class="col-fixed-300"',
            'class="booking-details-actions',
            'data-booking-ticket-action',
            'href="'.route('guest.bookings.ticket', $booking->booking_id).'"',
            'Download Ticket',
        ], false);
});

test('check-out follows a newly selected check-in date', function () {
    ['unit' => $unit] = makeGuestBookingScenario();

    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('checkIn', '2028-12-01')
        ->assertSet('checkOut', '2028-12-02')
        ->assertSee('Stay/room charge')
        ->assertSee('Stay total')
        ->set('checkOut', '2028-12-05')
        ->set('checkIn', '2028-12-03')
        ->assertSet('checkOut', '2028-12-05')
        ->set('checkIn', '2028-12-05')
        ->assertSet('checkOut', '2028-12-06');
});

test('online booking accepts only a later date-only checkout', function (
    string $checkIn,
    string $checkOut,
    bool $isValid,
    string $expectedTotal
) {
    $this->travelTo('2026-12-01 10:00:00');
    Mail::fake();
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $unit->update([
        'check_in_time' => '15:30',
        'check_out_time' => '11:00',
    ]);

    $this->actingAs($guest);
    $component = Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('checkIn', $checkIn)
        ->set('checkOut', $checkOut)
        ->set('ackPolicy', true);

    if (! $isValid) {
        $component->call('submit')
            ->assertHasErrors('checkOut')
            ->assertSee('Check-out must be after check-in.');

        $this->assertDatabaseCount('bookings', 0);

        return;
    }

    $component->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect(route('guest.bookings.show', 1));

    $booking = Booking::query()->firstOrFail();
    expect($booking->check_in->toDateString())->toBe($checkIn)
        ->and($booking->check_out->toDateString())->toBe($checkOut)
        ->and($booking->total_price)->toBe($expectedTotal);
})->with([
    'January 1 to January 5' => ['2027-01-01', '2027-01-05', true, '7200.00'],
    'January 1 to January 2' => ['2027-01-01', '2027-01-02', true, '1800.00'],
    'same-day checkout' => ['2027-01-01', '2027-01-01', false, '0.00'],
    'checkout before check-in' => ['2027-01-05', '2027-01-01', false, '0.00'],
]);

test('booking edits enforce strict date-only checkout ordering', function () {
    $this->travelTo('2026-12-01 10:00:00');
    Mail::fake();
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $unit->update([
        'check_in_time' => '15:30',
        'check_out_time' => '11:00',
    ]);
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => '2027-01-01',
        'check_out' => '2027-01-05',
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);
    $this->actingAs($owner);

    $workflow = app(BookingWorkflowService::class);
    $bookingService = app(BookingService::class);
    foreach ([
        ['2027-01-01', '2027-01-01'],
        ['2027-01-05', '2027-01-01'],
    ] as [$invalidCheckIn, $invalidCheckOut]) {
        expect(fn () => $workflow->editBooking(
            $owner,
            $booking->booking_id,
            [
                'check_in' => $invalidCheckIn,
                'check_out' => $invalidCheckOut,
            ],
            'Check date ordering.',
            $bookingService
        ))->toThrow(ValidationException::class);
    }

    $this->put(route('owner.bookings.update', $booking->booking_id), [
        'check_in' => '2027-01-01',
        'check_out' => '2027-01-01',
        'reason' => 'Check date ordering.',
    ])->assertSessionHasErrors('check_out');

    $this->put(route('owner.bookings.update', $booking->booking_id), [
        'check_in' => '2027-01-05',
        'check_out' => '2027-01-01',
        'reason' => 'Check date ordering.',
    ])->assertSessionHasErrors('check_out');

    $this->put(route('owner.bookings.update', $booking->booking_id), [
        'check_in' => '2027-01-01',
        'check_out' => '2027-01-05',
        'reason' => 'Confirm the valid stay dates.',
    ])->assertRedirect(route('owner.bookings.show', $booking->booking_id));

    expect($booking->fresh()->check_in->toDateString())->toBe('2027-01-01')
        ->and($booking->fresh()->check_out->toDateString())->toBe('2027-01-05')
        ->and($booking->fresh()->total_price)->toBe('7200.00');
});

test('guest booking page loads the navbar font and icon assets', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $unit->update([
        'check_in_time' => '15:30',
        'check_out_time' => '11:00',
    ]);

    $this->actingAs($guest)
        ->get(route('guest.book', $unit))
        ->assertSee('fonts.googleapis.com/css2?family=DM+Sans')
        ->assertSee('cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css')
        ->assertSee('fa-home')
        ->assertSee('Check-in is at 3:30 PM; check-out is at 11:00 AM.');
});

test('a guest cannot reuse a payment reference number from another payment', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $checkIn = now()->addDays(5);
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $checkIn->toDateString(),
        'check_out' => $checkIn->copy()->addDays(2)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::ACCEPTED,
        'accepted_at' => now(),
        'payment_deadline' => now()->addDay(),
    ]);
    $previousBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $checkIn->copy()->addDays(4)->toDateString(),
        'check_out' => $checkIn->copy()->addDays(6)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    Payment::create([
        'booking_id' => $previousBooking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
        'reference_number' => 'Used-Reference-001',
    ]);

    expect(fn () => app(BookingWorkflowService::class)->submitPayment($guest, $booking->booking_id, [
        'payment_method' => 'GCash',
        'amount' => 1800,
        'reference_number' => 'used-reference-001',
    ]))->toThrow(ValidationException::class);
});

test('the downpayment form checks duplicate references on blur and keeps the entered payment details', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(7)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);
    app(BookingWorkflowService::class)->accept($owner, $booking->booking_id);

    $previousBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(9),
        'check_out' => now()->addDays(11),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    Payment::create([
        'booking_id' => $previousBooking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
        'reference_number' => 'DUPLICATE-REF-001',
    ]);

    $this->actingAs($guest);
    Storage::fake('local');
    Livewire::test(DownpaymentForm::class, [
        'booking' => $booking,
        'downpaymentAmount' => 1800,
        'remainingBalance' => 3600,
    ])
        ->set('paymentMethod', 'GCash')
        ->set('amount', '1800')
        ->set('paymentReceipt', UploadedFile::fake()->image('proof.png'))
        ->set('referenceNumber', 'duplicate-ref-001')
        ->assertHasErrors('referenceNumber')
        ->assertSee('Reference number already used.')
        ->assertSee('downpayment-proof-preview', false)
        ->assertSet('referenceNumber', 'duplicate-ref-001')
        ->assertSet('amount', '1800')
        ->call('submit')
        ->assertHasErrors('referenceNumber')
        ->assertSet('referenceNumber', 'duplicate-ref-001')
        ->assertSet('amount', '1800')
        ->set('referenceNumber', 'UNIQUE-REF-002')
        ->assertHasNoErrors('referenceNumber')
        ->call('submit')
        ->assertRedirect(route('guest.bookings.show', $booking->booking_id));

    $this->assertDatabaseHas('payments', [
        'booking_id' => $booking->booking_id,
        'reference_number' => 'UNIQUE-REF-002',
        'payment_status' => PaymentStatus::UNDER_REVIEW,
    ]);
    $payment = $booking->payments()->firstOrFail();
    expect($payment->payment_receipt)->toStartWith('payment-proofs/');
    Storage::disk('local')->assertExists($payment->payment_receipt);
});

test('downpayment submission requires an allowed proof file', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(7)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);
    app(BookingWorkflowService::class)->accept($owner, $booking->booking_id);

    $this->actingAs($guest)
        ->from(route('guest.bookings.payment', $booking->booking_id))
        ->post(route('guest.bookings.payments.store', $booking->booking_id), [
            'payment_method' => 'GCash',
            'amount' => 1800,
        ])
        ->assertRedirect(route('guest.bookings.payment', $booking->booking_id))
        ->assertSessionHasErrors('payment_receipt');

    $this->assertDatabaseCount('payments', 0);
});

test('rejecting a payment requires a reason and shows it to the guest', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(7)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);
    app(BookingWorkflowService::class)->accept($owner, $booking->booking_id);
    $payment = app(BookingWorkflowService::class)->submitPayment($guest, $booking->booking_id, [
        'payment_method' => 'GCash',
        'amount' => 1800,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.show', $booking->booking_id))
        ->assertSee('data-payment-reason-panel  hidden', false)
        ->assertSee('Verify')
        ->assertSee('Reject');

    $this->actingAs($owner)
        ->from(route('owner.bookings.show', $booking->booking_id))
        ->post(route('owner.payments.review', $payment->payment_id), [
            'status' => PaymentStatus::INCORRECT,
        ])
        ->assertRedirect(route('owner.bookings.show', $booking->booking_id))
        ->assertSessionHasErrors([
            'notes' => 'Please explain why this payment cannot be accepted.',
        ]);
    expect($payment->fresh()->payment_status)->toBe(PaymentStatus::UNDER_REVIEW);
    $this->get(route('owner.bookings.show', $booking->booking_id))
        ->assertSee('Why can’t this payment be accepted?')
        ->assertSee('Please explain why this payment cannot be accepted.');

    $this->post(route('owner.payments.review', $payment->payment_id), [
        'status' => PaymentStatus::INCORRECT,
        'notes' => 'The reference does not match.',
    ])->assertSessionHas('success');

    expect($payment->fresh()->notes)->toBe('The reference does not match.');
    $this->assertDatabaseHas('notifications', [
        'user_id' => $guest->user_id,
        'booking_id' => $booking->booking_id,
        'type' => 'payment_incorrect',
        'message' => 'The reference does not match.',
    ]);
    $this->actingAs($guest)
        ->get(route('guest.bookings.show', $booking->booking_id))
        ->assertSee('The reference does not match.');
});

test('overlap checks respect the half-open booking interval and same-day transition', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $older = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => '2026-01-01 14:00:00',
        'check_out' => '2026-01-05 12:00:00',
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'status' => BookingStatus::PENDING,
    ]);
    $later = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => '2026-01-05 14:00:00',
        'check_out' => '2026-01-10 12:00:00',
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'status' => BookingStatus::PENDING,
    ]);

    $service = app(BookingWorkflowService::class);
    $overlapping = $service->overlappingPendingBookings($older);

    expect($overlapping->pluck('booking_id')->all())->not->toContain($later->booking_id)
        ->and($service->overlappingPendingBookings($later)->pluck('booking_id')->all())->not->toContain($older->booking_id);
    expect($unit->fresh()->check_out_time)->toBe('12:00:00')
        ->and($unit->fresh()->check_in_time)->toBe('14:00:00');

    $service->accept($owner, $later->booking_id);
    expect($later->fresh()->status)->toBe(BookingStatus::ACCEPTED);
});

test('guest booking uses the finalized unit and booking schema', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $unit->update(['child_free_through_age' => 3]);
    $checkIn = now()->addDays(3)->toDateString();
    $checkOut = now()->addDays(5)->toDateString();

    $this->actingAs($guest);
    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('checkIn', $checkIn)
        ->set('checkOut', $checkOut)
        ->set('paxCount', 2)
        ->set('freeChildCount', 1)
        ->set('seniorCount', 1)
        ->set('seniorIds.0', 'SENIOR-BOOKING-ID')
        ->set('ackPolicy', true)
        ->call('submit')
        ->assertRedirect(route('guest.bookings.show', 1));

    $booking = Booking::firstOrFail();
    expect($booking->user_id)->toBe($guest->user_id)
        ->and($booking->unit_id)->toBe($unit->unit_id)
        ->and($booking->adult_count)->toBe(2)
        ->and($booking->child_count)->toBe(1)
        ->and($booking->total_price)->toBe('3600.00')
        ->and($booking->status)->toBe(BookingStatus::PENDING);
    $this->assertDatabaseCount('booking_guests', 3);
    $this->assertDatabaseHas('booking_guests', [
        'booking_id' => $booking->booking_id,
        'full_name' => 'Senior Citizen 1',
        'guest_type' => 'adult',
        'is_primary_guest' => true,
        'share_amount' => '1800.00',
    ]);
    $this->assertDatabaseHas('booking_guests', [
        'booking_id' => $booking->booking_id,
        'full_name' => 'Child 1',
        'guest_type' => 'child',
        'age' => null,
        'is_free_child' => true,
        'is_primary_guest' => false,
        'share_amount' => '0.00',
    ]);
    $this->assertDatabaseHas('booking_discounts', [
        'booking_id' => $booking->booking_id,
        'discount_type' => 'Senior Citizen',
        'id_reference' => 'SENIOR-BOOKING-ID',
    ]);
    expect(Notification::where('user_id', $owner->user_id)
        ->where('booking_id', $booking->booking_id)
        ->exists())->toBeTrue();

    $this->actingAs($guest)
        ->get(route('guest.bookings.show', $booking->booking_id))
        ->assertSee('Senior Citizen discount for Senior Citizen 1')
        ->assertDontSee('Guest Count')
        ->assertDontSee('Counted pax')
        ->assertDontSee('Excluded young children')
        ->assertDontSee('Total reported guests')
        ->assertDontSee('Child 1')
        ->assertDontSee('full_name')
        ->assertSee('₱3,600.00');
});

test('booking summary estimates verified discounts without requiring a deposit', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario(2);
    $unit->update([
        'maximum_capacity' => 3,
        'extra_guest_fee' => 150,
    ]);
    $checkIn = now()->addDays(3)->toDateString();
    $checkOut = now()->addDays(5)->toDateString();

    $this->actingAs($guest);
    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('checkIn', $checkIn)
        ->set('checkOut', $checkOut)
        ->set('paxCount', 3)
        ->set('seniorCount', 1)
        ->set('seniorIds.0', 'SENIOR-EXTRA-CAPACITY')
        ->set('ackPolicy', true)
        ->assertSee('Family Room · Booking House · Baguio')
        ->assertSee('booking-price-lines')
        ->assertSee('Stay/room charge · Per Unit')
        ->assertSee('2 nights')
        ->assertSee('₱300.00')
        ->assertSee('Stay total')
        ->assertSee('Estimated total after discount')
        ->assertSee('₱3,900.00')
        ->assertSee('Estimated Senior/PWD discount · 1 guest')
        ->assertSee('₱260.00')
        ->assertSee('₱3,640.00')
        ->assertDontSee('Estimated booking amount after discount')
        ->call('submit')
        ->assertRedirect(route('guest.bookings.show', 1));

    $booking = Booking::firstOrFail();
    expect($booking->room_subtotal)->toBe('3600.00')
        ->and($booking->extra_guest_count)->toBe(1)
        ->and($booking->extra_guest_total)->toBe('300.00')
        ->and($booking->total_price)->toBe('3900.00')
        ->and($booking->guests->first()->share_amount)->toBe('1300.00');
    $this->assertDatabaseHas('booking_discounts', [
        'booking_id' => $booking->booking_id,
        'eligible_amount' => '1300.00',
        'discount_amount' => '260.00',
    ]);
    $this->get(route('guest.bookings.show', $booking->booking_id))
        ->assertSee('Extra heads')
        ->assertSee('Estimated total before discount verification, including checkout charges')
        ->assertSee('Senior Citizen discount for Senior Citizen 1')
        ->assertSee('physical ID to be checked at checkout')
        ->assertDontSee('₱260.00')
        ->assertDontSee('₱1,300.00')
        ->assertDontSee('Eligible share');

    expect($booking->downpaymentAmount())->toBe(1950.0)
        ->and($booking->fresh()->status)->toBe(BookingStatus::PENDING);
    $this->assertDatabaseCount('payments', 0);

    expect(fn () => app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(8)->toDateString(),
        'check_out' => now()->addDays(10)->toDateString(),
        'adult_count' => 4,
        'child_count' => 0,
        'senior_count' => 0,
        'pwd_count' => 0,
        'senior_ids' => [],
        'pwd_ids' => [],
        'free_child_count' => 0,
    ]))->toThrow(ValidationException::class);
});

test('free children count toward maximum capacity but are not charged', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario(2);
    $unit->update([
        'maximum_capacity' => 3,
        'child_free_through_age' => 3,
    ]);

    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(3)->toDateString(),
        'check_out' => now()->addDays(5)->toDateString(),
        'adult_count' => 1,
        'child_count' => 2,
        'senior_count' => 0,
        'pwd_count' => 0,
        'senior_ids' => [],
        'pwd_ids' => [],
        'free_child_count' => 2,
    ]);

    expect($booking->guests)->toHaveCount(3)
        ->and($booking->guests->where('is_free_child', true))->toHaveCount(2)
        ->and($booking->guests->where('is_free_child', true)->sum(fn ($bookingGuest): float => (float) $bookingGuest->share_amount))->toBe(0.0)
        ->and($booking->guests->where('is_free_child', false)->sum(fn ($bookingGuest): float => (float) $bookingGuest->share_amount))->toBe(3600.0)
        ->and($booking->extra_guest_count)->toBe(0)
        ->and($booking->extra_guest_total)->toBe('0.00')
        ->and($booking->total_price)->toBe('3600.00');

    expect(fn () => app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(8)->toDateString(),
        'check_out' => now()->addDays(10)->toDateString(),
        'adult_count' => 1,
        'child_count' => 3,
        'senior_count' => 0,
        'pwd_count' => 0,
        'senior_ids' => [],
        'pwd_ids' => [],
        'free_child_count' => 3,
    ]))->toThrow(ValidationException::class);
});

test('Per Unit shares distribute the remainder to the final paying guest', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario(1);
    $unit->update([
        'maximum_capacity' => 5,
        'child_free_through_age' => 3,
        'pricing_mode' => 'Per Unit',
        'price' => 50,
        'extra_guest_fee' => 25,
    ]);

    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(3)->toDateString(),
        'check_out' => now()->addDays(4)->toDateString(),
        'adult_count' => 3,
        'child_count' => 2,
        'free_child_count' => 2,
        'senior_count' => 0,
        'pwd_count' => 0,
        'senior_ids' => [],
        'pwd_ids' => [],
    ]);
    $payingShares = $booking->guests
        ->where('is_free_child', false)
        ->pluck('share_amount')
        ->all();

    expect($booking->room_subtotal)->toBe('50.00')
        ->and($booking->extra_guest_count)->toBe(2)
        ->and($booking->extra_guest_total)->toBe('50.00')
        ->and($booking->total_price)->toBe('100.00')
        ->and($payingShares)->toBe(['33.33', '33.33', '33.34'])
        ->and(array_sum(array_map(fn (string $share): float => (float) $share, $payingShares)))->toBe(100.0)
        ->and($booking->guests->where('is_free_child', true)->pluck('share_amount')->all())->toBe(['0.00', '0.00']);
});

test('Per Unit shares divide evenly between two paying guests', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario(2);
    $unit->update([
        'pricing_mode' => 'Per Unit',
        'price' => 100,
    ]);

    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(3)->toDateString(),
        'check_out' => now()->addDays(4)->toDateString(),
        'adult_count' => 2,
        'child_count' => 0,
        'senior_count' => 0,
        'pwd_count' => 0,
        'senior_ids' => [],
        'pwd_ids' => [],
    ]);

    expect($booking->guests->pluck('share_amount')->all())->toBe(['50.00', '50.00'])
        ->and($booking->guests->sum(fn ($bookingGuest): float => (float) $bookingGuest->share_amount))->toBe(100.0)
        ->and($booking->total_price)->toBe('100.00');
});

test('per-head bookings charge paying guests only and discount each verified guest share', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario(2);
    $unit->update([
        'maximum_capacity' => 3,
        'child_free_through_age' => 3,
        'pricing_mode' => 'Per Head',
        'price' => 500,
    ]);

    $this->actingAs($guest);
    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('checkIn', now()->addDays(3)->toDateString())
        ->set('checkOut', now()->addDays(5)->toDateString())
        ->set('paxCount', 1)
        ->set('freeChildCount', 1)
        ->assertSee('Stay/room charge · Per Head')
        ->assertSee('₱500.00 / head / night')
        ->assertSee('₱1,000.00')
        ->set('paxCount', 2)
        ->assertSee('2 paying guests × 2 nights')
        ->assertSee('₱2,000.00');

    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(3)->toDateString(),
        'check_out' => now()->addDays(5)->toDateString(),
        'adult_count' => 2,
        'child_count' => 1,
        'free_child_count' => 1,
        'senior_count' => 1,
        'pwd_count' => 0,
        'senior_ids' => ['SENIOR-PER-HEAD'],
        'pwd_ids' => [],
    ]);

    expect($booking->pricing_mode)->toBe('Per Head')
        ->and($booking->nightly_rate)->toBe('500.00')
        ->and($booking->room_subtotal)->toBe('2000.00')
        ->and($booking->extra_guest_count)->toBe(0)
        ->and($booking->extra_guest_total)->toBe('0.00')
        ->and($booking->total_price)->toBe('2000.00')
        ->and($booking->guests->where('is_free_child', false)->first()->share_amount)->toBe('1000.00')
        ->and($booking->guests->where('is_free_child', true)->first()->share_amount)->toBe('0.00');

    $discount = BookingDiscount::where('booking_id', $booking->booking_id)->firstOrFail();
    expect($discount->eligible_amount)->toBe('1000.00')
        ->and($discount->discount_amount)->toBe('200.00')
        ->and($discount->verification_status)->toBe(BookingStatus::PENDING);

    $unit->update([
        'pricing_mode' => 'Per Unit',
        'price' => 900,
        'extra_guest_fee' => 250,
    ]);
    $booking->status = BookingStatus::CONFIRMED;
    $booking->save();

    app(BookingWorkflowService::class)->reviewDiscount($owner, $discount->discount_id, 'Verified');

    expect($booking->fresh()->pricing_mode)->toBe('Per Head')
        ->and($booking->fresh()->nightly_rate)->toBe('500.00')
        ->and($booking->fresh()->extra_guest_fee)->toBe('0.00')
        ->and($booking->fresh()->total_price)->toBe('1800.00');
});

test('the booking form shows the maximum-capacity error without replacing entered guest counts', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario(2);
    $unit->update([
        'maximum_capacity' => 3,
        'child_free_through_age' => 3,
    ]);

    $this->actingAs($guest);
    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('checkIn', now()->addDays(3)->toDateString())
        ->set('checkOut', now()->addDays(5)->toDateString())
        ->set('paxCount', '2')
        ->set('freeChildCount', '2')
        ->assertSet('paxCount', '2')
        ->assertSet('freeChildCount', '2')
        ->assertSee('Counted pax')
        ->assertSee('4 / 3 max')
        ->assertSee('Paying: 2 | Free children: 2')
        ->assertSee('Standard: 2 guests | Max: 3 guests')
        ->assertSee('inputmode="numeric"', false)
        ->assertSee('wire:model.live.debounce.300ms="paxCount"', false)
        ->assertHasErrors(['freeChildCount'])
        ->assertSee('Maximum is 3 guests.')
        ->set('ackPolicy', true)
        ->call('submit')
        ->assertHasErrors(['freeChildCount'])
        ->assertSet('paxCount', '2')
        ->assertSet('freeChildCount', '2')
        ->set('freeChildCount', '1')
        ->assertHasNoErrors(['freeChildCount']);

    $this->assertDatabaseCount('bookings', 0);
});

test('booking form reports unavailable check-in dates and blocked stay ranges immediately', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $blockedCheckIn = now()->addDays(5)->toDateString();
    $stayStart = now()->addDays(10);
    UnitCalendar::create([
        'unit_id' => $unit->unit_id,
        'date' => $blockedCheckIn,
        'status' => 'Unavailable',
        'note' => 'Maintenance',
    ]);
    UnitCalendar::create([
        'unit_id' => $unit->unit_id,
        'date' => $stayStart->copy()->addDay()->toDateString(),
        'status' => 'Unavailable',
        'note' => 'Maintenance',
    ]);
    $blockedCheckoutStart = now()->addDays(20);
    UnitCalendar::create([
        'unit_id' => $unit->unit_id,
        'date' => $blockedCheckoutStart->copy()->addDays(2)->toDateString(),
        'status' => 'Unavailable',
        'note' => 'Maintenance',
    ]);

    expect(app(BookingService::class)->dateRangeConflict(
        $unit,
        $blockedCheckoutStart->toDateString(),
        $blockedCheckoutStart->copy()->addDays(2)->toDateString()
    ))->toBe('blocked');

    $this->actingAs($guest);
    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('paxCount', '2')
        ->set('freeChildCount', '1')
        ->set('checkIn', $blockedCheckIn)
        ->assertHasErrors('checkIn')
        ->assertSee('This check-in date is unavailable or already booked. Choose another date.')
        ->assertSet('paxCount', '2')
        ->assertSet('freeChildCount', '1')
        ->set('checkIn', $stayStart->toDateString())
        ->set('checkOut', $stayStart->copy()->addDays(3)->toDateString())
        ->assertHasErrors('checkOut')
        ->assertSee('Your selected stay includes an unavailable date. Choose different dates.')
        ->assertSet('checkIn', $stayStart->toDateString())
        ->assertSet('checkOut', $stayStart->copy()->addDays(3)->toDateString())
        ->assertSet('paxCount', '2')
        ->assertSet('freeChildCount', '1');

    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('checkIn', $blockedCheckoutStart->toDateString())
        ->set('checkOut', $blockedCheckoutStart->copy()->addDays(2)->toDateString())
        ->assertHasErrors()
        ->assertSee('Your selected stay includes an unavailable date. Choose different dates.')
        ->set('checkOut', $blockedCheckoutStart->copy()->addDay()->toDateString())
        ->assertHasNoErrors(['checkIn', 'checkOut']);
});

test('booking form rejects active booking overlaps but allows turnover and cancelled or rejected dates', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $activeCheckIn = now()->addDays(5);
    $activeCheckOut = $activeCheckIn->copy()->addDays(3);
    Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $activeCheckIn,
        'check_out' => $activeCheckOut,
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'status' => BookingStatus::CONFIRMED,
    ]);
    $cancelledCheckIn = now()->addDays(12);
    Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $cancelledCheckIn,
        'check_out' => $cancelledCheckIn->copy()->addDays(2),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'status' => BookingStatus::CANCELLED,
    ]);
    $rejectedCheckIn = now()->addDays(17);
    Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $rejectedCheckIn,
        'check_out' => $rejectedCheckIn->copy()->addDays(2),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'status' => BookingStatus::REJECTED,
    ]);

    $this->actingAs($guest);
    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('checkIn', $activeCheckIn->copy()->addDay()->toDateString())
        ->assertHasErrors('checkIn')
        ->assertSee('This check-in date is unavailable or already booked. Choose another date.');

    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('checkIn', $activeCheckOut->toDateString())
        ->set('checkOut', $activeCheckOut->copy()->addDays(2)->toDateString())
        ->assertHasNoErrors(['checkIn', 'checkOut'])
        ->set('checkIn', $cancelledCheckIn->toDateString())
        ->set('checkOut', $cancelledCheckIn->copy()->addDays(2)->toDateString())
        ->assertHasNoErrors(['checkIn', 'checkOut'])
        ->set('checkIn', $rejectedCheckIn->toDateString())
        ->set('checkOut', $rejectedCheckIn->copy()->addDays(2)->toDateString())
        ->assertHasNoErrors(['checkIn', 'checkOut']);
});

test('unit check-in and checkout times use the same display format', function () {
    expect(UnitTimeFormatter::format('14:00:00'))->toBe('2:00 PM')
        ->and(UnitTimeFormatter::format('12:00:00'))->toBe('12:00 NN');
});

test('the application timezone is set to Manila', function () {
    expect(config('app.timezone'))->toBe('Asia/Manila');
});

test('senior and PWD counts expand to separate required ID references', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();

    $this->actingAs($guest);
    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->assertDontSee('Senior Citizen 1 reference ID')
        ->set('checkIn', now()->addDays(3)->toDateString())
        ->set('checkOut', now()->addDays(5)->toDateString())
        ->set('paxCount', 2)
        ->call('addSenior')
        ->call('addPwd')
        ->assertSee('Senior Citizen 1 reference ID')
        ->assertSee('PWD 1 reference ID')
        ->set('seniorIds.0', 'SC-REFERENCE')
        ->set('pwdIds.0', 'PWD-REFERENCE')
        ->set('ackPolicy', true)
        ->call('submit')
        ->assertRedirect(route('guest.bookings.show', 1));

    $booking = Booking::firstOrFail();
    expect($booking->discounts)->toHaveCount(2)
        ->and($booking->discounts->pluck('discount_type')->all())->toContain('Senior Citizen', 'PWD');

    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('paxCount', 1)
        ->call('addSenior')
        ->call('addPwd')
        ->assertSet('pwdCount', 0)
        ->assertHasErrors(['pwdCount']);

    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('paxCount', 4)
        ->call('addSenior')
        ->call('addSenior')
        ->call('addSenior')
        ->call('addPwd')
        ->assertSet('seniorCount', 3)
        ->assertSet('pwdCount', 1)
        ->assertSee('Senior Citizen 3 reference ID')
        ->assertSee('PWD 1 reference ID');
});

test('pending requests can be accepted when another booking checks out that day', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $acceptedBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->startOfDay()->addDays(4)->setTime(8, 0),
        'check_out' => now()->startOfDay()->addDays(4)->setTime(12, 0),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'status' => BookingStatus::ACCEPTED,
    ]);
    $pendingBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->startOfDay()->addDays(4)->setTime(16, 0),
        'check_out' => now()->startOfDay()->addDays(5)->setTime(10, 0),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'status' => BookingStatus::PENDING,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.show', $pendingBooking->booking_id))
        ->assertOk()
        ->assertSee('Accept Booking Request')
        ->assertSee('Reject Booking Request')
        ->assertDontSee('Another accepted booking is holding overlapping dates.');

    app(BookingWorkflowService::class)->accept($owner, $pendingBooking->booking_id);

    expect($acceptedBooking->fresh()->status)->toBe(BookingStatus::ACCEPTED)
        ->and($pendingBooking->fresh()->status)->toBe(BookingStatus::ACCEPTED);
});

test('owner booking details keep status actions and omit edit and checkout buttons', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(5),
        'check_out' => now()->addDays(7),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::PENDING,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.index'))
        ->assertOk()
        ->assertSee('Pending')
        ->assertSee('History')
        ->assertSee('Search bookings')
        ->assertSee('Stay overlaps from')
        ->assertDontSee('Date filters show stays that overlap')
        ->assertSee('data-booking-filters', false)
        ->assertSee('data-booking-sort', false)
        ->assertSee('data-booking-filter-panel', false)
        ->assertDontSee('data-ajax-search', false)
        ->assertSee(route('owner.bookings.index', ['section' => 'calendar']))
        ->assertSee(route('owner.bookings.index', ['section' => 'reports']))
        ->assertSee(route('owner.bookings.show', $booking->booking_id));

    $this->get(route('owner.bookings.show', $booking->booking_id))
        ->assertOk()
        ->assertSee('Booking Summary')
        ->assertSee('booking-price-line', false)
        ->assertSee('Stay/room charge')
        ->assertSee('Guest Info')
        ->assertSee('Payments')
        ->assertSee('class="booking-details-layout"', false)
        ->assertSee('class="card booking-details-summary"', false)
        ->assertSee('class="card booking-details-payments"', false)
        ->assertSee('class="card booking-details-guest"', false)
        ->assertSee('class="card booking-details-actions"', false)
        ->assertSee('class="booking-details-side"', false)
        ->assertDontSee('Group composition')
        ->assertDontSee('Senior/PWD')
        ->assertDontSee('Edit History')
        ->assertSee('Actions')
        ->assertSee('Accept Booking Request')
        ->assertSee('Reject Booking Request')
        ->assertDontSee('Edit Booking')
        ->assertDontSee('Checkout');

    $this->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get(route('owner.bookings.index', ['q' => (string) $booking->booking_id]))
        ->assertOk()
        ->assertSee('id="booking-results"', false)
        ->assertSee('#'.$booking->booking_id)
        ->assertDontSee('<x-shell', false);

    $this->post(route('owner.bookings.accept', $booking->booking_id))
        ->assertRedirect(route('owner.bookings.show', $booking->booking_id));
    expect($booking->fresh()->status)->toBe(BookingStatus::ACCEPTED);

    $this->get(route('owner.bookings.show', $booking->booking_id))
        ->assertSee('Actions')
        ->assertSee('Cancel Booking')
        ->assertSee('id="cancellation-reason"', false)
        ->assertDontSee('Edit Booking')
        ->assertDontSee('Checkout');

    $this->from(route('owner.bookings.show', $booking->booking_id))
        ->post(route('owner.bookings.cancel', $booking->booking_id), [])
        ->assertRedirect(route('owner.bookings.show', $booking->booking_id))
        ->assertSessionHasErrors('reason');
    expect($booking->fresh()->status)->toBe(BookingStatus::ACCEPTED);

    $this->post(route('owner.bookings.cancel', $booking->booking_id), ['reason' => 'Unit maintenance'])
        ->assertRedirect(route('owner.bookings.show', $booking->booking_id));
    expect($booking->fresh()->status)->toBe(BookingStatus::CANCELLED)
        ->and($booking->fresh()->cancellation_reason)->toBe('Unit maintenance');
    $this->actingAs($guest)
        ->get(route('guest.bookings.show', $booking->booking_id))
        ->assertSee('data-status-tooltip="Cancellation reason: Unit maintenance"', false);
    $this->actingAs($owner)
        ->get(route('owner.bookings.show', $booking->booking_id))
        ->assertSee('data-status-tooltip="Cancellation reason: Unit maintenance"', false);
    $this->assertDatabaseHas('notifications', [
        'user_id' => $guest->user_id,
        'booking_id' => $booking->booking_id,
        'type' => 'booking_cancelled',
        'message' => 'Your booking was cancelled. Reason: Unit maintenance',
    ]);
    $this->assertDatabaseHas('notifications', [
        'user_id' => $owner->user_id,
        'booking_id' => $booking->booking_id,
        'type' => 'booking_cancelled',
        'message' => "Booking #{$booking->booking_id} was cancelled by {$owner->first_name} {$owner->last_name}. Reason: Unit maintenance",
    ]);
    $this->get(route('owner.bookings.index'))
        ->assertSee('data-status-tooltip="Cancellation reason: Unit maintenance"', false);
});

test('owner can cancel a pending booking from its details with a reason', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->addDays(5),
        'check_out' => today()->addDays(7),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::PENDING,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.index'))
        ->assertSee('Pending')
        ->assertSee('History')
        ->assertDontSee(route('owner.bookings.cancel', $booking->booking_id));
    $this->get(route('owner.bookings.show', $booking->booking_id))
        ->assertSee('Cancel Booking')
        ->assertSee(route('owner.bookings.cancel', $booking->booking_id));

    $this->post(route('owner.bookings.cancel', $booking->booking_id), [
        'reason' => 'Dates no longer available',
    ])->assertRedirect(route('owner.bookings.show', $booking->booking_id));

    expect($booking->fresh()->status)->toBe(BookingStatus::CANCELLED)
        ->and($booking->fresh()->cancellation_reason)->toBe('Dates no longer available');
    $this->assertDatabaseHas('notifications', [
        'user_id' => $guest->user_id,
        'booking_id' => $booking->booking_id,
        'type' => 'booking_cancelled',
    ]);
    $this->get(route('owner.bookings.index'))
        ->assertSee('History')
        ->assertSee('data-status-tooltip="Cancellation reason: Dates no longer available"', false);
});

test('owner can archive one or many eligible history bookings from the booking list', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $bookings = collect(range(0, 3))->map(fn (int $offset): Booking => Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->subDays(8 - ($offset * 2)),
        'check_out' => today()->subDays(7 - ($offset * 2)),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::COMPLETED,
    ]));
    app(BookingWorkflowService::class)->archiveBooking($owner, $bookings[0]->booking_id, 'Already archived');

    $this->actingAs($owner)
        ->get(route('owner.bookings.index'))
        ->assertSee('History')
        ->assertSee('data-tab="history-bookings"', false)
        ->assertSee('data-tab="archive-bookings"', false)
        ->assertSee('data-archive-select-all', false)
        ->assertSee('Archive selected')
        ->assertSee('Select all')
        ->assertSee('#'.$bookings[0]->booking_id)
        ->assertSee('name="booking_ids[]" value="'.$bookings[1]->booking_id.'"', false)
        ->assertSee('name="booking_ids[]" value="'.$bookings[2]->booking_id.'"', false)
        ->assertSee('name="booking_ids[]" value="'.$bookings[3]->booking_id.'"', false)
        ->assertDontSee('name="booking_ids[]" value="'.$bookings[0]->booking_id.'"', false)
        ->assertDontSee('<th>Archived</th>', false)
        ->assertDontSee('<th>Archive note</th>', false)
        ->assertDontSee('Already archived')
        ->assertDontSee('booking-status-indicator__dot', false);
    $this->get(route('owner.bookings.show', $bookings[0]->booking_id))
        ->assertDontSee('Archive Booking');

    $this->post(route('owner.bookings.archive-selected'), [
        'booking_ids' => [$bookings[1]->booking_id],
    ])
        ->assertRedirect(route('owner.bookings.index', ['tab' => 'archive']))
        ->assertSessionHas('success', 'The booking was archived.');

    $this->post(route('owner.bookings.archive-selected'), [
        'booking_ids' => [$bookings[2]->booking_id, $bookings[3]->booking_id],
    ])
        ->assertRedirect(route('owner.bookings.index', ['tab' => 'archive']))
        ->assertSessionHas('success', '2 bookings were archived.');

    foreach ($bookings as $booking) {
        $this->assertDatabaseHas('transaction_archives', [
            'booking_id' => $booking->booking_id,
            'archived_by' => $owner->user_id,
        ]);
    }

    $this->get(route('owner.bookings.index', ['tab' => 'archive']))
        ->assertSee('class="tab-pane active" id="archive-bookings"', false)
        ->assertSee('#'.$bookings[1]->booking_id)
        ->assertSee('#'.$bookings[2]->booking_id)
        ->assertSee('#'.$bookings[3]->booking_id)
        ->assertDontSee('name="booking_ids[]"', false);
});

test('owner single archive redirects to the Archive tab', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->subDays(3),
        'check_out' => today()->subDays(2),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'status' => BookingStatus::COMPLETED,
    ]);

    $this->actingAs($owner)
        ->post(route('owner.bookings.archive', $booking->booking_id), ['note' => 'Stay closed'])
        ->assertRedirect(route('owner.bookings.index', ['tab' => 'archive']))
        ->assertSessionHas('success', 'The booking was archived and is available in the Archive tab.');

    $this->assertDatabaseHas('transaction_archives', [
        'booking_id' => $booking->booking_id,
        'archived_by' => $owner->user_id,
        'archive_note' => 'Stay closed',
    ]);

    $this->get(route('owner.bookings.index', ['tab' => 'archive']))
        ->assertSee('class="tab-pane active" id="archive-bookings"', false)
        ->assertSee('#'.$booking->booking_id);
});

test('staff single archive redirects to the Archive tab', function () {
    ['owner' => $owner, 'house' => $house, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $staffRoleId = Role::where('name', 'Staff')->firstOrFail()->role_id;
    $staff = User::create([
        'role_id' => $staffRoleId,
        'first_name' => 'Assigned',
        'last_name' => 'Staff',
        'email' => 'archive-staff@example.com',
        'password' => 'StaffPass123!',
        'is_active' => true,
    ]);
    Invitation::create([
        'invited_by' => $owner->user_id,
        'house_id' => $house->house_id,
        'role_id' => $staffRoleId,
        'email' => $staff->email,
        'token' => 'archive-staff-invitation',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->subDays(3),
        'check_out' => today()->subDays(2),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'status' => BookingStatus::COMPLETED,
    ]);

    $this->actingAs($staff)
        ->post(route('staff.bookings.archive', $booking->booking_id))
        ->assertRedirect(route('staff.bookings.index', ['tab' => 'archive']));

    $this->get(route('staff.bookings.index', ['tab' => 'archive']))
        ->assertSee('class="tab-pane active" id="archive-bookings"', false)
        ->assertSee('#'.$booking->booking_id);
});

test('bulk archive rejects an unauthorized selection without archiving any booking', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $ownedBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->subDays(3),
        'check_out' => today()->subDays(2),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'status' => BookingStatus::COMPLETED,
    ]);
    $otherOwner = User::create([
        'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Other',
        'last_name' => 'Owner',
        'email' => 'bulk-archive-other-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $otherHouse = TransientHouse::create([
        'owner_id' => $otherOwner->user_id,
        'name' => 'Other Booking House',
        'address' => '20 Test Road',
        'city' => 'Baguio',
        'contact_number' => '5552001',
        'is_active' => true,
    ]);
    $otherUnit = Unit::create([
        'house_id' => $otherHouse->house_id,
        'unit_type' => 'Other Room',
        'capacity' => 2,
        'price' => 1200,
        'status' => 'Available',
    ]);
    $otherBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $otherUnit->unit_id,
        'check_in' => today()->subDays(5),
        'check_out' => today()->subDays(4),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1200,
        'status' => BookingStatus::COMPLETED,
    ]);

    $this->actingAs($owner)
        ->post(route('owner.bookings.archive-selected'), [
            'booking_ids' => [$ownedBooking->booking_id, $otherBooking->booking_id],
        ])
        ->assertNotFound();

    $this->assertDatabaseMissing('transaction_archives', [
        'booking_id' => $ownedBooking->booking_id,
    ]);
    $this->assertDatabaseMissing('transaction_archives', [
        'booking_id' => $otherBooking->booking_id,
    ]);
});

test('guest can cancel an accepted unpaid stay with a reason and releases its dates', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $checkInDate = now()->addDays(5);
    $checkIn = $checkInDate->toDateString();
    $checkOut = $checkInDate->copy()->addDays(2)->toDateString();
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $booking->booking_id);

    $this->actingAs($guest)
        ->get(route('guest.dashboard'))
        ->assertSee('Accepted (1)')
        ->assertDontSee(route('guest.bookings.cancel', $booking->booking_id));

    $this->actingAs($guest)
        ->get(route('guest.bookings.show', $booking->booking_id))
        ->assertOk()
        ->assertSee('Cancel this booking')
        ->assertSee('Why are you cancelling?')
        ->assertSee('class="policy-box guest-booking-cancel"', false)
        ->assertDontSee('guest-booking-cancel" open', false)
        ->assertDontSee('non-refundable')
        ->assertDontSee('For refund requests')
        ->assertDontSee('acknowledge_non_refund');

    $otherGuest = User::create([
        'role_id' => Role::where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Other',
        'last_name' => 'Guest',
        'email' => 'other-guest@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);
    $this->actingAs($otherGuest)
        ->post(route('guest.bookings.cancel', $booking->booking_id), [
            'reason' => 'Not my booking',
        ])
        ->assertNotFound();

    expect(fn () => app(BookingWorkflowService::class)->cancelByGuest($guest, $booking->booking_id, '   '))
        ->toThrow(ValidationException::class);
    expect($booking->fresh()->status)->toBe(BookingStatus::ACCEPTED);

    $this->actingAs($guest)
        ->from(route('guest.bookings.show', $booking->booking_id))
        ->post(route('guest.bookings.cancel', $booking->booking_id), ['reason' => '   '])
        ->assertRedirect(route('guest.bookings.show', $booking->booking_id))
        ->assertSessionHasErrors([
            'reason' => 'Please explain why you are cancelling this booking.',
        ]);
    $this->get(route('guest.bookings.show', $booking->booking_id))
        ->assertSee('Why are you cancelling?')
        ->assertSee('Please explain why you are cancelling this booking.');

    $this->actingAs($guest)
        ->post(route('guest.bookings.cancel', $booking->booking_id), [
            'reason' => 'Plans changed',
        ])
        ->assertRedirect(route('guest.bookings.show', $booking->booking_id))
        ->assertSessionHas('success');

    expect($booking->fresh()->status)->toBe(BookingStatus::CANCELLED)
        ->and($booking->fresh()->cancellation_reason)->toBe('Cancelled by guest: Plans changed')
        ->and(Payment::where('booking_id', $booking->booking_id)->count())->toBe(0)
        ->and(Refund::where('booking_id', $booking->booking_id)->count())->toBe(0)
        ->and(UnitCalendar::where('unit_id', $unit->unit_id)->where('note', 'Confirmed booking #'.$booking->booking_id)->count())->toBe(0);
    expect(app(BookingService::class)->hasBlockedDate($unit, $checkIn, $checkOut))->toBeFalse();
    $this->actingAs($owner)
        ->get(route('owner.bookings.index', [
            'section' => 'calendar',
            'unit_id' => $unit->unit_id,
            'month' => $checkInDate->month,
            'year' => $checkInDate->year,
        ]))
        ->assertSee('data-calendar-select-date="'.$checkIn.'"', false);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $owner->user_id,
        'booking_id' => $booking->booking_id,
        'type' => 'booking_cancelled',
        'message' => "{$guest->first_name} {$guest->last_name} cancelled booking #{$booking->booking_id}. Reason: Plans changed",
    ]);
    $this->actingAs($guest)
        ->get(route('guest.dashboard'))
        ->assertSee('data-status-tooltip="Cancellation reason: Cancelled by guest: Plans changed"', false);

    $newBooking = app(BookingService::class)->createBooking($otherGuest, $unit, [
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Other Guest']],
    ]);
    app(BookingWorkflowService::class)->accept($owner, $newBooking->booking_id);

    expect($newBooking->fresh()->status)->toBe(BookingStatus::ACCEPTED);
});

test('guest can cancel a pending booking from the pending tab with a reason', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today(),
        'check_out' => today()->addDays(2),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::PENDING,
    ]);

    $this->actingAs($guest)
        ->get(route('guest.dashboard'))
        ->assertSee('History')
        ->assertDontSee(route('guest.bookings.cancel', $booking->booking_id));
    $this->get(route('guest.bookings.show', $booking->booking_id))
        ->assertSee('Cancel this booking')
        ->assertSee(route('guest.bookings.cancel', $booking->booking_id));

    $this->post(route('guest.bookings.cancel', $booking->booking_id), [
        'reason' => 'Plans changed',
    ])->assertRedirect(route('guest.bookings.show', $booking->booking_id));

    expect($booking->fresh()->status)->toBe(BookingStatus::CANCELLED)
        ->and($booking->fresh()->cancellation_reason)->toBe('Cancelled by guest: Plans changed');
    $this->get(route('guest.dashboard'))
        ->assertSee('History')
        ->assertSee('data-status-tooltip="Cancellation reason: Cancelled by guest: Plans changed"', false);
});

test('guest cancellation shows refund guidance after submitting a downpayment', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(7)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);
    app(BookingWorkflowService::class)->accept($owner, $booking->booking_id);
    Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'Cash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::UNDER_REVIEW,
        'payment_date' => now(),
    ]);

    $this->actingAs($guest)
        ->get(route('guest.bookings.show', $booking->booking_id))
        ->assertSee('Ask staff about refunds.');
});

test('owner calendar shows unit availability, booking contact details, and links booked dates', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $guest->update(['phone' => '09123456789']);
    $calendarMonth = now()->addMonth()->startOfMonth();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $calendarMonth->copy()->addDays(4)->setTime(14, 0),
        'check_out' => $calendarMonth->copy()->addDays(6)->setTime(11, 0),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    $checkedInBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $calendarMonth->copy()->addDays(8)->setTime(14, 0),
        'check_out' => $calendarMonth->copy()->addDays(10)->setTime(11, 0),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CHECKED_IN,
    ]);
    $blockedDate = $calendarMonth->copy()->addDays(12)->toDateString();
    UnitCalendar::create([
        'unit_id' => $unit->unit_id,
        'date' => $blockedDate,
        'status' => 'Unavailable',
        'note' => 'Maintenance',
    ]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.index', [
            'section' => 'calendar',
            'unit_id' => $unit->unit_id,
            'month' => $calendarMonth->month,
            'year' => $calendarMonth->year,
        ]))
        ->assertOk()
        ->assertSee('Unit Calendar')
        ->assertSee('Booking #'.$booking->booking_id)
        ->assertSee('Booking #'.$checkedInBooking->booking_id)
        ->assertSee('Hover over or focus a booked date', false)
        ->assertSee('data-calendar-guest="Booking Guest"', false)
        ->assertSee('data-calendar-phone="09123456789"', false)
        ->assertDontSee('Calendar key')
        ->assertDontSee('calendar-summary')
        ->assertDontSee('Available dates')
        ->assertDontSee('Booked dates')
        ->assertDontSee('Unavailable dates')
        ->assertDontSee('Units in your access')
        ->assertSee('Unavailable')
        ->assertSee('Maintenance')
        ->assertSee(route('owner.bookings.show', $booking->booking_id));
});

test('accepted reservations appear as booked on owner and public calendars with a payment deadline', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $checkIn = now()->addDays(10);
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => $checkIn->toDateString(),
        'check_out' => $checkIn->copy()->addDays(2)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);
    app(BookingWorkflowService::class)->accept($owner, $booking->booking_id);

    $this->actingAs($owner)
        ->get(route('owner.bookings.index', [
            'section' => 'calendar',
            'unit_id' => $unit->unit_id,
            'month' => $checkIn->month,
            'year' => $checkIn->year,
        ]))
        ->assertOk()
        ->assertDontSee('calendar-summary')
        ->assertDontSee('Available dates')
        ->assertDontSee('Booked dates')
        ->assertDontSee('Unavailable dates')
        ->assertDontSee('Units in your access')
        ->assertSee('Booked')
        ->assertDontSee('Temporary hold')
        ->assertDontSee('Payment due');

    expect($booking->fresh()->status)->toBe(BookingStatus::ACCEPTED)
        ->and($booking->fresh()->payment_deadline)
        ->toEqual($booking->fresh()->accepted_at->copy()->addHours(config('booking.payment_deadline_hours')));

    $this->get(route('public.unit', $unit->unit_id))
        ->assertOk()
        ->assertDontSee('status-held')
        ->assertDontSee('Temporarily held while payment is pending.');
});

test('a ticket is emailed only after payment verification confirms the reserved booking', function () {
    Mail::fake();
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $unit->update([
        'check_in_time' => '15:30',
        'check_out_time' => '11:00',
    ]);
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(7)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);

    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $booking->booking_id);

    Mail::assertSent(BookingAccepted::class, function (BookingAccepted $mail) use ($guest, $booking): bool {
        $content = $mail->render();

        return $mail->hasTo($guest->email)
            && str_contains($content, 'Submit the downpayment of')
            && str_contains($content, '₱1,800.00')
            && str_contains($content, 'at 3:30 PM')
            && str_contains($content, 'at 11:00 AM')
            && str_contains($content, route('guest.bookings.payment', $booking->booking_id));
    });
    $this->actingAs($guest)
        ->get(route('guest.bookings.payment', $booking->booking_id))
        ->assertOk()
        ->assertSee('Submit payment details')
        ->assertSee('₱1,800.00')
        ->assertSee('downpayment-layout', false)
        ->assertSee('GCash')
        ->assertSee('Bank Transfer')
        ->assertSee('Bill breakdown')
        ->assertSee('booking-price-line', false)
        ->assertSee('Estimated balance after downpayment');
    $this->get(route('guest.bookings.show', $booking->booking_id))
        ->assertDontSee('Download Ticket');
    $this->get(route('guest.bookings.ticket', $booking->booking_id))
        ->assertNotFound();

    expect(app(BookingTicketDeliveryService::class)->send($booking->booking_id))
        ->toBe('ineligible');
    Mail::assertNotSent(BookingTicket::class);

    $payment = $workflow->submitPayment($guest, $booking->booking_id, [
        'payment_method' => 'GCash',
        'amount' => 1800,
    ]);
    $workflow->reviewPayment($owner, $payment->payment_id, PaymentStatus::VERIFIED, null);

    Mail::assertSent(BookingTicket::class, function (BookingTicket $mail) use ($guest): bool {
        expect($mail->pdfContents)->toStartWith('%PDF');
        $attachments = $mail->attachments();
        expect($attachments)->toHaveCount(1)
            ->and($attachments[0]->as)->toBe('booking-ticket-'.$mail->booking->booking_reference.'.pdf')
            ->and($attachments[0]->mime)->toBe('application/pdf');

        return $mail->hasTo($guest->email)
            && str_contains($mail->render(), 'Your booking is confirmed');
    });

    $additionalPayment = Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 100,
        'payment_status' => PaymentStatus::UNDER_REVIEW,
        'payment_date' => now(),
    ]);
    $workflow->reviewPayment($owner, $additionalPayment->payment_id, PaymentStatus::VERIFIED, null);
    Mail::assertSent(BookingTicket::class, 1);
    expect($booking->fresh()->ticket_email_status)->toBe('sent')
        ->and($booking->fresh()->ticket_email_sent_at)->not->toBeNull();

    $this->get(route('guest.bookings.show', $booking->booking_id))
        ->assertSee('Your Booking Ticket was emailed to '.$guest->email)
        ->assertDontSee('Download Ticket')
        ->assertSeeInOrder([
            'class="col-fixed-300"',
            'class="booking-details-actions',
            'data-booking-ticket-action',
        ], false)
        ->assertDontSee('Please download your ticket')
        ->assertDontSee('Back to My Bookings')
        ->assertDontSee('<div class="text-muted fs-sm">Guests</div>', false);
    $ticketHtml = view('guest.bookings.ticket', [
        'booking' => $booking->fresh()->load('unit.house', 'guest'),
    ])->render();
    expect($ticketHtml)->toContain('3:30 PM')
        ->and($ticketHtml)->toContain('11:00 AM')
        ->and($ticketHtml)->toContain('Booking reference')
        ->and($ticketHtml)->toContain('background: #fffaf5')
        ->and($ticketHtml)->toContain('Booking status')
        ->and($ticketHtml)->not->toContain('Number of guests');

    $ticketResponse = $this->get(route('guest.bookings.ticket', $booking->booking_id));
    $ticketResponse->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertHeader('content-disposition');
    expect($ticketResponse->getContent())
        ->toStartWith('%PDF')
        ->not->toContain('/Subtype /Image');
    preg_match_all('~/Type\s*/Page\b~', $ticketResponse->getContent(), $pageObjects);
    expect($pageObjects[0])->toHaveCount(1);
});

test('a ticket email failure does not roll back verified payment or booking confirmation', function () {
    config([
        'mail.default' => 'brevo',
        'services.brevo.key' => 'test-only-api-key',
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'https://api.brevo.com/v3/smtp/email' => Http::response(['message' => 'rejected'], 500),
    ]);

    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(5),
        'check_out' => now()->addDays(7),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'booking_reference' => 'MCTBS-000102',
        'status' => BookingStatus::ACCEPTED,
        'accepted_at' => now(),
        'payment_deadline' => now()->addDay(),
    ]);
    $payment = Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::UNDER_REVIEW,
        'payment_date' => now(),
    ]);

    app(BookingWorkflowService::class)
        ->reviewPayment($owner, $payment->payment_id, PaymentStatus::VERIFIED, null);

    expect($booking->fresh()->status)->toBe(BookingStatus::CONFIRMED)
        ->and($booking->fresh()->ticket_email_status)->toBe('failed')
        ->and($payment->fresh()->payment_status)->toBe(PaymentStatus::VERIFIED);
    Http::assertSentCount(1);
});

test('only the guest who owns a confirmed booking can download its ticket', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(7)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 0,
        'status' => BookingStatus::CONFIRMED,
    ]);
    $otherGuest = User::create([
        'role_id' => Role::where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Different',
        'last_name' => 'Guest',
        'email' => 'different-guest@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);

    $this->actingAs($otherGuest)
        ->get(route('guest.bookings.ticket', $booking->booking_id))
        ->assertNotFound();
});

test('owner sends a unique seven-day feedback link from guest check-in', function () {
    Mail::fake();
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->subDays(3)->toDateString(),
        'check_out' => now()->subDay()->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 0,
        'status' => BookingStatus::CHECKED_IN,
    ]);

    $this->actingAs($owner)
        ->post(route('owner.guests.feedback.send', $booking->booking_id))
        ->assertRedirect()
        ->assertSessionHas('success', 'The feedback link was emailed to the guest.');

    $sentMail = null;
    Mail::assertSent(BookingFeedbackRequested::class, function (BookingFeedbackRequested $mail) use ($guest, &$sentMail): bool {
        $sentMail = $mail;

        return $mail->hasTo($guest->email);
    });

    $token = basename((string) parse_url($sentMail->feedbackUrl, PHP_URL_PATH));
    expect($booking->fresh()->feedback_token)->toBe(hash('sha256', $token));
    expect($booking->fresh()->feedback_token_expires_at->isBetween(now()->addDays(7)->subSeconds(2), now()->addDays(7)->addSeconds(2)))->toBeTrue();
    expect($sentMail->render())->toContain($sentMail->feedbackUrl);
    expect($sentMail->render())->toContain('expires in 7 days');
});

test('checkout does not automatically send feedback mail', function () {
    Mail::fake();
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->subDays(3)->toDateString(),
        'check_out' => now()->subDay()->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 0,
        'status' => BookingStatus::CONFIRMED,
    ]);

    app(BookingWorkflowService::class)->completeCheckout($owner, $booking->booking_id);

    Mail::assertNothingSent();
});

test('guest feedback saves a review and notifies the owner and assigned staff', function () {
    ['owner' => $owner, 'house' => $house, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->subDays(3)->toDateString(),
        'check_out' => now()->subDay()->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 0,
        'status' => BookingStatus::CHECKED_IN,
        'feedback_token' => hash('sha256', $token = 'guest-feedback-token'),
        'feedback_token_expires_at' => now()->addDays(7),
    ]);
    $staffRoleId = Role::where('name', 'Staff')->firstOrFail()->role_id;
    $staff = User::create([
        'role_id' => $staffRoleId,
        'first_name' => 'Assigned',
        'last_name' => 'Staff',
        'email' => 'feedback-staff@example.com',
        'password' => 'StaffPass123!',
        'is_active' => true,
    ]);
    Invitation::create([
        'invited_by' => $owner->user_id,
        'house_id' => $house->house_id,
        'role_id' => $staffRoleId,
        'email' => $staff->email,
        'token' => 'feedback-staff-assignment',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);
    $feedbackUrl = route('guest.feedback.show', ['booking' => $booking->booking_id, 'token' => $token]);

    $this->get(route('public.unit', $unit))->assertSee('No reviews yet');
    $this->get($feedbackUrl)->assertRedirect(route('login'));
    $this->post(route('login'), [
        'email' => $guest->email,
        'password' => 'GuestPass123!',
    ])->assertRedirect($feedbackUrl);
    $this->get($feedbackUrl)->assertSee('How was your stay?')->assertSee('Submit feedback');
    $this->post($feedbackUrl, [
        'rating' => 5,
        'comment' => 'We had a lovely stay.',
    ])->assertRedirect($feedbackUrl);
    $this->get($feedbackUrl)->assertSee('Thank you for sharing your feedback.');

    $this->assertDatabaseHas('reviews', [
        'booking_id' => $booking->booking_id,
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'rating' => 5,
        'comment' => 'We had a lovely stay.',
    ]);
    $this->assertDatabaseHas('notifications', [
        'user_id' => $owner->user_id,
        'booking_id' => $booking->booking_id,
        'type' => 'review_submitted',
    ]);
    $this->assertDatabaseHas('notifications', [
        'user_id' => $staff->user_id,
        'booking_id' => $booking->booking_id,
        'type' => 'review_submitted',
    ]);
    $this->get($feedbackUrl)->assertOk()->assertSee('This feedback link has already been used');
    $this->post($feedbackUrl, ['rating' => 4])
        ->assertSessionHasErrors('feedback');
    expect(Review::query()->where('booking_id', $booking->booking_id)->count())->toBe(1);

    $this->get(route('public.unit', $unit))
        ->assertSee('5.0 out of 5')
        ->assertSee('1 review')
        ->assertSee('Booking Guest')
        ->assertSee('We had a lovely stay.');

    $this->actingAs($owner)
        ->get(route('owner.bookings.show', $booking->booking_id))
        ->assertSee('Guest Feedback')
        ->assertSee('Current review')
        ->assertSee('We had a lovely stay.');
    $this->actingAs($staff)
        ->get(route('staff.bookings.show', $booking->booking_id))
        ->assertSee('Current review')
        ->assertSee('We had a lovely stay.');
});

test('historical feedback remains readable and prevents a duplicate canonical review', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->subDays(3)->toDateString(),
        'check_out' => now()->subDay()->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 0,
        'status' => BookingStatus::CHECKED_IN,
        'feedback_token' => hash('sha256', 'legacy-feedback-token'),
        'feedback_token_expires_at' => now()->addDays(7),
    ]);
    $legacyFeedback = Feedback::create([
        'booking_id' => $booking->booking_id,
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'rating' => 4,
        'review' => 'Original legacy feedback.',
        'status' => 'Submitted',
    ]);
    expect(fn () => app(BookingWorkflowService::class)->sendFeedbackRequest($owner, $booking->booking_id))
        ->toThrow(ValidationException::class);

    $feedbackUrl = route('guest.feedback.show', [
        'booking' => $booking->booking_id,
        'token' => 'legacy-feedback-token',
    ]);

    $otherGuest = User::create([
        'role_id' => Role::where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Other',
        'last_name' => 'Guest',
        'email' => 'other-feedback-guest@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);
    $this->actingAs($otherGuest)
        ->post($feedbackUrl, ['rating' => 5, 'comment' => 'Unauthorized.'])
        ->assertForbidden();

    $this->actingAs($guest)
        ->get($feedbackUrl)
        ->assertSee('This feedback link has already been used');
    $this->post($feedbackUrl, ['rating' => 5, 'comment' => 'Duplicate.'])
        ->assertSessionHasErrors('feedback');

    expect($booking->fresh()->legacyFeedback()->first()->feedback_id)->toBe($legacyFeedback->feedback_id)
        ->and($booking->fresh()->review()->exists())->toBeFalse();
    $this->assertDatabaseHas('feedback', [
        'feedback_id' => $legacyFeedback->feedback_id,
        'review' => 'Original legacy feedback.',
    ]);
    $this->assertDatabaseCount('reviews', 0);

    $this->actingAs($owner)
        ->get(route('owner.bookings.show', $booking->booking_id))
        ->assertSee('Historical feedback')
        ->assertSee('Original legacy feedback.');
});

test('expired and invalid feedback links display clear messages', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->subDays(3)->toDateString(),
        'check_out' => now()->subDay()->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 0,
        'status' => BookingStatus::COMPLETED,
        'feedback_token' => hash('sha256', 'expired-feedback-token'),
        'feedback_token_expires_at' => now()->subSecond(),
    ]);
    $this->actingAs($guest);
    $expiredUrl = route('guest.feedback.show', ['booking' => $booking->booking_id, 'token' => 'expired-feedback-token']);

    $this->get($expiredUrl)->assertSee('This feedback link has expired.');
    $this->get(route('guest.feedback.show', ['booking' => $booking->booking_id, 'token' => 'wrong-token']))
        ->assertSee('This feedback link is invalid or has expired.');
    $this->from($expiredUrl)->post($expiredUrl, ['rating' => 5])
        ->assertSessionHasErrors('feedback');
    $this->assertDatabaseCount('reviews', 0);
});

test('public availability calendar hides booking numbers and omits the temporary hold legend', function () {
    ['unit' => $unit] = makeGuestBookingScenario();
    UnitCalendar::create([
        'unit_id' => $unit->unit_id,
        'date' => now()->addDays(10)->toDateString(),
        'status' => 'Booked',
        'note' => 'Confirmed booking #765432',
    ]);

    $this->get(route('public.unit', $unit->unit_id))
        ->assertSee('This date is already booked.')
        ->assertDontSee('Confirmed booking #765432')
        ->assertDontSee('Temporary hold');
});

test('owner can block selected dates and unblock one from the unit calendar', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $startDate = now()->addDays(10)->toDateString();
    $endDate = now()->addDays(12)->toDateString();

    $this->actingAs($owner)
        ->post(route('owner.bookings.calendar-blocks.store', [
            'section' => 'calendar',
            'unit_id' => $unit->unit_id,
        ]), [
            'unit_id' => $unit->unit_id,
            'dates' => [$startDate, now()->addDays(11)->toDateString(), $endDate],
            'note' => "Owner's maintenance",
        ])
        ->assertSessionHasNoErrors();

    foreach ([$startDate, now()->addDays(11)->toDateString(), $endDate] as $date) {
        expect(UnitCalendar::query()
            ->where('unit_id', $unit->unit_id)
            ->whereDate('date', $date)
            ->where('status', 'Unavailable')
            ->where('note', "Owner's maintenance")
            ->exists())->toBeTrue();
    }

    expect(fn () => app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => $startDate,
        'check_out' => now()->addDays(13)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]))->toThrow(ValidationException::class);

    $availability = UnitCalendar::query()
        ->where('unit_id', $unit->unit_id)
        ->whereDate('date', $startDate)
        ->firstOrFail();

    $this->get(route('owner.bookings.index', [
        'section' => 'calendar',
        'unit_id' => $unit->unit_id,
        'month' => now()->addDays(10)->month,
        'year' => now()->addDays(10)->year,
    ]))
        ->assertOk()
        ->assertSee('Block date(s)')
        ->assertSee("Owner's maintenance")
        ->assertSee('Unblock');

    $this->get(route('public.unit', $unit->unit_id))
        ->assertOk()
        ->assertSee('Owner')
        ->assertSee('encodeURIComponent(JSON.stringify({type:', false);

    $this->delete(route('owner.bookings.calendar-blocks.destroy', [
        'availabilityId' => $availability->availability_id,
        'section' => 'calendar',
        'unit_id' => $unit->unit_id,
    ]))->assertSessionHasNoErrors();

    $this->assertDatabaseMissing('unit_availability', [
        'availability_id' => $availability->availability_id,
    ]);
    $this->assertDatabaseCount('unit_availability', 2);
});

test('owner cannot block dates held by an accepted or confirmed booking', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $checkIn = now()->addDays(10);
    Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $checkIn->copy()->setTime(14, 0),
        'check_out' => $checkIn->copy()->addDays(2)->setTime(11, 0),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::ACCEPTED,
        'accepted_at' => now(),
        'payment_deadline' => now()->addDay(),
    ]);

    $this->actingAs($owner)
        ->from(route('owner.bookings.index', ['section' => 'calendar', 'unit_id' => $unit->unit_id]))
        ->post(route('owner.bookings.calendar-blocks.store'), [
            'unit_id' => $unit->unit_id,
            'dates' => [$checkIn->toDateString(), $checkIn->copy()->addDay()->toDateString()],
            'note' => 'Maintenance',
        ])
        ->assertSessionHasErrors('dates');

    $this->assertDatabaseCount('unit_availability', 0);
});

test('owner reports total only verified payments for the selected period', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(5),
        'check_out' => now()->addDays(7),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::COMPLETED,
    ]);
    Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'Cash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
    ]);
    Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::UNDER_REVIEW,
        'payment_date' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.index', ['section' => 'reports', 'period' => 'monthly']))
        ->assertOk()
        ->assertSee('Reports')
        ->assertDontSee('Manage Bookings')
        ->assertSee('Net Revenue')
        ->assertSee('Payments received')
        ->assertSee('Verified payments')
        ->assertDontSee('Main Revenue')
        ->assertSee('₱1,800.00')
        ->assertSee('Refunds')
        ->assertSee('₱0.00')
        ->assertSee('Cash')
        ->assertDontSee('GCash');

    $this->actingAs($owner)
        ->get(route('owner.bookings.index', ['section' => 'reports']))
        ->assertOk()
        ->assertSee('aria-current="page"', false)
        ->assertSee('Monthly');
});

test('owner reports count completed refunds only when linked to verified payments', function () {
    ['owner' => $owner, 'house' => $house, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(5),
        'check_out' => now()->addDays(7),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::COMPLETED,
    ]);
    $verifiedPayment = Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'Cash',
        'amount' => 1000,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
    ]);
    $unverifiedPayment = Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 500,
        'payment_status' => PaymentStatus::UNDER_REVIEW,
        'payment_date' => now(),
    ]);
    Refund::create([
        'booking_id' => $booking->booking_id,
        'payment_id' => $verifiedPayment->payment_id,
        'amount' => 100,
        'reason' => 'Completed refund',
        'refund_status' => 'Refunded',
        'processed_at' => now(),
    ]);
    Refund::create([
        'booking_id' => $booking->booking_id,
        'payment_id' => $unverifiedPayment->payment_id,
        'amount' => 250,
        'reason' => 'Refund linked to unverified payment',
        'refund_status' => 'Refunded',
        'processed_at' => now(),
    ]);
    Refund::create([
        'booking_id' => $booking->booking_id,
        'payment_id' => $verifiedPayment->payment_id,
        'amount' => 50,
        'reason' => 'Pending refund',
        'refund_status' => 'Pending',
        'processed_at' => now(),
    ]);

    $report = app(BookingReportService::class)->generate(collect([$house->house_id]), [
        'period' => 'monthly',
        'year' => (int) now()->year,
        'quarter' => 'All',
        'house_id' => null,
        'from_date' => today()->toDateString(),
        'to_date' => today()->toDateString(),
    ]);

    expect($report['totalReceived'])->toBe(1000.0)
        ->and($report['totalRefunded'])->toBe(100.0)
        ->and($report['totalRevenue'])->toBe(900.0)
        ->and($report['refunds'])->toHaveCount(1)
        ->and($report['revenueByPeriodRows'][0]['payments'])->toBe(1000.0)
        ->and($report['revenueByPeriodRows'][0]['refunds'])->toBe(100.0);

    $this->actingAs($owner)
        ->get(route('owner.bookings.index', ['section' => 'reports', 'period' => 'monthly']))
        ->assertOk()
        ->assertSee('-₱100.00')
        ->assertDontSee('-₱250.00');
});

test('owner reports booking activity and verified payments within an inclusive custom date range', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $fromDate = now()->toDateString();
    $toDate = now()->toDateString();
    $insideBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(5),
        'check_out' => now()->addDays(7),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::COMPLETED,
    ]);
    $outsideBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(8),
        'check_out' => now()->addDays(10),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::PENDING,
    ]);
    $outsideBooking->forceFill(['created_at' => now()->subDay()])->saveQuietly();
    Payment::create([
        'booking_id' => $insideBooking->booking_id,
        'payment_method' => 'Cash',
        'amount' => 1200,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
    ]);
    Payment::create([
        'booking_id' => $outsideBooking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 9000,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now()->subDay(),
    ]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.index', [
            'section' => 'reports',
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]))
        ->assertSee(now()->format('M j, Y').' to '.now()->format('M j, Y'))
        ->assertSee('₱1,200.00')
        ->assertSee('Cash')
        ->assertDontSee('₱9,000.00')
        ->assertDontSee('GCash')
        ->assertSee('value="'.$fromDate.'"', false)
        ->assertSee('value="'.$toDate.'"', false)
        ->assertSee('value="Custom" selected', false);
});

test('reports and booking history include legacy checked-out records as completed without rewriting them', function () {
    ['owner' => $owner, 'house' => $house, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $completedBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->subDays(3),
        'check_out' => today()->subDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::COMPLETED,
        'checked_out_at' => today()->subDay()->setTime(12, 0),
    ]);
    $legacyBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->subDays(5),
        'check_out' => today()->subDays(3),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CHECKED_OUT,
        'checked_out_at' => today()->subDays(3)->setTime(12, 0),
    ]);
    $report = app(BookingReportService::class)->generate(collect([$house->house_id]), [
        'period' => 'monthly',
        'year' => (int) now()->year,
        'quarter' => 'All',
        'house_id' => null,
        'from_date' => today()->subDays(10)->toDateString(),
        'to_date' => today()->toDateString(),
    ]);

    expect($report['totalBookings'])->toBe(2)
        ->and($report['completedBookings'])->toBe(2)
        ->and($legacyBooking->fresh()->status)->toBe(BookingStatus::CHECKED_OUT)
        ->and(BookingStatus::canonical($legacyBooking->fresh()->status))->toBe(BookingStatus::COMPLETED)
        ->and(BookingStatus::isTerminal($legacyBooking->fresh()->status))->toBeTrue();

    $this->actingAs($owner)
        ->get(route('owner.bookings.index'))
        ->assertOk()
        ->assertSee('Completed')
        ->assertDontSee('Checked-out');
    $this->actingAs($guest)
        ->get(route('guest.bookings.show', $legacyBooking->booking_id))
        ->assertOk()
        ->assertSee('Completed')
        ->assertDontSee('Checked-out');
});

test('owner report counts canonical booking statuses and excludes rejected and cancelled bookings', function () {
    ['house' => $house, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $statuses = [
        BookingStatus::PENDING,
        BookingStatus::ACCEPTED,
        BookingStatus::CONFIRMED,
        BookingStatus::CHECKED_IN,
        BookingStatus::COMPLETED,
        BookingStatus::CHECKED_OUT,
        BookingStatus::CANCELLED,
        BookingStatus::REJECTED,
    ];

    foreach ($statuses as $status) {
        Booking::create([
            'user_id' => $guest->user_id,
            'unit_id' => $unit->unit_id,
            'check_in' => today(),
            'check_out' => today()->addDay(),
            'adult_count' => 1,
            'child_count' => 0,
            'total_price' => 3600,
            'status' => $status,
        ]);
    }

    $report = app(BookingReportService::class)->generate(collect([$house->house_id]), [
        'period' => 'monthly',
        'year' => (int) now()->year,
        'quarter' => 'All',
        'house_id' => null,
        'from_date' => today()->toDateString(),
        'to_date' => today()->toDateString(),
    ]);

    expect($report['totalBookings'])->toBe(6)
        ->and($report['completedBookings'])->toBe(2)
        ->and($report['pendingBookings'])->toBe(1)
        ->and($report['periodBookings'])->toHaveCount(6);
});

test('owner reports require a valid complete date range', function () {
    ['owner' => $owner] = makeGuestBookingScenario();
    $today = now()->toDateString();
    $yesterday = now()->subDay()->toDateString();

    $this->actingAs($owner)
        ->get(route('owner.bookings.index', [
            'section' => 'reports',
            'from_date' => $today,
            'to_date' => $yesterday,
        ]))
        ->assertRedirect(route('owner.bookings.index', ['section' => 'reports']))
        ->assertSessionHasErrors('to_date');

    $this->get(route('owner.bookings.index', [
        'section' => 'reports',
        'from_date' => $today,
    ]))
        ->assertRedirect(route('owner.bookings.index', ['section' => 'reports']))
        ->assertSessionHasErrors('to_date');
});

test('owner report filters revenue by quarter and selected house and subtracts processed refunds', function () {
    ['owner' => $owner, 'house' => $house, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $year = now()->year;
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->setYear($year)->setMonth(2)->setDay(15),
        'check_out' => now()->setYear($year)->setMonth(2)->setDay(17),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    $payment = Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'Cash',
        'amount' => 1000,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now()->setYear($year)->setMonth(2)->setDay(10),
    ]);
    Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 2400,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now()->setYear($year)->setMonth(4)->setDay(10),
    ]);
    Refund::create([
        'booking_id' => $booking->booking_id,
        'payment_id' => $payment->payment_id,
        'amount' => 200,
        'reason' => 'Guest refund',
        'refund_status' => 'Refunded',
        'processed_at' => now()->setYear($year)->setMonth(2)->setDay(11),
    ]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.index', [
            'section' => 'reports',
            'period' => 'annually',
            'year' => $year,
            'quarter' => 'Q1',
            'house_id' => $house->house_id,
            'report_tab' => 'breakdown',
            'breakdown_section' => 'quarter',
        ]))
        ->assertOk()
        ->assertSee('Quarterly Revenue')
        ->assertSee('class="analytics-breakdown-panel active" data-breakdown-panel="quarter"', false)
        ->assertDontSee('Main Revenue')
        ->assertSee('Q1')
        ->assertSee('₱1,000.00')
        ->assertSee('₱800.00')
        ->assertSee('Refunds')
        ->assertSee('-₱200.00')
        ->assertDontSee('₱2,400.00')
        ->assertSee('value="Q1" selected', false);
});

test('owner report downloads include the shared filters and filtered tables', function () {
    ['owner' => $owner, 'house' => $house, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $year = now()->year;
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now(),
        'check_out' => now()->addDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'status' => BookingStatus::CONFIRMED,
    ]);
    $payment = Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'Cash',
        'amount' => 1250,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
    ]);
    Refund::create([
        'booking_id' => $booking->booking_id,
        'payment_id' => $payment->payment_id,
        'amount' => 250,
        'reason' => 'Test refund',
        'refund_status' => 'Refunded',
        'processed_at' => now(),
    ]);
    $filters = [
        'period' => 'annually',
        'year' => $year,
        'quarter' => 'All',
        'house_id' => $house->house_id,
        'from_date' => $year.'-01-01',
        'to_date' => $year.'-12-31',
    ];

    $excelResponse = $this->actingAs($owner)
        ->get(route('owner.reports.excel', $filters))
        ->assertOk()
        ->assertHeader('content-disposition', 'attachment; filename=booking-report-'.$year.'.xlsx');

    $report = app(BookingReportService::class)->generate(collect([$house->house_id]), [
        'period' => $filters['period'],
        'year' => $filters['year'],
        'quarter' => $filters['quarter'],
        'house_id' => $house->house_id,
        'from_date' => $filters['from_date'],
        'to_date' => $filters['to_date'],
    ]);
    $excelContent = Maatwebsite\Excel\Facades\Excel::raw(
        new BookingReportExport($report),
        Excel::XLSX
    );
    $temporaryFile = tmpfile();
    fwrite($temporaryFile, $excelContent);
    $temporaryPath = stream_get_meta_data($temporaryFile)['uri'];
    $workbook = IOFactory::createReader('Xlsx')->load($temporaryPath);
    $archive = new ZipArchive;
    $archive->open($temporaryPath);
    $revenueSheetXml = $archive->getFromName('xl/worksheets/sheet1.xml');
    $archive->close();
    $revenueSheet = $workbook->getSheetByName('Revenue');
    $houseSheet = $workbook->getSheetByName('Revenue per House');
    $unitSheet = $workbook->getSheetByName('Revenue per Unit');
    $quarterSheet = $workbook->getSheetByName('Revenue per Quarter');
    expect($workbook->getSheetNames())->toBe([
        'Revenue',
        'Revenue per House',
        'Revenue per Unit',
        'Revenue per Quarter',
        'Booking Activity',
    ])
        ->and($revenueSheet->getCell('A1')->getValue())->toContain('MCTBS Booking Report')
        ->and($revenueSheet->getCell('A1')->getValue())->toContain($house->name)
        ->and($revenueSheet->getCell('A2')->getValue())->toContain('Generated:')
        ->and($revenueSheet->getCell('A3')->getValue())->toBe('Refunds: -₱250.00')
        ->and($revenueSheet->getCell('D4')->getValue())->toBe('Net Revenue (after refunds)')
        ->and($revenueSheet->getCell('C5')->getStyle()->getNumberFormat()->getFormatCode())->toBe('"₱"#,##0.00;[Red]-"₱"#,##0.00')
        ->and($revenueSheet->getFreezePane())->toBe('A5')
        ->and((float) $revenueSheet->getCell('C5')->getValue())->toBe(-250.0)
        ->and($revenueSheet->getCell('C5')->getStyle()->getFont()->getColor()->getRGB())->toBe('C00000')
        ->and($revenueSheet->getStyle('A4')->getFill()->getStartColor()->getRGB())->toBe('6B3A14')
        ->and($revenueSheet->getStyle('A4')->getFont()->getColor()->getRGB())->toBe('FFFFFF')
        ->and($revenueSheet->getCell('A6')->getValue())->toBe('Total')
        ->and((float) $revenueSheet->getCell('D6')->getValue())->toBe(1000.0)
        ->and($revenueSheet->getStyle('A6')->getFont()->getBold())->toBeTrue()
        ->and($revenueSheet->getColumnDimension('A')->getWidth())->toBe(28.0)
        ->and($revenueSheetXml)->toMatch('/<c r="C5"[^>]*><v>-250\.0<\/v><\/c>/')
        ->and($houseSheet->getCell('D4')->getValue())->toBe('Net Revenue (after refunds)')
        ->and($unitSheet->getCell('E4')->getValue())->toBe('Net Revenue (after refunds)')
        ->and($unitSheet->getCell('D5')->getStyle()->getFont()->getColor()->getRGB())->toBe('C00000')
        ->and($quarterSheet->getCell('A5')->getValue())->toBe('Q1')
        ->and($quarterSheet->getCell('C5')->getStyle()->getNumberFormat()->getFormatCode())->toBe('"₱"#,##0.00;[Red]-"₱"#,##0.00');
    fclose($temporaryFile);

    $pdfResponse = $this->get(route('owner.reports.pdf', $filters));
    $pdfResponse->assertOk()
        ->assertHeader('content-disposition', 'attachment; filename=booking-report-'.$year.'.pdf');

    expect($pdfResponse->getContent())->toStartWith('%PDF');
});

test('owner cannot filter reports to a house they do not manage', function () {
    ['owner' => $owner] = makeGuestBookingScenario();
    $otherOwner = User::create([
        'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Other',
        'last_name' => 'Owner',
        'email' => 'other-report-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $otherHouse = TransientHouse::create([
        'owner_id' => $otherOwner->user_id,
        'name' => 'Other House',
        'address' => '20 Test Road',
        'city' => 'Baguio',
        'contact_number' => '5552001',
        'is_active' => true,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.index', [
            'section' => 'reports',
            'house_id' => $otherHouse->house_id,
        ]))
        ->assertNotFound();
});

test('opening a booking notification redirects to its booking and marks it read', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(5),
        'check_out' => now()->addDays(7),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::PENDING,
    ]);
    $notification = Notification::create([
        'user_id' => $owner->user_id,
        'booking_id' => $booking->booking_id,
        'type' => 'booking_requested',
        'title' => 'New booking request',
        'message' => 'A guest requested these dates.',
        'is_read' => false,
        'created_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('notifications.open', $notification->notification_id))
        ->assertRedirect(route('owner.bookings.show', $booking->booking_id));

    expect($notification->fresh()->is_read)->toBeTrue()
        ->and($notification->fresh()->read_at)->not->toBeNull();
});

test('notification bell refreshes new notifications and polls while visible', function () {
    ['guest' => $guest] = makeGuestBookingScenario();
    $this->actingAs($guest);

    $component = Livewire::test(NotificationBell::class)
        ->assertSee('wire:poll.15s.visible', false)
        ->assertSee('You have no notifications.');

    Notification::create([
        'user_id' => $guest->user_id,
        'booking_id' => null,
        'type' => 'account_update',
        'title' => 'New account update',
        'message' => 'Your account details were updated.',
        'is_read' => false,
        'created_at' => now(),
    ]);

    $component->call('$refresh')->assertSee('New account update');
});

test('routine success notices are non-modal and the sitewide back control is absent', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(5),
        'check_out' => now()->addDays(7),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::PENDING,
    ]);

    $this->actingAs($guest)
        ->withSession(['success' => 'A routine update was saved.'])
        ->get(route('guest.bookings.show', $booking->booking_id))
        ->assertOk()
        ->assertSee('system-message-toast', false)
        ->assertSee('A routine update was saved.')
        ->assertDontSee('data-auto-open-dialog', false)
        ->assertDontSee('global-back-button', false);
});

test('staff calendar cannot select a unit outside the assigned property', function () {
    ['owner' => $owner, 'house' => $house, 'unit' => $assignedUnit] = makeGuestBookingScenario();
    $otherOwner = User::create([
        'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Other',
        'last_name' => 'Owner',
        'email' => 'other-calendar-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $otherHouse = TransientHouse::create([
        'owner_id' => $otherOwner->user_id,
        'name' => 'Other House',
        'address' => '20 Other Road',
        'city' => 'Baguio',
        'contact_number' => '5552001',
        'is_active' => true,
    ]);
    $otherUnit = Unit::create([
        'house_id' => $otherHouse->house_id,
        'unit_type' => 'Other Room',
        'capacity' => 2,
        'price' => 1200,
        'status' => 'Available',
    ]);
    $staff = User::create([
        'role_id' => Role::where('name', 'Staff')->firstOrFail()->role_id,
        'first_name' => 'Assigned',
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
        'token' => 'calendar-staff-invitation',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($staff)
        ->get(route('staff.bookings.index', ['section' => 'calendar', 'unit_id' => $assignedUnit->unit_id]))
        ->assertOk()
        ->assertSee('Family Room')
        ->assertDontSee('calendar-summary')
        ->assertDontSee('Available dates')
        ->assertDontSee('Booked dates')
        ->assertDontSee('Unavailable dates')
        ->assertDontSee('Units in your access')
        ->assertDontSee('Manage Bookings');

    $this->get(route('staff.bookings.index', ['section' => 'calendar', 'unit_id' => $otherUnit->unit_id]))
        ->assertNotFound();
});

test('owner can reject a pending booking with a reason', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(5),
        'check_out' => now()->addDays(7),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::PENDING,
    ]);
    UnitCalendar::create([
        'unit_id' => $unit->unit_id,
        'date' => now()->addDays(5)->toDateString(),
        'status' => 'Booked',
        'note' => 'Confirmed booking #'.$booking->booking_id,
    ]);

    $this->actingAs($owner)
        ->from(route('owner.bookings.show', $booking->booking_id))
        ->post(route('owner.bookings.reject', $booking->booking_id), [])
        ->assertRedirect(route('owner.bookings.show', $booking->booking_id))
        ->assertSessionHasErrors('reason');
    expect($booking->fresh()->status)->toBe(BookingStatus::PENDING);

    $this->actingAs($owner)
        ->post(route('owner.bookings.reject', $booking->booking_id), ['reason' => 'Dates are unavailable'])
        ->assertRedirect(route('owner.bookings.show', $booking->booking_id));

    expect($booking->fresh()->status)->toBe(BookingStatus::REJECTED)
        ->and($booking->fresh()->rejection_reason)->toBe('Dates are unavailable')
        ->and(UnitCalendar::where('unit_id', $unit->unit_id)->where('note', 'Confirmed booking #'.$booking->booking_id)->exists())->toBeFalse()
        ->and(app(BookingService::class)->hasBlockedDate(
            $unit,
            now()->addDays(5)->toDateString(),
            now()->addDays(7)->toDateString()
        ))->toBeFalse();
    $this->assertDatabaseHas('notifications', [
        'user_id' => $guest->user_id,
        'booking_id' => $booking->booking_id,
        'type' => 'booking_rejected',
        'message' => 'Your booking request was not accepted. Reason: Dates are unavailable',
    ]);
    $this->actingAs($guest)
        ->get(route('guest.bookings.show', $booking->booking_id))
        ->assertSeeText('Rejection reason: Dates are unavailable');
    $this->actingAs($owner)
        ->get(route('owner.bookings.show', $booking->booking_id))
        ->assertSeeText('Rejection reason: Dates are unavailable');
    $this->assertDatabaseHas('notifications', [
        'user_id' => $owner->user_id,
        'booking_id' => $booking->booking_id,
        'type' => 'booking_rejected',
        'message' => "Booking #{$booking->booking_id} was rejected. Reason: Dates are unavailable",
    ]);
    $this->actingAs($owner)
        ->get(route('owner.bookings.index'))
        ->assertSee('data-status-tooltip="Rejection reason: Dates are unavailable"', false)
        ->assertSee('booking-status-indicator__dot', false)
        ->assertSee('Rejected');
    $this->actingAs($guest)
        ->get(route('guest.dashboard'))
        ->assertSee('data-status-tooltip="Rejection reason: Dates are unavailable"', false)
        ->assertSee('booking-status-indicator__dot', false)
        ->assertDontSeeText('Rejection reason: Dates are unavailable');
});

test('owner can cancel a confirmed booking only with a reason', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(5),
        'check_out' => now()->addDays(7),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.index'))
        ->assertSee('Accepted')
        ->assertDontSee(route('owner.bookings.cancel', $booking->booking_id));

    $this->actingAs($owner)
        ->get(route('owner.bookings.show', $booking->booking_id))
        ->assertSee('Cancel Booking')
        ->assertSee('id="cancellation-reason"', false)
        ->assertSee('name="reason" rows="3" maxlength="500" required', false);

    $this->from(route('owner.bookings.show', $booking->booking_id))
        ->post(route('owner.bookings.cancel', $booking->booking_id), [])
        ->assertRedirect(route('owner.bookings.show', $booking->booking_id))
        ->assertSessionHasErrors('reason');
    expect($booking->fresh()->status)->toBe(BookingStatus::CONFIRMED);

    $this->post(route('owner.bookings.cancel', $booking->booking_id), [
        'reason' => 'Property maintenance',
    ])->assertRedirect(route('owner.bookings.show', $booking->booking_id));

    expect($booking->fresh()->status)->toBe(BookingStatus::CANCELLED)
        ->and($booking->fresh()->cancellation_reason)->toBe('Property maintenance');
    $this->get(route('owner.bookings.show', $booking->booking_id))
        ->assertSee('data-status-tooltip="Cancellation reason: Property maintenance"', false);
});

test('migration removes saved calendar holds for previously cancelled and rejected bookings', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $cancelled = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(5),
        'check_out' => now()->addDays(7),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CANCELLED,
    ]);
    $rejected = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(8),
        'check_out' => now()->addDays(10),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::REJECTED,
    ]);
    $active = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(11),
        'check_out' => now()->addDays(13),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);

    foreach ([$cancelled, $rejected, $active] as $booking) {
        UnitCalendar::create([
            'unit_id' => $unit->unit_id,
            'date' => $booking->check_in->toDateString(),
            'status' => 'Booked',
            'note' => 'Confirmed booking #'.$booking->booking_id,
        ]);
    }

    $migration = require database_path('migrations/2026_10_24_055214_remove_stale_cancelled_booking_calendar_holds.php');
    $migration->up();

    $this->assertDatabaseMissing('unit_availability', [
        'unit_id' => $unit->unit_id,
        'note' => 'Confirmed booking #'.$cancelled->booking_id,
    ]);
    $this->assertDatabaseMissing('unit_availability', [
        'unit_id' => $unit->unit_id,
        'note' => 'Confirmed booking #'.$rejected->booking_id,
    ]);
    $this->assertDatabaseHas('unit_availability', [
        'unit_id' => $unit->unit_id,
        'note' => 'Confirmed booking #'.$active->booking_id,
    ]);
});

test('rejecting the first overlapping request moves the next request forward', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $bookingService = app(BookingService::class);
    $dates = [
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(7)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ];
    $firstRequest = $bookingService->createBooking($guest, $unit, $dates);
    $nextRequest = $bookingService->createBooking($guest, $unit, $dates);
    $workflow = app(BookingWorkflowService::class);

    $workflow->reject($owner, $firstRequest->booking_id, 'Dates are unavailable');

    expect($nextRequest->fresh()->status)->toBe(BookingStatus::PENDING);
    $this->assertDatabaseHas('notifications', [
        'user_id' => $guest->user_id,
        'booking_id' => $nextRequest->booking_id,
        'type' => 'booking_next_in_queue',
    ]);
    $workflow->accept($owner, $nextRequest->booking_id);
    expect($nextRequest->fresh()->status)->toBe(BookingStatus::ACCEPTED);
});

test('cancelling an accepted booking releases its hold for the next pending request', function () {
    $this->travelTo('2026-11-10 10:00:00');
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $bookingService = app(BookingService::class);
    $dates = [
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(7)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ];
    $heldBooking = $bookingService->createBooking($guest, $unit, $dates);
    $nextRequest = $bookingService->createBooking($guest, $unit, $dates);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $heldBooking->booking_id);

    $workflow->cancelAccepted($owner, $heldBooking->booking_id, 'Unit maintenance');

    expect($heldBooking->fresh()->status)->toBe(BookingStatus::CANCELLED)
        ->and($nextRequest->fresh()->status)->toBe(BookingStatus::PENDING);
    $this->assertDatabaseMissing('unit_availability', [
        'unit_id' => $unit->unit_id,
        'note' => 'Confirmed booking #'.$heldBooking->booking_id,
    ]);
    $this->assertDatabaseHas('notifications', [
        'user_id' => $guest->user_id,
        'booking_id' => $nextRequest->booking_id,
        'type' => 'booking_next_in_queue',
    ]);

    $this->travelTo('2026-11-11 14:30:00');
    $workflow->accept($owner, $nextRequest->booking_id);

    expect($nextRequest->fresh()->accepted_at->toDateTimeString())->toBe('2026-11-11 14:30:00')
        ->and($nextRequest->fresh()->payment_deadline->toDateTimeString())->toBe('2026-11-12 14:30:00');
});

test('guest can pay the accepted booking downpayment and has no Pay Now action afterward', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(3)->toDateString(),
        'check_out' => now()->addDays(5)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $booking->booking_id);

    expect($booking->fresh()->status)->toBe(BookingStatus::ACCEPTED)
        ->and($booking->fresh()->payment_deadline)
        ->toEqual($booking->fresh()->accepted_at->copy()->addHours(config('booking.payment_deadline_hours')));

    $this->actingAs($owner)
        ->get(route('owner.bookings.show', $booking->booking_id))
        ->assertOk()
        ->assertSee('Downpayment due');

    $this->actingAs($guest)
        ->get(route('guest.bookings.payment', $booking->booking_id))
        ->assertOk()
        ->assertSee('Submit payment details')
        ->assertSee('₱1,800.00')
        ->assertSee('min="1800.00"', false)
        ->assertSee('max="3600.00"', false);

    $this->get(route('guest.bookings.show', $booking->booking_id))
        ->assertOk()
        ->assertSee('Pay Now')
        ->assertSee('guest-payment-action', false);

    $this->actingAs($guest)
        ->post(route('guest.bookings.payments.store', $booking->booking_id), [
            'payment_method' => 'GCash',
            'amount' => 1800,
            'reference_number' => 'GCASH-DOWNPAYMENT-001',
            'payment_receipt' => UploadedFile::fake()->createWithContent('proof.pdf', "%PDF-1.4\nProof"),
        ])
        ->assertRedirect(route('guest.bookings.show', $booking->booking_id))
        ->assertSessionHas('success', 'Your payment was submitted and is waiting for review.');

    $payment = $booking->payments()->firstOrFail();
    expect($payment->payment_status)->toBe(PaymentStatus::UNDER_REVIEW)
        ->and($booking->fresh()->status)->toBe(BookingStatus::ACCEPTED);
    $this->assertDatabaseHas('payments', [
        'booking_id' => $booking->booking_id,
        'amount' => '1800.00',
        'payment_status' => PaymentStatus::UNDER_REVIEW,
    ]);

    $this->get(route('guest.bookings.show', $booking->booking_id))
        ->assertOk()
        ->assertSee('Payment under review.')
        ->assertDontSee('Pay Now');

    $this->actingAs($owner)
        ->get(route('owner.bookings.show', $booking->booking_id))
        ->assertSee('View Receipt')
        ->assertSee('payment-proof-dialog', false)
        ->assertSee('data-proof-reference="GCASH-DOWNPAYMENT-001"', false)
        ->assertSee('data-proof-amount="₱1,800.00"', false)
        ->assertSee('data-payment-reason-panel  hidden', false);

    $this->actingAs($owner)
        ->post(route('owner.payments.review', $payment->payment_id), [
            'status' => PaymentStatus::VERIFIED,
            'notes' => '',
        ])
        ->assertSessionHas('success');

    expect($payment->fresh()->payment_status)->toBe(PaymentStatus::VERIFIED)
        ->and($payment->fresh()->notes)->toBeNull()
        ->and($booking->fresh()->status)->toBe(BookingStatus::CONFIRMED)
        ->and($booking->fresh()->payment_deadline)->toBeNull()
        ->and($booking->fresh()->booking_reference)->toBe('MCTBS-'.str_pad((string) $booking->booking_id, 6, '0', STR_PAD_LEFT));
    $this->actingAs($guest)
        ->get(route('guest.bookings.show', $booking->booking_id))
        ->assertOk()
        ->assertDontSee('Pay Now')
        ->assertSee('Balance due at checkout')
        ->assertSee('₱1,800.00');

    $this->get(route('guest.bookings.payment', $booking->booking_id))
        ->assertRedirect(route('guest.bookings.show', $booking->booking_id))
        ->assertSessionHas('info', 'A downpayment can only be submitted while an accepted booking is awaiting payment.');
});

test('acceptance records a 24 hour payment deadline from its acceptance timestamp', function () {
    $this->travelTo('2026-11-10 10:00:00');
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(7)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);

    $acceptedBooking = app(BookingWorkflowService::class)->accept($owner, $booking->booking_id)->fresh();

    expect(config('booking.payment_deadline_hours'))->toBe(24)
        ->and($acceptedBooking->accepted_at->toDateTimeString())->toBe('2026-11-10 10:00:00')
        ->and($acceptedBooking->payment_deadline->toDateTimeString())->toBe('2026-11-11 10:00:00');
});

test('acceptance uses the configured payment deadline duration', function () {
    $this->travelTo('2026-11-10 10:00:00');
    config(['booking.payment_deadline_hours' => 36]);
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(7)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);

    $acceptedBooking = app(BookingWorkflowService::class)->accept($owner, $booking->booking_id)->fresh();

    expect($acceptedBooking->payment_deadline->toDateTimeString())->toBe('2026-11-11 22:00:00');
});

test('an accepted booking without a timely payment becomes overdue without releasing its hold', function () {
    $this->travelTo('2026-11-10 10:00:00');
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $data = [
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(7)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ];
    $bookingService = app(BookingService::class);
    $booking = $bookingService->createBooking($guest, $unit, $data);
    $waitingBooking = $bookingService->createBooking($guest, $unit, $data);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $booking->booking_id);
    $this->travelTo('2026-11-11 10:00:01');

    expect($workflow->isPaymentOverdue($booking->fresh()))->toBeTrue()
        ->and($booking->fresh()->status)->toBe(BookingStatus::ACCEPTED)
        ->and($waitingBooking->fresh()->status)->toBe(BookingStatus::PENDING);
    expect($unit->availability()
        ->whereDate('date', '2026-11-15')
        ->where('status', 'Booked')
        ->value('note'))->toBe('Confirmed booking #'.$booking->booking_id);
    $this->assertDatabaseCount('bookings', 2);
    $this->assertDatabaseMissing('notifications', [
        'booking_id' => $waitingBooking->booking_id,
        'type' => 'booking_next_in_queue',
    ]);
    $this->actingAs($owner)
        ->get(route('owner.bookings.show', $booking->booking_id))
        ->assertOk()
        ->assertSee('The payment deadline has passed without a qualifying downpayment.')
        ->assertSee('This booking remains on hold until Owner/Staff resolves it.');
    $this->get(route('owner.bookings.index'))
        ->assertOk()
        ->assertSee('Overdue since Nov 11, 2026 10:00 AM');
});

test('a timely payment below the required downpayment does not protect the booking hold', function () {
    $this->travelTo('2026-11-10 10:00:00');
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(7)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $booking->booking_id);
    Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 100,
        'payment_status' => PaymentStatus::UNDER_REVIEW,
        'payment_date' => now(),
    ]);
    $this->travelTo('2026-11-11 10:00:01');

    expect($workflow->isPaymentOverdue($booking->fresh()))->toBeTrue()
        ->and($booking->fresh()->status)->toBe(BookingStatus::ACCEPTED);
});

test('a payment submitted before acceptance does not protect the booking hold', function () {
    $this->travelTo('2026-11-10 09:59:00');
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(7)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);
    Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => $booking->downpaymentAmount(),
        'payment_status' => PaymentStatus::UNDER_REVIEW,
        'payment_date' => now(),
    ]);
    $this->travelTo('2026-11-10 10:00:00');
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $booking->booking_id);
    $this->travelTo('2026-11-11 10:00:01');

    expect($workflow->isPaymentOverdue($booking->fresh()))->toBeTrue();
});

test('a timely payment under review protects an accepted booking hold after the deadline', function () {
    $this->travelTo('2026-11-10 10:00:00');
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $data = [
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(7)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ];
    $bookingService = app(BookingService::class);
    $booking = $bookingService->createBooking($guest, $unit, $data);
    $waitingBooking = $bookingService->createBooking($guest, $unit, $data);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $booking->booking_id);
    $this->travelTo('2026-11-11 10:00:00');
    $payment = $workflow->submitPayment($guest, $booking->booking_id, [
        'payment_method' => 'GCash',
        'amount' => $booking->downpaymentAmount(),
    ]);
    $this->travelTo('2026-11-11 10:01:00');

    expect($payment->fresh()->payment_status)->toBe(PaymentStatus::UNDER_REVIEW)
        ->and($payment->fresh()->payment_date->toDateTimeString())->toBe('2026-11-11 10:00:00')
        ->and($workflow->isPaymentOverdue($booking->fresh()))->toBeFalse()
        ->and($booking->fresh()->status)->toBe(BookingStatus::ACCEPTED)
        ->and($waitingBooking->fresh()->status)->toBe(BookingStatus::PENDING);
    expect((float) $booking->payments()
        ->where('payment_status', PaymentStatus::VERIFIED)
        ->sum('amount'))->toBe(0.0);
    $this->assertDatabaseHas('unit_availability', [
        'unit_id' => $unit->unit_id,
        'note' => 'Confirmed booking #'.$booking->booking_id,
        'status' => 'Booked',
    ]);
    $this->assertDatabaseMissing('notifications', [
        'booking_id' => $waitingBooking->booking_id,
        'type' => 'booking_next_in_queue',
    ]);
    $this->actingAs($owner)
        ->get(route('owner.dashboard'))
        ->assertOk()
        ->assertSee('Payments to Verify')
        ->assertSee('GCash')
        ->assertSee('Verify');
});

test('a timely payment rejected after the deadline no longer protects the booking hold', function () {
    $this->travelTo('2026-11-10 10:00:00');
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $data = [
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(7)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ];
    $bookingService = app(BookingService::class);
    $booking = $bookingService->createBooking($guest, $unit, $data);
    $waitingBooking = $bookingService->createBooking($guest, $unit, $data);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $booking->booking_id);
    $this->travelTo('2026-11-11 09:59:00');
    $payment = $workflow->submitPayment($guest, $booking->booking_id, [
        'payment_method' => 'GCash',
        'amount' => $booking->downpaymentAmount(),
    ]);
    $this->travelTo('2026-11-11 10:01:00');
    $workflow->reviewPayment($owner, $payment->payment_id, PaymentStatus::INCORRECT, 'The reference is invalid.');

    expect($payment->fresh()->payment_status)->toBe(PaymentStatus::INCORRECT)
        ->and($workflow->isPaymentOverdue($booking->fresh()))->toBeTrue()
        ->and($booking->fresh()->status)->toBe(BookingStatus::ACCEPTED)
        ->and($waitingBooking->fresh()->status)->toBe(BookingStatus::PENDING);
    $this->assertDatabaseHas('unit_availability', [
        'unit_id' => $unit->unit_id,
        'note' => 'Confirmed booking #'.$booking->booking_id,
        'status' => 'Booked',
    ]);
    $this->assertDatabaseMissing('notifications', [
        'booking_id' => $waitingBooking->booking_id,
        'type' => 'booking_next_in_queue',
    ]);
});

test('a separate timely payment under review remains valid when another timely payment is rejected', function () {
    $this->travelTo('2026-11-10 10:00:00');
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(7)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $booking->booking_id);
    $this->travelTo('2026-11-11 09:59:00');
    $rejectedPayment = $workflow->submitPayment($guest, $booking->booking_id, [
        'payment_method' => 'GCash',
        'amount' => $booking->downpaymentAmount(),
    ]);
    $validPayment = Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'Bank Transfer',
        'amount' => $booking->downpaymentAmount(),
        'payment_status' => PaymentStatus::UNDER_REVIEW,
        'payment_date' => now(),
    ]);
    $this->travelTo('2026-11-11 10:01:00');
    $workflow->reviewPayment($owner, $rejectedPayment->payment_id, PaymentStatus::INCORRECT, 'The reference is invalid.');

    expect($workflow->isPaymentOverdue($booking->fresh()))->toBeFalse();

    $workflow->reviewPayment($owner, $validPayment->payment_id, PaymentStatus::INCORRECT, 'The proof is invalid.');

    expect($workflow->isPaymentOverdue($booking->fresh()))->toBeTrue();
});

test('a late payment does not erase the overdue condition before verification', function () {
    $this->travelTo('2026-11-10 10:00:00');
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(7)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $booking->booking_id);
    $this->travelTo('2026-11-11 10:01:00');
    $payment = $workflow->submitPayment($guest, $booking->booking_id, [
        'payment_method' => 'GCash',
        'amount' => $booking->downpaymentAmount(),
    ]);

    expect($payment->payment_date->gt($booking->fresh()->payment_deadline))->toBeTrue()
        ->and($workflow->isPaymentOverdue($booking->fresh()))->toBeTrue()
        ->and($booking->fresh()->status)->toBe(BookingStatus::ACCEPTED);

    $workflow->reviewPayment($owner, $payment->payment_id, PaymentStatus::VERIFIED, null);

    expect($payment->fresh()->payment_status)->toBe(PaymentStatus::VERIFIED)
        ->and($booking->fresh()->status)->toBe(BookingStatus::CONFIRMED)
        ->and($workflow->isPaymentOverdue($booking->fresh()))->toBeFalse();
});

test('payment verification preserves the original payment submission timestamp', function () {
    $this->travelTo('2026-11-10 10:00:00');
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(7)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $booking->booking_id);
    $this->travelTo('2026-11-10 20:00:00');
    $payment = $workflow->submitPayment($guest, $booking->booking_id, [
        'payment_method' => 'GCash',
        'amount' => $booking->downpaymentAmount(),
    ]);
    $submittedAt = $payment->payment_date->toDateTimeString();
    $this->travelTo('2026-11-10 21:00:00');

    $workflow->reviewPayment($owner, $payment->payment_id, PaymentStatus::VERIFIED, null);

    expect($payment->fresh()->payment_status)->toBe(PaymentStatus::VERIFIED)
        ->and($payment->fresh()->payment_date->toDateTimeString())->toBe($submittedAt)
        ->and($booking->fresh()->status)->toBe(BookingStatus::CONFIRMED);
});

test('queue notification skips a blocked earlier request and notifies the next eligible request', function () {
    $this->travelTo('2026-11-10 10:00:00');
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $bookingService = app(BookingService::class);
    $baseDate = now()->addDays(6);
    $heldBooking = $bookingService->createBooking($guest, $unit, [
        'check_in' => $baseDate->toDateString(),
        'check_out' => $baseDate->copy()->addDays(2)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Held Guest']],
    ]);
    $blockedRequest = $bookingService->createBooking($guest, $unit, [
        'check_in' => $baseDate->copy()->subDay()->toDateString(),
        'check_out' => $baseDate->copy()->addDay()->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Blocked Request Guest']],
    ]);
    $eligibleRequest = $bookingService->createBooking($guest, $unit, [
        'check_in' => $baseDate->copy()->addDays(1)->toDateString(),
        'check_out' => $baseDate->copy()->addDays(3)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Eligible Request Guest']],
    ]);
    UnitCalendar::create([
        'unit_id' => $unit->unit_id,
        'date' => $baseDate->copy()->subDay()->toDateString(),
        'status' => 'Unavailable',
        'note' => 'Maintenance',
    ]);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $heldBooking->booking_id);

    $workflow->cancelAccepted($owner, $heldBooking->booking_id, 'Released by Owner/Staff.');

    expect($blockedRequest->fresh()->status)->toBe(BookingStatus::PENDING)
        ->and($eligibleRequest->fresh()->status)->toBe(BookingStatus::PENDING);
    $this->assertDatabaseMissing('notifications', [
        'booking_id' => $blockedRequest->booking_id,
        'type' => 'booking_next_in_queue',
    ]);
    $this->assertDatabaseHas('notifications', [
        'booking_id' => $eligibleRequest->booking_id,
        'type' => 'booking_next_in_queue',
    ]);
});

test('queue notification is withheld when the only waiting request has blocked dates', function () {
    $this->travelTo('2026-11-10 10:00:00');
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $bookingService = app(BookingService::class);
    $baseDate = now()->addDays(6);
    $heldBooking = $bookingService->createBooking($guest, $unit, [
        'check_in' => $baseDate->toDateString(),
        'check_out' => $baseDate->copy()->addDays(2)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Held Guest']],
    ]);
    $waitingBooking = $bookingService->createBooking($guest, $unit, [
        'check_in' => $baseDate->copy()->subDay()->toDateString(),
        'check_out' => $baseDate->copy()->addDay()->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Waiting Guest']],
    ]);
    UnitCalendar::create([
        'unit_id' => $unit->unit_id,
        'date' => $baseDate->copy()->subDay()->toDateString(),
        'status' => 'Unavailable',
        'note' => 'Maintenance',
    ]);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $heldBooking->booking_id);

    $workflow->cancelAccepted($owner, $heldBooking->booking_id, 'Released by Owner/Staff.');

    expect($waitingBooking->fresh()->status)->toBe(BookingStatus::PENDING);
    $this->assertDatabaseMissing('notifications', [
        'booking_id' => $waitingBooking->booking_id,
        'type' => 'booking_next_in_queue',
    ]);
});

test('queue notification is withheld when the waiting request checkout date is unavailable', function () {
    $this->travelTo('2026-11-10 10:00:00');
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $bookingService = app(BookingService::class);
    $checkIn = now()->addDays(6);
    $heldBooking = $bookingService->createBooking($guest, $unit, [
        'check_in' => $checkIn->toDateString(),
        'check_out' => $checkIn->copy()->addDays(2)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Held Guest']],
    ]);
    $waitingBooking = $bookingService->createBooking($guest, $unit, [
        'check_in' => $checkIn->copy()->addDay()->toDateString(),
        'check_out' => $checkIn->copy()->addDays(2)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Waiting Guest']],
    ]);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $heldBooking->booking_id);
    UnitCalendar::create([
        'unit_id' => $unit->unit_id,
        'date' => $waitingBooking->check_out->toDateString(),
        'status' => 'Unavailable',
        'note' => 'Maintenance',
    ]);

    $workflow->cancelAccepted($owner, $heldBooking->booking_id, 'Released by Owner/Staff.');

    expect($waitingBooking->fresh()->status)->toBe(BookingStatus::PENDING);
    $this->assertDatabaseHas('bookings', [
        'booking_id' => $waitingBooking->booking_id,
        'status' => BookingStatus::PENDING,
    ]);
    $this->assertDatabaseMissing('notifications', [
        'booking_id' => $waitingBooking->booking_id,
        'type' => 'booking_next_in_queue',
    ]);
});

test('queue notification is withheld when the requested unit is unavailable', function () {
    $this->travelTo('2026-11-10 10:00:00');
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $bookingService = app(BookingService::class);
    $data = [
        'check_in' => now()->addDays(6)->toDateString(),
        'check_out' => now()->addDays(8)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ];
    $heldBooking = $bookingService->createBooking($guest, $unit, $data);
    $waitingBooking = $bookingService->createBooking($guest, $unit, $data);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $heldBooking->booking_id);
    $unit->update(['status' => 'Unavailable']);

    $workflow->cancelAccepted($owner, $heldBooking->booking_id, 'Released by Owner/Staff.');

    expect($waitingBooking->fresh()->status)->toBe(BookingStatus::PENDING);
    $this->assertDatabaseMissing('notifications', [
        'booking_id' => $waitingBooking->booking_id,
        'type' => 'booking_next_in_queue',
    ]);
});

test('queue notification is withheld when another active booking conflicts with the waiting request', function () {
    $this->travelTo('2026-11-10 10:00:00');
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $bookingService = app(BookingService::class);
    $baseDate = now()->addDays(6);
    $heldBooking = $bookingService->createBooking($guest, $unit, [
        'check_in' => $baseDate->toDateString(),
        'check_out' => $baseDate->copy()->addDays(2)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Held Guest']],
    ]);
    $waitingBooking = $bookingService->createBooking($guest, $unit, [
        'check_in' => $baseDate->toDateString(),
        'check_out' => $baseDate->copy()->addDays(3)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Waiting Guest']],
    ]);
    Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $baseDate->copy()->addDays(2),
        'check_out' => $baseDate->copy()->addDays(4),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $heldBooking->booking_id);

    $workflow->cancelAccepted($owner, $heldBooking->booking_id, 'Released by Owner/Staff.');

    expect($waitingBooking->fresh()->status)->toBe(BookingStatus::PENDING);
    $this->assertDatabaseMissing('notifications', [
        'booking_id' => $waitingBooking->booking_id,
        'type' => 'booking_next_in_queue',
    ]);
});

test('guest booking rejects more counted guests than the unit capacity', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario(capacity: 2);

    $this->actingAs($guest);
    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('paxCount', 3)
        ->set('ackPolicy', true)
        ->call('submit')
        ->assertHasErrors(['paxCount']);

    $this->assertDatabaseCount('bookings', 0);
});

test('guest count inputs can be cleared without breaking the booking form', function () {
    ['unit' => $unit] = makeGuestBookingScenario();

    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('freeChildCount', '')
        ->assertSet('freeChildCount', '')
        ->set('paxCount', '')
        ->assertSet('paxCount', '')
        ->assertSee('booking-date-fields')
        ->assertSee('Standard: 4 guests | Max: 4 guests')
        ->assertSee('₱1,800.00 / night | Extra guest: ₱0.00 / night')
        ->assertSee('Add at least 1 paying guest.')
        ->assertSee('Senior Citizen and PWD discounts are deducted at checkout')
        ->assertSee('booking-form-layout');
});

test('guest booking records a senior citizen discount request for that guest share', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $checkIn = now()->addDays(3)->toDateString();
    $checkOut = now()->addDays(5)->toDateString();

    $this->actingAs($guest);
    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('checkIn', $checkIn)
        ->set('checkOut', $checkOut)
        ->set('paxCount', 2)
        ->set('seniorCount', 1)
        ->set('seniorIds.0', 'SC-12345')
        ->set('ackPolicy', true)
        ->call('submit')
        ->assertRedirect(route('guest.bookings.show', 1));

    $booking = Booking::firstOrFail();
    $bookingGuest = $booking->guests()->where('full_name', 'Senior Citizen 1')->firstOrFail();
    $this->assertDatabaseHas('booking_discounts', [
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $bookingGuest->booking_guest_id,
        'discount_type' => 'Senior Citizen',
        'id_reference' => 'SC-12345',
        'verification_status' => BookingStatus::PENDING,
        'eligible_amount' => '1800.00',
        'discount_rate' => '20.00',
        'discount_amount' => '360.00',
        'final_amount' => '1440.00',
        'refund_amount' => '0.00',
        'refund_status' => 'None',
    ]);
    expect($booking->total_price)->toBe('3600.00');
});

test('guest booking rejects dates overlapping reserved bookings', function (string $status) {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(3)->setTime(14, 0),
        'check_out' => now()->addDays(5)->setTime(12, 0),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => $status,
    ]);

    $this->actingAs($guest);
    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('checkIn', now()->addDays(3)->toDateString())
        ->set('checkOut', now()->addDays(5)->toDateString())
        ->set('ackPolicy', true)
        ->call('submit')
        ->assertHasErrors(['checkIn']);

    $this->assertDatabaseCount('bookings', 1);
})->with([
    'confirmed' => BookingStatus::CONFIRMED,
    'checked in' => BookingStatus::CHECKED_IN,
]);

test('overlapping pending requests are queued and only the earliest can be accepted', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $checkIn = now()->addDays(3)->toDateString();
    $checkOut = now()->addDays(5)->toDateString();
    $data = [
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ];
    $bookingService = app(BookingService::class);
    $first = $bookingService->createBooking($guest, $unit, $data);
    $second = $bookingService->createBooking($guest, $unit, $data);
    $workflow = app(BookingWorkflowService::class);

    expect(fn () => $workflow->accept($owner, $second->booking_id))
        ->toThrow(ValidationException::class);

    $workflow->accept($owner, $first->booking_id);

    expect($first->fresh()->status)->toBe(BookingStatus::ACCEPTED)
        ->and($second->fresh()->status)->toBe(BookingStatus::PENDING);
    $this->assertDatabaseMissing('notifications', [
        'user_id' => $guest->user_id,
        'booking_id' => $second->booking_id,
        'type' => 'booking_dates_unavailable',
    ]);

    $payment = $workflow->submitPayment($guest, $first->booking_id, [
        'payment_method' => 'GCash',
        'amount' => $first->downpaymentAmount(),
    ]);
    $workflow->reviewPayment($owner, $payment->payment_id, PaymentStatus::VERIFIED, null);

    expect($first->fresh()->status)->toBe(BookingStatus::CONFIRMED)
        ->and($second->fresh()->status)->toBe(BookingStatus::CANCELLED);
    $this->assertDatabaseHas('notifications', [
        'user_id' => $guest->user_id,
        'booking_id' => $second->booking_id,
        'type' => 'booking_dates_unavailable',
        'message' => 'Your booking was cancelled automatically. Reason: The requested dates were confirmed for an earlier booking. You may request another unit or different dates.',
    ]);
});

test('pending booking acceptance rechecks unit availability after request submission', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $checkIn = now()->addDays(5);
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => $checkIn->toDateString(),
        'check_out' => $checkIn->copy()->addDays(2)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);
    $unit->update(['status' => 'Unavailable']);

    expect(fn () => app(BookingWorkflowService::class)->accept($owner, $booking->booking_id))
        ->toThrow(ValidationException::class);

    expect($booking->fresh()->status)->toBe(BookingStatus::PENDING);
    $this->assertDatabaseMissing('unit_availability', [
        'unit_id' => $unit->unit_id,
        'note' => 'Confirmed booking #'.$booking->booking_id,
    ]);
});

test('only staff assigned to a house can access and accept its booking requests', function () {
    ['owner' => $owner, 'house' => $house, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $staffRoleId = Role::where('name', 'Staff')->firstOrFail()->role_id;
    $staff = User::create([
        'role_id' => $staffRoleId,
        'first_name' => 'Assigned',
        'last_name' => 'Staff',
        'email' => 'assigned-staff@example.com',
        'password' => 'StaffPass123!',
        'is_active' => true,
    ]);
    $unassignedStaff = User::create([
        'role_id' => $staffRoleId,
        'first_name' => 'Unassigned',
        'last_name' => 'Staff',
        'email' => 'unassigned-staff@example.com',
        'password' => 'StaffPass123!',
        'is_active' => true,
    ]);
    Invitation::create([
        'invited_by' => $owner->user_id,
        'house_id' => $house->house_id,
        'role_id' => $staffRoleId,
        'email' => $staff->email,
        'token' => 'used-staff-assignment-token',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(3)->toDateString(),
        'check_out' => now()->addDays(5)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);

    $this->actingAs($unassignedStaff)
        ->post(route('staff.bookings.accept', $booking->booking_id))
        ->assertNotFound();

    $this->actingAs($staff)
        ->get(route('staff.bookings.show', $booking->booking_id))
        ->assertOk()
        ->assertSee('Booking #'.$booking->booking_id);
    $this->post(route('staff.bookings.accept', $booking->booking_id))
        ->assertSessionHas('success');

    expect($booking->fresh()->status)->toBe(BookingStatus::ACCEPTED);
});

test('owner acceptance reserves dates while the downpayment is pending', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $checkIn = now()->addDays(3)->toDateString();
    $checkOut = now()->addDays(5)->toDateString();
    $bookingService = app(BookingService::class);
    $booking = $bookingService->createBooking($guest, $unit, [
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);
    $competingBooking = $bookingService->createBooking($guest, $unit, [
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $booking->booking_id);

    expect($booking->fresh()->status)->toBe(BookingStatus::ACCEPTED)
        ->and($competingBooking->fresh()->status)->toBe(BookingStatus::PENDING)
        ->and($booking->fresh()->payment_deadline)
        ->toEqual($booking->fresh()->accepted_at->copy()->addHours(config('booking.payment_deadline_hours')));
    $this->assertDatabaseMissing('notifications', [
        'user_id' => $guest->user_id,
        'booking_id' => $competingBooking->booking_id,
        'type' => 'booking_dates_unavailable',
    ]);
    expect($unit->availability()->whereDate('date', $checkIn)->value('status'))->toBe('Booked')
        ->and($unit->availability()->whereDate('date', now()->addDays(4)->toDateString())->value('status'))->toBe('Booked');

    $payment = $workflow->submitPayment($guest, $booking->booking_id, [
        'payment_method' => 'GCash',
        'amount' => $booking->downpaymentAmount(),
    ]);
    $workflow->reviewPayment($owner, $payment->payment_id, PaymentStatus::VERIFIED, null);

    expect($booking->fresh()->status)->toBe(BookingStatus::CONFIRMED)
        ->and($competingBooking->fresh()->status)->toBe(BookingStatus::CANCELLED);
    $this->assertDatabaseHas('notifications', [
        'user_id' => $guest->user_id,
        'booking_id' => $competingBooking->booking_id,
        'type' => 'booking_dates_unavailable',
    ]);
});

test('accepted bookings reject a payment below the required downpayment', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $bookingService = app(BookingService::class);
    $data = [
        'check_in' => now()->addDays(3)->toDateString(),
        'check_out' => now()->addDays(5)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ];
    $booking = $bookingService->createBooking($guest, $unit, $data);
    $queuedBooking = $bookingService->createBooking($guest, $unit, $data);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $booking->booking_id);

    expect(fn () => $workflow->submitPayment($guest, $booking->booking_id, [
        'payment_method' => 'GCash',
        'amount' => $booking->downpaymentAmount() - 0.01,
        'reference_number' => 'TIMELY-PROOF',
        'payment_receipt' => 'payment-proofs/test.png',
    ]))->toThrow(ValidationException::class)
        ->and($booking->fresh()->status)->toBe(BookingStatus::ACCEPTED)
        ->and($queuedBooking->fresh()->status)->toBe(BookingStatus::PENDING);
    $this->assertDatabaseCount('payments', 0);
});

test('guest can pay the full current balance when submitting the downpayment', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(3)->toDateString(),
        'check_out' => now()->addDays(5)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $booking->booking_id);

    $this->actingAs($guest)
        ->post(route('guest.bookings.payments.store', $booking->booking_id), [
            'payment_method' => 'GCash',
            'amount' => 3600,
            'reference_number' => 'GCASH-FULL-PAYMENT-001',
            'payment_receipt' => UploadedFile::fake()->createWithContent('proof.pdf', "%PDF-1.4\nProof"),
        ])
        ->assertRedirect(route('guest.bookings.show', $booking->booking_id))
        ->assertSessionHas('success', 'Your payment was submitted and is waiting for review.');

    $payment = $booking->payments()->firstOrFail();
    expect((float) $payment->amount)->toBe(3600.0)
        ->and($payment->payment_status)->toBe(PaymentStatus::UNDER_REVIEW);

    $this->actingAs($owner)
        ->post(route('owner.payments.review', $payment->payment_id), [
            'status' => PaymentStatus::VERIFIED,
        ])
        ->assertSessionHas('success');

    expect($booking->fresh()->status)->toBe(BookingStatus::CONFIRMED);
    $this->actingAs($guest)
        ->get(route('guest.bookings.show', $booking->booking_id))
        ->assertOk()
        ->assertSee('Paid in full')
        ->assertDontSee('Pay Now');
});

test('guest payment submission is only allowed for an accepted booking', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(3)->toDateString(),
        'check_out' => now()->addDays(5)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ]);

    $this->from(route('guest.bookings.show', $booking->booking_id))
        ->actingAs($guest)
        ->post(route('guest.bookings.payments.store', $booking->booking_id), [
            'payment_method' => 'GCash',
            'amount' => 1800,
            'reference_number' => 'GCASH-HTTP-REF',
            'payment_receipt' => UploadedFile::fake()->createWithContent('proof.pdf', "%PDF-1.4\nProof"),
        ])
        ->assertRedirect(route('guest.bookings.show', $booking->booking_id))
        ->assertSessionHasErrors('booking');

    $this->assertDatabaseCount('payments', 0);
    expect($booking->fresh()->status)->toBe(BookingStatus::PENDING);
    $this->actingAs($guest)
        ->get(route('guest.dashboard'))
        ->assertSee('Pending (1)')
        ->assertSee('Accepted (0)')
        ->assertSee('Booking #'.$booking->booking_id);
});

test('accepted booking remains visible in the guest dashboard while downpayment is due', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $bookingService = app(BookingService::class);
    $data = [
        'check_in' => now()->addDays(3)->toDateString(),
        'check_out' => now()->addDays(5)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Booking Guest']],
    ];
    $booking = $bookingService->createBooking($guest, $unit, $data);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $booking->booking_id);

    $this->actingAs($guest)
        ->get(route('guest.dashboard'))
        ->assertOk()
        ->assertSee('Pending (0)')
        ->assertSee('Accepted (1)')
        ->assertSee('Booking #'.$booking->booking_id);

    expect($booking->fresh()->status)->toBe(BookingStatus::ACCEPTED)
        ->and($booking->fresh()->payment_deadline)
        ->toEqual($booking->fresh()->accepted_at->copy()->addHours(config('booking.payment_deadline_hours')));
});

test('guest dashboard does not show booking list filters', function () {
    ['guest' => $guest] = makeGuestBookingScenario();

    $this->actingAs($guest)
        ->get(route('guest.dashboard'))
        ->assertDontSee('Search bookings')
        ->assertDontSee('Stay overlaps from')
        ->assertDontSee('Through')
        ->assertDontSee('Newest first')
        ->assertDontSee('Clear');
});

test('guest dashboard does not expire a legacy accepted booking with a past deadline', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(3),
        'check_out' => now()->addDays(5),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
        'accepted_at' => now()->subDay(),
        'payment_deadline' => null,
    ]);

    $this->actingAs($guest)
        ->get(route('guest.dashboard'))
        ->assertOk()
        ->assertSee('Confirmed')
        ->assertSee('Booking #'.$booking->booking_id);

    expect($booking->fresh()->status)->toBe(BookingStatus::CONFIRMED);
});

test('guest booking rejects dates blocked in unit availability', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $unit->availability()->create([
        'date' => now()->addDays(4)->toDateString(),
        'status' => 'Unavailable',
    ]);

    $this->actingAs($guest);
    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('checkIn', now()->addDays(3)->toDateString())
        ->set('checkOut', now()->addDays(5)->toDateString())
        ->set('ackPolicy', true)
        ->call('submit')
        ->assertHasErrors(['checkIn']);

    $this->assertDatabaseCount('bookings', 0);
});

test('booking request shows separate check-in and check-out date fields', function () {
    ['unit' => $unit] = makeGuestBookingScenario();

    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->assertSee('id="booking-check-in"', false)
        ->assertSee('id="booking-check-out"', false)
        ->assertSee('wire:model.live="checkIn"', false)
        ->assertSee('wire:model.live="checkOut"', false)
        ->assertSee('type="date"', false)
        ->assertDontSee('booking-calendar-day', false);
});

test('guest can book starting on the previous booking checkout date', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $unit->update([
        'check_in_time' => '14:00',
        'check_out_time' => '12:00',
    ]);
    Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(3)->setTime(14, 0),
        'check_out' => now()->addDays(5)->setTime(11, 0),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);

    $this->actingAs($guest);
    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('checkIn', now()->addDays(5)->toDateString())
        ->set('checkOut', now()->addDays(7)->toDateString())
        ->set('ackPolicy', true)
        ->call('submit')
        ->assertRedirect(route('guest.bookings.show', 2));

    $this->assertDatabaseCount('bookings', 2);
    $nextBooking = Booking::query()->where('status', BookingStatus::PENDING)->firstOrFail();
    app(BookingWorkflowService::class)->accept($owner, $nextBooking->booking_id);

    expect($nextBooking->fresh()->status)->toBe(BookingStatus::ACCEPTED);
});

test('guest cannot view another guests booking details', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $otherGuest = User::create([
        'role_id' => Role::where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Other',
        'last_name' => 'Guest',
        'email' => 'other-booking-guest@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(3),
        'check_out' => now()->addDays(5),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::PENDING,
    ]);

    $this->actingAs($otherGuest)
        ->get(route('guest.bookings.show', $booking->booking_id))
        ->assertNotFound();
});

test('owner dashboard prioritizes scoped booking and payment queues without discount ID review', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $pending = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(5),
        'check_out' => now()->addDays(7),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::PENDING,
    ]);
    $confirmed = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(8),
        'check_out' => now()->addDays(10),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    $bookingGuest = BookingGuest::create([
        'booking_id' => $confirmed->booking_id,
        'user_id' => $guest->user_id,
        'full_name' => 'Booking Guest',
        'guest_type' => 'adult',
        'share_amount' => 3600,
        'is_primary_guest' => true,
    ]);
    BookingDiscount::create([
        'booking_id' => $confirmed->booking_id,
        'booking_guest_id' => $bookingGuest->booking_guest_id,
        'discount_type' => 'Senior Citizen',
        'id_reference' => 'SC-QUEUED',
        'verification_status' => BookingStatus::PENDING,
        'eligible_amount' => 3600,
        'discount_rate' => 20,
        'discount_amount' => 720,
        'final_amount' => 2880,
        'refund_amount' => 0,
        'refund_status' => 'None',
    ]);
    Payment::create([
        'booking_id' => $confirmed->booking_id,
        'payment_method' => 'GCash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::UNDER_REVIEW,
        'payment_date' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('owner.dashboard'))
        ->assertOk()
        ->assertSee('New Booking Requests')
        ->assertSee('Payments to Verify')
        ->assertDontSee('Senior / PWD IDs to Verify')
        ->assertSee('No upcoming arrivals scheduled.')
        ->assertSee('No guests are currently checked in.')
        ->assertSee('#'.$pending->booking_id)
        ->assertSee('#'.$confirmed->booking_id);
});

test('owner can check in a guest with a verified downpayment on the booking start date', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today(),
        'check_out' => today()->addDay(),
        'adult_count' => 2,
        'child_count' => 1,
        'total_price' => 3600,
        'booking_reference' => 'MCTBS-000123',
        'status' => BookingStatus::CONFIRMED,
    ]);
    $primaryBookingGuest = BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'user_id' => $guest->user_id,
        'full_name' => 'Booking Guest',
        'guest_type' => 'adult',
        'share_amount' => 1800,
        'is_primary_guest' => true,
    ]);
    BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'full_name' => 'Second Guest',
        'guest_type' => 'adult',
        'share_amount' => 1800,
    ]);
    BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'full_name' => 'Child Guest',
        'guest_type' => 'child',
        'age' => 4,
        'is_free_child' => true,
        'share_amount' => 0,
    ]);
    BookingDiscount::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $primaryBookingGuest->booking_guest_id,
        'discount_type' => 'Senior Citizen',
        'id_reference' => 'SC-CHECKIN-PRIVATE',
        'verification_status' => BookingStatus::PENDING,
        'eligible_amount' => 1800,
        'discount_rate' => 20,
        'discount_amount' => 360,
        'final_amount' => 1440,
        'refund_amount' => 0,
        'refund_status' => 'None',
    ]);
    Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('owner.dashboard'))
        ->assertOk()
        ->assertSee('Upcoming Check-ins')
        ->assertSee('Booking Guest')
        ->assertSee('Family Room')
        ->assertSee('MCTBS-000123')
        ->assertSee('Review & Check-in')
        ->assertSee('Review arrival details')
        ->assertSee('Second Guest')
        ->assertSee('Child Guest')
        ->assertSee('Senior/PWD Verification')
        ->assertSee('Headcount')
        ->assertSee(route('owner.bookings.check-in', $booking->booking_id), false)
        ->assertSee(route('owner.guests.check-in', $booking->booking_id), false)
        ->assertSee('SC-CHECKIN-PRIVATE')
        ->assertDontSee('Check in this guest?', false);

    $this->post(route('owner.bookings.check-in', $booking->booking_id), [
        'guest_details_confirmed' => '1',
    ])
        ->assertRedirect(route('owner.dashboard'))
        ->assertSessionHas('success', 'Guest checked in successfully.');

    expect($booking->fresh()->status)->toBe(BookingStatus::CHECKED_IN)
        ->and($booking->fresh()->checked_in_at)->not->toBeNull();

    $this->get(route('owner.dashboard'))
        ->assertSee('Current Guests')
        ->assertSee('MCTBS-000123')
        ->assertSee(today()->addDay()->format('M j, Y'))
        ->assertSee('Manage Guest')
        ->assertDontSee('Review IDs')
        ->assertDontSee('id="arrival-review-'.$booking->booking_id.'"', false)
        ->assertDontSee('Check in this guest?', false);

    $this->get(route('owner.guests.check-in', $booking->booking_id))
        ->assertSee('Stay Details')
        ->assertSee('SC-CHECKIN-PRIVATE')
        ->assertSee('This ID has not been reviewed.')
        ->assertDontSee('Verify ID')
        ->assertDontSee('Reject ID')
        ->assertSee('Amenities Added');
});

test('owner dashboard limits operational previews while showing accurate totals', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();

    foreach (range(1, 6) as $index) {
        Booking::create([
            'user_id' => $guest->user_id,
            'unit_id' => $unit->unit_id,
            'check_in' => today()->addDays($index),
            'check_out' => today()->addDays($index + 1),
            'adult_count' => 1,
            'child_count' => 0,
            'total_price' => 3600,
            'booking_reference' => 'PREVIEW-'.$index,
            'status' => BookingStatus::PENDING,
        ]);
    }

    $this->actingAs($owner)
        ->get(route('owner.dashboard'))
        ->assertOk()
        ->assertSee('6 pending')
        ->assertSee('PREVIEW-1')
        ->assertSee('PREVIEW-5')
        ->assertDontSee('PREVIEW-6')
        ->assertSee('View all');
});

test('owner can open a confirmed guest ID review modal outside the dashboard preview', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $bookings = collect(range(1, 6))->map(fn (int $index): Booking => Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->addDays($index),
        'check_out' => today()->addDays($index + 1),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'booking_reference' => 'CONFIRMED-'.$index,
        'status' => BookingStatus::CONFIRMED,
    ]));
    foreach ($bookings as $confirmedBooking) {
        Payment::create([
            'booking_id' => $confirmedBooking->booking_id,
            'payment_method' => 'GCash',
            'amount' => 1800,
            'payment_status' => PaymentStatus::VERIFIED,
            'payment_date' => now(),
        ]);
    }
    $booking = $bookings->last();
    $bookingGuest = BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'full_name' => 'Outside Preview Guest',
        'guest_type' => 'adult',
        'share_amount' => 3600,
        'is_primary_guest' => true,
    ]);
    BookingDiscount::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $bookingGuest->booking_guest_id,
        'discount_type' => 'PWD',
        'id_reference' => 'OUTSIDE-PREVIEW-ID',
        'verification_status' => BookingStatus::PENDING,
        'eligible_amount' => 3600,
        'discount_rate' => 20,
        'discount_amount' => 720,
        'final_amount' => 2880,
        'refund_amount' => 0,
        'refund_status' => 'None',
    ]);

    $this->actingAs($owner)
        ->get(route('owner.dashboard', ['review' => $booking->booking_id]))
        ->assertSee('OUTSIDE-PREVIEW-ID')
        ->assertSee('data-auto-open-arrival-review="true"', false)
        ->assertSee('id="arrival-review-'.$booking->booking_id.'"', false)
        ->assertSee(route('owner.discounts.review', BookingDiscount::query()
            ->where('booking_id', $booking->booking_id)
            ->value('discount_id')), false)
        ->assertDontSee('data-open-arrival-review="'.$booking->booking_id.'"', false);
});

test('owner dashboard lists paid confirmed arrivals on all future dates', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $arrivalDate = today()->addDay();
    $nearTermBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $arrivalDate,
        'check_out' => $arrivalDate->copy()->addDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    Payment::create([
        'booking_id' => $nearTermBooking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
    ]);
    $futureArrival = today()->addDays(30);
    $futureBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $futureArrival,
        'check_out' => $futureArrival->copy()->addDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    Payment::create([
        'booking_id' => $futureBooking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('owner.dashboard'))
        ->assertSee('Upcoming Check-ins')
        ->assertSee($arrivalDate->format('M j, Y'))
        ->assertSee(route('owner.guests.check-in', $nearTermBooking->booking_id))
        ->assertSee($futureArrival->format('M j, Y'))
        ->assertSee(route('owner.guests.check-in', $futureBooking->booking_id));

    $this->post(route('owner.bookings.check-in', $futureBooking->booking_id), [
        'guest_details_confirmed' => '1',
    ])
        ->assertRedirect(route('owner.dashboard'))
        ->assertSessionHas('success', 'Guest checked in successfully.');

    expect($futureBooking->fresh()->status)->toBe(BookingStatus::CHECKED_IN);
});

test('owner can check in a paid confirmed guest before a future arrival date', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $arrivalDate = today()->addDays(14);
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $arrivalDate,
        'check_out' => $arrivalDate->copy()->addDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
    ]);

    $this->actingAs($owner)
        ->post(route('owner.bookings.check-in', $booking->booking_id), [
            'guest_details_confirmed' => '1',
        ])
        ->assertRedirect(route('owner.dashboard'))
        ->assertSessionHas('success', 'Guest checked in successfully.');

    expect($booking->fresh()->status)->toBe(BookingStatus::CHECKED_IN)
        ->and($booking->fresh()->checked_in_at)->not->toBeNull();
});

test('staff sees only assigned houses on guest dashboard sections', function () {
    ['owner' => $owner, 'house' => $house, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $otherOwner = User::create([
        'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Other',
        'last_name' => 'Owner',
        'email' => 'other-dashboard-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $otherHouse = TransientHouse::create([
        'owner_id' => $otherOwner->user_id,
        'name' => 'Unassigned House',
        'address' => '20 Test Road',
        'city' => 'Baguio',
        'contact_number' => '5553000',
        'is_active' => true,
    ]);
    $otherUnit = Unit::create([
        'house_id' => $otherHouse->house_id,
        'unit_type' => 'Private Suite',
        'capacity' => 2,
        'price' => 1800,
        'status' => 'Available',
    ]);
    $staff = User::create([
        'role_id' => Role::where('name', 'Staff')->firstOrFail()->role_id,
        'first_name' => 'Assigned',
        'last_name' => 'Staff',
        'email' => 'dashboard-check-in-staff@example.com',
        'password' => 'StaffPass123!',
        'is_active' => true,
    ]);
    Invitation::create([
        'invited_by' => $owner->user_id,
        'house_id' => $house->house_id,
        'role_id' => Role::where('name', 'Staff')->firstOrFail()->role_id,
        'email' => $staff->email,
        'token' => 'dashboard-check-in-assignment',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);
    $staffArrivalDate = today()->addDays(30);
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $staffArrivalDate,
        'check_out' => $staffArrivalDate->copy()->addDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'booking_reference' => 'MCTBS-STAFF-01',
        'status' => BookingStatus::CONFIRMED,
    ]);
    Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
    ]);
    $unassignedBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $otherUnit->unit_id,
        'check_in' => today(),
        'check_out' => today()->addDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'booking_reference' => 'MCTBS-UNASSIGNED',
        'status' => BookingStatus::CONFIRMED,
    ]);
    Payment::create([
        'booking_id' => $unassignedBooking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('owner.dashboard'))
        ->assertSee('MCTBS-STAFF-01')
        ->assertDontSee('MCTBS-UNASSIGNED');
    $this->actingAs($staff)
        ->get(route('staff.dashboard'))
        ->assertSee('MCTBS-STAFF-01')
        ->assertSee($staffArrivalDate->format('M j, Y'))
        ->assertDontSee('>Manage bookings</a>', false)
        ->assertDontSee('>Properties</a>', false)
        ->assertDontSee('MCTBS-UNASSIGNED')
        ->assertDontSee('No upcoming guests expected.');
    $this->post(route('staff.bookings.check-in', $booking->booking_id), [
        'guest_details_confirmed' => '1',
    ])
        ->assertRedirect(route('staff.dashboard'));
    $this->post(route('staff.bookings.check-in', $unassignedBooking->booking_id), [
        'guest_details_confirmed' => '1',
    ])
        ->assertNotFound();
    $this->get(route('staff.guests.check-in', $unassignedBooking->booking_id))
        ->assertNotFound();
});

test('check-in refuses bookings that do not meet the required conditions', function (
    string $status,
    float $verifiedAmount,
    string $errorMessage,
) {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today(),
        'check_out' => today()->addDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => $status,
    ]);
    Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => $verifiedAmount,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
    ]);

    $this->actingAs($owner)
        ->from(route('owner.dashboard'))
        ->post(route('owner.bookings.check-in', $booking->booking_id), [
            'guest_details_confirmed' => '1',
        ])
        ->assertRedirect(route('owner.dashboard'))
        ->assertSessionHasErrors(['check_in' => $errorMessage]);

    expect($booking->fresh()->status)->toBe($status)
        ->and($booking->fresh()->checked_in_at)->toBeNull();
})->with([
    'not confirmed' => [
        BookingStatus::ACCEPTED,
        1800,
        'Only confirmed bookings can be checked in.',
    ],
    'downpayment not fully verified' => [
        BookingStatus::CONFIRMED,
        1000,
        'A verified downpayment is required before check-in.',
    ],
]);

test('upcoming guests can verify an ID from the dashboard modal and apply it only to that guest share', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today(),
        'check_out' => today()->addDays(2),
        'adult_count' => 2,
        'child_count' => 0,
        'total_price' => 3600,
        'room_subtotal' => 3600,
        'booking_reference' => 'MCTBS-ID-01',
        'status' => BookingStatus::CONFIRMED,
    ]);
    $seniorGuest = BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'full_name' => 'Senior Guest',
        'guest_type' => 'adult',
        'share_amount' => 1800,
        'is_primary_guest' => true,
    ]);
    BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'full_name' => 'Other Guest',
        'guest_type' => 'adult',
        'share_amount' => 1800,
        'is_primary_guest' => false,
    ]);
    $discount = BookingDiscount::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $seniorGuest->booking_guest_id,
        'discount_type' => 'Senior Citizen',
        'id_reference' => 'SENIOR-CHECKIN-01',
        'verification_status' => BookingStatus::PENDING,
        'eligible_amount' => 1800,
        'discount_rate' => 20,
        'discount_amount' => 360,
        'final_amount' => 1440,
        'refund_amount' => 0,
        'refund_status' => 'None',
    ]);
    Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('owner.guests.check-in', $booking->booking_id))
        ->assertSee('Senior/PWD Verification')
        ->assertSee('SENIOR-CHECKIN-01')
        ->assertSee('Senior Guest')
        ->assertSee('Open arrival review')
        ->assertDontSee('Verify ID')
        ->assertDontSee('Reject ID');

    $this->get(route('owner.dashboard'))
        ->assertSee('SENIOR-CHECKIN-01')
        ->assertSee('Verify ID')
        ->assertSee('Reject ID');

    $this->post(route('owner.discounts.review', $discount->discount_id), [
        'status' => 'Verified',
        'verification_note' => '',
        'dashboard_modal' => '1',
        'dashboard_booking_id' => $booking->booking_id,
        'dashboard_discount_id' => $discount->discount_id,
    ])
        ->assertRedirect(route('owner.dashboard'))
        ->assertSessionHas('openArrivalReview', $booking->booking_id);

    $this->get(route('owner.dashboard'))
        ->assertSee('data-auto-open-arrival-review="true"', false)
        ->assertSee('SENIOR-CHECKIN-01')
        ->assertSee('Reviewed by '.$owner->first_name.' '.$owner->last_name);

    expect($discount->fresh()->verification_status)->toBe('Verified')
        ->and($discount->fresh()->verified_by)->toBe($owner->user_id)
        ->and($discount->fresh()->verified_at)->not->toBeNull()
        ->and($discount->fresh()->verification_note)->toBeNull()
        ->and($booking->fresh()->total_price)->toBe('3240.00');

    $this->post(route('owner.bookings.check-in', $booking->booking_id), [
        'guest_details_confirmed' => '1',
    ])->assertRedirect(route('owner.dashboard'));

    expect($booking->fresh()->status)->toBe(BookingStatus::CHECKED_IN);

    $this->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertSee('Senior/PWD discount')
        ->assertSee('₱360.00')
        ->assertSee('₱3,240.00');
});

test('upcoming guests with invalid Senior/PWD IDs see the required message and no discount', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today(),
        'check_out' => today()->addDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    $bookingGuest = BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'full_name' => 'PWD Guest',
        'guest_type' => 'adult',
        'share_amount' => 3600,
        'is_primary_guest' => true,
    ]);
    $discount = BookingDiscount::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $bookingGuest->booking_guest_id,
        'discount_type' => 'PWD',
        'id_reference' => 'PWD-CHECKIN-02',
        'verification_status' => BookingStatus::PENDING,
        'eligible_amount' => 3600,
        'discount_rate' => 20,
        'discount_amount' => 720,
        'final_amount' => 2880,
        'refund_amount' => 0,
        'refund_status' => 'None',
    ]);
    Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
    ]);

    $this->actingAs($owner)
        ->from(route('owner.dashboard'))
        ->post(route('owner.discounts.review', $discount->discount_id), [
            'status' => 'Invalid',
            'dashboard_modal' => '1',
            'dashboard_booking_id' => $booking->booking_id,
            'dashboard_discount_id' => $discount->discount_id,
        ])
        ->assertRedirect(route('owner.dashboard'))
        ->assertSessionHasErrors([
            'verification_note' => 'Please explain why this ID cannot be accepted.',
        ])
        ->assertSessionHas('openArrivalReview', $booking->booking_id);

    expect($discount->fresh()->verification_status)->toBe(BookingStatus::PENDING);
    $this->get(route('owner.dashboard'))
        ->assertSee('Why can’t this ID be accepted?')
        ->assertSee('Please explain why this ID cannot be accepted.')
        ->assertSee('data-auto-open-arrival-review="true"', false);

    $this->post(route('owner.discounts.review', $discount->discount_id), [
        'status' => 'Invalid',
        'verification_note' => 'The ID details do not match.',
        'dashboard_modal' => '1',
        'dashboard_booking_id' => $booking->booking_id,
        'dashboard_discount_id' => $discount->discount_id,
    ])->assertRedirect(route('owner.dashboard'));

    expect($discount->fresh()->verification_status)->toBe('Invalid')
        ->and($discount->fresh()->verification_note)->toBe('The ID details do not match.')
        ->and($discount->fresh()->verified_by)->toBe($owner->user_id)
        ->and($booking->fresh()->total_price)->toBe('3600.00');

    $this->assertDatabaseHas('notifications', [
        'user_id' => $guest->user_id,
        'booking_id' => $booking->booking_id,
        'type' => 'discount_Invalid',
        'message' => 'The PWD ID for PWD Guest was not verified at checkout. Reason: The ID details do not match.',
    ]);

    $this->actingAs($guest)
        ->get(route('guest.bookings.show', $booking->booking_id))
        ->assertSeeText('Reason: The ID details do not match.');

    $this->actingAs($owner)
        ->get(route('owner.guests.check-in', $booking->booking_id))
        ->assertSee('ID is invalid. Please tell the guest in person.')
        ->assertSee('The ID details do not match.')
        ->assertDontSee('Reject ID');
});

test('refunds for checked-in guests cannot exceed payments after previous refunds', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today(),
        'check_out' => today()->addDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CHECKED_IN,
    ]);
    $payment = Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 4800,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
    ]);

    $this->actingAs($owner)
        ->post(route('owner.guests.refunds.store', $booking->booking_id), [
            'amount' => '1000.00',
            'reason' => 'Guest needs to go home early',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $refund = $booking->fresh()->refunds()->firstOrFail();
    expect($refund->amount)->toBe('1000.00')
        ->and($refund->payment_id)->toBe($payment->payment_id)
        ->and($refund->processed_by)->toBe($owner->user_id)
        ->and($refund->processed_at)->not->toBeNull()
        ->and($refund->refund_status)->toBe('Refunded');

    $this->from(route('owner.guests.check-in', $booking->booking_id))
        ->post(route('owner.guests.refunds.store', $booking->booking_id), [
            'amount' => '200.01',
            'reason' => 'Another refund',
        ])
        ->assertRedirect(route('owner.guests.check-in', $booking->booking_id))
        ->assertSessionHasErrors('amount');

    expect($booking->fresh()->refunds()->count())->toBe(1);

    $this->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertSee('Refunds')
        ->assertSee('₱1,000.00')
        ->assertSee('Refund History');
});

test('checked-in guests can add unit amenities as price snapshots included in checkout', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today(),
        'check_out' => today()->addDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'booking_reference' => 'MCTBS-ADDON-01',
        'status' => BookingStatus::CHECKED_IN,
    ]);
    $addon = UnitAddon::create([
        'unit_id' => $unit->unit_id,
        'name' => 'Karaoke',
        'price' => 800,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.guests.check-in', $booking->booking_id))
        ->assertSee('Add Amenities')
        ->assertSee('Karaoke')
        ->assertSee('₱800.00')
        ->assertSee('class="amenity-card"', false)
        ->assertSee('class="amenity-card-selection" type="checkbox"', false)
        ->assertSee('data-amenity-check', false)
        ->assertSee('data-amenity-quantity', false);

    $this->post(route('owner.guests.addons.store', $booking->booking_id), [
        'addons' => [
            ['unit_addon_id' => $addon->unit_addon_id, 'quantity' => 2],
        ],
    ])->assertRedirect(route('owner.guests.check-in', $booking->booking_id))
        ->assertSessionHas('success');

    $addon->update(['price' => 1000]);
    $bookingAddon = $booking->fresh()->addons->firstOrFail();
    expect($bookingAddon->name)->toBe('Karaoke')
        ->and($bookingAddon->price)->toBe('800.00')
        ->and($bookingAddon->quantity)->toBe(2)
        ->and($booking->fresh()->amountDueAtCheckout())->toBe(5200.0);

    $this->get(route('owner.guests.check-in', $booking->booking_id))
        ->assertSee('Amenities Added')
        ->assertSee('Karaoke')
        ->assertSee('₱1,600.00');
    $this->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertSee('Karaoke')
        ->assertSee('₱1,600.00')
        ->assertSee('₱5,200.00');
});

test('checked-in amenities merge by item and saved price', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today(),
        'check_out' => today()->addDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CHECKED_IN,
    ]);
    $addon = UnitAddon::create([
        'unit_id' => $unit->unit_id,
        'name' => 'Extra bed',
        'price' => 500,
    ]);

    $this->actingAs($owner)
        ->post(route('owner.guests.addons.store', $booking->booking_id), [
            'addons' => [['unit_addon_id' => $addon->unit_addon_id, 'quantity' => 2]],
        ])
        ->assertRedirect(route('owner.guests.check-in', $booking->booking_id));
    $this->post(route('owner.guests.addons.store', $booking->booking_id), [
        'addons' => [['unit_addon_id' => $addon->unit_addon_id, 'quantity' => 3]],
    ])->assertRedirect(route('owner.guests.check-in', $booking->booking_id));

    $addon->update(['price' => 600]);
    $this->post(route('owner.guests.addons.store', $booking->booking_id), [
        'addons' => [['unit_addon_id' => $addon->unit_addon_id, 'quantity' => 1]],
    ])->assertRedirect(route('owner.guests.check-in', $booking->booking_id));

    expect($booking->fresh()->addons)->toHaveCount(2);
    $this->assertDatabaseHas('booking_addons', [
        'booking_id' => $booking->booking_id,
        'name' => 'Extra bed',
        'price' => '500.00',
        'quantity' => 5,
    ]);
    $this->assertDatabaseHas('booking_addons', [
        'booking_id' => $booking->booking_id,
        'name' => 'Extra bed',
        'price' => '600.00',
        'quantity' => 1,
    ]);
});

test('checked-in amenities can be increased and decreased without deleting zero-quantity history', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today(),
        'check_out' => today()->addDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CHECKED_IN,
    ]);
    $addon = UnitAddon::create([
        'unit_id' => $unit->unit_id,
        'name' => 'Extra bed',
        'price' => 500,
    ]);

    $this->actingAs($owner)
        ->post(route('owner.guests.addons.store', $booking->booking_id), [
            'addons' => [['unit_addon_id' => $addon->unit_addon_id, 'quantity' => 2]],
        ])->assertSessionHas('success');
    $bookingAddon = $booking->fresh()->addons->firstOrFail();

    $this->post(route('owner.guests.addons.quantity', [$booking->booking_id, $bookingAddon->booking_addon_id]), [
        'change' => 1,
    ])->assertSessionHas('success');
    expect($bookingAddon->fresh()->quantity)->toBe(3)
        ->and($booking->fresh()->amountDueAtCheckout())->toBe(5100.0);

    foreach ([1, 1, 1] as $change) {
        $this->post(route('owner.guests.addons.quantity', [$booking->booking_id, $bookingAddon->booking_addon_id]), [
            'change' => -$change,
        ])->assertSessionHas('success');
    }

    $this->assertDatabaseHas('booking_addons', [
        'booking_addon_id' => $bookingAddon->booking_addon_id,
        'quantity' => 0,
    ]);
    expect($booking->fresh()->amountDueAtCheckout())->toBe(3600.0);
    $this->get(route('owner.guests.check-in', $booking->booking_id))
        ->assertDontSee('Extra bed · 0')
        ->assertDontSee('Increase Extra bed quantity');

    $this->post(route('owner.guests.addons.store', $booking->booking_id), [
        'addons' => [['unit_addon_id' => $addon->unit_addon_id, 'quantity' => 1]],
    ])->assertSessionHas('success');

    expect($booking->fresh()->addons)->toHaveCount(1)
        ->and($bookingAddon->fresh()->quantity)->toBe(1)
        ->and($booking->fresh()->amountDueAtCheckout())->toBe(4100.0);
});

test('checked-in booking edits update stay and guest counts while rejecting overlapping dates', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $unit->update([
        'maximum_capacity' => 4,
        'child_free_through_age' => 5,
    ]);
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today(),
        'check_out' => today()->addDays(2),
        'adult_count' => 2,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CHECKED_IN,
    ]);
    $otherBooking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->addDays(2),
        'check_out' => today()->addDays(4),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.edit', $booking->booking_id))
        ->assertSee('Paying guests (counted guests)')
        ->assertSee('Free children');

    $this->from(route('owner.bookings.edit', $booking->booking_id))
        ->put(route('owner.bookings.update', $booking->booking_id), [
            'check_in' => today()->toDateString(),
            'check_out' => today()->addDays(3)->toDateString(),
            'adult_count' => 3,
            'child_count' => 1,
            'reason' => 'Guest extended the stay.',
        ])
        ->assertRedirect(route('owner.bookings.edit', $booking->booking_id))
        ->assertSessionHasErrors('check_in');

    expect($booking->fresh()->check_out->toDateString())->toBe(today()->addDays(2)->toDateString());

    $otherBooking->update(['status' => BookingStatus::CANCELLED]);
    $this->put(route('owner.bookings.update', $booking->booking_id), [
        'check_in' => today()->toDateString(),
        'check_out' => today()->addDays(3)->toDateString(),
        'adult_count' => 3,
        'child_count' => 1,
        'reason' => 'Guest extended the stay.',
    ])->assertRedirect(route('owner.guests.check-in', $booking->booking_id))
        ->assertSessionHas('success');

    expect($booking->fresh()->check_out->toDateString())->toBe(today()->addDays(3)->toDateString())
        ->and($booking->fresh()->adult_count)->toBe(3)
        ->and($booking->fresh()->child_count)->toBe(1)
        ->and($booking->fresh()->total_price)->toBe('5400.00');
});

test('checked-in booking edits preserve the original check-in date and hide details actions', function () {
    $this->travelTo('2026-11-12 10:00:00');
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => '2026-11-21',
        'check_out' => '2026-11-23',
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CHECKED_IN,
        'checked_in_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.edit', $booking->booking_id))
        ->assertSee('The recorded check-in date is fixed for an active stay.')
        ->assertSee('value="2026-11-21" disabled', false);

    $this->put(route('owner.bookings.update', $booking->booking_id), [
        'check_out' => '2026-11-24',
        'adult_count' => 2,
        'child_count' => 0,
        'reason' => 'The guest count was corrected and the stay extended.',
    ])->assertRedirect(route('owner.guests.check-in', $booking->booking_id))
        ->assertSessionHas('success');

    expect($booking->fresh()->check_in->toDateString())->toBe('2026-11-21')
        ->and($booking->fresh()->check_out->toDateString())->toBe('2026-11-24')
        ->and($booking->fresh()->adult_count)->toBe(2);

    $this->get(route('owner.bookings.show', $booking->booking_id))
        ->assertDontSee('booking-details-actions', false)
        ->assertDontSee('Cancel Booking');
});

test('booking edits cannot move checkout onto an unavailable date', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $checkIn = now()->addDays(8);
    $checkOut = $checkIn->copy()->addDays(2);
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $checkIn->toDateString(),
        'check_out' => $checkOut->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    foreach ([$checkIn, $checkIn->copy()->addDay()] as $date) {
        UnitCalendar::create([
            'unit_id' => $unit->unit_id,
            'date' => $date->toDateString(),
            'status' => 'Booked',
            'note' => 'Confirmed booking #'.$booking->booking_id,
        ]);
    }
    $unavailableCheckout = $checkOut->copy()->addDay()->toDateString();
    UnitCalendar::create([
        'unit_id' => $unit->unit_id,
        'date' => $unavailableCheckout,
        'status' => 'Unavailable',
        'note' => 'Maintenance',
    ]);

    $this->actingAs($owner)
        ->from(route('owner.bookings.edit', $booking->booking_id))
        ->put(route('owner.bookings.update', $booking->booking_id), [
            'check_in' => $checkIn->toDateString(),
            'check_out' => $unavailableCheckout,
            'reason' => 'Extend the booking.',
        ])
        ->assertRedirect(route('owner.bookings.edit', $booking->booking_id))
        ->assertSessionHasErrors('check_in');

    expect($booking->fresh()->check_out->toDateString())->toBe($checkOut->toDateString());
    expect(UnitCalendar::query()
        ->where('unit_id', $unit->unit_id)
        ->whereDate('date', $checkIn->toDateString())
        ->where('note', 'Confirmed booking #'.$booking->booking_id)
        ->exists())->toBeTrue();
});

test('early checkout requires a reason and records it without changing the scheduled checkout date', function () {
    $this->travelTo('2026-11-12 10:00:00');
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today()->subDay(),
        'check_out' => today()->addDays(3),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 100,
        'status' => BookingStatus::CHECKED_IN,
        'checked_in_at' => now()->subDay(),
    ]);
    Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'Cash',
        'amount' => 100,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now()->subDay(),
    ]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertSee('recorded as an early checkout')
        ->assertSee('Reason for early checkout')
        ->assertSee('form="checkout-complete-form"', false)
        ->assertSee('id="checkout-complete-form"', false)
        ->assertSeeInOrder([
            'name="early_checkout_reason"',
            'action="'.route('owner.bookings.checkout.complete', $booking->booking_id).'"',
            'Complete Checkout',
        ]);

    $this->post(route('owner.bookings.checkout.complete', $booking->booking_id))
        ->assertSessionHasErrors('early_checkout_reason');

    expect($booking->fresh()->status)->toBe(BookingStatus::CHECKED_IN)
        ->and($booking->fresh()->receipt)->toBeNull();

    $this->post(route('owner.bookings.checkout.complete', $booking->booking_id), [
        'early_checkout_reason' => 'The guest needed to leave sooner than planned.',
    ])->assertRedirect(route('owner.bookings.checkout', $booking->booking_id));

    expect($booking->fresh()->status)->toBe(BookingStatus::COMPLETED)
        ->and($booking->fresh()->check_out->toDateString())->toBe(today()->addDays(3)->toDateString())
        ->and($booking->fresh()->early_checkout_reason)->toBe('The guest needed to leave sooner than planned.')
        ->and($booking->fresh()->checkedOutEarly())->toBeTrue();

    $this->get(route('owner.bookings.show', $booking->booking_id))
        ->assertDontSee('Early checkout recorded.')
        ->assertSee('data-status-tooltip="Early Check-out: The guest needed to leave sooner than planned."', false);

    $this->actingAs($guest)
        ->get(route('guest.dashboard'))
        ->assertSee('History (1)')
        ->assertSee('data-status-tooltip="Early Check-out: The guest needed to leave sooner than planned."', false);

    $this->actingAs($owner)
        ->get(route('owner.bookings.index'))
        ->assertSee('History')
        ->assertSee('data-status-tooltip="Early Check-out: The guest needed to leave sooner than planned."', false);
    $this->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertDontSee('Early checkout recorded.');
});

test('booking edits allow the next stay to start on the previous checkout date', function () {
    Mail::fake();
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $unit->update([
        'check_in_time' => '14:00',
        'check_out_time' => '12:00',
    ]);
    $bookingService = app(BookingService::class);
    $workflow = app(BookingWorkflowService::class);
    $firstCheckIn = now()->addDays(5);
    $previousBooking = $bookingService->createBooking($guest, $unit, [
        'check_in' => $firstCheckIn->toDateString(),
        'check_out' => $firstCheckIn->copy()->addDays(2)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Previous Guest']],
    ]);
    $workflow->accept($owner, $previousBooking->booking_id);
    $nextCheckIn = $firstCheckIn->copy()->addDays(5);
    $nextBooking = $bookingService->createBooking($guest, $unit, [
        'check_in' => $nextCheckIn->toDateString(),
        'check_out' => $nextCheckIn->copy()->addDays(2)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Next Guest']],
    ]);
    $workflow->accept($owner, $nextBooking->booking_id);

    $this->actingAs($owner)
        ->put(route('owner.bookings.update', $nextBooking->booking_id), [
            'check_in' => $previousBooking->check_out->toDateString(),
            'check_out' => $previousBooking->check_out->copy()->addDays(2)->toDateString(),
            'reason' => 'Move the stay to the checkout date.',
        ])
        ->assertRedirect(route('owner.bookings.show', $nextBooking->booking_id));

    expect($nextBooking->fresh()->check_in->toDateString())
        ->toBe($previousBooking->check_out->toDateString());
});

test('guest check-in page supports confirmed and checked-in bookings while checkout can open for active guests', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today(),
        'check_out' => today()->addDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.guests.check-in', $booking->booking_id))
        ->assertOk()
        ->assertSee('Guest Headcount')
        ->assertDontSee('Guest Roster and Attendance')
        ->assertSee('Complete Booking Check-in');
    $this->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertOk();

    $booking->update(['status' => BookingStatus::CHECKED_IN]);
    $checkInResponse = $this->get(route('owner.guests.check-in', $booking->booking_id))
        ->assertSee('Stay Details')
        ->assertSee('Booking reference')
        ->assertSee('Adult Paying Guests')
        ->assertSee('Free Children')
        ->assertDontSee('Guest Roster and Attendance')
        ->assertDontSee('Attendance')
        ->assertSee('guest-checkin-layout', false)
        ->assertSee('guest-checkin-detail-grid', false)
        ->assertSee('guest-checkin-detail-date', false)
        ->assertSee('guest-checkin-detail-value', false)
        ->assertSee('guest-checkin-action-grid', false)
        ->assertSee('Actions')
        ->assertDontSee('fa-bolt')
        ->assertSee('Refund')
        ->assertSee('Edit Booking')
        ->assertSee('Add Amenities')
        ->assertSee('View Booking Details')
        ->assertSee('Send Feedback')
        ->assertSee('Check-out')
        ->assertDontSee('Close')
        ->assertDontSee('Guest Count')
        ->assertDontSee('Total reported guests')
        ->assertSee('.guest-checkin-layout { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); grid-template-areas: "stay actions" "verification addons" "refunds .";', false)
        ->assertSee('.guest-checkin-layout { grid-template-areas: "stay" "actions" "verification" "addons" "refunds"; }', false);

    $this->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertSee('Checkout Breakdown')
        ->assertSee('Back to Guest Check-in')
        ->assertDontSee('Guest Count')
        ->assertDontSee('Total reported guests');
    $this->get(route('owner.bookings.show', $booking->booking_id))
        ->assertSee('Back to Guest Check-in');
    $this->get(route('owner.bookings.edit', $booking->booking_id))
        ->assertSee('Back to Guest Check-in')
        ->assertSee(route('owner.guests.check-in', $booking->booking_id));
});

test('owner can edit pending dates with an audit record and recalculated total', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(5),
        'check_out' => now()->addDays(7),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::PENDING,
    ]);
    BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'user_id' => $guest->user_id,
        'full_name' => 'Booking Guest',
        'guest_type' => 'adult',
        'share_amount' => 3600,
        'is_primary_guest' => true,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.edit', $booking->booking_id))
        ->assertOk()
        ->assertSee('Update booking details');

    $this->put(route('owner.bookings.update', $booking->booking_id), [
        'check_in' => now()->addDays(8)->toDateString(),
        'check_out' => now()->addDays(11)->toDateString(),
        'reason' => 'Guest requested a longer stay',
    ])->assertRedirect(route('owner.bookings.show', $booking->booking_id));

    expect($booking->fresh()->total_price)->toBe('5400.00')
        ->and($booking->fresh()->check_in->toDateString())->toBe(now()->addDays(8)->toDateString());
    $this->assertDatabaseHas('booking_edits', [
        'booking_id' => $booking->booking_id,
        'edited_by' => $owner->user_id,
        'old_total_price' => '3600.00',
        'new_total_price' => '5400.00',
        'edit_reason' => 'Guest requested a longer stay',
    ]);
});

test('owner edit recalculates a confirmed booking without requesting another payment', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $currentCheckIn = now()->addDays(10);
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $currentCheckIn->toDateString(),
        'check_out' => $currentCheckIn->copy()->addDays(2)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'nightly_rate' => 1800,
        'room_subtotal' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'user_id' => $guest->user_id,
        'full_name' => 'Booking Guest',
        'guest_type' => 'adult',
        'share_amount' => 3600,
        'is_primary_guest' => true,
    ]);
    Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 2300,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
    ]);
    foreach ([$currentCheckIn, $currentCheckIn->copy()->addDay()] as $date) {
        UnitCalendar::create([
            'unit_id' => $unit->unit_id,
            'date' => $date->toDateString(),
            'status' => 'Booked',
            'note' => 'Confirmed booking #'.$booking->booking_id,
        ]);
    }

    $this->actingAs($owner)
        ->get(route('owner.bookings.edit', $booking->booking_id))
        ->assertOk()
        ->assertSee('Update booking details')
        ->assertSee('Verified payments to date');

    $newCheckIn = now()->addDays(15);
    $this->put(route('owner.bookings.update', $booking->booking_id), [
        'check_in' => $newCheckIn->toDateString(),
        'check_out' => $newCheckIn->copy()->addDays(3)->toDateString(),
        'reason' => 'Guest requested a longer stay',
    ])->assertRedirect(route('owner.bookings.show', $booking->booking_id));

    $booking->refresh();
    expect($booking->status)->toBe(BookingStatus::CONFIRMED)
        ->and($booking->total_price)->toBe('5400.00')
        ->and($booking->amountDueBeforeCheckout())->toBe(5400.0);
    expect(UnitCalendar::query()
        ->where('unit_id', $unit->unit_id)
        ->whereDate('date', $currentCheckIn->toDateString())
        ->exists())->toBeFalse()
        ->and(UnitCalendar::query()
            ->where('unit_id', $unit->unit_id)
            ->whereDate('date', $newCheckIn->toDateString())
            ->where('note', 'Confirmed booking #'.$booking->booking_id)
            ->exists())->toBeTrue();

    $edit = $booking->edits()->firstOrFail();
    expect((float) $edit->old_total_price)->toBe(3600.0)
        ->and((float) $edit->new_total_price)->toBe(5400.0);
    expect(fn () => app(BookingWorkflowService::class)->submitPayment($guest, $booking->booking_id, [
        'payment_method' => 'GCash',
        'amount' => 399,
    ]))->toThrow(ValidationException::class);
    expect(fn () => app(BookingWorkflowService::class)->submitPayment($guest, $booking->booking_id, [
        'payment_method' => 'GCash',
        'amount' => 400,
    ]))->toThrow(ValidationException::class);
    $this->assertDatabaseCount('payments', 1);
    $this->assertDatabaseMissing('payments', [
        'booking_id' => $booking->booking_id,
        'amount' => 400,
    ]);

    $this->actingAs($guest)
        ->get(route('guest.bookings.show', $booking->booking_id))
        ->assertOk()
        ->assertSee('Booking Change History')
        ->assertSee('Guest requested a longer stay')
        ->assertSee('₱5,400.00')
        ->assertSee('₱3,100.00');
});

test('Senior and PWD ID numbers may be reused', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario(2);
    $checkIn = now()->addDays(3)->toDateString();

    $this->actingAs($guest);
    Livewire::test(BookingForm::class, ['unit' => $unit])
        ->set('checkIn', $checkIn)
        ->set('checkOut', now()->addDays(5)->toDateString())
        ->set('paxCount', 2)
        ->set('seniorCount', 1)
        ->set('pwdCount', 1)
        ->set('seniorIds.0', 'SHARED-ID-123')
        ->set('pwdIds.0', 'SHARED-ID-123')
        ->set('ackPolicy', true)
        ->call('submit')
        ->assertRedirect(route('guest.bookings.show', 1));

    expect(BookingDiscount::query()->where('id_reference', 'SHARED-ID-123')->count())->toBe(2);
});

test('booking add-on and damage prices remain unchanged when unit prices change', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $unitAddon = UnitAddon::create([
        'unit_id' => $unit->unit_id,
        'name' => 'Extra bed',
        'price' => 500,
    ]);
    $unitDamageItem = UnitDamageItem::create([
        'unit_id' => $unit->unit_id,
        'name' => 'Electric stove',
        'price' => 3500,
    ]);
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(3),
        'check_out' => now()->addDays(5),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    $bookingAddon = BookingAddon::create([
        'booking_id' => $booking->booking_id,
        'name' => $unitAddon->name,
        'price' => $unitAddon->price,
        'quantity' => 1,
    ]);
    $bookingDamage = BookingDamage::create([
        'booking_id' => $booking->booking_id,
        'name' => $unitDamageItem->name,
        'price' => $unitDamageItem->price,
        'quantity' => 1,
    ]);

    $unitAddon->update(['price' => 600]);
    $unitDamageItem->update(['price' => 4000]);

    expect($booking->addons()->first()->price)->toBe('500.00')
        ->and($booking->damages()->first()->price)->toBe('3500.00')
        ->and($unit->addons()->first()->unit_addon_id)->toBe($unitAddon->unit_addon_id)
        ->and($unit->damageItems()->first()->unit_damage_item_id)->toBe($unitDamageItem->unit_damage_item_id)
        ->and($bookingAddon->refresh()->name)->toBe('Extra bed')
        ->and($bookingDamage->refresh()->name)->toBe('Electric stove');

    $this->actingAs($owner)
        ->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertSee('Added amenities · Extra bed')
        ->assertSee('booking-price-amount--blue', false)
        ->assertSee('Damage/Missing Items')
        ->assertSee('Electric stove')
        ->assertSee('₱4,000.00')
        ->assertDontSee('Damage/missing items · Electric stove');
});

test('checkout damage quantities are billable immediately and zero removes charges from the active bill', function () {
    Mail::fake();
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today(),
        'check_out' => today()->addDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CHECKED_IN,
    ]);
    $stove = UnitDamageItem::create([
        'unit_id' => $unit->unit_id,
        'name' => 'Electric stove',
        'price' => 3500,
    ]);
    $key = UnitDamageItem::create([
        'unit_id' => $unit->unit_id,
        'name' => 'Room key',
        'price' => 200,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertSee('Damage/Missing Items')
        ->assertSee('Electric stove')
        ->assertSee('Room key')
        ->assertSee('checkout-layout', false)
        ->assertSee('checkout-breakdown', false)
        ->assertSee('booking-price-line', false)
        ->assertSee('checkout-payment', false)
        ->assertSee('checkout-history', false)
        ->assertSee('position: fixed; inset: 0; margin: auto', false)
        ->assertSee('class="damage-card-selection" type="checkbox"', false)
        ->assertSee('data-damage-check', false)
        ->assertSee('data-damage-quantity', false)
        ->assertDontSee('Approve')
        ->assertDontSee('Waive')
        ->assertDontSee('Senior/PWD Verification')
        ->assertDontSee('Add Checkout Charge');

    $this->post(route('owner.bookings.checkout.damages.store', $booking->booking_id), [
        'items' => [
            ['unit_damage_item_id' => $stove->unit_damage_item_id, 'quantity' => 2],
            ['unit_damage_item_id' => $key->unit_damage_item_id, 'quantity' => 1],
        ],
    ])->assertSessionHas('success');

    $stoveDamage = DamageReport::query()
        ->where('booking_id', $booking->booking_id)
        ->where('description', 'Electric stove')
        ->firstOrFail();
    $keyDamage = DamageReport::query()
        ->where('booking_id', $booking->booking_id)
        ->where('description', 'Room key')
        ->firstOrFail();
    expect($stoveDamage->unit_price)->toBe('3500.00')
        ->and($stoveDamage->quantity)->toBe(2)
        ->and($stoveDamage->amount)->toBe('7000.00')
        ->and($stoveDamage->approval_status)->toBe(DamageReport::CHARGE_INCLUDED)
        ->and($booking->fresh()->amountDueAtCheckout())->toBe(10800.0);

    $this->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertSee('Damage/missing items · Electric stove')
        ->assertSee('7,000.00')
        ->assertSee('booking-price-amount--red', false)
        ->assertSee('Decrease Electric stove quantity')
        ->assertSee('Increase Electric stove quantity')
        ->assertSee('₱10,800.00')
        ->assertDontSee('Approve')
        ->assertDontSee('Reject')
        ->assertDontSee('Waive');

    $this->post(route('owner.bookings.checkout.damages.quantity', [$booking->booking_id, $stoveDamage->damage_id]), [
        'change' => 1,
    ])->assertSessionHas('success');
    expect($stoveDamage->fresh()->quantity)->toBe(3)
        ->and($stoveDamage->fresh()->amount)->toBe('10500.00')
        ->and($booking->fresh()->amountDueAtCheckout())->toBe(14300.0);

    foreach ([1, 1, 1] as $change) {
        $this->post(route('owner.bookings.checkout.damages.quantity', [$booking->booking_id, $stoveDamage->damage_id]), [
            'change' => -$change,
        ])->assertSessionHas('success');
    }

    $this->assertDatabaseHas('damage_reports', [
        'damage_id' => $stoveDamage->damage_id,
        'quantity' => 0,
        'amount' => '0.00',
        'approval_status' => DamageReport::CHARGE_INCLUDED,
    ]);
    expect($booking->fresh()->amountDueAtCheckout())->toBe(3800.0);
    $this->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertDontSee('Damage/missing items · Electric stove')
        ->assertSee('Damage/missing items · Room key')
        ->assertSee('₱3,800.00');

    $this->post(route('owner.bookings.checkout.complete', $booking->booking_id))
        ->assertSessionHasErrors('booking');
    expect($booking->fresh()->status)->toBe(BookingStatus::CHECKED_IN);

    $this->post(route('owner.bookings.checkout.payments', $booking->booking_id), [
        'payment_method' => 'Cash',
        'amount' => 3800,
    ])->assertSessionHas('success');
    $this->post(route('owner.bookings.checkout.complete', $booking->booking_id), [
        'early_checkout_reason' => 'The guest needed to leave sooner than planned.',
    ])
        ->assertRedirect(route('owner.bookings.checkout', $booking->booking_id));

    expect($booking->fresh()->status)->toBe(BookingStatus::COMPLETED)
        ->and($booking->fresh()->checked_out_at)->not->toBeNull()
        ->and($booking->fresh()->amountDueAtCheckout())->toBe(3800.0);
    $this->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertSee('This page is read-only.')
        ->assertDontSee('Increase Electric stove quantity')
        ->assertDontSee('Complete Checkout');
    expect(fn () => app(BookingWorkflowService::class)->cancelAccepted(
        $owner,
        $booking->booking_id,
        'Completed bookings cannot be cancelled.'
    ))->toThrow(ValidationException::class);
    expect(fn () => app(BookingWorkflowService::class)->accept($owner, $booking->booking_id))
        ->toThrow(ValidationException::class);
    expect($booking->fresh()->status)->toBe(BookingStatus::COMPLETED);

    $this->post(route('owner.bookings.checkout.damages.store', $booking->booking_id), [
        'items' => [['unit_damage_item_id' => $key->unit_damage_item_id, 'quantity' => 1]],
    ])->assertSessionHasErrors('booking');
    $this->post(route('owner.bookings.checkout.payments', $booking->booking_id), [
        'payment_method' => 'Cash',
        'amount' => 1,
    ])->assertSessionHasErrors('booking');
    expect($booking->damages()->count())->toBe(0)
        ->and($booking->damageReports()->count())->toBe(2)
        ->and($booking->payments()->count())->toBe(1);
});

test('a booking can have only one review with a rating from one to five', function () {
    ['unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->subDays(2),
        'check_out' => now(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::COMPLETED,
    ]);
    $review = Review::create([
        'booking_id' => $booking->booking_id,
        'unit_id' => $unit->unit_id,
        'user_id' => $guest->user_id,
        'rating' => 5,
        'comment' => 'Great stay.',
    ]);

    expect($booking->fresh()->review->review_id)->toBe($review->review_id)
        ->and($unit->reviews()->first()->review_id)->toBe($review->review_id)
        ->and($guest->reviews()->first()->review_id)->toBe($review->review_id);
    expect(fn () => Review::create([
        'booking_id' => $booking->booking_id,
        'unit_id' => $unit->unit_id,
        'user_id' => $guest->user_id,
        'rating' => 4,
    ]))->toThrow(UniqueConstraintViolationException::class);
    expect(fn () => Review::create([
        'booking_id' => Booking::create([
            'user_id' => $guest->user_id,
            'unit_id' => $unit->unit_id,
            'check_in' => now()->subDays(4),
            'check_out' => now()->subDays(2),
            'adult_count' => 1,
            'child_count' => 0,
            'total_price' => 3600,
            'status' => BookingStatus::COMPLETED,
        ])->booking_id,
        'unit_id' => $unit->unit_id,
        'user_id' => $guest->user_id,
        'rating' => 6,
    ]))->toThrow(ValidationException::class);
});

test('owner can verify discounts, record checkout charges and payment, complete stay, issue receipt, and archive', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->subDays(2),
        'check_out' => now(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    $bookingGuest = BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'user_id' => $guest->user_id,
        'full_name' => 'Booking Guest',
        'guest_type' => 'adult',
        'share_amount' => 3600,
        'is_primary_guest' => true,
    ]);
    $discount = BookingDiscount::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $bookingGuest->booking_guest_id,
        'discount_type' => 'PWD',
        'id_reference' => 'PWD-1234',
        'verification_status' => BookingStatus::PENDING,
        'eligible_amount' => 3600,
        'discount_rate' => 20,
        'discount_amount' => 720,
        'final_amount' => 2880,
        'refund_amount' => 0,
        'refund_status' => 'None',
    ]);
    Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now()->subDay(),
    ]);
    $this->actingAs($owner)
        ->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertOk()
        ->assertSee('Checkout Breakdown')
        ->assertSee('Stay/room charge')
        ->assertDontSee('Senior / PWD Verification')
        ->assertDontSee('Add Checkout Charge')
        ->assertSee('Record Payment');

    $this->post(route('owner.discounts.review', $discount->discount_id), ['status' => 'Verified'])
        ->assertSessionHas('success');
    expect($discount->fresh()->verification_status)->toBe('Verified')
        ->and($discount->fresh()->verified_by)->toBe($owner->user_id)
        ->and($booking->fresh()->total_price)->toBe('2880.00');

    $this->post(route('owner.bookings.checkout.charges', $booking->booking_id), [
        'description' => 'Broken plate',
        'quantity' => 2,
        'unit_price' => 150,
    ])->assertSessionHas('success');
    $this->post(route('owner.bookings.checkout.damages.custom', $booking->booking_id), [
        'description' => 'Damaged bedside lamp',
        'quantity' => 1,
        'unit_price' => 80,
    ])->assertSessionHas('success');
    $temporaryCharge = AdditionalCharge::create([
        'booking_id' => $booking->booking_id,
        'created_by' => $owner->user_id,
        'description' => 'Incorrectly entered charge',
        'quantity' => 1,
        'unit_price' => 5,
        'total_amount' => 5,
    ]);
    $this->post(route('owner.bookings.checkout.charges.quantity', [$booking->booking_id, $temporaryCharge->additional_charge_id]), [
        'change' => -1,
    ])->assertSessionHas('success');
    $this->assertDatabaseHas('additional_charges', [
        'additional_charge_id' => $temporaryCharge->additional_charge_id,
        'quantity' => 0,
        'total_amount' => '0.00',
    ]);

    $this->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertOk()
        ->assertSee('Damage/missing items · Broken plate')
        ->assertSee('Damage/missing items · Damaged bedside lamp')
        ->assertSee('₱1,460.00')
        ->assertSee('Balance to pay')
        ->assertSee('Record Payment');
    $this->actingAs($guest)
        ->get(route('guest.bookings.show', $booking->booking_id))
        ->assertOk()
        ->assertSee('Broken plate')
        ->assertSee('Damaged bedside lamp')
        ->assertSee('Balance due at checkout')
        ->assertSee('₱1,460.00');
    $this->get(route('guest.bookings.payment', $booking->booking_id))
        ->assertRedirect(route('guest.bookings.show', $booking->booking_id))
        ->assertSessionHas('info', 'A downpayment can only be submitted while an accepted booking is awaiting payment.');

    $this->actingAs($owner)
        ->post(route('owner.bookings.checkout.complete', $booking->booking_id))
        ->assertSessionHasErrors('booking');
    expect($booking->fresh()->status)->toBe(BookingStatus::CONFIRMED);
    $this->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertOk()
        ->assertSee('Pay the remaining balance before completing checkout.')
        ->assertSee('disabled', false);

    $this->post(route('owner.bookings.checkout.payments', $booking->booking_id), [
        'payment_method' => 'Cash',
        'amount' => 1461,
    ])->assertSessionHasErrors('amount');
    $this->post(route('owner.bookings.checkout.payments', $booking->booking_id), [
        'payment_method' => 'Cash',
        'amount' => 1460,
        'notes' => 'Balance paid at checkout',
    ])->assertSessionHas('success');
    $this->assertDatabaseHas('additional_charges', [
        'booking_id' => $booking->booking_id,
        'description' => 'Broken plate',
        'total_amount' => '300.00',
    ]);
    $this->assertDatabaseHas('payments', [
        'booking_id' => $booking->booking_id,
        'payment_method' => 'Cash',
        'amount' => '1460.00',
        'payment_status' => PaymentStatus::VERIFIED,
    ]);
    $this->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertOk()
        ->assertSee('Balance to pay')
        ->assertSee('₱0.00');

    $this->post(route('owner.bookings.checkout.complete', $booking->booking_id))
        ->assertRedirect(route('owner.bookings.checkout', $booking->booking_id));
    expect($booking->fresh()->status)->toBe(BookingStatus::COMPLETED)
        ->and($booking->fresh()->checked_out_at)->not->toBeNull();
    $this->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertOk()
        ->assertSee('This page is read-only.')
        ->assertDontSee('Complete Checkout')
        ->assertDontSee('Damage/Missing Items');
    $this->assertDatabaseHas('booking_receipts', [
        'booking_id' => $booking->booking_id,
        'receipt_number' => 'MCTBS-RCP-'.str_pad((string) $booking->booking_id, 8, '0', STR_PAD_LEFT),
    ]);
    $this->actingAs($guest)
        ->get(route('guest.bookings.show', $booking->booking_id))
        ->assertSee('booking-breakdown-action', false)
        ->assertSee('class="btn btn-primary">View Booking Breakdown', false);

    $receiptResponse = $this->actingAs($guest)
        ->get(route('guest.bookings.receipt', $booking->booking_id))
        ->assertOk()
        ->assertDontSee('Rental packages')
        ->assertSee('Booking Breakdown')
        ->assertSee('booking-price-line', false)
        ->assertSee('Payments made')
        ->assertSee('This is not an official receipt. An official receipt will be given by the staff at check-out.')
        ->assertSee('Broken plate')
        ->assertSee('Download PDF')
        ->assertDontSee('<span>Guests</span>', false)
        ->assertDontSee('Print');
    expect($receiptResponse->getContent())->not->toContain('booking-breakdown-grid');

    $pdfResponse = $this->get(route('guest.bookings.receipt.pdf', $booking->booking_id))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertHeader('content-disposition');
    expect($pdfResponse->getContent())
        ->toStartWith('%PDF');
    expect($pdfResponse->headers->get('content-disposition'))
        ->toContain('Booking-Breakdown-'.$booking->receipt->receipt_number.'.pdf');

    $otherGuest = User::create([
        'role_id' => Role::where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Other',
        'last_name' => 'Guest',
        'email' => 'other-receipt-guest@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);
    $this->actingAs($otherGuest)
        ->get(route('guest.bookings.receipt', $booking->booking_id))
        ->assertNotFound();
    $this->get(route('guest.bookings.receipt.pdf', $booking->booking_id))
        ->assertNotFound();

    $this->actingAs($owner)
        ->post(route('owner.bookings.archive', $booking->booking_id), ['note' => 'Stay closed'])
        ->assertRedirect(route('owner.bookings.index', ['tab' => 'archive']));
    $this->assertDatabaseHas('transaction_archives', [
        'booking_id' => $booking->booking_id,
        'archived_by' => $owner->user_id,
        'archive_note' => 'Stay closed',
    ]);
    $this->get(route('owner.bookings.index', ['tab' => 'archive']))
        ->assertOk()
        ->assertSee('Archive')
        ->assertSee('class="tab-pane active" id="archive-bookings"', false)
        ->assertSee('#'.$booking->booking_id)
        ->assertDontSee('<th>Archived</th>', false)
        ->assertDontSee('<th>Archive note</th>', false)
        ->assertDontSee('Stay closed');
});

test('checkout damage items merge by item and saved price while billing immediately', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today(),
        'check_out' => today()->addDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CHECKED_IN,
    ]);
    $item = UnitDamageItem::create([
        'unit_id' => $unit->unit_id,
        'name' => 'Room key',
        'price' => 200,
    ]);

    $this->actingAs($owner)
        ->post(route('owner.bookings.checkout.damages.store', $booking->booking_id), [
            'items' => [['unit_damage_item_id' => $item->unit_damage_item_id, 'quantity' => 2]],
        ])
        ->assertRedirect(route('owner.bookings.checkout', $booking->booking_id));
    $this->post(route('owner.bookings.checkout.damages.store', $booking->booking_id), [
        'items' => [['unit_damage_item_id' => $item->unit_damage_item_id, 'quantity' => 3]],
    ])->assertRedirect(route('owner.bookings.checkout', $booking->booking_id));

    $item->update(['price' => 250]);
    $this->post(route('owner.bookings.checkout.damages.store', $booking->booking_id), [
        'items' => [['unit_damage_item_id' => $item->unit_damage_item_id, 'quantity' => 1]],
    ])->assertRedirect(route('owner.bookings.checkout', $booking->booking_id));

    expect($booking->fresh()->damageReports)->toHaveCount(2);
    $this->assertDatabaseHas('damage_reports', [
        'booking_id' => $booking->booking_id,
        'unit_damage_item_id' => $item->unit_damage_item_id,
        'description' => 'Room key',
        'unit_price' => '200.00',
        'amount' => '1000.00',
        'quantity' => 5,
        'approval_status' => DamageReport::CHARGE_INCLUDED,
    ]);
    $this->assertDatabaseHas('damage_reports', [
        'booking_id' => $booking->booking_id,
        'unit_damage_item_id' => $item->unit_damage_item_id,
        'description' => 'Room key',
        'unit_price' => '250.00',
        'amount' => '250.00',
        'quantity' => 1,
        'approval_status' => DamageReport::CHARGE_INCLUDED,
    ]);
    expect($booking->fresh()->amountDueAtCheckout())->toBe(4850.0);
});

test('catalog and custom damage charges support authorized quantity changes and preserve zero-quantity history', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today(),
        'check_out' => today()->addDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1000,
        'status' => BookingStatus::CHECKED_IN,
    ]);
    $catalogItem = UnitDamageItem::create([
        'unit_id' => $unit->unit_id,
        'name' => 'Broken chair',
        'price' => 100,
    ]);

    $this->actingAs($owner)
        ->post(route('owner.bookings.checkout.damages.store', $booking->booking_id), [
            'items' => [['unit_damage_item_id' => $catalogItem->unit_damage_item_id, 'quantity' => 2]],
        ])
        ->assertSessionHas('success');
    $this->post(route('owner.bookings.checkout.damages.store', $booking->booking_id), [
        'items' => [['unit_damage_item_id' => $catalogItem->unit_damage_item_id, 'quantity' => 1]],
    ])->assertSessionHas('success');
    $this->post(route('owner.bookings.checkout.damages.custom', $booking->booking_id), [
        'description' => 'Broken lamp',
        'quantity' => 2,
        'unit_price' => 50,
    ])->assertSessionHas('success');
    $this->post(route('owner.bookings.checkout.damages.custom', $booking->booking_id), [
        'description' => 'Invalid charge',
        'quantity' => 1,
        'unit_price' => 0,
    ])->assertSessionHasErrors('unit_price');
    $this->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertSee('Damage/Missing Items')
        ->assertSee('Broken lamp')
        ->assertDontSee('Custom damage or missing item')
        ->assertDontSee('custom-damage-description', false)
        ->assertDontSee('Approve')
        ->assertDontSee('Reject')
        ->assertDontSee('Waive');

    $catalogDamage = $booking->damageReports()
        ->where('unit_damage_item_id', $catalogItem->unit_damage_item_id)
        ->firstOrFail();
    $customDamage = $booking->damageReports()
        ->whereNull('unit_damage_item_id')
        ->firstOrFail();
    expect($booking->damageReports()->count())->toBe(2)
        ->and($catalogDamage->quantity)->toBe(3)
        ->and($catalogDamage->amount)->toBe('300.00')
        ->and($catalogDamage->reported_by)->toBe($owner->user_id)
        ->and($catalogDamage->approval_status)->toBe(DamageReport::CHARGE_INCLUDED)
        ->and($customDamage->amount)->toBe('100.00')
        ->and($customDamage->approval_status)->toBe(DamageReport::CHARGE_INCLUDED)
        ->and($booking->fresh()->amountDueAtCheckout())->toBe(1400.0);

    $otherOwner = User::create([
        'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Other',
        'last_name' => 'Owner',
        'email' => 'other-damage-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $this->actingAs($otherOwner)
        ->post(route('owner.bookings.checkout.damages.quantity', [$booking->booking_id, $catalogDamage->damage_id]), [
            'change' => -1,
        ])
        ->assertNotFound();

    $this->actingAs($owner)
        ->post(route('owner.bookings.checkout.damages.quantity', [$booking->booking_id, $catalogDamage->damage_id]), [
            'change' => -1,
        ])->assertSessionHas('success');
    $this->post(route('owner.bookings.checkout.damages.quantity', [$booking->booking_id, $customDamage->damage_id]), [
        'change' => -1,
    ])->assertSessionHas('success');
    $this->post(route('owner.bookings.checkout.damages.quantity', [$booking->booking_id, $customDamage->damage_id]), [
        'change' => -1,
    ])->assertSessionHas('success');

    $this->assertDatabaseHas('damage_reports', [
        'damage_id' => $customDamage->damage_id,
        'quantity' => 0,
        'amount' => '0.00',
        'approval_status' => DamageReport::CHARGE_INCLUDED,
    ]);
    expect($catalogDamage->fresh()->quantity)->toBe(2)
        ->and($booking->fresh()->amountDueAtCheckout())->toBe(1200.0)
        ->and($booking->damageReports()->count())->toBe(2);

    $this->post(route('owner.bookings.checkout.damages.quantity', [$booking->booking_id, $customDamage->damage_id]), [
        'change' => -1,
    ])->assertSessionHasErrors('quantity');
    $this->post(route('owner.bookings.checkout.damages.custom', $booking->booking_id), [
        'description' => 'Broken lamp',
        'quantity' => 1,
        'unit_price' => 50,
    ])->assertSessionHas('success');

    expect($customDamage->fresh()->quantity)->toBe(1)
        ->and($customDamage->fresh()->amount)->toBe('50.00')
        ->and($booking->fresh()->amountDueAtCheckout())->toBe(1250.0)
        ->and($booking->damageReports()->count())->toBe(2);
});

test('verified checkout discount refunds the excess from a full downpayment payment', function () {
    Mail::fake();
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => now()->addDays(3)->toDateString(),
        'check_out' => now()->addDays(5)->toDateString(),
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Senior Guest']],
    ]);
    $workflow = app(BookingWorkflowService::class);
    $workflow->accept($owner, $booking->booking_id);

    $this->actingAs($guest)
        ->post(route('guest.bookings.payments.store', $booking->booking_id), [
            'payment_method' => 'GCash',
            'amount' => 3600,
            'reference_number' => 'GCASH-FULL-DOWNPAYMENT-001',
            'payment_receipt' => UploadedFile::fake()->createWithContent('proof.pdf', "%PDF-1.4\nProof"),
        ])
        ->assertRedirect(route('guest.bookings.show', $booking->booking_id))
        ->assertSessionHas('success');
    $payment = $booking->payments()->firstOrFail();
    $this->actingAs($owner)
        ->post(route('owner.payments.review', $payment->payment_id), [
            'status' => PaymentStatus::VERIFIED,
        ])
        ->assertSessionHas('success');
    expect($booking->fresh()->status)->toBe(BookingStatus::CONFIRMED);

    $bookingGuest = BookingGuest::where('booking_id', $booking->booking_id)->firstOrFail();
    $discount = BookingDiscount::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $bookingGuest->booking_guest_id,
        'discount_type' => 'Senior Citizen',
        'id_reference' => 'SC-PAID',
        'verification_status' => BookingStatus::PENDING,
        'eligible_amount' => 3600,
        'discount_rate' => 20,
        'discount_amount' => 720,
        'final_amount' => 2880,
        'refund_amount' => 0,
        'refund_status' => 'None',
    ]);

    $this->actingAs($owner)
        ->post(route('owner.discounts.review', $discount->discount_id), ['status' => 'Verified'])
        ->assertSessionHas('success');

    expect($booking->fresh()->total_price)->toBe('2880.00')
        ->and($discount->fresh()->refund_amount)->toBe('0.00')
        ->and($discount->fresh()->refund_status)->toBe('None');
    $this->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertOk()
        ->assertSee('Senior/PWD discount')
        ->assertSee('₱720.00');
    $this->get(route('owner.bookings.show', $booking->booking_id))
        ->assertOk()
        ->assertDontSee('class="card-header">Refunds', false);

    $this->post(route('owner.bookings.checkout.complete', $booking->booking_id))
        ->assertRedirect(route('owner.bookings.checkout', $booking->booking_id))
        ->assertSessionHas('success', 'Checkout completed. The refund is ready to return to the Guest from the bill breakdown.');
    $refund = Refund::where('booking_id', $booking->booking_id)->firstOrFail();
    expect($discount->fresh()->refund_amount)->toBe('720.00')
        ->and($discount->fresh()->refund_status)->toBe('Pending');
    $this->assertDatabaseHas('refunds', [
        'booking_id' => $booking->booking_id,
        'payment_id' => Payment::where('booking_id', $booking->booking_id)->value('payment_id'),
        'amount' => '720.00',
        'refund_status' => 'Pending',
    ]);

    $this->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertOk()
        ->assertSee('Refund History')
        ->assertSee('₱720.00')
        ->assertSee('Pending')
        ->assertSee('This page is read-only.')
        ->assertDontSee(route('owner.bookings.checkout.refunds.process', [$booking->booking_id, $refund->refund_id]));
    $this->post(route('owner.bookings.checkout.refunds.process', [$booking->booking_id, $refund->refund_id]), [
        'refund_reference' => 'GCASH-REFUND-720',
    ])->assertRedirect(route('owner.bookings.checkout', $booking->booking_id))
        ->assertSessionHas('success', 'The refund was marked as issued and the Guest was notified.');

    $refund->refresh();
    expect($refund->refund_status)->toBe('Refunded')
        ->and($refund->processed_by)->toBe($owner->user_id)
        ->and($refund->refund_reference)->toBe('GCASH-REFUND-720')
        ->and($refund->processed_at)->not->toBeNull()
        ->and($discount->fresh()->refund_status)->toBe('Refunded');
    $this->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertOk()
        ->assertSee('Refund History')
        ->assertSee('Refunds')
        ->assertSee('booking-price-amount--orange', false)
        ->assertSee('Balance to pay')
        ->assertSee('₱0.00')
        ->assertDontSee('-₱720.00');
    $this->assertDatabaseHas('notifications', [
        'user_id' => $guest->user_id,
        'booking_id' => $booking->booking_id,
        'type' => 'refund_issued',
    ]);

    $this->actingAs($guest)
        ->get(route('guest.bookings.receipt', $booking->booking_id))
        ->assertOk()
        ->assertSee('Refund status')
        ->assertSee('₱720.00 · Refunded')
        ->assertSee('GCASH-REFUND-720');
});

test('rejecting a discount leaves the booking total unchanged', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(5),
        'check_out' => now()->addDays(7),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    $bookingGuest = BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'user_id' => $guest->user_id,
        'full_name' => 'PWD Guest',
        'guest_type' => 'adult',
        'share_amount' => 3600,
        'is_primary_guest' => true,
    ]);
    $discount = BookingDiscount::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $bookingGuest->booking_guest_id,
        'discount_type' => 'PWD',
        'id_reference' => 'PWD-REVIEW',
        'verification_status' => BookingStatus::PENDING,
        'eligible_amount' => 3600,
        'discount_rate' => 20,
        'discount_amount' => 720,
        'final_amount' => 2880,
        'refund_amount' => 0,
        'refund_status' => 'None',
    ]);

    $this->actingAs($owner)
        ->post(route('owner.discounts.review', $discount->discount_id), [
            'status' => 'Rejected',
            'verification_note' => 'The ID could not be verified.',
        ])
        ->assertSessionHas('success');

    expect($booking->fresh()->total_price)->toBe('3600.00')
        ->and($discount->fresh()->verification_status)->toBe('Rejected')
        ->and($discount->fresh()->verification_note)->toBe('The ID could not be verified.')
        ->and($discount->fresh()->verified_by)->toBe($owner->user_id);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $guest->user_id,
        'booking_id' => $booking->booking_id,
        'type' => 'discount_Rejected',
        'message' => 'The PWD ID for PWD Guest was not verified at checkout. Reason: The ID could not be verified.',
    ]);
});

test('checkout cannot complete while a submitted payment is under review', function () {
    ['owner' => $owner, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(5),
        'check_out' => now()->addDays(7),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 1800,
        'payment_status' => PaymentStatus::UNDER_REVIEW,
        'payment_date' => now(),
    ]);

    $this->actingAs($owner)
        ->post(route('owner.bookings.checkout.complete', $booking->booking_id))
        ->assertSessionHasErrors('booking');

    expect($booking->fresh()->status)->toBe(BookingStatus::CONFIRMED);
    $this->assertDatabaseMissing('booking_receipts', ['booking_id' => $booking->booking_id]);
});

test('staff work queues and checkout actions are restricted to assigned properties', function () {
    ['owner' => $owner, 'house' => $house, 'unit' => $unit, 'guest' => $guest] = makeGuestBookingScenario();
    $staff = User::create([
        'role_id' => Role::where('name', 'Staff')->firstOrFail()->role_id,
        'first_name' => 'Assigned',
        'last_name' => 'Staff',
        'email' => 'checkout-staff@example.com',
        'password' => 'StaffPass123!',
        'is_active' => true,
    ]);
    Invitation::create([
        'invited_by' => $owner->user_id,
        'house_id' => $house->house_id,
        'role_id' => Role::where('name', 'Staff')->firstOrFail()->role_id,
        'email' => $staff->email,
        'token' => 'checkout-staff-assignment',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(5),
        'check_out' => now()->addDays(7),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);

    $this->actingAs($staff)
        ->get(route('staff.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard');
    $this->get(route('staff.bookings.checkout', $booking->booking_id))->assertOk();
    $damageItem = UnitDamageItem::create([
        'unit_id' => $unit->unit_id,
        'name' => 'Staff-reported lamp',
        'price' => 75,
    ]);
    $this->post(route('staff.bookings.checkout.damages.store', $booking->booking_id), [
        'items' => [['unit_damage_item_id' => $damageItem->unit_damage_item_id, 'quantity' => 2]],
    ])->assertRedirect(route('staff.bookings.checkout', $booking->booking_id));
    $this->assertDatabaseHas('damage_reports', [
        'booking_id' => $booking->booking_id,
        'description' => 'Staff-reported lamp',
        'quantity' => 2,
        'unit_price' => '75.00',
        'amount' => '150.00',
        'approval_status' => DamageReport::CHARGE_INCLUDED,
    ]);

    $otherStaff = User::create([
        'role_id' => Role::where('name', 'Staff')->firstOrFail()->role_id,
        'first_name' => 'Unassigned',
        'last_name' => 'Staff',
        'email' => 'unassigned-checkout@example.com',
        'password' => 'StaffPass123!',
        'is_active' => true,
    ]);
    $this->actingAs($otherStaff)
        ->get(route('staff.bookings.checkout', $booking->booking_id))
        ->assertNotFound();
});

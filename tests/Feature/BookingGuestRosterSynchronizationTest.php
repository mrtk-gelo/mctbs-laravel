<?php

use App\Models\Booking;
use App\Models\BookingDiscount;
use App\Models\BookingGuest;
use App\Models\BookingGuestEvent;
use App\Models\Invitation;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\User;
use App\Services\BookingService;
use App\Services\BookingWorkflowService;
use App\Support\BookingStatus;
use App\Support\PaymentStatus;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

uses(RefreshDatabase::class);

function makeGuestRosterSyncScenario(
    string $status = BookingStatus::PENDING,
    int $payingGuestCount = 1,
    int $freeChildCount = 0,
    int $capacity = 2,
    int $maximumCapacity = 6,
    string $pricingMode = 'Per Unit',
    float $price = 100,
    float $extraGuestFee = 20,
    array $payingGuestTypes = [],
    array $attendanceStatuses = []
): array {
    $owner = User::create([
        'role_id' => Role::query()->where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Roster',
        'last_name' => 'Owner',
        'email' => 'roster-sync-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $guest = User::create([
        'role_id' => Role::query()->where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Roster',
        'last_name' => 'Guest',
        'email' => 'roster-sync-guest@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Roster Sync House',
        'address' => '10 Test Street',
        'city' => 'Baguio',
        'contact_number' => '5551000',
        'is_active' => true,
    ]);
    $unit = Unit::create([
        'house_id' => $house->house_id,
        'unit_type' => 'Roster Sync Room',
        'capacity' => $capacity,
        'maximum_capacity' => $maximumCapacity,
        'child_free_through_age' => 5,
        'pricing_mode' => $pricingMode,
        'price' => $price,
        'extra_guest_fee' => $extraGuestFee,
        'status' => 'Available',
    ]);
    $checkIn = $status === BookingStatus::CHECKED_IN ? today() : today()->addDays(10);
    $checkOut = $checkIn->copy()->addDays(2);
    $pricing = app(BookingService::class)->calculate(
        $unit,
        $checkIn->toDateString(),
        $checkOut->toDateString(),
        $payingGuestCount
    );
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'adult_count' => $payingGuestCount,
        'child_count' => $freeChildCount,
        'total_price' => $pricing['total'],
        'pricing_mode' => $pricingMode,
        'nightly_rate' => $price,
        'room_subtotal' => $pricing['room_subtotal'],
        'extra_guest_count' => $pricing['extra_guest_count'],
        'extra_guest_fee' => $extraGuestFee,
        'extra_guest_total' => $pricing['extra_guest_total'],
        'status' => $status,
    ]);
    $shares = app(BookingService::class)->allocateGuestShares($pricing, $payingGuestCount);

    for ($index = 1; $index <= $payingGuestCount; $index++) {
        BookingGuest::create([
            'booking_id' => $booking->booking_id,
            'full_name' => 'Paying Guest '.$index,
            'guest_type' => $payingGuestTypes[$index - 1] ?? 'adult',
            'is_free_child' => false,
            'share_amount' => $shares[$index - 1],
            'is_primary_guest' => $index === 1,
            'attendance_status' => $attendanceStatuses[$index - 1] ?? BookingGuest::ATTENDANCE_EXPECTED,
            'identity_verification_status' => BookingGuest::IDENTITY_UNVERIFIED,
        ]);
    }

    for ($index = 1; $index <= $freeChildCount; $index++) {
        BookingGuest::create([
            'booking_id' => $booking->booking_id,
            'full_name' => 'Free Child '.$index,
            'guest_type' => 'child',
            'age' => 5,
            'is_free_child' => true,
            'share_amount' => '0.00',
            'is_primary_guest' => false,
            'attendance_status' => BookingGuest::ATTENDANCE_EXPECTED,
            'identity_verification_status' => BookingGuest::IDENTITY_UNVERIFIED,
        ]);
    }

    return compact('owner', 'guest', 'house', 'unit', 'booking');
}

function addGuestRosterSyncDiscount(Booking $booking, BookingGuest $guest, float $amount, string $status = 'Pending'): BookingDiscount
{
    return BookingDiscount::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $guest->booking_guest_id,
        'discount_type' => 'PWD',
        'id_reference' => 'PWD-ROSTER-'.$guest->booking_guest_id,
        'verification_status' => $status,
        'eligible_amount' => $guest->share_amount,
        'discount_rate' => 20,
        'discount_amount' => $amount,
        'final_amount' => (float) $guest->share_amount - $amount,
        'refund_amount' => 0,
        'refund_status' => 'None',
    ]);
}

function editGuestRosterSyncBooking(User $actor, Booking $booking, array $counts, ?BookingService $bookingService = null): Booking
{
    return app(BookingWorkflowService::class)->editBooking(
        $actor,
        $booking->booking_id,
        [
            'check_in' => $booking->check_in->toDateString(),
            'check_out' => $booking->check_out->toDateString(),
            ...$counts,
        ],
        'Guest requested a booking adjustment.',
        $bookingService ?? app(BookingService::class)
    );
}

test('pre-check-in edits append paying and free-child guests with exact active shares', function () {
    ['owner' => $owner, 'booking' => $booking] = makeGuestRosterSyncScenario(
        payingGuestCount: 1,
        capacity: 1,
        maximumCapacity: 5
    );
    $initialGuest = $booking->guests()->firstOrFail();
    $discount = addGuestRosterSyncDiscount($booking, $initialGuest, 40);

    editGuestRosterSyncBooking($owner, $booking, ['adult_count' => 3, 'child_count' => 1]);

    $activeGuests = $booking->activeGuests()->orderBy('booking_guest_id')->get();
    $payingGuests = $activeGuests->where('is_free_child', false);
    $freeChildren = $activeGuests->where('is_free_child', true);
    $payingShares = $payingGuests->pluck('share_amount')->map(fn ($amount): float => (float) $amount);

    expect($activeGuests)->toHaveCount(4)
        ->and($payingShares->all())->toBe([93.33, 93.33, 93.34])
        ->and(round($payingShares->sum(), 2))->toBe(280.0)
        ->and($freeChildren->first()->share_amount)->toBe('0.00')
        ->and($booking->fresh()->extra_guest_total)->toBe('80.00')
        ->and($booking->fresh()->total_price)->toBe('280.00');
    expect($discount->fresh()->booking_guest_id)->toBe($initialGuest->booking_guest_id)
        ->and($discount->fresh()->discount_amount)->toBe('18.67');
    expect(BookingDiscount::query()
        ->whereIn('booking_guest_id', $activeGuests->where('booking_guest_id', '>', 1)->modelKeys())
        ->exists())->toBeFalse();
});

test('reductions retire highest-id eligible guests and preserve their history and shares', function () {
    ['owner' => $owner, 'booking' => $booking] = makeGuestRosterSyncScenario(payingGuestCount: 3);
    $guests = $booking->guests()->orderBy('booking_guest_id')->get();
    $removedGuest = $guests->last();
    $removedGuest->update(['share_amount' => '31.25']);
    $discount = addGuestRosterSyncDiscount($booking, $removedGuest, 6.25, 'Verified');
    $replacement = BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'full_name' => 'Historical Replacement',
        'guest_type' => 'adult',
        'share_amount' => '31.25',
        'roster_status' => BookingGuest::ROSTER_REMOVED,
        'replaces_booking_guest_id' => $removedGuest->booking_guest_id,
    ]);
    $priorEvent = BookingGuestEvent::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $removedGuest->booking_guest_id,
        'related_booking_guest_id' => $replacement->booking_guest_id,
        'event_type' => BookingGuestEvent::TYPE_REPLACEMENT,
        'reason' => 'Replacement relationship was recorded.',
        'event_at' => now(),
    ]);

    editGuestRosterSyncBooking($owner, $booking, ['adult_count' => 2, 'child_count' => 0]);

    $removedGuest->refresh();
    $removalEvent = $removedGuest->events()
        ->where('event_type', BookingGuestEvent::TYPE_ROSTER_REMOVAL)
        ->firstOrFail();
    expect($removedGuest->roster_status)->toBe(BookingGuest::ROSTER_REMOVED)
        ->and($removedGuest->share_amount)->toBe('31.25')
        ->and($removedGuest->removed_at)->not->toBeNull()
        ->and($removedGuest->removed_by)->toBe($owner->user_id)
        ->and($removedGuest->removal_reason)->toBe('Guest requested a booking adjustment.')
        ->and($removedGuest->discounts->modelKeys())->toBe([$discount->discount_id])
        ->and($removedGuest->replacements->modelKeys())->toContain($replacement->booking_guest_id)
        ->and($removedGuest->events->modelKeys())->toContain($priorEvent->booking_guest_event_id)
        ->and($removalEvent->performed_by)->toBe($owner->user_id)
        ->and($removalEvent->reason)->toBe('Guest requested a booking adjustment.')
        ->and($removalEvent->after_state['roster_status'])->toBe(BookingGuest::ROSTER_REMOVED)
        ->and($removedGuest->attendance_status)->toBe(BookingGuest::ATTENDANCE_EXPECTED)
        ->and($removedGuest->identity_verification_status)->toBe(BookingGuest::IDENTITY_UNVERIFIED);
});

test('free-child reductions retire only free children and retain paying children', function () {
    ['owner' => $owner, 'booking' => $booking] = makeGuestRosterSyncScenario(
        payingGuestCount: 1,
        freeChildCount: 1,
        payingGuestTypes: ['child']
    );
    $payingChild = $booking->guests()->where('is_free_child', false)->firstOrFail();
    $freeChild = $booking->guests()->where('is_free_child', true)->firstOrFail();

    editGuestRosterSyncBooking($owner, $booking, ['adult_count' => 1, 'child_count' => 0]);

    expect($payingChild->fresh()->roster_status)->toBe(BookingGuest::ROSTER_ACTIVE)
        ->and($payingChild->fresh()->is_free_child)->toBeFalse()
        ->and($freeChild->fresh()->roster_status)->toBe(BookingGuest::ROSTER_REMOVED)
        ->and($freeChild->fresh()->share_amount)->toBe('0.00')
        ->and($booking->activeGuests()->count())->toBe(1);
});

test('checked-in edits allow increases but reject paying or free-child reductions', function () {
    ['owner' => $owner, 'booking' => $booking] = makeGuestRosterSyncScenario(
        status: BookingStatus::CHECKED_IN,
        payingGuestCount: 1
    );

    editGuestRosterSyncBooking($owner, $booking, ['adult_count' => 2, 'child_count' => 1]);
    $countsAfterIncrease = [$booking->fresh()->adult_count, $booking->fresh()->child_count];
    $activeCountAfterIncrease = $booking->activeGuests()->count();

    $this->actingAs($owner)
        ->from(route('owner.bookings.edit', $booking->booking_id))
        ->put(route('owner.bookings.update', $booking->booking_id), [
            'check_in' => today()->toDateString(),
            'check_out' => today()->addDays(2)->toDateString(),
            'adult_count' => 1,
            'child_count' => 1,
            'reason' => 'Guest requested a count reduction.',
        ])
        ->assertRedirect(route('owner.bookings.edit', $booking->booking_id))
        ->assertSessionHasErrors([
            'adult_count' => 'Guest-count reductions cannot be made after check-in.',
        ]);

    expect(fn () => editGuestRosterSyncBooking($owner, $booking, ['adult_count' => 1, 'child_count' => 1]))
        ->toThrow(ValidationException::class);
    expect(fn () => editGuestRosterSyncBooking($owner, $booking, ['adult_count' => 2, 'child_count' => 0]))
        ->toThrow(ValidationException::class);

    expect([$booking->fresh()->adult_count, $booking->fresh()->child_count])->toBe($countsAfterIncrease)
        ->and($booking->activeGuests()->count())->toBe($activeCountAfterIncrease);
});

test('arrived guests are never retired during a reduction', function () {
    ['owner' => $owner, 'booking' => $booking] = makeGuestRosterSyncScenario(
        payingGuestCount: 2,
        attendanceStatuses: [
            BookingGuest::ATTENDANCE_EXPECTED,
            BookingGuest::ATTENDANCE_ARRIVED,
        ]
    );
    $arrivedGuest = $booking->guests()->where('attendance_status', BookingGuest::ATTENDANCE_ARRIVED)->firstOrFail();

    editGuestRosterSyncBooking($owner, $booking, ['adult_count' => 1, 'child_count' => 0]);

    expect($arrivedGuest->fresh()->roster_status)->toBe(BookingGuest::ROSTER_ACTIVE)
        ->and($arrivedGuest->fresh()->attendance_status)->toBe(BookingGuest::ATTENDANCE_ARRIVED)
        ->and($booking->activeGuests()->where('attendance_status', BookingGuest::ATTENDANCE_EXPECTED)->exists())->toBeFalse();
});

test('reductions fail without mutation when every excess guest has arrived', function () {
    ['owner' => $owner, 'booking' => $booking] = makeGuestRosterSyncScenario(
        payingGuestCount: 2,
        attendanceStatuses: [
            BookingGuest::ATTENDANCE_ARRIVED,
            BookingGuest::ATTENDANCE_ARRIVED,
        ]
    );

    expect(fn () => editGuestRosterSyncBooking($owner, $booking, ['adult_count' => 1, 'child_count' => 0]))
        ->toThrow(ValidationException::class);
    expect($booking->fresh()->adult_count)->toBe(2)
        ->and($booking->activeGuests()->count())->toBe(2)
        ->and($booking->guestEvents()->count())->toBe(0);
});

test('active paying shares retain the per-head formula and free children remain zero', function () {
    ['owner' => $owner, 'booking' => $booking] = makeGuestRosterSyncScenario(
        payingGuestCount: 1,
        freeChildCount: 1,
        pricingMode: 'Per Head',
        price: 75
    );

    editGuestRosterSyncBooking($owner, $booking, ['adult_count' => 2, 'child_count' => 2]);

    expect($booking->fresh()->total_price)->toBe('300.00')
        ->and($booking->activeGuests()->where('is_free_child', false)->pluck('share_amount')->all())
        ->toBe(['150.00', '150.00'])
        ->and($booking->activeGuests()->where('is_free_child', true)->pluck('share_amount')->all())
        ->toBe(['0.00', '0.00']);
});

test('removed guests keep their discount values and cannot have a pending discount verified', function () {
    ['owner' => $owner, 'booking' => $booking] = makeGuestRosterSyncScenario(
        status: BookingStatus::CHECKED_IN,
        payingGuestCount: 1
    );
    $guest = $booking->guests()->firstOrFail();
    $discount = addGuestRosterSyncDiscount($booking, $guest, 20);
    $guest->update(['roster_status' => BookingGuest::ROSTER_REMOVED]);

    expect(fn () => app(BookingWorkflowService::class)->reviewDiscount(
        $owner,
        $discount->discount_id,
        'Verified'
    ))->toThrow(ValidationException::class);
    $this->actingAs($owner)
        ->get(route('owner.guests.check-in', $booking->booking_id))
        ->assertSee('This guest was removed from the reservation, so the discount cannot be verified.')
        ->assertDontSee('Verify');

    expect($discount->fresh()->verification_status)->toBe('Pending')
        ->and($discount->fresh()->discount_amount)->toBe('20.00')
        ->and($booking->fresh()->total_price)->toBe('200.00');
});

test('editing recalculates the bill from active discounts once and preserves removed shares', function () {
    ['owner' => $owner, 'booking' => $booking] = makeGuestRosterSyncScenario(payingGuestCount: 2);
    $activeGuest = $booking->guests()->orderBy('booking_guest_id')->firstOrFail();
    $removedGuest = $booking->guests()->orderByDesc('booking_guest_id')->firstOrFail();
    $activeDiscount = addGuestRosterSyncDiscount($booking, $activeGuest, 20, 'Verified');
    $removedDiscount = addGuestRosterSyncDiscount($booking, $removedGuest, 30, 'Verified');
    $removedGuest->update([
        'roster_status' => BookingGuest::ROSTER_REMOVED,
        'share_amount' => '55.00',
    ]);

    editGuestRosterSyncBooking($owner, $booking, ['adult_count' => 1, 'child_count' => 0]);

    expect($booking->fresh()->total_price)->toBe('160.00')
        ->and($activeGuest->fresh()->share_amount)->toBe('200.00')
        ->and($activeDiscount->fresh()->eligible_amount)->toBe('200.00')
        ->and($activeDiscount->fresh()->discount_amount)->toBe('40.00')
        ->and($removedGuest->fresh()->share_amount)->toBe('55.00')
        ->and($removedDiscount->fresh()->discount_amount)->toBe('30.00')
        ->and($removedGuest->fresh()->roster_status)->toBe(BookingGuest::ROSTER_REMOVED);

    editGuestRosterSyncBooking($owner, $booking, []);

    expect($booking->fresh()->total_price)->toBe('160.00')
        ->and($activeDiscount->fresh()->discount_amount)->toBe('40.00');
});

test('pending and rejected discounts do not reduce the recalculated booking total', function () {
    ['owner' => $owner, 'booking' => $booking] = makeGuestRosterSyncScenario(payingGuestCount: 2);
    $guests = $booking->activeGuests()->orderBy('booking_guest_id')->get();
    $pendingDiscount = addGuestRosterSyncDiscount($booking, $guests[0], 20, 'Pending');
    $rejectedDiscount = addGuestRosterSyncDiscount($booking, $guests[1], 20, 'Rejected');

    editGuestRosterSyncBooking($owner, $booking, ['adult_count' => 2, 'child_count' => 0]);

    expect($booking->fresh()->total_price)->toBe('200.00')
        ->and($pendingDiscount->fresh()->verification_status)->toBe('Pending')
        ->and($rejectedDiscount->fresh()->verification_status)->toBe('Rejected')
        ->and($pendingDiscount->fresh()->discount_amount)->toBe('20.00')
        ->and($rejectedDiscount->fresh()->discount_amount)->toBe('20.00');
});

test('maximum occupancy failures leave booking and guest records unchanged', function () {
    ['owner' => $owner, 'booking' => $booking] = makeGuestRosterSyncScenario(
        payingGuestCount: 1,
        maximumCapacity: 2
    );

    expect(fn () => editGuestRosterSyncBooking($owner, $booking, ['adult_count' => 2, 'child_count' => 1]))
        ->toThrow(ValidationException::class);

    expect($booking->fresh()->adult_count)->toBe(1)
        ->and($booking->fresh()->child_count)->toBe(0)
        ->and($booking->activeGuests()->count())->toBe(1)
        ->and($booking->guestEvents()->count())->toBe(0);
});

test('failed roster audit insertion rolls back booking, guest, and event changes', function () {
    ['owner' => $owner, 'booking' => $booking] = makeGuestRosterSyncScenario(payingGuestCount: 2);
    DB::statement("
        CREATE TRIGGER reject_roster_removal_event
        BEFORE INSERT ON booking_guest_events
        WHEN NEW.event_type = 'Roster removal'
        BEGIN
            SELECT RAISE(ABORT, 'roster audit rejected');
        END
    ");

    expect(fn () => editGuestRosterSyncBooking($owner, $booking, ['adult_count' => 1, 'child_count' => 0]))
        ->toThrow(QueryException::class);

    expect($booking->fresh()->adult_count)->toBe(2)
        ->and($booking->activeGuests()->count())->toBe(2)
        ->and($booking->guestEvents()->count())->toBe(0);
});

test('owners and assigned staff can edit while unassigned staff cannot', function () {
    [
        'owner' => $owner,
        'house' => $house,
        'booking' => $booking,
    ] = makeGuestRosterSyncScenario();
    $staffRoleId = Role::query()->where('name', 'Staff')->firstOrFail()->role_id;
    $assignedStaff = User::create([
        'role_id' => $staffRoleId,
        'first_name' => 'Assigned',
        'last_name' => 'Staff',
        'email' => 'roster-assigned-staff@example.com',
        'password' => 'StaffPass123!',
        'is_active' => true,
    ]);
    Invitation::create([
        'invited_by' => $owner->user_id,
        'house_id' => $house->house_id,
        'role_id' => $staffRoleId,
        'email' => $assignedStaff->email,
        'token' => 'roster-assigned-staff',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);
    $unassignedStaff = User::create([
        'role_id' => $staffRoleId,
        'first_name' => 'Unassigned',
        'last_name' => 'Staff',
        'email' => 'roster-unassigned-staff@example.com',
        'password' => 'StaffPass123!',
        'is_active' => true,
    ]);

    expect(fn () => editGuestRosterSyncBooking($unassignedStaff, $booking, ['adult_count' => 2, 'child_count' => 0]))
        ->toThrow(NotFoundHttpException::class);

    editGuestRosterSyncBooking($assignedStaff, $booking, ['adult_count' => 2, 'child_count' => 0]);

    expect($booking->fresh()->adult_count)->toBe(2)
        ->and($booking->activeGuests()->count())->toBe(2);
});

test('checkout shows and refunds discounts only for active roster members', function () {
    ['owner' => $owner, 'booking' => $booking] = makeGuestRosterSyncScenario(
        status: BookingStatus::CHECKED_IN,
        payingGuestCount: 2
    );
    $activeGuest = $booking->guests()->orderBy('booking_guest_id')->firstOrFail();
    $removedGuest = $booking->guests()->orderByDesc('booking_guest_id')->firstOrFail();
    addGuestRosterSyncDiscount($booking, $activeGuest, 20, 'Verified');
    $removedDiscount = addGuestRosterSyncDiscount($booking, $removedGuest, 30, 'Verified');
    $removedGuest->update(['roster_status' => BookingGuest::ROSTER_REMOVED]);
    $booking->update(['total_price' => 180]);
    Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => 200,
        'payment_status' => PaymentStatus::VERIFIED,
        'payment_date' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertOk()
        ->assertSee('₱20.00')
        ->assertDontSee('₱30.00');

    app(BookingWorkflowService::class)->completeCheckout($owner, $booking->booking_id);

    expect(Refund::query()->where('booking_id', $booking->booking_id)->sum('amount'))->toBe(20)
        ->and($removedDiscount->fresh()->refund_amount)->toBe('0.00')
        ->and($removedDiscount->fresh()->refund_status)->toBe('None');
});

test('pre-check-in booking edit form exposes guest counts and synchronizes them on update', function () {
    ['owner' => $owner, 'booking' => $booking] = makeGuestRosterSyncScenario();
    $this->actingAs($owner)
        ->get(route('owner.bookings.edit', $booking->booking_id))
        ->assertOk()
        ->assertSee('name="adult_count"', false)
        ->assertSee('name="child_count"', false);

    $this->put(route('owner.bookings.update', $booking->booking_id), [
        'check_in' => $booking->check_in->toDateString(),
        'check_out' => $booking->check_out->toDateString(),
        'adult_count' => 2,
        'child_count' => 1,
        'reason' => 'The guest updated the roster.',
    ])->assertRedirect(route('owner.bookings.show', $booking->booking_id));

    expect($booking->fresh()->adult_count)->toBe(2)
        ->and($booking->fresh()->child_count)->toBe(1)
        ->and($booking->activeGuests()->count())->toBe(3);
});

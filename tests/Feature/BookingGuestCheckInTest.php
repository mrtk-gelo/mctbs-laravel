<?php

use App\Models\Booking;
use App\Models\BookingDiscount;
use App\Models\BookingGuest;
use App\Models\BookingGuestEvent;
use App\Models\Invitation;
use App\Models\Payment;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\User;
use App\Support\BookingStatus;
use App\Support\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeBookingGuestCheckInScenario(array $guestRows = []): array
{
    $owner = User::create([
        'role_id' => Role::query()->where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Check-in',
        'last_name' => 'Owner',
        'email' => 'checkin-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Check-in House',
        'address' => '10 Test Street',
        'city' => 'Baguio',
        'contact_number' => '5551000',
        'is_active' => true,
    ]);
    $unit = Unit::create([
        'house_id' => $house->house_id,
        'unit_type' => 'Check-in Room',
        'capacity' => 4,
        'maximum_capacity' => 6,
        'child_free_through_age' => 5,
        'price' => 1800,
        'status' => 'Available',
    ]);
    $guestUser = User::create([
        'role_id' => Role::query()->where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Booking',
        'last_name' => 'Guest',
        'email' => 'checkin-booking-guest@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);
    $booking = Booking::create([
        'user_id' => $guestUser->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today(),
        'check_out' => today()->addDay(),
        'adult_count' => count(array_filter($guestRows, fn (array $row): bool => ($row['guest_type'] ?? 'adult') === 'adult')),
        'child_count' => count(array_filter($guestRows, fn (array $row): bool => ($row['guest_type'] ?? 'adult') === 'child')),
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

    $guests = collect($guestRows)->map(function (array $row, int $index) use ($booking): BookingGuest {
        return BookingGuest::create(array_merge([
            'booking_id' => $booking->booking_id,
            'full_name' => 'Check-in Guest '.($index + 1),
            'guest_type' => 'adult',
            'age' => null,
            'is_free_child' => false,
            'share_amount' => '1800.00',
            'is_primary_guest' => $index === 0,
            'roster_status' => BookingGuest::ROSTER_ACTIVE,
            'attendance_status' => BookingGuest::ATTENDANCE_EXPECTED,
            'identity_verification_status' => BookingGuest::IDENTITY_UNVERIFIED,
        ], $row));
    });

    return compact('owner', 'house', 'unit', 'guestUser', 'booking', 'guests');
}

test('confirmed booking check-in page shows aggregate counts without roster or attendance tracking', function () {
    [
        'owner' => $owner,
        'booking' => $booking,
    ] = makeBookingGuestCheckInScenario([
        ['full_name' => 'Paying Adult', 'share_amount' => '1800.00'],
        ['full_name' => 'Free Child', 'guest_type' => 'child', 'age' => 4, 'is_free_child' => true, 'share_amount' => '0.00'],
    ]);

    $this->actingAs($owner)
        ->get(route('owner.guests.check-in', $booking->booking_id))
        ->assertOk()
        ->assertSee('Stay Details')
        ->assertSee('Booking reference')
        ->assertSee('Guest Headcount')
        ->assertSee('Adult Paying Guests')
        ->assertSee('Free Children')
        ->assertSee('value="1"', false)
        ->assertSee('value="1"', false)
        ->assertSee('Confirm booking details')
        ->assertDontSee('Guest Roster and Attendance')
        ->assertDontSee('Physical occupancy')
        ->assertDontSee('Record arrival');
});

test('check-in headcount updates synchronize guests and recalculate per-unit pricing without moving discounts', function () {
    [
        'owner' => $owner,
        'house' => $house,
        'unit' => $unit,
        'booking' => $booking,
        'guests' => $guests,
    ] = makeBookingGuestCheckInScenario([
        ['full_name' => 'Discounted Adult', 'share_amount' => '1800.00'],
    ]);
    $unit->update([
        'capacity' => 1,
        'maximum_capacity' => 4,
        'pricing_mode' => 'Per Unit',
        'price' => 100,
        'extra_guest_fee' => 20,
    ]);
    $booking->update([
        'pricing_mode' => 'Per Unit',
        'nightly_rate' => 100,
        'extra_guest_fee' => 20,
    ]);
    $discountedGuest = $guests->first();
    $discount = BookingDiscount::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $discountedGuest->booking_guest_id,
        'discount_type' => 'PWD',
        'id_reference' => 'PWD-CHECKIN-01',
        'verification_status' => 'Verified',
        'eligible_amount' => 1800,
        'discount_rate' => 20,
        'discount_amount' => 360,
        'final_amount' => 1440,
        'refund_amount' => 0,
        'refund_status' => 'None',
    ]);
    $staffRoleId = Role::query()->where('name', 'Staff')->firstOrFail()->role_id;
    $staff = User::create([
        'role_id' => $staffRoleId,
        'first_name' => 'Assigned',
        'last_name' => 'Headcount Staff',
        'email' => 'headcount-staff@example.com',
        'password' => 'StaffPass123!',
        'is_active' => true,
    ]);
    Invitation::create([
        'invited_by' => $owner->user_id,
        'house_id' => $house->house_id,
        'role_id' => $staffRoleId,
        'email' => $staff->email,
        'token' => 'headcount-staff-assignment',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($staff)
        ->post(route('staff.guests.headcount.update', $booking->booking_id), [
            'adult_count' => 2,
            'child_count' => 1,
        ])
        ->assertRedirect(route('staff.guests.check-in', $booking->booking_id))
        ->assertSessionHas('success', 'Guest headcount updated and booking pricing recalculated.');

    $activeGuests = $booking->fresh()->activeGuests()->orderBy('booking_guest_id')->get();
    $newAdult = $activeGuests->firstWhere('booking_guest_id', '!=', $discountedGuest->booking_guest_id);
    $freeChild = $activeGuests->firstWhere('is_free_child', true);

    expect($booking->fresh()->adult_count)->toBe(2)
        ->and($booking->fresh()->child_count)->toBe(1)
        ->and($booking->fresh()->room_subtotal)->toBe('100.00')
        ->and($booking->fresh()->extra_guest_count)->toBe(1)
        ->and($booking->fresh()->extra_guest_total)->toBe('20.00')
        ->and($booking->fresh()->total_price)->toBe('108.00')
        ->and($discountedGuest->fresh()->share_amount)->toBe('60.00')
        ->and($discount->fresh()->booking_guest_id)->toBe($discountedGuest->booking_guest_id)
        ->and($discount->fresh()->discount_amount)->toBe('12.00')
        ->and($newAdult->share_amount)->toBe('60.00')
        ->and($newAdult->discounts)->toBeEmpty()
        ->and($freeChild->share_amount)->toBe('0.00')
        ->and($freeChild->discounts)->toBeEmpty()
        ->and($activeGuests)->toHaveCount(3);

    $this->post(route('staff.guests.headcount.update', $booking->booking_id), [
        'adult_count' => 2,
        'child_count' => 1,
    ])->assertRedirect(route('staff.guests.check-in', $booking->booking_id));

    expect($booking->fresh()->guests)->toHaveCount(3)
        ->and($booking->fresh()->activeGuests)->toHaveCount(3);
});

test('check-in headcount rejects combined counts above maximum capacity without changing the roster', function () {
    ['owner' => $owner, 'unit' => $unit, 'booking' => $booking] = makeBookingGuestCheckInScenario([
        ['full_name' => 'Current Adult'],
    ]);
    $unit->update(['maximum_capacity' => 2]);

    $this->actingAs($owner)
        ->post(route('owner.guests.headcount.update', $booking->booking_id), [
            'adult_count' => 2,
            'child_count' => 1,
        ])
        ->assertSessionHasErrors([
            'adult_count' => 'The total number of guests, including free children, cannot exceed this unit’s maximum capacity.',
        ]);

    expect($booking->fresh()->adult_count)->toBe(1)
        ->and($booking->fresh()->child_count)->toBe(0)
        ->and($booking->fresh()->guests)->toHaveCount(1)
        ->and($booking->fresh()->total_price)->toBe('3600.00');
});

test('staff can proceed directly when declared headcount is correct without individual attendance entries', function () {
    [
        'owner' => $owner,
        'house' => $house,
        'booking' => $booking,
        'guests' => $guests,
    ] = makeBookingGuestCheckInScenario([
        ['full_name' => 'Expected Adult'],
    ]);
    $discount = BookingDiscount::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $guests->first()->booking_guest_id,
        'discount_type' => 'Senior Citizen',
        'id_reference' => 'SC-HEADCOUNT-01',
        'verification_status' => 'Pending',
        'eligible_amount' => 1800,
        'discount_rate' => 20,
        'discount_amount' => 360,
        'final_amount' => 1440,
        'refund_amount' => 0,
        'refund_status' => 'None',
    ]);
    $staffRoleId = Role::query()->where('name', 'Staff')->firstOrFail()->role_id;
    $staff = User::create([
        'role_id' => $staffRoleId,
        'first_name' => 'Assigned',
        'last_name' => 'Headcount Staff',
        'email' => 'headcount-proceed-staff@example.com',
        'password' => 'StaffPass123!',
        'is_active' => true,
    ]);
    Invitation::create([
        'invited_by' => $owner->user_id,
        'house_id' => $house->house_id,
        'role_id' => $staffRoleId,
        'email' => $staff->email,
        'token' => 'headcount-proceed-staff-assignment',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($staff)
        ->get(route('staff.guests.check-in', $booking->booking_id))
        ->assertSee('Guest Headcount')
        ->assertSee('Adult Paying Guests')
        ->assertSee('Free Children')
        ->assertSee('value="1"', false)
        ->assertSee('value="0"', false)
        ->assertSee('Senior/PWD Verification')
        ->assertSee('Confirm Details & Complete Check-in');

    $this->post(route('staff.bookings.check-in', $booking->booking_id), [
        'guest_details_confirmed' => '1',
    ])
        ->assertRedirect(route('staff.dashboard'));

    expect($booking->fresh()->status)->toBe(BookingStatus::CHECKED_IN)
        ->and($guests->first()->fresh()->attendance_status)->toBe(BookingGuest::ATTENDANCE_EXPECTED)
        ->and($guests->first()->fresh()->identity_verification_status)->toBe(BookingGuest::IDENTITY_UNVERIFIED)
        ->and($discount->fresh()->verification_status)->toBe('Pending');
});

test('check-in requires owner or staff confirmation of the ticket reference and aggregate guest counts', function () {
    ['owner' => $owner, 'booking' => $booking] = makeBookingGuestCheckInScenario([
        ['full_name' => 'Expected Adult'],
    ]);

    $this->actingAs($owner)
        ->post(route('owner.bookings.check-in', $booking->booking_id))
        ->assertSessionHasErrors('guest_details_confirmed');

    expect($booking->fresh()->status)->toBe(BookingStatus::CONFIRMED)
        ->and($booking->fresh()->checked_in_at)->toBeNull();

    $this->post(route('owner.bookings.check-in', $booking->booking_id), [
        'guest_details_confirmed' => '1',
    ])->assertRedirect(route('owner.dashboard'));

    expect($booking->fresh()->status)->toBe(BookingStatus::CHECKED_IN)
        ->and($booking->fresh()->checked_in_at)->not->toBeNull();
});

test('check-in rejects a confirmed booking before its scheduled check-in date', function () {
    ['owner' => $owner, 'booking' => $booking] = makeBookingGuestCheckInScenario([
        ['full_name' => 'Future Guest'],
    ]);
    $booking->update(['check_in' => today()->addDay()]);

    $this->actingAs($owner)
        ->post(route('owner.bookings.check-in', $booking->booking_id), [
            'guest_details_confirmed' => '1',
        ])
        ->assertSessionHasErrors('check_in');

    expect($booking->fresh()->status)->toBe(BookingStatus::CONFIRMED)
        ->and($booking->fresh()->checked_in_at)->toBeNull();
});

test('owner records one expected guest arrival with an append-only event', function () {
    ['owner' => $owner, 'booking' => $booking, 'guests' => $guests] = makeBookingGuestCheckInScenario([
        ['full_name' => 'Expected Guest'],
    ]);
    $this->travelTo('2026-11-11 10:15:00');
    $guest = $guests->first();
    $guest->update([
        'attendance_status' => null,
        'identity_verification_status' => null,
    ]);

    $this->actingAs($owner)
        ->post(route('owner.guests.arrivals.store', [$booking->booking_id, $guest->booking_guest_id]))
        ->assertRedirect(route('owner.guests.check-in', $booking->booking_id));

    expect($guest->fresh()->attendance_status)->toBe(BookingGuest::ATTENDANCE_ARRIVED)
        ->and($booking->fresh()->physicalOccupancy())->toBe(1)
        ->and($guest->fresh()->events)->toHaveCount(1)
        ->and($guest->fresh()->events->first()->event_type)->toBe(BookingGuestEvent::TYPE_ARRIVAL)
        ->and($guest->fresh()->events->first()->performed_by)->toBe($owner->user_id)
        ->and($guest->fresh()->events->first()->event_at->toDateTimeString())->toBe('2026-11-11 10:15:00');
});

test('free children count toward physical occupancy without receiving a paying share', function () {
    ['owner' => $owner, 'booking' => $booking, 'guests' => $guests] = makeBookingGuestCheckInScenario([
        ['full_name' => 'Paying Guest'],
        ['full_name' => 'Free Child', 'guest_type' => 'child', 'age' => 4, 'is_free_child' => true, 'share_amount' => '0.00'],
    ]);
    $this->actingAs($owner);

    foreach ($guests as $guest) {
        $this->post(route('owner.guests.arrivals.store', [$booking->booking_id, $guest->booking_guest_id]));
    }

    expect($booking->fresh()->effectiveGuests)->toHaveCount(2)
        ->and($booking->fresh()->physicalOccupancy())->toBe(2)
        ->and($guests[1]->fresh()->share_amount)->toBe('0.00')
        ->and($guests[1]->fresh()->is_free_child)->toBeTrue();
});

test('arrival cannot be recorded twice or for a removed roster member', function () {
    ['owner' => $owner, 'booking' => $booking, 'guests' => $guests] = makeBookingGuestCheckInScenario([
        ['full_name' => 'Arrived Guest'],
        ['full_name' => 'Removed Guest', 'roster_status' => BookingGuest::ROSTER_REMOVED],
    ]);
    $arrivedGuest = $guests[0];
    $removedGuest = $guests[1];
    $this->actingAs($owner);

    $this->post(route('owner.guests.arrivals.store', [$booking->booking_id, $arrivedGuest->booking_guest_id]));
    $this->post(route('owner.guests.arrivals.store', [$booking->booking_id, $arrivedGuest->booking_guest_id]))
        ->assertSessionHasErrors('attendance');
    $this->post(route('owner.guests.arrivals.store', [$booking->booking_id, $removedGuest->booking_guest_id]))
        ->assertSessionHasErrors('attendance');
    $this->post(route('owner.guests.identity.review', [$booking->booking_id, $removedGuest->booking_guest_id]), [
        'identity_status' => BookingGuest::IDENTITY_VERIFIED,
    ])->assertSessionHasErrors('identity');

    expect($arrivedGuest->fresh()->events)->toHaveCount(1)
        ->and($removedGuest->fresh()->attendance_status)->toBe(BookingGuest::ATTENDANCE_EXPECTED)
        ->and($booking->fresh()->physicalOccupancy())->toBe(1);
});

test('no-show and replacement transitions preserve predecessor history and allow replacement chains', function () {
    [
        'owner' => $owner,
        'booking' => $booking,
        'guests' => $guests,
    ] = makeBookingGuestCheckInScenario([
        ['full_name' => 'No-show Guest', 'is_primary_guest' => false],
        ['full_name' => 'Original Guest', 'is_primary_guest' => true, 'share_amount' => '1800.00'],
    ]);
    $noShowGuest = $guests[0];
    $originalGuest = $guests[1];
    $discount = BookingDiscount::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $originalGuest->booking_guest_id,
        'discount_type' => 'Senior Citizen',
        'id_reference' => 'SC-ORIGINAL',
        'verification_status' => 'Pending',
        'eligible_amount' => 1800,
        'discount_rate' => 20,
        'discount_amount' => 360,
        'final_amount' => 1440,
        'refund_amount' => 0,
        'refund_status' => 'None',
    ]);
    $this->actingAs($owner);

    $this->post(route('owner.guests.no-shows.store', [$booking->booking_id, $noShowGuest->booking_guest_id]))
        ->assertRedirect(route('owner.guests.check-in', $booking->booking_id));
    $this->post(route('owner.guests.arrivals.store', [$booking->booking_id, $noShowGuest->booking_guest_id]))
        ->assertSessionHasErrors('attendance');
    $this->post(route('owner.guests.replacements.store', [$booking->booking_id, $originalGuest->booking_guest_id]), [
        'full_name' => 'Replacement B',
    ])->assertRedirect(route('owner.guests.check-in', $booking->booking_id));
    $this->post(route('owner.guests.arrivals.store', [$booking->booking_id, $originalGuest->booking_guest_id]))
        ->assertSessionHasErrors('attendance');
    $replacementB = $booking->guests()->where('full_name', 'Replacement B')->firstOrFail();
    $this->post(route('owner.guests.replacements.store', [$booking->booking_id, $replacementB->booking_guest_id]), [
        'full_name' => 'Replacement C',
    ])->assertRedirect(route('owner.guests.check-in', $booking->booking_id));
    $replacementC = $booking->guests()->where('full_name', 'Replacement C')->firstOrFail();

    expect($noShowGuest->fresh()->attendance_status)->toBe(BookingGuest::ATTENDANCE_NO_SHOW)
        ->and($originalGuest->fresh()->attendance_status)->toBe(BookingGuest::ATTENDANCE_REPLACED)
        ->and($replacementB->fresh()->attendance_status)->toBe(BookingGuest::ATTENDANCE_REPLACED)
        ->and($replacementC->attendance_status)->toBe(BookingGuest::ATTENDANCE_EXPECTED)
        ->and($replacementC->replaces_booking_guest_id)->toBe($replacementB->booking_guest_id)
        ->and($originalGuest->fresh()->share_amount)->toBe('1800.00')
        ->and($replacementC->share_amount)->toBe('1800.00')
        ->and($replacementC->identity_verification_status)->toBe(BookingGuest::IDENTITY_UNVERIFIED)
        ->and($replacementC->discounts)->toBeEmpty()
        ->and($discount->fresh()->booking_guest_id)->toBe($originalGuest->booking_guest_id)
        ->and($booking->fresh()->effectiveGuests->modelKeys())->toBe([$replacementC->booking_guest_id])
        ->and($booking->fresh()->guests)->toHaveCount(4)
        ->and($noShowGuest->fresh()->events->first()->event_type)->toBe(BookingGuestEvent::TYPE_NO_SHOW)
        ->and($originalGuest->fresh()->events->first()->related_booking_guest_id)->toBe($replacementB->booking_guest_id)
        ->and($booking->fresh()->physicalOccupancy())->toBe(0);
});

test('booking check-in completes without individual attendance or identity verification', function () {
    [
        'owner' => $owner,
        'booking' => $booking,
        'guests' => $guests,
    ] = makeBookingGuestCheckInScenario([
        ['full_name' => 'Arrived Guest'],
    ]);
    $guest = $guests->first();
    $this->actingAs($owner)
        ->post(route('owner.guests.arrivals.store', [$booking->booking_id, $guest->booking_guest_id]));

    $this->post(route('owner.bookings.check-in', $booking->booking_id), [
        'guest_details_confirmed' => '1',
    ])
        ->assertRedirect(route('owner.dashboard'));

    expect($booking->fresh()->status)->toBe(BookingStatus::CHECKED_IN)
        ->and($booking->fresh()->checked_in_at)->not->toBeNull()
        ->and($guest->fresh()->attendance_status)->toBe(BookingGuest::ATTENDANCE_ARRIVED)
        ->and($guest->fresh()->identity_verification_status)->toBe(BookingGuest::IDENTITY_UNVERIFIED);
});

test('identity exceptions still require a reason and identity status does not gate aggregate check-in', function () {
    [
        'owner' => $owner,
        'booking' => $booking,
        'guests' => $guests,
    ] = makeBookingGuestCheckInScenario([
        ['full_name' => 'Failed Identity Guest'],
    ]);
    $guest = $guests->first();
    $this->travelTo('2026-11-11 10:15:00');
    $this->actingAs($owner)
        ->post(route('owner.guests.arrivals.store', [$booking->booking_id, $guest->booking_guest_id]))
        ->assertRedirect(route('owner.guests.check-in', $booking->booking_id));
    $this->post(route('owner.guests.identity.review', [$booking->booking_id, $guest->booking_guest_id]), [
        'identity_status' => BookingGuest::IDENTITY_EXCEPTION,
    ])->assertSessionHasErrors('reason');
    $this->post(route('owner.guests.identity.review', [$booking->booking_id, $guest->booking_guest_id]), [
        'identity_status' => BookingGuest::IDENTITY_FAILED,
    ])->assertRedirect(route('owner.guests.check-in', $booking->booking_id));
    $this->post(route('owner.bookings.check-in', $booking->booking_id), [
        'guest_details_confirmed' => '1',
    ])
        ->assertRedirect(route('owner.dashboard'));

    $this->travelTo('2026-11-11 10:20:00');
    $this->post(route('owner.guests.identity.review', [$booking->booking_id, $guest->booking_guest_id]), [
        'identity_status' => BookingGuest::IDENTITY_EXCEPTION,
        'reason' => 'The supervisor confirmed the guest in person.',
    ])->assertRedirect(route('owner.guests.check-in', $booking->booking_id));

    expect($guest->fresh()->identity_verification_status)->toBe(BookingGuest::IDENTITY_EXCEPTION)
        ->and($guest->fresh()->identity_exception_reason)->toBe('The supervisor confirmed the guest in person.')
        ->and($guest->fresh()->identityVerifiedBy->is($owner))->toBeTrue()
        ->and($guest->fresh()->events)->toHaveCount(3)
        ->and($guest->fresh()->events[1]->event_at->toDateTimeString())->toBe('2026-11-11 10:15:00')
        ->and($guest->fresh()->events[2]->before_state['identity_verified_at'])->toBe('2026-11-11 10:15:00')
        ->and($guest->fresh()->events[2]->event_at->toDateTimeString())->toBe('2026-11-11 10:20:00')
        ->and($guest->fresh()->events->last()->event_type)->toBe(BookingGuestEvent::TYPE_IDENTITY_EXCEPTION)
        ->and($booking->fresh()->status)->toBe(BookingStatus::CHECKED_IN);
});

test('a legacy identity exception without audit data does not block aggregate check-in', function () {
    ['owner' => $owner, 'booking' => $booking, 'guests' => $guests] = makeBookingGuestCheckInScenario([
        ['full_name' => 'Legacy Exception Guest'],
    ]);
    $guest = $guests->first();
    $this->actingAs($owner)
        ->post(route('owner.guests.arrivals.store', [$booking->booking_id, $guest->booking_guest_id]));
    $guest->update(['identity_verification_status' => BookingGuest::IDENTITY_EXCEPTION]);

    $this->post(route('owner.bookings.check-in', $booking->booking_id), [
        'guest_details_confirmed' => '1',
    ])
        ->assertRedirect(route('owner.dashboard'));

    expect($guest->fresh()->hasCheckInIdentityApproval())->toBeFalse()
        ->and($booking->fresh()->status)->toBe(BookingStatus::CHECKED_IN);
});

test('owners cannot alter attendance for another house or a guest from another booking', function () {
    [
        'owner' => $owner,
        'unit' => $unit,
        'booking' => $booking,
        'guests' => $guests,
    ] = makeBookingGuestCheckInScenario([
        ['full_name' => 'Owned Guest'],
    ]);
    $otherOwner = User::create([
        'role_id' => Role::query()->where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Other',
        'last_name' => 'Owner',
        'email' => 'other-checkin-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $otherBooking = Booking::create([
        'user_id' => $booking->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => today(),
        'check_out' => today()->addDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => BookingStatus::CONFIRMED,
    ]);
    $otherBookingGuest = BookingGuest::create([
        'booking_id' => $otherBooking->booking_id,
        'full_name' => 'Another Booking Guest',
        'guest_type' => 'adult',
        'share_amount' => '1800.00',
        'is_primary_guest' => true,
    ]);
    $guest = $guests->first();

    $this->actingAs($otherOwner)
        ->post(route('owner.guests.arrivals.store', [$booking->booking_id, $guest->booking_guest_id]))
        ->assertNotFound();
    $this->actingAs($owner)
        ->post(route('owner.guests.arrivals.store', [$booking->booking_id, $otherBookingGuest->booking_guest_id]))
        ->assertNotFound();

    expect($guest->fresh()->events)->toBeEmpty()
        ->and($otherBookingGuest->fresh()->attendance_status)->toBeNull();
});

test('staff can record guest attendance only for an assigned house', function () {
    [
        'owner' => $owner,
        'house' => $house,
        'booking' => $booking,
        'guests' => $guests,
    ] = makeBookingGuestCheckInScenario([
        ['full_name' => 'Staff Check-in Guest'],
    ]);
    $staffRoleId = Role::query()->where('name', 'Staff')->firstOrFail()->role_id;
    $staff = User::create([
        'role_id' => $staffRoleId,
        'first_name' => 'Assigned',
        'last_name' => 'Staff',
        'email' => 'checkin-staff@example.com',
        'password' => 'StaffPass123!',
        'is_active' => true,
    ]);
    Invitation::create([
        'invited_by' => $owner->user_id,
        'house_id' => $house->house_id,
        'role_id' => $staffRoleId,
        'email' => $staff->email,
        'token' => 'checkin-staff-assignment',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);
    $guest = $guests->first();

    $this->actingAs($staff)
        ->post(route('staff.guests.arrivals.store', [$booking->booking_id, $guest->booking_guest_id]))
        ->assertRedirect(route('staff.guests.check-in', $booking->booking_id));

    expect($guest->fresh()->events->first()->performed_by)->toBe($staff->user_id)
        ->and($guest->fresh()->attendance_status)->toBe(BookingGuest::ATTENDANCE_ARRIVED);
});

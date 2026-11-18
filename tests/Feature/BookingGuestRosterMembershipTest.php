<?php

use App\Models\Booking;
use App\Models\BookingDiscount;
use App\Models\BookingGuest;
use App\Models\BookingGuestEvent;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function makeBookingGuestRosterMembershipScenario(): array
{
    $owner = User::create([
        'role_id' => Role::query()->where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Roster',
        'last_name' => 'Owner',
        'email' => 'roster-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Roster House',
        'address' => '10 Test Street',
        'city' => 'Baguio',
        'contact_number' => '5551000',
        'is_active' => true,
    ]);
    $unit = Unit::create([
        'house_id' => $house->house_id,
        'unit_type' => 'Roster Room',
        'capacity' => 2,
        'price' => 1800,
        'status' => 'Available',
    ]);
    $booking = Booking::create([
        'user_id' => $owner->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->addDays(5),
        'check_out' => now()->addDays(7),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 3600,
        'status' => 'Pending',
    ]);
    $guest = BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'full_name' => 'Roster Guest',
        'guest_type' => 'adult',
        'share_amount' => 3600,
        'is_primary_guest' => true,
    ]);

    return compact('owner', 'booking', 'guest');
}

test('new guest records default to active roster membership', function () {
    ['booking' => $booking] = makeBookingGuestRosterMembershipScenario();

    $guest = $booking->guests()->create([
        'full_name' => 'New Roster Guest',
        'guest_type' => 'adult',
        'share_amount' => 1800,
    ]);
    $databaseGuestId = DB::table('booking_guests')->insertGetId([
        'booking_id' => $booking->booking_id,
        'full_name' => 'Database Default Guest',
        'guest_type' => 'adult',
        'share_amount' => 1800,
        'is_primary_guest' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ], 'booking_guest_id');

    expect($guest->roster_status)->toBe(BookingGuest::ROSTER_ACTIVE)
        ->and(DB::table('booking_guests')->where('booking_guest_id', $databaseGuestId)->value('roster_status'))
        ->toBe(BookingGuest::ROSTER_ACTIVE);
});

test('the roster migration backfills existing guest records as active', function () {
    ['booking' => $booking, 'guest' => $existingGuest] = makeBookingGuestRosterMembershipScenario();
    $migration = require database_path('migrations/2026_11_10_231029_add_roster_membership_to_booking_guests_table.php');

    $migration->down();
    $legacyGuestId = DB::table('booking_guests')->insertGetId([
        'booking_id' => $booking->booking_id,
        'full_name' => 'Legacy Roster Guest',
        'guest_type' => 'child',
        'share_amount' => 0,
        'is_primary_guest' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ], 'booking_guest_id');

    $migration->up();

    expect(DB::table('booking_guests')
        ->whereIn('booking_guest_id', [$existingGuest->booking_guest_id, $legacyGuestId])
        ->pluck('roster_status')
        ->all())->toBe([
            BookingGuest::ROSTER_ACTIVE,
            BookingGuest::ROSTER_ACTIVE,
        ]);
});

test('removing a guest preserves the historical guest record', function () {
    ['owner' => $owner, 'guest' => $guest] = makeBookingGuestRosterMembershipScenario();
    $guest->update([
        'roster_status' => BookingGuest::ROSTER_REMOVED,
        'removed_at' => now(),
        'removed_by' => $owner->user_id,
        'removal_reason' => 'Guest count was reduced.',
    ]);

    $guest->refresh();

    expect($guest->exists)->toBeTrue()
        ->and($guest->roster_status)->toBe(BookingGuest::ROSTER_REMOVED)
        ->and($guest->removed_at)->not->toBeNull()
        ->and($guest->removedBy->is($owner))->toBeTrue()
        ->and($guest->removal_reason)->toBe('Guest count was reduced.');
});

test('removed guests retain their individual discount relationships', function () {
    ['booking' => $booking, 'guest' => $guest] = makeBookingGuestRosterMembershipScenario();
    $discount = BookingDiscount::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $guest->booking_guest_id,
        'discount_type' => 'Senior Citizen',
        'id_reference' => 'SC-ROSTER-01',
        'verification_status' => 'Pending',
        'eligible_amount' => 3600,
        'discount_rate' => 20,
        'discount_amount' => 720,
        'final_amount' => 2880,
        'refund_amount' => 0,
        'refund_status' => 'None',
    ]);

    $guest->update(['roster_status' => BookingGuest::ROSTER_REMOVED]);

    expect($guest->fresh()->discounts->modelKeys())->toBe([$discount->discount_id])
        ->and($discount->fresh()->guest->is($guest))->toBeTrue();
});

test('removed guests retain replacement relationships and event history', function () {
    [
        'owner' => $owner,
        'booking' => $booking,
        'guest' => $originalGuest,
    ] = makeBookingGuestRosterMembershipScenario();
    $replacementGuest = BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'full_name' => 'Replacement Guest',
        'guest_type' => 'adult',
        'share_amount' => 3600,
        'replaces_booking_guest_id' => $originalGuest->booking_guest_id,
    ]);
    $event = BookingGuestEvent::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $originalGuest->booking_guest_id,
        'related_booking_guest_id' => $replacementGuest->booking_guest_id,
        'event_type' => BookingGuestEvent::TYPE_REPLACEMENT,
        'performed_by' => $owner->user_id,
        'reason' => 'Original guest could not attend.',
        'event_at' => now(),
        'before_state' => ['attendance_status' => BookingGuest::ATTENDANCE_EXPECTED],
        'after_state' => ['attendance_status' => BookingGuest::ATTENDANCE_REPLACED],
    ]);
    $removalEvent = BookingGuestEvent::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $originalGuest->booking_guest_id,
        'event_type' => BookingGuestEvent::TYPE_ROSTER_REMOVAL,
        'performed_by' => $owner->user_id,
        'reason' => 'Guest was removed from the reservation.',
        'event_at' => now(),
        'before_state' => ['roster_status' => BookingGuest::ROSTER_ACTIVE],
        'after_state' => ['roster_status' => BookingGuest::ROSTER_REMOVED],
    ]);

    $originalGuest->update(['roster_status' => BookingGuest::ROSTER_REMOVED]);
    $eventIds = $originalGuest->fresh()->events()->pluck('booking_guest_event_id')->all();

    expect($originalGuest->fresh()->replacements->modelKeys())
        ->toBe([$replacementGuest->booking_guest_id])
        ->and($eventIds)->toContain($event->booking_guest_event_id)
        ->and($eventIds)->toContain($removalEvent->booking_guest_event_id)
        ->and($removalEvent->fresh()->event_type)->toBe(BookingGuestEvent::TYPE_ROSTER_REMOVAL)
        ->and($replacementGuest->fresh()->originalGuest->is($originalGuest))->toBeTrue();
});

test('changing roster membership preserves attendance and identity statuses', function (string $attendanceStatus) {
    ['guest' => $guest] = makeBookingGuestRosterMembershipScenario();
    $guest->update([
        'attendance_status' => $attendanceStatus,
        'identity_verification_status' => BookingGuest::IDENTITY_VERIFIED,
    ]);

    $guest->update(['roster_status' => BookingGuest::ROSTER_REMOVED]);

    expect($guest->fresh()->attendance_status)->toBe($attendanceStatus)
        ->and($guest->fresh()->identity_verification_status)->toBe(BookingGuest::IDENTITY_VERIFIED);
})->with([
    'Expected' => [BookingGuest::ATTENDANCE_EXPECTED],
    'Arrived' => [BookingGuest::ATTENDANCE_ARRIVED],
    'No-show' => [BookingGuest::ATTENDANCE_NO_SHOW],
    'Replaced' => [BookingGuest::ATTENDANCE_REPLACED],
]);

test('the booking guests relationship returns active and removed records', function () {
    ['booking' => $booking, 'guest' => $removedGuest] = makeBookingGuestRosterMembershipScenario();
    $removedGuest->update(['roster_status' => BookingGuest::ROSTER_REMOVED]);
    $activeGuest = BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'full_name' => 'Active Roster Guest',
        'guest_type' => 'adult',
        'share_amount' => 1800,
    ]);

    expect($booking->fresh()->guests->modelKeys())
        ->toContain($removedGuest->booking_guest_id, $activeGuest->booking_guest_id)
        ->and($booking->fresh()->guests)->toHaveCount(2);
});

test('the active guests relationship excludes removed roster records', function () {
    ['booking' => $booking, 'guest' => $removedGuest] = makeBookingGuestRosterMembershipScenario();
    $removedGuest->update(['roster_status' => BookingGuest::ROSTER_REMOVED]);
    $activeGuest = BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'full_name' => 'Active Roster Guest',
        'guest_type' => 'adult',
        'share_amount' => 1800,
    ]);

    expect($booking->activeGuests->modelKeys())->toBe([$activeGuest->booking_guest_id]);
});

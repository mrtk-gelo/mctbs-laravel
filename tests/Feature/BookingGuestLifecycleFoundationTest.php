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

uses(RefreshDatabase::class);

function makeBookingGuestLifecycleFoundationScenario(): array
{
    $owner = User::create([
        'role_id' => Role::query()->where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Lifecycle',
        'last_name' => 'Owner',
        'email' => 'guest-lifecycle-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Guest Lifecycle House',
        'address' => '10 Test Street',
        'city' => 'Baguio',
        'contact_number' => '5551000',
        'is_active' => true,
    ]);
    $unit = Unit::create([
        'house_id' => $house->house_id,
        'unit_type' => 'Lifecycle Room',
        'capacity' => 2,
        'child_free_through_age' => 3,
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
        'full_name' => 'Original Guest',
        'guest_type' => 'adult',
        'share_amount' => 3600,
        'is_primary_guest' => true,
    ]);

    return compact('owner', 'unit', 'booking', 'guest');
}

test('legacy guest rows retain null lifecycle fields while supported values can be stored', function () {
    ['booking' => $booking] = makeBookingGuestLifecycleFoundationScenario();
    $legacyGuest = $booking->guests()->firstOrFail();

    expect($legacyGuest->attendance_status)->toBeNull()
        ->and($legacyGuest->identity_verification_status)->toBeNull();

    foreach (BookingGuest::ATTENDANCE_STATUSES as $index => $attendanceStatus) {
        $guest = BookingGuest::create([
            'booking_id' => $booking->booking_id,
            'full_name' => 'Lifecycle Guest '.$index,
            'guest_type' => 'adult',
            'share_amount' => 0,
            'attendance_status' => $attendanceStatus,
            'identity_verification_status' => BookingGuest::IDENTITY_VERIFICATION_STATUSES[$index],
        ]);

        expect($guest->fresh()->attendance_status)->toBe($attendanceStatus)
            ->and($guest->fresh()->identity_verification_status)
            ->toBe(BookingGuest::IDENTITY_VERIFICATION_STATUSES[$index]);
    }
});

test('replacement guests can resolve their original and be listed by the original guest', function () {
    ['booking' => $booking, 'guest' => $originalGuest] = makeBookingGuestLifecycleFoundationScenario();
    $replacementGuest = BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'full_name' => 'Replacement Guest',
        'guest_type' => 'adult',
        'share_amount' => 3600,
        'attendance_status' => BookingGuest::ATTENDANCE_EXPECTED,
        'replaces_booking_guest_id' => $originalGuest->booking_guest_id,
    ]);

    expect($replacementGuest->originalGuest->is($originalGuest))->toBeTrue()
        ->and($originalGuest->fresh()->replacements->modelKeys())->toBe([$replacementGuest->booking_guest_id]);
});

test('guest events retain related records, actor, and JSON snapshots', function () {
    [
        'owner' => $owner,
        'booking' => $booking,
        'guest' => $affectedGuest,
    ] = makeBookingGuestLifecycleFoundationScenario();
    $relatedGuest = BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'full_name' => 'Related Guest',
        'guest_type' => 'child',
        'age' => 8,
        'is_free_child' => false,
        'share_amount' => 0,
    ]);
    $event = BookingGuestEvent::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $affectedGuest->booking_guest_id,
        'related_booking_guest_id' => $relatedGuest->booking_guest_id,
        'event_type' => BookingGuestEvent::TYPE_REPLACEMENT,
        'performed_by' => $owner->user_id,
        'reason' => 'Guest could not attend.',
        'event_at' => '2026-11-10 12:00:00',
        'before_state' => ['attendance_status' => BookingGuest::ATTENDANCE_EXPECTED],
        'after_state' => ['attendance_status' => BookingGuest::ATTENDANCE_REPLACED],
    ]);
    $event->refresh();

    expect($event->booking->is($booking))->toBeTrue()
        ->and($event->affectedGuest->is($affectedGuest))->toBeTrue()
        ->and($event->relatedGuest->is($relatedGuest))->toBeTrue()
        ->and($event->responsibleUser->is($owner))->toBeTrue()
        ->and($event->event_at->toDateTimeString())->toBe('2026-11-10 12:00:00')
        ->and($event->before_state)->toBe(['attendance_status' => BookingGuest::ATTENDANCE_EXPECTED])
        ->and($event->after_state)->toBe(['attendance_status' => BookingGuest::ATTENDANCE_REPLACED])
        ->and($booking->fresh()->guestEvents->modelKeys())->toBe([$event->booking_guest_event_id])
        ->and($affectedGuest->fresh()->events->modelKeys())->toBe([$event->booking_guest_event_id])
        ->and($relatedGuest->fresh()->relatedEvents->modelKeys())->toBe([$event->booking_guest_event_id]);
});

test('identity exceptions and classification overrides preserve reason and responsible user', function () {
    ['owner' => $owner, 'guest' => $guest] = makeBookingGuestLifecycleFoundationScenario();
    $guest->update([
        'identity_verification_status' => BookingGuest::IDENTITY_EXCEPTION,
        'identity_verified_by' => $owner->user_id,
        'identity_verified_at' => '2026-11-10 12:30:00',
        'identity_exception_reason' => 'Original ID was unavailable; supervisor approved an exception.',
        'classification_overridden' => true,
        'classification_override_reason' => 'Age documentation reviewed by staff.',
        'classification_overridden_by' => $owner->user_id,
        'classification_overridden_at' => '2026-11-10 12:35:00',
    ]);
    $guest->refresh();

    expect($guest->identity_verification_status)->toBe(BookingGuest::IDENTITY_EXCEPTION)
        ->and($guest->identity_exception_reason)->toBe('Original ID was unavailable; supervisor approved an exception.')
        ->and($guest->identityVerifiedBy->is($owner))->toBeTrue()
        ->and($guest->identity_verified_at->toDateTimeString())->toBe('2026-11-10 12:30:00')
        ->and($guest->classification_overridden)->toBeTrue()
        ->and($guest->classification_override_reason)->toBe('Age documentation reviewed by staff.')
        ->and($guest->classificationOverriddenBy->is($owner))->toBeTrue()
        ->and($guest->classification_overridden_at->toDateTimeString())->toBe('2026-11-10 12:35:00');
});

test('booking retains its child-free age snapshot after the unit policy changes', function () {
    ['booking' => $booking, 'unit' => $unit] = makeBookingGuestLifecycleFoundationScenario();
    $booking->child_free_through_age = 3;
    $booking->save();
    $unit->update(['child_free_through_age' => 5]);

    expect($booking->fresh()->child_free_through_age)->toBe(3)
        ->and($unit->fresh()->child_free_through_age)->toBe(5);
});

test('existing booking and guest discount relationships remain intact', function () {
    ['booking' => $booking, 'guest' => $guest] = makeBookingGuestLifecycleFoundationScenario();
    $discount = BookingDiscount::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $guest->booking_guest_id,
        'discount_type' => 'Senior Citizen',
        'id_reference' => 'SC-FOUNDATION-01',
        'verification_status' => 'Pending',
        'eligible_amount' => 3600,
        'discount_rate' => 20,
        'discount_amount' => 720,
        'final_amount' => 2880,
        'refund_amount' => 0,
        'refund_status' => 'None',
    ]);

    expect($booking->discounts()->firstOrFail()->is($discount))->toBeTrue()
        ->and($discount->guest->is($guest))->toBeTrue()
        ->and($booking->guests()->firstOrFail()->is($guest))->toBeTrue();
});

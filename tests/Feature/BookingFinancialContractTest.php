<?php

use App\Models\AdditionalCharge;
use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\BookingDamage;
use App\Models\BookingDiscount;
use App\Models\BookingGuest;
use App\Models\DamageReport;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\User;
use App\Services\BookingWorkflowService;
use App\Support\BookingFinancialContract;
use App\Support\BookingStatus;
use App\Support\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function makeBookingFinancialScenario(
    string $bookingStatus = BookingStatus::CONFIRMED,
    float $accommodationGross = 200,
    float $storedAccommodationTotal = 180
): array {
    $owner = User::create([
        'role_id' => Role::query()->where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Financial',
        'last_name' => 'Owner',
        'email' => 'financial-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $guest = User::create([
        'role_id' => Role::query()->where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Financial',
        'last_name' => 'Guest',
        'email' => 'financial-guest@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Financial House',
        'address' => '10 Finance Street',
        'city' => 'Baguio',
        'contact_number' => '5556000',
        'is_active' => true,
    ]);
    $unit = Unit::create([
        'house_id' => $house->house_id,
        'unit_type' => 'Financial Room',
        'capacity' => 2,
        'maximum_capacity' => 4,
        'pricing_mode' => 'Per Unit',
        'price' => $accommodationGross / 2,
        'status' => 'Available',
    ]);
    $checkIn = $bookingStatus === BookingStatus::CHECKED_IN ? today() : today()->addDays(5);
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => $checkIn,
        'check_out' => $checkIn->copy()->addDays(2),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => $storedAccommodationTotal,
        'pricing_mode' => 'Per Unit',
        'nightly_rate' => $accommodationGross / 2,
        'room_subtotal' => $accommodationGross,
        'extra_guest_count' => 0,
        'extra_guest_total' => 0,
        'status' => $bookingStatus,
    ]);
    $activeGuest = BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'full_name' => 'Active Discount Guest',
        'guest_type' => 'adult',
        'share_amount' => $accommodationGross,
        'is_primary_guest' => true,
    ]);

    return compact('owner', 'guest', 'house', 'unit', 'booking', 'activeGuest');
}

function createFinancialDiscount(Booking $booking, BookingGuest $guest, float $amount, string $status): BookingDiscount
{
    return BookingDiscount::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $guest->booking_guest_id,
        'discount_type' => 'PWD',
        'id_reference' => 'FIN-'.$guest->booking_guest_id.'-'.$status,
        'verification_status' => $status,
        'eligible_amount' => 100,
        'discount_rate' => 20,
        'discount_amount' => $amount,
        'final_amount' => 100 - $amount,
        'refund_amount' => 0,
        'refund_status' => 'None',
    ]);
}

function createFinancialPayment(Booking $booking, float $amount, string $status): Payment
{
    return Payment::create([
        'booking_id' => $booking->booking_id,
        'payment_method' => 'GCash',
        'amount' => $amount,
        'payment_status' => $status,
        'payment_date' => now(),
    ]);
}

function createFinancialRefund(Booking $booking, Payment $payment, float $amount, string $status): Refund
{
    return Refund::create([
        'booking_id' => $booking->booking_id,
        'payment_id' => $payment->payment_id,
        'amount' => $amount,
        'reason' => 'Financial contract test refund.',
        'refund_status' => $status,
        'processed_at' => $status === 'Refunded' ? now() : null,
        'created_at' => now(),
    ]);
}

test('final amount uses the net active discount once and only authorized bill records', function () {
    [
        'owner' => $owner,
        'booking' => $booking,
        'activeGuest' => $activeGuest,
    ] = makeBookingFinancialScenario();
    $removedGuest = BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'full_name' => 'Removed Discount Guest',
        'guest_type' => 'adult',
        'share_amount' => 100,
        'roster_status' => BookingGuest::ROSTER_REMOVED,
    ]);
    createFinancialDiscount($booking, $activeGuest, 20, 'Verified');
    createFinancialDiscount($booking, $activeGuest, 15, 'Pending');
    createFinancialDiscount($booking, $removedGuest, 30, 'Verified');
    createFinancialDiscount($booking, $activeGuest, 10, 'Rejected');
    AdditionalCharge::create([
        'booking_id' => $booking->booking_id,
        'created_by' => $owner->user_id,
        'description' => 'Authorized checkout charge',
        'quantity' => 1,
        'unit_price' => 15,
        'total_amount' => 15,
    ]);
    BookingAddon::create([
        'booking_id' => $booking->booking_id,
        'name' => 'Authorized amenity',
        'price' => 5,
        'quantity' => 1,
    ]);
    DamageReport::create([
        'booking_id' => $booking->booking_id,
        'unit_id' => $booking->unit_id,
        'reported_by' => $owner->user_id,
        'description' => 'Unapproved damage report',
        'amount' => 75,
        'status' => 'Reported',
        'reported_at' => now(),
    ]);
    DamageReport::create([
        'booking_id' => $booking->booking_id,
        'unit_id' => $booking->unit_id,
        'reported_by' => $owner->user_id,
        'description' => 'Approved damage charge',
        'amount' => 50,
        'quantity' => 2,
        'unit_price' => 25,
        'status' => 'Reported',
        'approval_status' => DamageReport::APPROVAL_APPROVED,
        'reported_at' => now(),
        'resolved_at' => now(),
        'resolved_by' => $owner->user_id,
    ]);
    DamageReport::create([
        'booking_id' => $booking->booking_id,
        'unit_id' => $booking->unit_id,
        'reported_by' => $owner->user_id,
        'description' => 'Included damage charge',
        'amount' => 30,
        'quantity' => 2,
        'unit_price' => 15,
        'status' => 'Reported',
        'approval_status' => DamageReport::CHARGE_INCLUDED,
        'reported_at' => now(),
    ]);
    BookingDamage::create([
        'booking_id' => $booking->booking_id,
        'name' => 'Damage without approval status',
        'price' => 35,
        'quantity' => 1,
    ]);

    expect(BookingFinancialContract::approvedDiscounts($booking))->toBe(20.0)
        ->and(BookingFinancialContract::storedAccommodationAmountAfterAppliedDiscounts($booking))->toBe(180.0)
        ->and(BookingFinancialContract::additionalCharges($booking))->toBe(15.0)
        ->and(BookingFinancialContract::amenities($booking))->toBe(5.0)
        ->and(BookingFinancialContract::approvedDamageCharges($booking))->toBe(80.0)
        ->and(BookingFinancialContract::finalAmountDue($booking))->toBe(280.0)
        ->and($booking->amountDueBeforeCheckout())->toBe(180.0)
        ->and(BookingFinancialContract::roomAmountBeforeDiscounts($booking))->toBe(200.0)
        ->and(BookingFinancialContract::accommodationAmountBeforeDiscounts($booking))->toBe(200.0)
        ->and($booking->amountDueAtCheckout())->toBe(280.0);
});

test('legacy bookings derive their gross room amount from the stored total and verified discounts', function () {
    ['booking' => $booking, 'activeGuest' => $activeGuest] = makeBookingFinancialScenario();
    createFinancialDiscount($booking, $activeGuest, 20, 'Verified');
    $booking->room_subtotal = null;

    expect(BookingFinancialContract::roomAmountBeforeDiscounts($booking))->toBe(200.0)
        ->and(BookingFinancialContract::accommodationAmountBeforeDiscounts($booking))->toBe(200.0)
        ->and(BookingFinancialContract::storedAccommodationAmountAfterAppliedDiscounts($booking))->toBe(180.0);
});

test('financial balance counts verified payments and completed refunds only', function () {
    ['booking' => $booking] = makeBookingFinancialScenario(accommodationGross: 100, storedAccommodationTotal: 100);
    $verifiedPayment = createFinancialPayment($booking, 150, PaymentStatus::VERIFIED);
    createFinancialPayment($booking, 30, PaymentStatus::UNDER_REVIEW);
    createFinancialPayment($booking, 20, PaymentStatus::INSUFFICIENT);
    createFinancialRefund($booking, $verifiedPayment, 10, 'Refunded');
    createFinancialRefund($booking, $verifiedPayment, 25, 'Pending');

    expect(BookingFinancialContract::verifiedPayments($booking))->toBe(150.0)
        ->and(BookingFinancialContract::completedRefunds($booking))->toBe(10.0)
        ->and(BookingFinancialContract::outstandingBalance($booking))->toBe(0.0)
        ->and(BookingFinancialContract::refundableOverpayment($booking))->toBe(40.0)
        ->and($booking->outstandingBalance())->toBe(0.0)
        ->and($booking->refundableOverpayment())->toBe(40.0);
});

test('checked-in refunds allow partial amounts but never exceed remaining refundable credit', function () {
    ['owner' => $owner, 'booking' => $booking] = makeBookingFinancialScenario(
        bookingStatus: BookingStatus::CHECKED_IN,
        accommodationGross: 100,
        storedAccommodationTotal: 100
    );
    $verifiedPayment = createFinancialPayment($booking, 160, PaymentStatus::VERIFIED);
    createFinancialRefund($booking, $verifiedPayment, 20, 'Refunded');

    $firstRefund = app(BookingWorkflowService::class)->refundCheckedInGuest($owner, $booking->booking_id, 25, 'Partial refund.');

    expect($firstRefund->payment_id)->toBe($verifiedPayment->payment_id)
        ->and($firstRefund->amount)->toBe('25.00')
        ->and($firstRefund->refund_status)->toBe('Refunded')
        ->and($booking->refundableOverpayment())->toBe(15.0);
    expect(fn () => app(BookingWorkflowService::class)->refundCheckedInGuest(
        $owner,
        $booking->booking_id,
        16,
        'Excess refund.'
    ))->toThrow(ValidationException::class);
    expect($booking->refunds()->where('refund_status', 'Refunded')->sum('amount'))->toBe(45);
});

test('checkout rejects a refund not linked to a verified payment or beyond the remaining credit', function () {
    ['owner' => $owner, 'booking' => $booking] = makeBookingFinancialScenario(
        bookingStatus: BookingStatus::CHECKED_OUT,
        accommodationGross: 100,
        storedAccommodationTotal: 100
    );
    $verifiedPayment = createFinancialPayment($booking, 150, PaymentStatus::VERIFIED);
    $unverifiedPayment = createFinancialPayment($booking, 100, PaymentStatus::UNDER_REVIEW);
    $unverifiedRefund = createFinancialRefund($booking, $unverifiedPayment, 10, 'Pending');
    $overLimitRefund = createFinancialRefund($booking, $verifiedPayment, 60, 'Pending');

    expect(fn () => app(BookingWorkflowService::class)->processCheckoutRefund(
        $owner,
        $booking->booking_id,
        $unverifiedRefund->refund_id,
        null
    ))->toThrow(ValidationException::class);
    expect(fn () => app(BookingWorkflowService::class)->processCheckoutRefund(
        $owner,
        $booking->booking_id,
        $overLimitRefund->refund_id,
        null
    ))->toThrow(ValidationException::class);

    expect($unverifiedRefund->fresh()->refund_status)->toBe('Pending')
        ->and($overLimitRefund->fresh()->refund_status)->toBe('Pending');
});

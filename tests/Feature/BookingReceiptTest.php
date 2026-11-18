<?php

use App\Models\AdditionalCharge;
use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\BookingDiscount;
use App\Models\BookingGuest;
use App\Models\BookingReceipt;
use App\Models\DamageReport;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\User;
use App\Support\BookingFinancialContract;
use App\Support\BookingStatus;
use App\Support\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeReceiptStandardizationScenario(
    bool $overpaid = false,
    string $bookingStatus = BookingStatus::COMPLETED,
    bool $includeTransactions = true
): array {
    $owner = User::create([
        'role_id' => Role::query()->where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Receipt',
        'last_name' => 'Owner',
        'email' => 'receipt-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Receipt House',
        'address' => '10 Receipt Street',
        'city' => 'Baguio',
        'contact_number' => '5556000',
        'is_active' => true,
    ]);
    $unit = Unit::create([
        'house_id' => $house->house_id,
        'unit_type' => 'Receipt Room',
        'capacity' => 2,
        'price' => 200,
        'status' => 'Available',
    ]);
    $guestUser = User::create([
        'role_id' => Role::query()->where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Receipt',
        'last_name' => 'Guest',
        'email' => 'receipt-guest@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);
    $booking = Booking::create([
        'user_id' => $guestUser->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->subDays(2),
        'check_out' => now(),
        'adult_count' => 3,
        'child_count' => 2,
        'total_price' => 190,
        'room_subtotal' => 200,
        'extra_guest_count' => 1,
        'extra_guest_fee' => 5,
        'extra_guest_total' => 10,
        'status' => $bookingStatus,
    ]);
    $activeGuest = BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'user_id' => $guestUser->user_id,
        'full_name' => 'Active Paying Guest',
        'guest_type' => 'adult',
        'share_amount' => 200,
        'is_primary_guest' => true,
        'roster_status' => BookingGuest::ROSTER_ACTIVE,
    ]);
    $removedGuest = BookingGuest::create([
        'booking_id' => $booking->booking_id,
        'full_name' => 'Removed Paying Guest',
        'guest_type' => 'adult',
        'share_amount' => 50,
        'is_primary_guest' => false,
        'roster_status' => BookingGuest::ROSTER_REMOVED,
    ]);
    BookingDiscount::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $activeGuest->booking_guest_id,
        'verified_by' => $owner->user_id,
        'discount_type' => 'Senior Citizen',
        'id_reference' => 'SC-RECEIPT-01',
        'verification_status' => 'Verified',
        'verified_at' => now()->subDays(2),
        'eligible_amount' => 100,
        'discount_rate' => 20,
        'discount_amount' => 20,
        'final_amount' => 80,
        'refund_amount' => 0,
        'refund_status' => 'None',
    ]);
    BookingDiscount::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $activeGuest->booking_guest_id,
        'discount_type' => 'PWD',
        'id_reference' => 'PWD-PENDING',
        'verification_status' => 'Pending',
        'eligible_amount' => 100,
        'discount_rate' => 20,
        'discount_amount' => 20,
        'final_amount' => 80,
        'refund_amount' => 0,
        'refund_status' => 'None',
    ]);
    BookingDiscount::create([
        'booking_id' => $booking->booking_id,
        'booking_guest_id' => $removedGuest->booking_guest_id,
        'verified_by' => $owner->user_id,
        'discount_type' => 'Senior Citizen',
        'id_reference' => 'SC-REMOVED',
        'verification_status' => 'Verified',
        'verified_at' => now()->subDays(2),
        'eligible_amount' => 50,
        'discount_rate' => 20,
        'discount_amount' => 10,
        'final_amount' => 40,
        'refund_amount' => 0,
        'refund_status' => 'None',
    ]);
    BookingAddon::create([
        'booking_id' => $booking->booking_id,
        'name' => 'Late checkout amenity',
        'price' => 5,
        'quantity' => 1,
    ]);
    AdditionalCharge::create([
        'booking_id' => $booking->booking_id,
        'created_by' => $owner->user_id,
        'description' => 'Authorized additional charge',
        'quantity' => 1,
        'unit_price' => 15,
        'total_amount' => 15,
    ]);
    DamageReport::create([
        'booking_id' => $booking->booking_id,
        'unit_id' => $unit->unit_id,
        'reported_by' => $owner->user_id,
        'description' => 'Approved lamp damage',
        'amount' => 50,
        'quantity' => 2,
        'unit_price' => 25,
        'status' => 'Reported',
        'approval_status' => DamageReport::APPROVAL_APPROVED,
        'reported_at' => now()->subDays(2),
        'resolved_at' => now()->subDay(),
        'resolved_by' => $owner->user_id,
    ]);
    DamageReport::create([
        'booking_id' => $booking->booking_id,
        'unit_id' => $unit->unit_id,
        'reported_by' => $owner->user_id,
        'description' => 'Pending chair damage',
        'amount' => 100,
        'quantity' => 1,
        'unit_price' => 100,
        'status' => 'Reported',
        'approval_status' => DamageReport::APPROVAL_PENDING,
        'reported_at' => now()->subDay(),
    ]);
    DamageReport::create([
        'booking_id' => $booking->booking_id,
        'unit_id' => $unit->unit_id,
        'reported_by' => $owner->user_id,
        'description' => 'Legacy damage amount only',
        'amount' => 45,
        'quantity' => 3,
        'unit_price' => null,
        'status' => 'Reported',
        'approval_status' => null,
        'reported_at' => now()->subDay(),
    ]);
    $receipt = BookingReceipt::create([
        'booking_id' => $booking->booking_id,
        'receipt_number' => 'MCTBS-RCP-'.str_pad((string) $booking->booking_id, 8, '0', STR_PAD_LEFT),
        'issued_at' => now(),
    ]);

    $payments = collect();
    if ($includeTransactions) {
        $verifiedFirst = Payment::create([
            'booking_id' => $booking->booking_id,
            'payment_method' => 'GCash',
            'amount' => $overpaid ? 300 : 100,
            'payment_status' => PaymentStatus::VERIFIED,
            'payment_date' => now()->subDays(2)->setTime(8, 30),
            'reference_number' => 'PAY-VERIFIED-01',
        ]);
        $verifiedSecond = Payment::create([
            'booking_id' => $booking->booking_id,
            'payment_method' => 'Cash',
            'amount' => $overpaid ? 100 : 50,
            'payment_status' => PaymentStatus::VERIFIED,
            'payment_date' => now()->subDay()->setTime(12, 0),
            'reference_number' => 'PAY-VERIFIED-02',
        ]);
        Payment::create([
            'booking_id' => $booking->booking_id,
            'payment_method' => 'Bank Transfer',
            'amount' => 30,
            'payment_status' => PaymentStatus::UNDER_REVIEW,
            'payment_date' => now()->subDay(),
            'reference_number' => 'PAY-PENDING-01',
        ]);
        Payment::create([
            'booking_id' => $booking->booking_id,
            'payment_method' => 'GCash',
            'amount' => 10,
            'payment_status' => PaymentStatus::INSUFFICIENT,
            'payment_date' => now()->subDay(),
            'reference_number' => 'PAY-REJECTED-01',
        ]);
        Refund::create([
            'booking_id' => $booking->booking_id,
            'payment_id' => $verifiedFirst->payment_id,
            'processed_by' => $owner->user_id,
            'amount' => 10,
            'reason' => 'Completed partial refund',
            'refund_status' => 'Refunded',
            'refund_reference' => 'REF-001',
            'processed_at' => now()->subDay(),
            'created_at' => now()->subDays(2),
        ]);
        Refund::create([
            'booking_id' => $booking->booking_id,
            'payment_id' => $verifiedSecond->payment_id,
            'amount' => 15,
            'reason' => 'Pending partial refund',
            'refund_status' => 'Pending',
            'created_at' => now()->subDay(),
        ]);
        $payments = collect([$verifiedFirst, $verifiedSecond]);
    }

    return compact('owner', 'house', 'unit', 'guestUser', 'booking', 'activeGuest', 'removedGuest', 'receipt', 'payments');
}

test('guest HTML receipt displays contract totals and all financial record statuses', function () {
    [
        'guestUser' => $guestUser,
        'booking' => $booking,
    ] = makeReceiptStandardizationScenario();

    $response = $this->actingAs($guestUser)
        ->get(route('guest.bookings.receipt', $booking->booking_id))
        ->assertOk()
        ->assertSee('Accommodation after discounts')
        ->assertSee('₱190.00')
        ->assertSee('Adult paying guests')
        ->assertSee('<span>Adult paying guests</span><strong>3</strong>', false)
        ->assertSee('Free children')
        ->assertSee('<span>Free children</span><strong>2</strong>', false)
        ->assertSee('Final amount due')
        ->assertSee('₱260.00')
        ->assertSee('Payments made (verified only)')
        ->assertSee('₱150.00')
        ->assertSee('Completed refunds')
        ->assertSee('₱10.00')
        ->assertSee('Damage charges')
        ->assertDontSee('Approved damage charges')
        ->assertSee('Outstanding balance')
        ->assertSee('₱120.00')
        ->assertSee('PAY-VERIFIED-01')
        ->assertSee('Under Review')
        ->assertSee('PAY-REJECTED-01')
        ->assertSee('Pending partial refund')
        ->assertSee('REF-001')
        ->assertSee('Pending chair damage')
        ->assertSee('not included in amount due')
        ->assertSee('Approved lamp damage')
        ->assertSee('Qty 2')
        ->assertSee('included in amount due')
        ->assertDontSee('(Approved; included in amount due)')
        ->assertDontSee('(Pending; not included in amount due)')
        ->assertSee('₱25.00 / item')
        ->assertSee('₱50.00')
        ->assertSee('Legacy damage amount only')
        ->assertSee('Unit price unavailable')
        ->assertSee('₱45.00')
        ->assertDontSee('₱15.00 / item')
        ->assertSee('Removed guest; discount excluded')
        ->assertSee('Not applied to final amount due');

    expect($response->getContent())->not->toContain('Refundable overpayment');
    expect(BookingFinancialContract::receiptSummary($booking))->toBe([
        'roomCharges' => 200.0,
        'extraGuestCharges' => 10.0,
        'accommodationBeforeDiscounts' => 210.0,
        'approvedDiscounts' => 20.0,
        'accommodationAfterDiscounts' => 190.0,
        'additionalCharges' => 15.0,
        'amenities' => 5.0,
        'approvedDamageCharges' => 50.0,
        'finalAmountDue' => 260.0,
        'verifiedPayments' => 150.0,
        'completedRefunds' => 10.0,
        'outstandingBalance' => 120.0,
        'refundableOverpayment' => 0.0,
    ]);
});

test('guest HTML and PDF receipts render the same financial contract totals', function () {
    [
        'guestUser' => $guestUser,
        'booking' => $booking,
        'receipt' => $receipt,
    ] = makeReceiptStandardizationScenario(overpaid: true);

    $booking->load([
        'unit.house',
        'guest',
        'guests',
        'discounts.guest',
        'payments',
        'addons',
        'damages',
        'additionalCharges',
        'damageReports',
        'refunds.payment',
    ]);
    $viewData = [
        'booking' => $booking,
        'receipt' => $receipt,
        'financialSummary' => BookingFinancialContract::receiptSummary($booking),
        'nights' => 2,
    ];
    $pdfView = view('guest.bookings.receipt-pdf', $viewData)->render();
    $htmlResponse = $this->actingAs($guestUser)
        ->get(route('guest.bookings.receipt', $booking->booking_id))
        ->assertOk()
        ->assertSee('Refundable overpayment')
        ->assertSee('₱130.00')
        ->assertSee('Final amount due')
        ->assertSee('₱260.00');
    $pdfResponse = $this->get(route('guest.bookings.receipt.pdf', $booking->booking_id))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    expect($pdfView)
        ->toContain('Final amount due')
        ->toContain('₱260.00')
        ->toContain('Adult paying guests: <strong>3</strong>')
        ->toContain('Free children: <strong>2</strong>')
        ->toContain('Qty 2')
        ->toContain('included in amount due')
        ->not->toContain('(Approved; included in amount due)')
        ->not->toContain('(Pending; not included in amount due)')
        ->toContain('₱25.00 / item')
        ->toContain('₱50.00')
        ->toContain('Legacy damage amount only')
        ->toContain('Unit price unavailable')
        ->toContain('Refundable overpayment')
        ->toContain('₱130.00')
        ->toContain('PAY-VERIFIED-01')
        ->toContain('PAY-PENDING-01')
        ->toContain('PAY-REJECTED-01')
        ->toContain('Pending partial refund')
        ->and($pdfResponse->getContent())->toStartWith('%PDF')
        ->and(BookingFinancialContract::receiptSummary($booking)['finalAmountDue'])->toBe(260.0)
        ->and(BookingFinancialContract::receiptSummary($booking)['refundableOverpayment'])->toBe(130.0)
        ->and($htmlResponse->getContent())->toContain('₱130.00');
    expect($pdfView)->not->toContain('₱15.00 / item');
});

test('owner checkout shows matching contract totals and remains scoped to managed houses', function () {
    [
        'owner' => $owner,
        'booking' => $booking,
    ] = makeReceiptStandardizationScenario(overpaid: true);
    $otherOwner = User::create([
        'role_id' => Role::query()->where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Other',
        'last_name' => 'Owner',
        'email' => 'other-receipt-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertOk()
        ->assertSee('Final amount due')
        ->assertSee('₱260.00')
        ->assertSee('Refundable overpayment')
        ->assertSee('₱130.00')
        ->assertSee('PAY-VERIFIED-01')
        ->assertSee('PAY-PENDING-01')
        ->assertSee('REF-001');
    $this->actingAs($otherOwner)
        ->get(route('owner.bookings.checkout', $booking->booking_id))
        ->assertNotFound();
});

test('receipt handles no payments and historical checked-out booking status', function () {
    [
        'guestUser' => $guestUser,
        'booking' => $booking,
    ] = makeReceiptStandardizationScenario(
        bookingStatus: BookingStatus::CHECKED_OUT,
        includeTransactions: false
    );

    $this->actingAs($guestUser)
        ->get(route('guest.bookings.receipt', $booking->booking_id))
        ->assertOk()
        ->assertSee('No payments recorded.')
        ->assertSee('Final amount due')
        ->assertSee('₱260.00')
        ->assertSee('Outstanding balance')
        ->assertSee('₱260.00');

    expect($booking->outstandingBalance())->toBe(260.0)
        ->and($booking->refundableOverpayment())->toBe(0.0);
});

test('guests can view only their own completed booking receipt', function () {
    [
        'guestUser' => $guestUser,
        'booking' => $booking,
    ] = makeReceiptStandardizationScenario();

    $this->actingAs($guestUser)
        ->get(route('guest.bookings.receipt', $booking->booking_id))
        ->assertOk();

    $otherGuest = User::create([
        'role_id' => Role::query()->where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Another',
        'last_name' => 'Guest',
        'email' => 'another-receipt-guest@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);

    $this->actingAs($otherGuest)
        ->get(route('guest.bookings.receipt', $booking->booking_id))
        ->assertNotFound();
    $this->get(route('guest.bookings.receipt.pdf', $booking->booking_id))
        ->assertNotFound();

    $booking->update(['status' => BookingStatus::CONFIRMED]);
    $this->actingAs($guestUser)
        ->get(route('guest.bookings.receipt', $booking->booking_id))
        ->assertNotFound();
    $this->get(route('guest.bookings.receipt.pdf', $booking->booking_id))
        ->assertNotFound();
});

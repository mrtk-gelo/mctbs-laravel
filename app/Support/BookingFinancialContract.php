<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\BookingDiscount;
use App\Models\BookingGuest;
use App\Models\DamageReport;
use Illuminate\Database\Eloquent\Collection;

class BookingFinancialContract
{
    /**
     * Canonical source of booking-level financial data.
     * Payment status is sourced from Payment records, refunds from Refund records,
     * and guest-level discount amounts from BookingDiscount records.
     */
    public static function outstandingBalance(Booking $booking): float
    {
        $finalAmountDue = self::finalAmountDue($booking);
        $verifiedPayments = self::verifiedPayments($booking);
        $completedRefunds = self::completedRefunds($booking);

        return round(max(0.0, $finalAmountDue - $verifiedPayments + $completedRefunds), 2);
    }

    public static function refundableOverpayment(Booking $booking): float
    {
        $finalAmountDue = self::finalAmountDue($booking);
        $verifiedPayments = self::verifiedPayments($booking);
        $completedRefunds = self::completedRefunds($booking);

        return round(max(0.0, $verifiedPayments - $finalAmountDue - $completedRefunds), 2);
    }

    public static function storedAccommodationAmountAfterAppliedDiscounts(Booking $booking): float
    {
        if ($booking->room_subtotal === null) {
            return round((float) $booking->total_price, 2);
        }

        return round(max(
            0.0,
            self::accommodationAmountBeforeDiscounts($booking) - self::approvedDiscounts($booking)
        ), 2);
    }

    public static function accommodationAmountBeforeDiscounts(Booking $booking): float
    {
        return round(self::roomAmountBeforeDiscounts($booking) + (float) ($booking->extra_guest_total ?? 0), 2);
    }

    public static function roomAmountBeforeDiscounts(Booking $booking): float
    {
        if ($booking->room_subtotal !== null) {
            return round((float) $booking->room_subtotal, 2);
        }

        return round(max(
            0.0,
            (float) $booking->total_price
                + self::approvedDiscounts($booking)
                - (float) ($booking->extra_guest_total ?? 0)
        ), 2);
    }

    public static function approvedDiscounts(Booking $booking): float
    {
        /** @var Collection<int, BookingDiscount> $discounts */
        $discounts = $booking->relationLoaded('discounts')
            ? $booking->discounts->loadMissing('guest')
            : $booking->discounts()->with('guest')->get();

        return round((float) $discounts
            ->filter(fn (BookingDiscount $discount): bool => (string) $discount->verification_status === 'Verified'
                && $discount->guest?->roster_status === BookingGuest::ROSTER_ACTIVE)
            ->sum(fn (BookingDiscount $discount): float => (float) $discount->discount_amount), 2);
    }

    public static function additionalCharges(Booking $booking): float
    {
        // AdditionalCharge rows are created only by authorized checkout charge actions.
        return round((float) ($booking->relationLoaded('additionalCharges')
            ? $booking->additionalCharges->sum('total_amount')
            : $booking->additionalCharges()->sum('total_amount')), 2);
    }

    public static function amenities(Booking $booking): float
    {
        // BookingAddon rows are created only after an authorized amenity selection.
        return round((float) ($booking->relationLoaded('addons')
            ? $booking->addons->sum(fn (BookingAddon $addon): float => (float) $addon->price * $addon->quantity)
            : $booking->addons()->selectRaw('COALESCE(SUM(price * quantity), 0) as aggregate')->value('aggregate')), 2);
    }

    public static function approvedDamageCharges(Booking $booking): float
    {
        $damageReports = $booking->relationLoaded('damageReports')
            ? $booking->damageReports
            : $booking->damageReports()->get();

        return round((float) $damageReports
            ->filter(fn (DamageReport $damageReport): bool => $damageReport->isIncludedInFinalAmount())
            ->sum(fn (DamageReport $damageReport): float => $damageReport->calculatedChargeTotal()), 2);
    }

    public static function verifiedPayments(Booking $booking): float
    {
        return round((float) $booking->payments()
            ->where('payment_status', PaymentStatus::VERIFIED)
            ->sum('amount'), 2);
    }

    public static function completedRefunds(Booking $booking): float
    {
        return round((float) $booking->refunds()
            ->where('refund_status', 'Refunded')
            ->whereHas('payment', fn ($query) => $query->where('payment_status', PaymentStatus::VERIFIED))
            ->sum('amount'), 2);
    }

    public static function finalAmountDue(Booking $booking): float
    {
        $amount = self::storedAccommodationAmountAfterAppliedDiscounts($booking)
            + self::additionalCharges($booking)
            + self::amenities($booking)
            + self::approvedDamageCharges($booking);

        return round(max(0.0, $amount), 2);
    }

    /**
     * @return array{
     *     roomCharges: float,
     *     extraGuestCharges: float,
     *     accommodationBeforeDiscounts: float,
     *     approvedDiscounts: float,
     *     accommodationAfterDiscounts: float,
     *     additionalCharges: float,
     *     amenities: float,
     *     approvedDamageCharges: float,
     *     finalAmountDue: float,
     *     verifiedPayments: float,
     *     completedRefunds: float,
     *     outstandingBalance: float,
     *     refundableOverpayment: float
     * }
     */
    public static function receiptSummary(Booking $booking): array
    {
        return [
            'roomCharges' => self::roomAmountBeforeDiscounts($booking),
            'extraGuestCharges' => round((float) ($booking->extra_guest_total ?? 0), 2),
            'accommodationBeforeDiscounts' => self::accommodationAmountBeforeDiscounts($booking),
            'approvedDiscounts' => self::approvedDiscounts($booking),
            'accommodationAfterDiscounts' => self::storedAccommodationAmountAfterAppliedDiscounts($booking),
            'additionalCharges' => self::additionalCharges($booking),
            'amenities' => self::amenities($booking),
            'approvedDamageCharges' => self::approvedDamageCharges($booking),
            'finalAmountDue' => self::finalAmountDue($booking),
            'verifiedPayments' => self::verifiedPayments($booking),
            'completedRefunds' => self::completedRefunds($booking),
            'outstandingBalance' => self::outstandingBalance($booking),
            'refundableOverpayment' => self::refundableOverpayment($booking),
        ];
    }
}

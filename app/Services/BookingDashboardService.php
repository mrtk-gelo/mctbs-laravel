<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invitation;
use App\Models\Payment;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\User;
use App\Support\BookingStatus;
use App\Support\PaymentStatus;
use Illuminate\Support\Collection;

class BookingDashboardService
{
    public function __construct(private BookingWorkflowService $workflow) {}

    /**
     * @return array{houses: Collection, pendingBookings: Collection, pendingBookingsTotal: int, paymentReviews: Collection, paymentReviewsTotal: int, upcomingCheckIns: Collection, upcomingCheckInsTotal: int, currentGuests: Collection, currentGuestsTotal: int, focusedReviewBooking: ?Booking}
     */
    public function queuesFor(User $user, ?int $focusBookingId = null): array
    {
        if ($user->role?->name === 'Owner') {
            $houses = TransientHouse::query()
                ->where('owner_id', $user->user_id)
                ->orderBy('name')
                ->get();
        } else {
            $staffRoleId = Role::query()->where('name', 'Staff')->value('role_id');
            $houseIds = Invitation::query()
                ->where('email', $user->email)
                ->where('role_id', $staffRoleId)
                ->whereNotNull('used_at')
                ->whereNotNull('house_id')
                ->pluck('house_id')
                ->unique();
            $houses = TransientHouse::query()
                ->whereIn('house_id', $houseIds)
                ->orderBy('name')
                ->get();
        }

        $houseIds = $houses->pluck('house_id');
        $bookingScope = fn () => Booking::query()
            ->whereHas('unit', fn ($query) => $query->whereIn('house_id', $houseIds));
        $upcomingCheckIns = $bookingScope()
            ->where('status', BookingStatus::CONFIRMED)
            ->whereDate('check_in', '>=', today()->toDateString())
            ->with([
                'unit.house',
                'guest',
                'guests' => fn ($query) => $query
                    ->orderByDesc('is_primary_guest')
                    ->orderBy('booking_guest_id'),
                'discounts.guest',
                'discounts.verifiedBy',
                'payments' => fn ($query) => $query
                    ->where('payment_status', PaymentStatus::VERIFIED),
            ])
            ->orderBy('check_in')
            ->orderBy('booking_id')
            ->get()
            ->filter(fn (Booking $booking): bool => $booking->payments->isNotEmpty()
                && (float) $booking->payments->sum('amount') >= $booking->downpaymentAmount())
            ->values();
        $upcomingCheckInsTotal = $upcomingCheckIns->count();
        $upcomingCheckIns = $upcomingCheckIns->take(5)->values();

        $paymentReviewQuery = $bookingScope()
            ->whereIn('status', [BookingStatus::ACCEPTED, BookingStatus::CONFIRMED])
            ->where(function ($query): void {
                $query->whereHas('payments', fn ($paymentQuery) => $paymentQuery
                    ->where('payment_status', PaymentStatus::UNDER_REVIEW))
                    ->orWhere(function ($acceptedQuery): void {
                        $acceptedQuery->where('status', BookingStatus::ACCEPTED)
                            ->whereDoesntHave('payments', fn ($paymentQuery) => $paymentQuery
                                ->where('payment_status', PaymentStatus::VERIFIED));
                    });
            });
        $underReviewPaymentCount = Payment::query()
            ->where('payment_status', PaymentStatus::UNDER_REVIEW)
            ->whereHas('booking', fn ($query) => $query
                ->whereIn('status', [BookingStatus::ACCEPTED, BookingStatus::CONFIRMED])
                ->whereHas('unit', fn ($unitQuery) => $unitQuery->whereIn('house_id', $houseIds)))
            ->count();
        $unpaidAcceptedCount = $bookingScope()
            ->where('status', BookingStatus::ACCEPTED)
            ->whereDoesntHave('payments', fn ($paymentQuery) => $paymentQuery
                ->where('payment_status', PaymentStatus::UNDER_REVIEW))
            ->whereDoesntHave('payments', fn ($paymentQuery) => $paymentQuery
                ->where('payment_status', PaymentStatus::VERIFIED))
            ->count();
        $paymentReviewsTotal = $underReviewPaymentCount + $unpaidAcceptedCount;

        $paymentReviews = $paymentReviewQuery
            ->with(['unit.house', 'guest', 'guests', 'payments' => fn ($query) => $query
                ->where('payment_status', PaymentStatus::UNDER_REVIEW)
                ->orderBy('payment_date')])
            ->orderByRaw('CASE status WHEN ? THEN 1 ELSE 2 END', [BookingStatus::ACCEPTED])
            ->orderBy('updated_at')
            ->limit(5)
            ->get();

        $paymentReviews->each(function (Booking $booking): void {
            $booking->setAttribute('payment_overdue', $this->workflow->isPaymentOverdue($booking));
        });

        $pendingBookings = $bookingScope()
            ->where('status', BookingStatus::PENDING)
            ->with(['unit.house', 'guest', 'guests'])
            ->orderBy('created_at')
            ->orderBy('booking_id')
            ->limit(5)
            ->get();
        $pendingBookingsTotal = $bookingScope()
            ->where('status', BookingStatus::PENDING)
            ->count();
        $currentGuests = $bookingScope()
            ->where('status', BookingStatus::CHECKED_IN)
            ->with(['unit.house', 'guest', 'guests', 'discounts.guest', 'discounts.verifiedBy'])
            ->orderBy('check_out')
            ->orderBy('booking_id')
            ->get();
        $currentGuestsTotal = $currentGuests->count();
        $focusedReviewBooking = $focusBookingId === null
            ? null
            : $bookingScope()
                ->where('booking_id', $focusBookingId)
                ->whereIn('status', [BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN])
                ->with([
                    'unit.house',
                    'guest',
                    'guests' => fn ($query) => $query
                        ->orderByDesc('is_primary_guest')
                        ->orderBy('booking_guest_id'),
                    'discounts.guest',
                    'discounts.verifiedBy',
                    'payments' => fn ($query) => $query
                        ->where('payment_status', PaymentStatus::VERIFIED),
                ])
                ->first();

        return [
            'houses' => $houses,
            'pendingBookings' => $pendingBookings,
            'pendingBookingsTotal' => $pendingBookingsTotal,
            'paymentReviews' => $paymentReviews,
            'paymentReviewsTotal' => $paymentReviewsTotal,
            'upcomingCheckIns' => $upcomingCheckIns,
            'upcomingCheckInsTotal' => $upcomingCheckInsTotal,
            'currentGuests' => $currentGuests->take(5)->values(),
            'currentGuestsTotal' => $currentGuestsTotal,
            'focusedReviewBooking' => $focusedReviewBooking,
        ];
    }
}

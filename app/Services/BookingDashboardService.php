<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invitation;
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
     * @return array{houses: Collection, pendingBookings: Collection, paymentReviews: Collection, upcomingCheckIns: Collection, currentGuests: Collection}
     */
    public function queuesFor(User $user): array
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
            ->with(['unit', 'guest', 'guests', 'payments' => fn ($query) => $query
                ->where('payment_status', PaymentStatus::VERIFIED)])
            ->orderBy('check_in')
            ->orderBy('booking_id')
            ->get()
            ->filter(fn (Booking $booking): bool => $booking->payments->isNotEmpty()
                && (float) $booking->payments->sum('amount') >= $booking->downpaymentAmount())
            ->values();

        $paymentReviews = $bookingScope()
            ->whereIn('status', [BookingStatus::ACCEPTED, BookingStatus::CONFIRMED])
            ->where(function ($query): void {
                $query->whereHas('payments', fn ($paymentQuery) => $paymentQuery
                    ->where('payment_status', PaymentStatus::UNDER_REVIEW))
                    ->orWhere(function ($acceptedQuery): void {
                        $acceptedQuery->where('status', BookingStatus::ACCEPTED)
                            ->whereDoesntHave('payments', fn ($paymentQuery) => $paymentQuery
                                ->where('payment_status', PaymentStatus::VERIFIED));
                    });
            })
            ->with(['unit.house', 'guest', 'guests', 'payments' => fn ($query) => $query
                ->where('payment_status', PaymentStatus::UNDER_REVIEW)
                ->orderBy('payment_date')])
            ->orderByRaw('CASE status WHEN ? THEN 1 ELSE 2 END', [BookingStatus::ACCEPTED])
            ->orderBy('updated_at')
            ->limit(8)
            ->get();

        $paymentReviews->each(function (Booking $booking): void {
            $booking->setAttribute('payment_overdue', $this->workflow->isPaymentOverdue($booking));
        });

        return [
            'houses' => $houses,
            'pendingBookings' => $bookingScope()
                ->where('status', BookingStatus::PENDING)
                ->with(['unit.house', 'guest', 'guests'])
                ->orderBy('created_at')
                ->orderBy('booking_id')
                ->limit(8)
                ->get(),
            'paymentReviews' => $paymentReviews,
            'upcomingCheckIns' => $upcomingCheckIns,
            'currentGuests' => $bookingScope()
                ->where('status', BookingStatus::CHECKED_IN)
                ->with(['unit', 'guest', 'guests'])
                ->orderBy('check_out')
                ->orderBy('booking_id')
                ->get(),
        ];
    }
}

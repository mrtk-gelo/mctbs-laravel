<?php

namespace App\Services;

use App\Models\Amenity;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\DamageReport;
use App\Models\User;
use App\Support\BookingStatus;
use App\Support\PaymentStatus;
use App\Support\UnitTimeFormatter;
use Illuminate\Database\Eloquent\Collection;

class BookingContextService
{
    /**
     * @return array{
     *     bookings: Collection<int, Booking>,
     *     booking: ?Booking,
     *     paymentStatus: ?string,
     *     verifiedPayments: float,
     *     checkInTime: ?string,
     *     checkOutTime: ?string,
     *     policies: ?string,
     *     bookingUrl: ?string,
     *     ticketUrl: ?string,
     *     amenities: Collection<int, Amenity>
     * }
     */
    public function forConversation(User $viewer, Conversation $conversation): array
    {
        abort_unless(app(ConversationService::class)->canAccess($viewer, $conversation), 403);

        $bookings = Booking::query()
            ->where('user_id', $conversation->guest_user_id)
            ->whereHas('unit', fn ($query) => $query->where('house_id', $conversation->house_id))
            ->with(['unit.house.amenities', 'payments', 'damageReports'])
            ->latest('check_in')
            ->get();
        $booking = $bookings->first();
        $paymentStatus = $booking
            ? $booking->payments->sortByDesc('payment_date')->first()?->payment_status
            : null;
        $verifiedPayments = $booking
            ? (float) $booking->payments->where('payment_status', PaymentStatus::VERIFIED)->sum('amount')
            : 0.0;
        $isGuest = $viewer->role?->name === 'Guest';
        $bookingRoute = match ($viewer->role?->name) {
            'Guest' => 'guest.bookings.show',
            'Staff' => 'staff.bookings.show',
            default => 'owner.bookings.show',
        };

        return [
            'bookings' => $bookings,
            'booking' => $booking,
            'paymentStatus' => $paymentStatus,
            'verifiedPayments' => $verifiedPayments,
            'checkInTime' => $booking?->unit?->check_in_time,
            'checkOutTime' => $booking?->unit?->check_out_time,
            'policies' => $booking?->unit?->house?->policies,
            'bookingUrl' => $booking
                ? route($bookingRoute, $booking->booking_id)
                : null,
            'ticketUrl' => $booking
                && $isGuest
                && in_array($booking->status, [BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN], true)
                ? route('guest.bookings.ticket', $booking->booking_id)
                : null,
            'amenities' => $booking?->unit?->house?->amenities ?? new Collection,
        ];
    }

    public function answerGuestQuestion(Conversation $conversation, string $question): ?string
    {
        $context = $this->forConversation($conversation->guest, $conversation);
        $booking = $context['booking'];
        if (! $booking) {
            return null;
        }

        $question = strtolower(preg_replace('/[^a-z0-9]+/i', ' ', $question) ?? '');
        if (str_contains($question, 'downpayment') || str_contains($question, 'deposit')) {
            $remainingDownpayment = max(0, round($booking->downpaymentAmount() - $context['verifiedPayments'], 2));
            if ($booking->status === BookingStatus::ACCEPTED && $remainingDownpayment > 0) {
                return 'Your booking has been accepted and its dates are reserved while the downpayment is pending. The remaining downpayment is ₱'
                    .number_format($remainingDownpayment, 2).'.';
            }

            if ($booking->status === BookingStatus::PENDING) {
                return 'Your booking is pending property-team review. A downpayment is due after the booking is accepted.';
            }

            if ($remainingDownpayment === 0.0) {
                return 'There is no remaining downpayment due for your latest booking.';
            }

            return 'The remaining downpayment for your latest booking is ₱'.number_format($remainingDownpayment, 2).'.';
        }

        if (str_contains($question, 'payment')) {
            $status = $context['paymentStatus'] ?? PaymentStatus::MISSING;

            return "Your latest booking payment status is {$status}.";
        }

        if (str_contains($question, 'booking') || str_contains($question, 'reservation') || str_contains($question, 'status')) {
            $reference = $booking->booking_reference ?: '#'.$booking->booking_id;
            $status = BookingStatus::canonical($booking->status);

            $statusDetails = match ($status) {
                BookingStatus::PENDING => 'The property team is reviewing your request.',
                BookingStatus::ACCEPTED => 'Your dates are reserved while the downpayment is pending.',
                BookingStatus::CONFIRMED => 'Your booking is confirmed.',
                BookingStatus::CHECKED_IN => 'Your stay is currently checked in.',
                BookingStatus::COMPLETED => 'Your stay is complete.',
                default => null,
            };

            return "Your latest booking is {$reference} and its current status is {$status}."
                .($statusDetails ? ' '.$statusDetails : '');
        }

        if (str_contains($question, 'damage')) {
            $approvedDamageReports = $booking->damageReports
                ->where('approval_status', DamageReport::APPROVAL_APPROVED);

            if ($approvedDamageReports->isEmpty()) {
                return 'There are no approved damage charges recorded for your latest booking.';
            }

            $damageTotal = $approvedDamageReports->sum(
                fn (DamageReport $report): float => $report->calculatedChargeTotal()
            );

            return 'Approved damage charges recorded for your latest booking total ₱'.number_format((float) $damageTotal, 2).'.';
        }

        if (str_contains($question, 'price') || str_contains($question, 'rate') || str_contains($question, 'cost')) {
            $nightlyRate = $booking->nightly_rate ?? $booking->unit?->price;

            return $nightlyRate !== null
                ? 'The nightly rate recorded for your booking is ₱'.number_format((float) $nightlyRate, 2).'.'
                : null;
        }

        if (str_contains($question, 'check in') && str_contains($question, 'check out')) {
            return 'Your check-in date is '.$booking->check_in->format('M j, Y').' at '
                .($context['checkInTime'] ? UnitTimeFormatter::format($context['checkInTime']) : 'the property configured check-in time')
                .' and check-out date is '.$booking->check_out->format('M j, Y').' at '
                .($context['checkOutTime'] ? UnitTimeFormatter::format($context['checkOutTime']) : 'the property configured check-out time').'.';
        }

        if (str_contains($question, 'check in') || str_contains($question, 'check-in') || str_contains($question, 'arrival')) {
            return "Your check-in date is {$booking->check_in->format('M j, Y')} at ".($context['checkInTime'] ?: 'the property configured check-in time').'.';
        }

        if (str_contains($question, 'check out') || str_contains($question, 'check-out') || str_contains($question, 'departure')) {
            return "Your check-out date is {$booking->check_out->format('M j, Y')} at ".($context['checkOutTime'] ?: 'the property configured check-out time').'.';
        }

        if (str_contains($question, 'ticket')) {
            return $context['ticketUrl']
                ? 'Your booking ticket is available here: '.$context['ticketUrl']
                : 'A booking ticket is available after your booking is confirmed.';
        }

        return null;
    }
}

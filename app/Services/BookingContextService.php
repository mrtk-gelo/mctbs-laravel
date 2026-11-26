<?php

namespace App\Services;

use App\Models\Amenity;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\User;
use App\Support\BookingStatus;
use App\Support\PaymentStatus;
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
            ->with(['unit.house.amenities', 'payments'])
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

        return [
            'bookings' => $bookings,
            'booking' => $booking,
            'paymentStatus' => $paymentStatus,
            'verifiedPayments' => $verifiedPayments,
            'checkInTime' => $booking?->unit?->check_in_time,
            'checkOutTime' => $booking?->unit?->check_out_time,
            'policies' => $booking?->unit?->house?->policies,
            'bookingUrl' => $booking
                ? route($isGuest ? 'guest.bookings.show' : 'bookings.show', $booking->booking_id)
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

        $question = strtolower($question);
        if (str_contains($question, 'payment') || str_contains($question, 'downpayment')) {
            $status = $context['paymentStatus'] ?? PaymentStatus::MISSING;

            return "Your latest booking payment status is {$status}.";
        }

        if (str_contains($question, 'booking') || str_contains($question, 'reservation') || str_contains($question, 'status')) {
            $reference = $booking->booking_reference ?: '#'.$booking->booking_id;

            return "Your latest booking is {$reference} and its current status is {$booking->status}.";
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

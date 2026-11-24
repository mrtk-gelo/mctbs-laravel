<?php

namespace App\Services;

use App\Mail\BookingTicket;
use App\Models\Booking;
use App\Support\BookingStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class BookingTicketDeliveryService
{
    public function __construct(private readonly BookingTicketPdfGenerator $pdfGenerator) {}

    public function sendAfterCommit(int $bookingId): void
    {
        DB::afterCommit(fn () => $this->send($bookingId));
    }

    public function send(int $bookingId): string
    {
        $attemptedAt = now();
        $claim = DB::transaction(function () use ($bookingId, $attemptedAt): array {
            $booking = Booking::query()
                ->with(['unit.house', 'guest'])
                ->lockForUpdate()
                ->findOrFail($bookingId);

            if (! in_array($booking->status, [BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN], true)) {
                return ['status' => 'ineligible', 'booking' => null];
            }

            if ($booking->ticket_email_status === 'sent') {
                return ['status' => 'already_sent', 'booking' => null];
            }

            if ($booking->ticket_email_status === 'sending'
                && $booking->ticket_email_attempted_at?->isAfter(now()->subMinutes(10))) {
                return ['status' => 'in_progress', 'booking' => null];
            }

            if (! $booking->guest?->email) {
                $booking->ticket_email_status = 'failed';
                $booking->ticket_email_attempted_at = $attemptedAt;
                $booking->save();

                return ['status' => 'missing_recipient', 'booking' => null];
            }

            $booking->ticket_email_status = 'sending';
            $booking->ticket_email_attempted_at = $attemptedAt;
            $booking->save();

            return ['status' => 'claimed', 'booking' => $booking];
        });

        if ($claim['status'] !== 'claimed') {
            if ($claim['status'] === 'missing_recipient') {
                Log::warning('Booking ticket email has no registered guest email address.', [
                    'booking_id' => $bookingId,
                ]);
            }

            return $claim['status'];
        }

        /** @var Booking $booking */
        $booking = $claim['booking'];

        try {
            Mail::to($booking->guest->email)->send(
                new BookingTicket($booking, $this->pdfGenerator->generate($booking))
            );
        } catch (Throwable $exception) {
            Booking::query()
                ->whereKey($bookingId)
                ->where('ticket_email_status', 'sending')
                ->where('ticket_email_attempted_at', $attemptedAt)
                ->update(['ticket_email_status' => 'failed']);

            Log::warning('Booking ticket email delivery failed.', [
                'booking_id' => $bookingId,
                'exception' => $exception::class,
                'error' => $exception->getMessage(),
            ]);

            return 'failed';
        }

        Booking::query()
            ->whereKey($bookingId)
            ->where('ticket_email_status', 'sending')
            ->where('ticket_email_attempted_at', $attemptedAt)
            ->update([
                'ticket_email_status' => 'sent',
                'ticket_email_sent_at' => Carbon::now(),
            ]);

        return 'sent';
    }
}

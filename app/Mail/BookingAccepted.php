<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingAccepted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking accepted — downpayment required · #'.$this->booking->booking_id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            text: 'emails.booking-accepted',
            with: [
                'paymentUrl' => route('guest.bookings.payment', $this->booking->booking_id),
                'downpaymentAmount' => $this->booking->downpaymentAmount(),
                'guestName' => $this->booking->displayGuestName(),
            ],
        );
    }

    /**
     * Get the attachments for the e-mail.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

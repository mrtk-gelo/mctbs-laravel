<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingTicket extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking, public string $pdfContents) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your MCTBS booking is confirmed · '.$this->booking->booking_reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.booking-ticket',
            text: 'emails.text.booking-ticket',
            with: [
                'guestName' => $this->booking->displayGuestName(),
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn (): string => $this->pdfContents,
                'booking-ticket-'.$this->booking->booking_reference.'.pdf'
            )->withMime('application/pdf'),
        ];
    }
}

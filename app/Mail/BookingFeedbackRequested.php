<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingFeedbackRequested extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking, public string $feedbackUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'How was your stay? · '.$this->booking->booking_reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.booking-feedback-requested',
            text: 'emails.text.booking-feedback-requested',
            with: [
                'feedbackUrl' => $this->feedbackUrl,
                'guestName' => $this->booking->displayGuestName(),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

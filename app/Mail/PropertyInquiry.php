<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PropertyInquiry extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $propertyName,
        public ?string $unitName,
        public string $senderName,
        public string $senderEmail,
        public string $inquiryMessage
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->senderEmail, $this->senderName)],
            subject: $this->unitName
                ? "Transient inquiry: {$this->unitName} at {$this->propertyName}"
                : "Transient inquiry: {$this->propertyName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            text: 'emails.property-inquiry',
            with: [
                'propertyName' => $this->propertyName,
                'unitName' => $this->unitName,
                'senderName' => $this->senderName,
                'senderEmail' => $this->senderEmail,
                'inquiryMessage' => $this->inquiryMessage,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

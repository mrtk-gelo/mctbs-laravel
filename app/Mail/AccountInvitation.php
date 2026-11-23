<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invitation $invitation,
        public string $invitationUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You are invited to join MCTBS',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.account-invitation',
            text: 'emails.text.account-invitation',
            with: [
                'inviterName' => $this->invitation->invitedBy
                    ? trim($this->invitation->invitedBy->first_name.' '.$this->invitation->invitedBy->last_name)
                    : null,
                'roleName' => $this->invitation->role->displayName(),
                'houseName' => $this->invitation->house?->name,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

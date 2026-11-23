<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetLink extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $resetUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset your MCTBS password',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.password-reset',
            text: 'emails.text.password-reset',
            with: [
                'guestName' => trim($this->user->first_name.' '.$this->user->last_name),
                'expiresInMinutes' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
            ],
        );
    }
}

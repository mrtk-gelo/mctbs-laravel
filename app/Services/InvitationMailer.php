<?php

namespace App\Services;

use App\Mail\AccountInvitation;
use App\Models\Invitation;
use Illuminate\Support\Facades\Mail;

class InvitationMailer
{
    public function send(Invitation $invitation): void
    {
        $invitation->loadMissing(['invitedBy', 'role', 'house']);

        Mail::to($invitation->email)->send(
            new AccountInvitation(
                $invitation,
                route('register', ['token' => $invitation->token])
            )
        );
    }
}

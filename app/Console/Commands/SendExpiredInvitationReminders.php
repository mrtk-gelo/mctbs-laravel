<?php

namespace App\Console\Commands;

use App\Models\Invitation;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;

class SendExpiredInvitationReminders extends Command
{
    protected $signature = 'invitation:remind-expired';

    protected $description = 'Notify invite senders when an invitation has expired without being accepted.';

    public function handle(): int
    {
        $expiredInvitations = Invitation::query()
            ->whereNull('used_at')
            ->where('expires_at', '<=', now())
            ->where(function ($query): void {
                $query->whereNull('reminder_sent_at')
                    ->orWhere('reminder_sent_at', '<', now()->subDay());
            })
            ->with('role')
            ->get();

        foreach ($expiredInvitations as $invitation) {
            $inviter = User::query()->find($invitation->invited_by);

            if (! $inviter) {
                continue;
            }

            Notification::create([
                'user_id' => $inviter->user_id,
                'booking_id' => null,
                'type' => 'invitation_expired',
                'title' => 'Invitation expired',
                'message' => $this->buildMessage($invitation),
                'is_read' => false,
                'created_at' => now(),
            ]);

            $invitation->reminder_sent_at = now();
            $invitation->save();
        }

        $this->info('Expired invitation reminders sent: '.$expiredInvitations->count());

        return self::SUCCESS;
    }

    protected function buildMessage(Invitation $invitation): string
    {
        $roleName = $invitation->role?->name ?? 'Invitation';

        return "The {$roleName} invitation for {$invitation->email} expired before it was accepted. Please send a new invitation.";
    }
}

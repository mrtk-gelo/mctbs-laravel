<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function open(Request $request, int $notificationId): RedirectResponse
    {
        $notification = Notification::query()
            ->where('user_id', $request->user()->user_id)
            ->findOrFail($notificationId);

        if (! $notification->is_read) {
            $notification->is_read = true;
            $notification->read_at = now();
            $notification->save();
        }

        $role = $request->user()->role?->name;
        if ($notification->type === 'invitation_expired') {
            if ($role === 'Account Admin') {
                return redirect()->route('accadmin.owners.index');
            }

            if ($role === 'Owner') {
                return redirect()->route('owner.admins.index');
            }
        }

        if ($notification->booking_id && $role === 'Guest') {
            return redirect()->route('guest.bookings.show', $notification->booking_id);
        }

        if ($notification->booking_id && in_array($role, ['Owner', 'Staff'], true)) {
            $prefix = strtolower($role);

            return redirect()->route($prefix.'.bookings.show', $notification->booking_id);
        }

        if ($notification->conversation_id && $role === 'Guest') {
            return redirect()->route('guest.conversations.show', $notification->conversation_id);
        }

        if ($notification->conversation_id && in_array($role, ['Owner', 'Staff'], true)) {
            return redirect()->route(strtolower($role).'.conversations.show', $notification->conversation_id);
        }

        return redirect()->to($request->user()->dashboardPath());
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return back()->with('success', 'Notifications marked as read.');
    }
}

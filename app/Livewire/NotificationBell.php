<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Component;

class NotificationBell extends Component
{
    public function markAllRead(): void
    {
        auth()->user()->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function render(): View
    {
        $user = auth()->user();

        return view('livewire.notification-bell', [
            'unreadNotificationCount' => $user->notifications()->where('is_read', false)->count(),
            'recentNotifications' => $user->notifications()->latest('created_at')->limit(6)->get(),
        ]);
    }
}

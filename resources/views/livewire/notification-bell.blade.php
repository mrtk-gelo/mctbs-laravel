<div class="notif-wrapper" wire:poll.15s.visible>
    <button
        class="notif-btn"
        type="button"
        data-notification-toggle
        aria-label="Notifications"
        aria-expanded="false"
    >
        <i class="fa fa-bell"></i>
        @if ($unreadNotificationCount > 0)
            <span class="notif-badge">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
        @endif
    </button>
    <div class="notif-dropdown">
        <div class="notif-header">
            <span>Notifications</span>
            @if ($unreadNotificationCount > 0)
                <button type="button" class="mark-all-read" wire:click="markAllRead">Mark all read</button>
            @endif
        </div>
        <div class="notif-list">
            @forelse ($recentNotifications as $notification)
                <a
                    class="notif-item {{ $notification->is_read ? '' : 'unread' }}"
                    href="{{ route('notifications.open', $notification->notification_id) }}"
                >
                    <div class="notif-title">{{ $notification->title }}</div>
                    <div class="notif-msg">{{ $notification->message }}</div>
                    <div class="notif-time">{{ $notification->created_at?->diffForHumans() }}</div>
                </a>
            @empty
                <div class="notif-loading">You have no notifications.</div>
            @endforelse
        </div>
    </div>
</div>

<details class="chat-folder-menu">
    <summary class="chat-actions-trigger" aria-label="More chat options" title="More chat options">
        <i class="fa fa-ellipsis" aria-hidden="true"></i>
    </summary>
    <div class="chat-actions-menu-panel">
        @if ($folder === 'archived')
            <a href="{{ $inboxUrl }}"><i class="fa fa-inbox" aria-hidden="true"></i> Show inbox</a>
        @else
            <a href="{{ $archiveUrl }}"><i class="fa fa-box-archive" aria-hidden="true"></i> Show archive</a>
        @endif
    </div>
</details>

<div class="nav-dropdown profile-dropdown">
    <button
        class="profile-btn"
        type="button"
        aria-controls="profileMenu"
        aria-expanded="false"
        onclick="const isOpen = this.closest('.nav-dropdown').classList.toggle('open'); this.setAttribute('aria-expanded', isOpen)"
    >
        <span class="avatar-initials">{{ $navUser->initials() }}</span>
        <span>{{ $navUser->first_name }}</span>
        <i class="fa fa-chevron-down fa-xs"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-right" id="profileMenu">
        <div class="profile-menu-header">
            <div class="profile-menu-name">{{ $navUser->first_name }} {{ $navUser->last_name }}</div>
            <div class="profile-menu-role">{{ $navUser->role?->displayName() ?? 'Member' }}</div>
        </div>
        <a href="{{ route('profile.edit') }}"><i class="fa fa-user-pen"></i> Profile</a>
        <a href="{{ route('profile.security') }}"><i class="fa fa-shield-halved"></i> Session &amp; account</a>
        <hr>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-danger"><i class="fa fa-right-from-bracket"></i> Logout</button>
        </form>
    </div>
</div>

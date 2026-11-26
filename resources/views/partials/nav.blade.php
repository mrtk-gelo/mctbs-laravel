@php
    $navUser = auth()->user();
    $roleName = $navUser?->role?->name;
@endphp
<nav class="topnav">
    <div class="topnav-brand">
        <a href="{{ $navUser ? $navUser->dashboardPath() : route('home') }}" aria-label="MCTBS dashboard">
            <span class="brand-icon"></span>
        </a>
    </div>

    <div class="topnav-center">
        @if ($navUser)
            @switch($roleName)
                @case('Account Admin')
                    <a href="{{ route('accadmin.dashboard') }}" class="{{ ($activePage ?? '') === 'dashboard' ? 'active' : '' }}"><i class="fa fa-gauge"></i> Dashboard</a>
                    <a href="{{ route('accadmin.owners.index') }}" class="{{ ($activePage ?? '') === 'owners' ? 'active' : '' }}"><i class="fa fa-users"></i> Owners</a>
                    @break

                @case('Owner')
                    <a href="{{ route('owner.dashboard') }}" class="{{ ($activePage ?? '') === 'dashboard' ? 'active' : '' }}"><i class="fa fa-gauge"></i> Dashboard</a>
                    <a href="{{ route('owner.houses.index') }}" class="{{ in_array($activePage ?? '', ['houses', 'units'], true) ? 'active' : '' }}"><i class="fa fa-building"></i> Properties</a>
                    <a href="{{ route('owner.bookings.index') }}" class="{{ ($activePage ?? '') === 'bookings' ? 'active' : '' }}"><i class="fa fa-calendar-check"></i> Bookings</a>
                    <a href="{{ route('owner.conversations.index') }}" class="{{ ($activePage ?? '') === 'messages' ? 'active' : '' }}"><i class="fa fa-comments"></i> Messages</a>
                    <a href="{{ route('owner.assistance.index') }}" class="{{ ($activePage ?? '') === 'assistance' ? 'active' : '' }}"><i class="fa fa-book-open"></i> Assistance</a>
                    <a href="{{ route('owner.bookings.index', ['section' => 'calendar']) }}" class="{{ ($activePage ?? '') === 'calendar' ? 'active' : '' }}"><i class="fa fa-calendar"></i> Calendar</a>
                    <a href="{{ route('owner.bookings.index', ['section' => 'reports']) }}" class="{{ ($activePage ?? '') === 'reports' ? 'active' : '' }}"><i class="fa fa-chart-bar"></i> Reports</a>
                    <a href="{{ route('owner.admins.index') }}" class="{{ ($activePage ?? '') === 'staff' ? 'active' : '' }}"><i class="fa fa-user-shield"></i> Staff</a>
                    @break

                @case('Staff')
                    <a href="{{ route('staff.dashboard') }}" class="{{ ($activePage ?? '') === 'dashboard' ? 'active' : '' }}"><i class="fa fa-gauge"></i> Dashboard</a>
                    <a href="{{ route('staff.houses.index') }}" class="{{ in_array($activePage ?? '', ['houses', 'units'], true) ? 'active' : '' }}"><i class="fa fa-building"></i> Properties</a>
                    <a href="{{ route('staff.bookings.index') }}" class="{{ ($activePage ?? '') === 'bookings' ? 'active' : '' }}"><i class="fa fa-calendar-check"></i> Bookings</a>
                    <a href="{{ route('staff.conversations.index') }}" class="{{ ($activePage ?? '') === 'messages' ? 'active' : '' }}"><i class="fa fa-comments"></i> Messages</a>
                    <a href="{{ route('staff.assistance.index') }}" class="{{ ($activePage ?? '') === 'assistance' ? 'active' : '' }}"><i class="fa fa-book-open"></i> Assistance</a>
                    <a href="{{ route('staff.bookings.index', ['section' => 'calendar']) }}" class="{{ ($activePage ?? '') === 'calendar' ? 'active' : '' }}"><i class="fa fa-calendar"></i> Calendar</a>
                    <a href="{{ route('staff.bookings.index', ['section' => 'reports']) }}" class="{{ ($activePage ?? '') === 'reports' ? 'active' : '' }}"><i class="fa fa-chart-bar"></i> Reports</a>
                    @break

                @case('Guest')
                    <a href="{{ request()->routeIs('home') ? '#home' : route('home').'#home' }}" data-scroll-nav="home" class="{{ ($activePage ?? '') === 'home' ? 'active' : '' }}"><i class="fa fa-home"></i> Home</a>
                    <a href="{{ route('home').'#transients' }}" data-scroll-nav="transients" class="{{ ($activePage ?? '') === 'transients' ? 'active' : '' }}"><i class="fa fa-search"></i> Transients</a>
                    <a href="{{ route('guest.dashboard') }}" class="{{ ($activePage ?? '') === 'dashboard' ? 'active' : '' }}"><i class="fa fa-calendar-check"></i> Bookings</a>
                    <a href="{{ route('guest.conversations.index') }}" class="{{ ($activePage ?? '') === 'messages' ? 'active' : '' }}"><i class="fa fa-comments"></i> Messages</a>
                    @break
            @endswitch
        @else
            <a href="{{ request()->routeIs('home') ? '#home' : route('home').'#home' }}" data-scroll-nav="home" class="{{ ($activePage ?? '') === 'home' ? 'active' : '' }}"><i class="fa fa-home"></i> Home</a>
            <a href="{{ route('home').'#transients' }}" data-scroll-nav="transients" class="{{ ($activePage ?? '') === 'transients' ? 'active' : '' }}"><i class="fa fa-search"></i> Transients</a>
        @endif
    </div>

    <div class="topnav-right">
        @if ($navUser)
            @if (in_array($roleName, ['Account Admin', 'Owner', 'Staff', 'Guest'], true))
                @include('partials.notification-bell')
            @endif

            @include('partials.profile-menu')
        @else
            <a href="{{ route('login') }}" class="btn-outline-sm">Log in</a>
            <a href="{{ route('register') }}" class="btn-primary-sm">Register</a>
        @endif
    </div>
</nav>
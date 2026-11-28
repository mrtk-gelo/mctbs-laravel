<aside class="aa-sidebar" id="aa-sidebar" aria-label="Account Admin navigation">
    <a href="{{ route('accadmin.dashboard') }}" class="aa-brand" aria-label="MCTBS Account Admin dashboard">
        <span class="aa-brand-tile"><span class="brand-icon" aria-hidden="true"></span></span>
        <span class="aa-brand-text"><strong>MCTBS</strong><span>Account Admin</span></span>
    </a>

    <nav class="aa-nav">
        <div class="aa-nav-group">
            <div class="aa-nav-label">Overview</div>
            <a href="{{ route('accadmin.dashboard') }}" class="aa-nav-link" @if (request()->routeIs('accadmin.dashboard')) aria-current="page" @endif>
                <i class="fa fa-gauge" aria-hidden="true"></i> Dashboard
            </a>
        </div>

        <div class="aa-nav-group">
            <div class="aa-nav-label">Accounts</div>
            <a href="{{ route('accadmin.owners.index') }}" class="aa-nav-link" @if (request()->routeIs('accadmin.owners.index')) aria-current="page" @endif>
                <i class="fa fa-users" aria-hidden="true"></i> Owners
            </a>
            <a href="{{ route('accadmin.owners.deactivated') }}" class="aa-nav-link" @if (request()->routeIs('accadmin.owners.deactivated')) aria-current="page" @endif>
                <i class="fa fa-user-slash" aria-hidden="true"></i> Deactivated owners
            </a>
        </div>

        <div class="aa-nav-group">
            <div class="aa-nav-label">System</div>
            <a href="{{ route('accadmin.dashboard') }}#backups" class="aa-nav-link">
                <i class="fa fa-database" aria-hidden="true"></i> Database backups
            </a>
        </div>
    </nav>

    <div class="aa-sidebar-foot">Property listings and bookings are managed by owners and their staff.</div>
</aside>

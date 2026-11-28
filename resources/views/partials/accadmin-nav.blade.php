@php
    $activePage = match (true) {
        request()->routeIs('accadmin.backups.index') => 'backups',
        request()->routeIs('accadmin.owners.deactivated') => 'deactivated-owners',
        request()->routeIs('accadmin.owners.*') => 'owners',
        default => 'dashboard',
    };
@endphp
@include('partials.nav', ['activePage' => $activePage])

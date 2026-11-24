@php
    $activePage = $activePage ?? 'dashboard';
@endphp
@include('partials.nav', ['activePage' => $activePage])

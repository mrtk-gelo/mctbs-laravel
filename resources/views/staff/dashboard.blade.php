<x-shell title="Staff Dashboard | MCTBS">
    <x-slot:nav>@include('partials.staff-nav', ['activePage' => 'dashboard'])</x-slot:nav>
    @include('partials.booking-work-queues', ['dashboardPrefix' => 'staff'])
</x-shell>

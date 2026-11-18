<x-shell title="Owner Dashboard | MCTBS">
    <x-slot:nav>@include('partials.owner-nav', ['activePage' => 'dashboard'])</x-slot:nav>
    @include('partials.booking-work-queues', ['dashboardPrefix' => 'owner'])
</x-shell>

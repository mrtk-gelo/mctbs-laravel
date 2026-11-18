<x-shell title="Bookings | MCTBS">
    <x-slot:nav>
        @if ($dashboardPrefix === 'owner')
            @include('partials.owner-nav', ['activePage' => 'bookings'])
        @else
            @include('partials.staff-nav', ['activePage' => 'bookings'])
        @endif
    </x-slot:nav>

    <div class="container" style="margin-top:24px" data-booking-filter-root>


        <div class="page-header-row page-header">
            <div>
                <h1>Bookings</h1>
                <p>Review booking requests, active reservations, and booking history.</p>
            </div>
            <a href="{{ route($dashboardPrefix.'.bookings.create') }}" class="btn btn-primary">Add Booking</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success mb-3" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger mb-3" role="alert">{{ $errors->first() }}</div>
        @endif

        @include('bookings.partials.list-filters')

        @include('bookings.partials.manage-results')
    </div>
</x-shell>

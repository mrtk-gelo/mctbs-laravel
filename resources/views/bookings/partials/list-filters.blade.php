<form class="booking-list-filters" role="search" data-booking-filters>
    <div class="booking-list-filter-field booking-list-filter-search">
        <label for="booking-list-search">Search bookings</label>
        <input id="booking-list-search" type="search" value="{{ $search ?? '' }}" maxlength="100" placeholder="Booking number, guest, unit, or property" data-booking-search>
    </div>
    <div class="booking-list-filter-field">
        <label for="booking-list-from">Stay overlaps from</label>
        <input id="booking-list-from" type="date" data-booking-from>
    </div>
    <div class="booking-list-filter-field">
        <label for="booking-list-to">Through</label>
        <input id="booking-list-to" type="date" data-booking-to>
    </div>
    <button type="button" class="btn btn-outline booking-list-sort" data-booking-sort data-direction="newest">
        <i class="fa fa-arrow-down" data-booking-sort-icon aria-hidden="true"></i>
        <span data-booking-sort-label>Newest first</span>
    </button>
    <button type="reset" class="btn btn-outline">Clear</button>
</form>

<div id="transient-results">
    <div class="results-bar">
        <div class="results-count">
            Showing <strong>{{ $houses->count() }}</strong> propert{{ $houses->count() === 1 ? 'y' : 'ies' }}
            @if ($search) for "<strong>{{ $search }}</strong>"@endif
        </div>
    </div>

    @if ($houses->isNotEmpty())
        <div class="unit-grid transient-house-grid">
            @foreach ($houses as $h)
                <div class="unit-card">
                    <a href="{{ route('public.house', $h) }}" class="house-card">
                        @if ($h->cover_photo)
                            <img src="{{ asset('storage/' . $h->cover_photo) }}" class="house-card-img" alt="{{ $h->name }}">
                        @else
                            <div class="house-card-img-placeholder"><i class="fa fa-building"></i></div>
                        @endif
                        <div class="house-card-body">
                            <h3>{{ $h->name }}</h3>
                            <div class="house-card-location">
                                <i class="fa fa-map-marker-alt"></i>
                                {{ $h->city }}{{ $h->barangay ? ', '.$h->barangay : '' }}
                            </div>
                            @if ($h->description)
                                <p class="house-card-description">
                                    {{ \Illuminate\Support\Str::limit($h->description, 100, '...') }}
                                </p>
                            @endif
                            <div class="house-card-meta">
                                <div class="house-card-units">
                                    <i class="fa fa-door-open"></i> {{ $h->units_count }} unit{{ $h->units_count != 1 ? 's' : '' }}
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state transient-empty-state">
            <i class="fa fa-building"></i>
            <h3>No properties found</h3>
            <p>{{ $search ? 'Try a different search term or location.' : 'No transient houses are available yet.' }}</p>
        </div>
    @endif

    @if ($hasMoreHouses ?? false)
        <div class="transient-results-more">
            <a href="{{ route('public.transients') }}" class="btn btn-outline btn-sm">View More Transient</a>
        </div>
    @endif
</div>

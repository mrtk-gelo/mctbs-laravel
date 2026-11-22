<x-shell title="Transient Houses | MCTBS">
    <x-slot:nav>
        @if (auth()->user()->role->name === 'Staff')
            @include('partials.staff-nav', ['activePage' => 'houses'])
        @else
            @include('partials.owner-nav', ['activePage' => 'houses'])
        @endif
    </x-slot:nav>

    <div class="container" style="margin-top:24px">
        <div class="page-header-row page-header mb-3">
            <div>
                <h1>Transient Houses</h1>
                <p>Manage your transient houses and units.</p>
            </div>
            @if (auth()->user()->role->name === 'Owner')
                <a href="{{ route('owner.houses.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Add Transient House
                </a>
            @endif
        </div>

        <h2 class="management-section-title">{{ auth()->user()->role->name === 'Owner' ? 'Your Houses' : 'Assigned Houses' }}</h2>
        <div class="management-property-list">
            @forelse ($houses as $house)
                <article class="management-property-card unit-card">
                    <div class="management-property-image">
                        @if ($house->cover_photo)
                            <img class="unit-card-img" src="{{ asset('storage/' . $house->cover_photo) }}" alt="{{ $house->name }} cover photo">
                        @else
                            <div class="unit-card-img-placeholder" aria-label="No cover photo">
                                <i class="fa fa-building"></i>
                            </div>
                        @endif
                    </div>
                    <div class="management-property-content unit-card-body house-card-body">
                        <div class="management-property-details">
                            <div>
                                <span class="management-property-eyebrow">Transient House</span>
                                <h2>{{ $house->name }}</h2>
                                <p class="management-property-location">
                                    <i class="fa fa-location-dot"></i>
                                    {{ $house->address }}{{ $house->barangay ? ', '.$house->barangay : '' }}, {{ $house->city }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route(auth()->user()->role->name === 'Staff' ? 'staff.houses.availability' : 'owner.houses.availability', $house) }}" class="availability-control">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="is_active" value="0">
                                <label class="availability-switch">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        aria-label="{{ $house->is_active ? 'Hide house from public site' : 'Show house on public site' }}"
                                        @checked($house->is_active)
                                        onchange="this.form.requestSubmit()"
                                    >
                                    <span class="availability-slider"></span>
                                </label>
                                <span class="availability-label">{{ $house->is_active ? 'Public' : 'Hidden' }}</span>
                            </form>
                        </div>
                        <div class="management-property-footer house-card-meta">
                            <span class="management-property-meta">
                                <i class="fa fa-door-open"></i>
                                {{ $house->units_count }} unit{{ $house->units_count === 1 ? '' : 's' }}
                            </span>
                            <div class="management-property-actions">
                                <a href="{{ route(auth()->user()->role->name === 'Staff' ? 'staff.houses.edit' : 'owner.houses.edit', $house) }}" class="btn btn-outline btn-sm">
                                    <i class="fa fa-pen"></i> Edit
                                </a>
                                <a href="{{ route(auth()->user()->role->name === 'Staff' ? 'staff.houses.units' : 'owner.houses.units', $house) }}" class="btn btn-primary btn-sm">
                                    Manage Units <i class="fa fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-0">No transient houses found.</p>
                    </div>
                </div>
            @endforelse
        </div>
        <div class="mt-4">
            <a href="{{ route(auth()->user()->role->name === 'Staff' ? 'staff.houses.archive.index' : 'owner.houses.archive.index') }}" class="btn btn-outline">
                <i class="fa fa-box-archive"></i> View Archived Properties
            </a>
        </div>
    </div>
</x-shell>

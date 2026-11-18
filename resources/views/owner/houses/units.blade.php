<x-shell title="Manage Units | MCTBS">
    <x-slot:nav>
        @if (auth()->user()->role->name === 'Staff')
            @include('partials.staff-nav', ['activePage' => 'units'])
        @else
            @include('partials.owner-nav', ['activePage' => 'units'])
        @endif
    </x-slot:nav>

    <div class="container" style="margin-top:24px">
        <div class="flex-between mb-3">
            <div>
                <h1 class="mb-1">{{ $house->name }}</h1>
                <p class="text-muted">{{ $house->address }}{{ $house->barangay ? ', '.$house->barangay : '' }}, {{ $house->city }}</p>
            </div>
            @if (auth()->user()->role->name === 'Owner')
                <a href="{{ route('owner.houses.units.create', $house) }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Add Unit
                </a>
            @else
                <a href="{{ route('staff.houses.units.create', $house) }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Add Unit
                </a>
            @endif
        </div>

        <h2 class="management-section-title">Units</h2>
        <div class="management-property-list">
            @forelse ($units as $unit)
                <article class="management-property-card unit-card">
                    <div class="management-property-image">
                        @if ($unit->photos->isNotEmpty())
                            <img class="unit-card-img" src="{{ asset('storage/' . $unit->photos->first()->photo_path) }}" alt="{{ $unit->unit_type }} photo">
                        @elseif ($house->cover_photo)
                            <img class="unit-card-img" src="{{ asset('storage/' . $house->cover_photo) }}" alt="{{ $unit->unit_type }} at {{ $house->name }}">
                        @else
                            <div class="unit-card-img-placeholder" aria-label="No house cover photo">
                                <i class="fa fa-door-open"></i>
                            </div>
                        @endif
                    </div>
                    <div class="management-property-content unit-card-body house-card-body">
                        <div class="management-property-details">
                            <div>
                                <span class="management-property-eyebrow">Unit at {{ $house->name }}</span>
                                <h2>{{ $unit->unit_type }}</h2>
                                <p class="management-property-location">
                                    <i class="fa fa-users"></i> {{ $unit->capacity }} standard guests; max {{ $unit->maximum_capacity ?? $unit->capacity }}
                                    @if ((int) ($unit->maximum_capacity ?? $unit->capacity) > (int) $unit->capacity)
                                        · extra {{ number_format((float) $unit->extra_guest_fee, 2) }}/guest/night
                                    @endif
                                </p>
                                @if ($unit->description)
                                    <p class="management-property-description">{{ $unit->description }}</p>
                                @endif
                            </div>
                            @if (in_array($unit->status, ['Available', 'Unavailable'], true))
                                <form method="POST" action="{{ route(auth()->user()->role->name === 'Staff' ? 'staff.houses.units.availability' : 'owner.houses.units.availability', [$house, $unit]) }}" class="availability-control">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="Unavailable">
                                    <label class="availability-switch">
                                        <input
                                            type="checkbox"
                                            name="status"
                                            value="Available"
                                            aria-label="{{ $unit->status === 'Available' ? 'Hide unit from public site' : 'Show unit on public site' }}"
                                            @checked($unit->status === 'Available')
                                            onchange="this.form.requestSubmit()"
                                        >
                                        <span class="availability-slider"></span>
                                    </label>
                                    <span class="availability-label">{{ $unit->status === 'Available' ? 'Public' : 'Hidden' }}</span>
                                </form>
                            @else
                                <span class="badge badge-booked">Booked</span>
                            @endif
                        </div>
                        <div class="management-property-footer house-card-meta">
                            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                                <span class="management-property-price">₱{{ number_format($unit->price, 2) }} <small>/ night</small></span>
                                <span class="management-property-meta"><i class="fa fa-images"></i> {{ $unit->photos->count() }} photo{{ $unit->photos->count() === 1 ? '' : 's' }}</span>
                            </div>
                            <a href="{{ route(auth()->user()->role->name === 'Staff' ? 'staff.houses.units.edit' : 'owner.houses.units.edit', [$house, $unit]) }}" class="btn btn-outline btn-sm">
                                <i class="fa fa-pen"></i> Edit Unit
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-0">No units have been added to this house yet.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</x-shell>

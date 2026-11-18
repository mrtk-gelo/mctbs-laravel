<x-shell title="Edit Unit | MCTBS">
    <x-slot:nav>
        @if (auth()->user()->role->name === 'Staff')
            @include('partials.staff-nav', ['activePage' => 'units'])
        @else
            @include('partials.owner-nav', ['activePage' => 'units'])
        @endif
    </x-slot:nav>

    <div class="container" style="margin-top:24px;max-width:900px">
        <div class="page-header">
            <h1>Edit Unit</h1>
            <p>{{ $house->name }} · {{ $house->address }}{{ $house->barangay ? ', '.$house->barangay : '' }}, {{ $house->city }}</p>
        </div>



        <div class="card">
            <div class="card-header">Unit Details</div>
            <div class="card-body">
                <form method="POST" action="{{ route(auth()->user()->role->name === 'Staff' ? 'staff.houses.units.update' : 'owner.houses.units.update', [$house, $unit]) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="form-row">
                        <div class="form-group" style="flex:2">
                            <label class="required" for="unit_type">Unit type</label>
                            <input id="unit_type" type="text" name="unit_type" value="{{ old('unit_type', $unit->unit_type) }}" maxlength="100" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="required" for="status">Public availability</label>
                            <select id="status" name="status" required>
                                @foreach (['Available', 'Unavailable', 'Booked'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', $unit->status) === $status)>
                                        {{ $status === 'Available' ? 'Available to the public' : ($status === 'Unavailable' ? 'Hidden from the public' : $status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="flex:1">
                            <label class="required" for="capacity">Capacity</label>
                            <input id="capacity" type="number" name="capacity" value="{{ old('capacity', $unit->capacity) }}" min="1" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="required" for="maximum_capacity">Maximum counted guests</label>
                            <input id="maximum_capacity" type="number" name="maximum_capacity" value="{{ old('maximum_capacity', $unit->maximum_capacity ?? $unit->capacity) }}" min="{{ old('capacity', $unit->capacity) }}" required>
                            <p class="form-hint">The maximum total guests, including free children.</p>
                        </div>
                    </div>
                    <div class="form-row unit-time-fields">
                        <div class="form-group" style="flex:1">
                            <label class="required" for="check_in_time">Check-in time</label>
                            <input id="check_in_time" type="time" name="check_in_time" value="{{ old('check_in_time', substr((string) $unit->check_in_time, 0, 5)) }}" required>
                            @error('check_in_time')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="required" for="check_out_time">Check-out time</label>
                            <input id="check_out_time" type="time" name="check_out_time" value="{{ old('check_out_time', substr((string) $unit->check_out_time, 0, 5)) }}" required>
                            @error('check_out_time')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    @include('owner.houses.partials.unit-pricing-fields', [
                        'pricingMode' => old('pricing_mode', $unit->pricing_mode),
                        'price' => old('price', $unit->price),
                        'extraGuestFee' => old('extra_guest_fee', $unit->extra_guest_fee ?? 0),
                    ])
                    <div class="form-group">
                        <label for="child_free_through_age">Free child age limit <span class="text-muted fs-sm">(optional)</span></label>
                        <input id="child_free_through_age" type="number" name="child_free_through_age" value="{{ old('child_free_through_age', $unit->child_free_through_age) }}" min="0" max="17" placeholder="Leave blank if no free-child age">
                        <p class="form-hint">Children through this age are free but still count toward maximum capacity. For example, enter 3 for ages 0–3.</p>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4">{{ old('description', $unit->description) }}</textarea>
                    </div>
                    @include('owner.houses.partials.unit-extra-items', [
                        'addons' => old('addons', $unit->addons->map(fn ($addon) => ['name' => $addon->name, 'price' => $addon->price])->all()),
                        'damageItems' => old('damage_items', $unit->damageItems->map(fn ($item) => ['name' => $item->name, 'price' => $item->price])->all()),
                    ])
                    <div class="form-group">
                        <label>Current Unit Photos</label>
                        @if ($unit->photos->isNotEmpty())
                            <div class="unit-photo-management-grid">
                                @foreach ($unit->photos as $photo)
                                    <label class="unit-photo-management-item">
                                        <img src="{{ asset('storage/' . $photo->photo_path) }}" alt="Unit photo {{ $loop->iteration }}">
                                        <span class="unit-photo-management-caption">
                                            @if ($photo->is_cover)
                                                <strong>Main photo</strong>
                                            @endif
                                            <span><input type="checkbox" name="remove_photos[]" value="{{ $photo->photo_id }}"> Remove</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No unit photos uploaded yet.</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="photos">Add More Photos</label>
                        <input id="photos" type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple>
                        <p class="form-hint">The unit can have up to 10 photos total. JPG, PNG, or WebP; maximum 5 MB each.</p>
                    </div>
                    <div style="display:flex;gap:10px">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk"></i> Save Changes</button>
                        <a href="{{ route(auth()->user()->role->name === 'Staff' ? 'staff.houses.units' : 'owner.houses.units', $house) }}" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-shell>

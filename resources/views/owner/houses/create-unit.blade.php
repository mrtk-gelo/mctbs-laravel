<x-shell title="Add Unit | MCTBS">
    <x-slot:nav>
        @if (auth()->user()->role->name === 'Staff')
            @include('partials.staff-nav', ['activePage' => 'units'])
        @else
            @include('partials.owner-nav', ['activePage' => 'units'])
        @endif
    </x-slot:nav>

    <div class="container" style="margin-top:24px;max-width:900px">
        <div class="page-header">
            <h1>Add Unit</h1>
            <p>{{ $house->name }} · {{ $house->city }}</p>
        </div>



        <div class="card">
            <div class="card-header">Unit Details</div>
            <div class="card-body">
                @php
                    $routeName = auth()->user()->role->name === 'Staff'
                        ? 'staff.houses.units.store'
                        : 'owner.houses.units.store';
                @endphp
                <form method="POST" action="{{ route($routeName, $house) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-row">
                        <div class="form-group" style="flex:2">
                            <label class="required">Unit type</label>
                            <input type="text" name="unit_type" value="{{ old('unit_type') }}" maxlength="100" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="required">Status</label>
                            <select name="status" required>
                                @foreach (['Available', 'Unavailable', 'Booked'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', 'Available') === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="flex:1">
                            <label class="required">Capacity</label>
                            <input type="number" name="capacity" value="{{ old('capacity', 1) }}" min="1" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="required" for="maximum_capacity">Maximum counted guests</label>
                            <input id="maximum_capacity" type="number" name="maximum_capacity" value="{{ old('maximum_capacity', old('capacity', 1)) }}" min="{{ old('capacity', 1) }}" required>
                            <p class="form-hint">The maximum total guests, including free children.</p>
                        </div>
                    </div>
                    <div class="form-row unit-time-fields">
                        <div class="form-group" style="flex:1">
                            <label class="required" for="check_in_time">Check-in time</label>
                            <input id="check_in_time" type="time" name="check_in_time" value="{{ old('check_in_time', '14:00') }}" required>
                            @error('check_in_time')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="required" for="check_out_time">Check-out time</label>
                            <input id="check_out_time" type="time" name="check_out_time" value="{{ old('check_out_time', '12:00') }}" required>
                            @error('check_out_time')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    @include('owner.houses.partials.unit-pricing-fields', [
                        'pricingMode' => old('pricing_mode', 'Per Unit'),
                        'price' => old('price'),
                        'extraGuestFee' => old('extra_guest_fee', 0),
                    ])
                    <div class="form-group">
                        <label for="child_free_through_age">Free child age limit <span class="text-muted fs-sm">(optional)</span></label>
                        <input id="child_free_through_age" type="number" name="child_free_through_age" value="{{ old('child_free_through_age') }}" min="0" max="17" placeholder="Leave blank if no free-child age">
                        <p class="form-hint">Children through this age are free but still count toward maximum capacity. For example, enter 3 for ages 0–3.</p>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4">{{ old('description') }}</textarea>
                    </div>
                    @include('owner.houses.partials.unit-extra-items', [
                        'addons' => old('addons', []),
                        'damageItems' => old('damage_items', []),
                    ])
                    <div class="form-group">
                        <label for="photos">Unit Photos</label>
                        <input id="photos" type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple>
                        <p class="form-hint">Add up to 10 JPG, PNG, or WebP photos, maximum 10 MB each. The first photo is the main photo guests see.</p>
                    </div>
                    <div style="display:flex;gap:10px">
                        <button type="submit" class="btn btn-primary">Save Unit</button>
                        @if (auth()->user()->role->name === 'Staff')
                            <a href="{{ route('staff.houses.units', $house) }}" class="btn btn-outline">Cancel</a>
                        @else
                            <a href="{{ route('owner.houses.units', $house) }}" class="btn btn-outline">Cancel</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-shell>

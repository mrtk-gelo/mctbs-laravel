<x-shell title="Edit Transient House | MCTBS">
    <x-slot:nav>
        @if (auth()->user()->role->name === 'Staff')
            @include('partials.staff-nav', ['activePage' => 'houses'])
        @else
            @include('partials.owner-nav', ['activePage' => 'houses'])
        @endif
    </x-slot:nav>

    <div class="container" style="margin-top:24px;max-width:900px">
        <div class="page-header">
            <h1>Edit Transient House</h1>
            <p>Update the details for {{ $house->name }}.</p>
        </div>



        <div class="card">
            <div class="card-header">House Details</div>
            <div class="card-body">
                <form method="POST" action="{{ route(auth()->user()->role->name === 'Staff' ? 'staff.houses.update' : 'owner.houses.update', $house) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="form-row">
                        <div class="form-group" style="flex:1">
                            <label class="required" for="name">House name</label>
                            <input id="name" type="text" name="name" value="{{ old('name', $house->name) }}" maxlength="255" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="required" for="city">City</label>
                            <input id="city" type="text" name="city" value="{{ old('city', $house->city) }}" maxlength="100" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="flex:2">
                            <label class="required" for="address">Address</label>
                            <input id="address" type="text" name="address" value="{{ old('address', $house->address) }}" maxlength="500" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label for="barangay">Barangay</label>
                            <input id="barangay" type="text" name="barangay" value="{{ old('barangay', $house->barangay) }}" maxlength="100">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="required" for="contact_number">Contact number</label>
                        <input id="contact_number" type="text" name="contact_number" value="{{ old('contact_number', $house->contact_number) }}" maxlength="30" required>
                    </div>
                    @include('owner.houses.partials.social-links-fields', ['socialLinks' => $house->social_links ?? []])
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4">{{ old('description', $house->description) }}</textarea>
                    </div>
                    @include('owner.houses.partials.policy-fields', ['policies' => $house->policies])
                    <div class="form-group">
                        <label for="cover_photo">Cover photo</label>
                        @if ($house->cover_photo && ! old('remove_cover_photo'))
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $house->cover_photo) }}" alt="{{ $house->name }} cover photo" style="width:180px;height:120px;object-fit:cover;border-radius:8px">
                            </div>
                            <label class="checkbox-group">
                                <input type="checkbox" name="remove_cover_photo" value="1" @checked(old('remove_cover_photo'))>
                                <span>Remove current cover photo</span>
                            </label>
                        @endif
                        <input id="cover_photo" type="file" name="cover_photo" accept="image/jpeg,image/png,image/webp">
                        <p class="form-hint">Upload a new JPG, PNG, or WebP image to replace the current one; maximum 5 MB.</p>
                    </div>
                    <div style="display:flex;gap:10px">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk"></i> Save Changes</button>
                        <a href="{{ route(auth()->user()->role->name === 'Staff' ? 'staff.houses.index' : 'owner.houses.index') }}" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-shell>

<x-shell title="Add Transient House | MCTBS">
    <x-slot:nav>@include('partials.owner-nav', ['activePage' => 'houses'])</x-slot:nav>

    <div class="container" style="margin-top:24px;max-width:900px">
        <div class="page-header">
            <h1>Add Transient House</h1>
            <p>Enter the details for your new transient house.</p>
        </div>



        <div class="card">
            <div class="card-header">House Details</div>
            <div class="card-body">
                <form method="POST" action="{{ route('owner.houses.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-row">
                        <div class="form-group" style="flex:1">
                            <label class="required">House name</label>
                            <input type="text" name="name" value="{{ old('name') }}" maxlength="255" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="required">City</label>
                            <input type="text" name="city" value="{{ old('city') }}" maxlength="100" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="flex:2">
                            <label class="required">Address</label>
                            <input type="text" name="address" value="{{ old('address') }}" maxlength="500" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label>Barangay</label>
                            <input type="text" name="barangay" value="{{ old('barangay') }}" maxlength="100">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="required">Contact number</label>
                        <input type="text" name="contact_number" value="{{ old('contact_number') }}" maxlength="30" required>
                    </div>
                    @include('owner.houses.partials.social-links-fields')
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4">{{ old('description') }}</textarea>
                    </div>
                    @include('owner.houses.partials.policy-fields', ['policies' => []])
                    <div class="form-group">
                        <label>Cover photo</label>
                        <input type="file" name="cover_photo" accept="image/jpeg,image/png,image/webp">
                        <p class="form-hint">JPG, PNG, or WebP image; maximum 5 MB.</p>
                    </div>
                    <div style="display:flex;gap:10px">
                        <button type="submit" class="btn btn-primary">Save House</button>
                        <a href="{{ route('owner.houses.index') }}" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-shell>

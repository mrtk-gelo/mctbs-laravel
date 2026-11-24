<x-shell title="Edit Profile | MCTBS">
    <x-slot:nav>
        @switch($user->role->name)
            @case('Account Admin')
                @include('partials.accadmin-nav', ['activePage' => 'profile'])
                @break
            @case('Owner')
                @include('partials.owner-nav', ['activePage' => 'profile'])
                @break
            @case('Staff')
                @include('partials.staff-nav', ['activePage' => 'profile'])
                @break
            @default
                @include('partials.nav', ['activePage' => 'profile'])
        @endswitch
    </x-slot:nav>

    <div class="container workspace-page profile-page">
        <div class="page-header">
            <h1>Profile</h1>
            <p>Manage your personal information and contact details.</p>
        </div>

        <section class="card profile-settings-card" id="profile-information">
            <form method="POST" action="{{ route('profile.update') }}" class="profile-settings-form">
                @csrf
                @method('PUT')
                <div class="card-header"><span><i class="fa fa-user-pen"></i> Profile information</span></div>
                <div class="card-body">
                    <p class="profile-section-intro">These details are shared with guests and teammates when you manage a booking or property.</p>
                    <div class="profile-identity">
                        <span class="avatar-initials" aria-hidden="true">{{ $user->initials() }}</span>
                        <span><strong>{{ $user->first_name }} {{ $user->last_name }}</strong><small>Your initials are used throughout MCTBS.</small></span>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="flex:1">
                            <label class="required" for="first_name">First Name <span class="field-requirement">Required</span></label>
                            <input id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" maxlength="100" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label class="required" for="last_name">Last Name <span class="field-requirement">Required</span></label>
                            <input id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" maxlength="100" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address <span class="field-requirement">Required</span></label>
                        <input id="email" type="email" value="{{ $user->email }}" readonly>
                        <small class="text-muted">Email changes are disabled so account verification remains valid.</small>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone <span class="field-requirement">Optional</span></label>
                        <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="30">
                    </div>
                </div>
                <div class="profile-save-bar">
                    <span class="text-muted fs-sm">Your password is not required to update profile details.</span>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk"></i> Save profile</button>
                </div>
            </form>
        </section>

    </div>
</x-shell>

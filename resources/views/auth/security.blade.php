<x-shell title="Session &amp; Account | MCTBS">
    <x-slot:nav>
        @switch($user->role->name)
            @case('System Admin')
                @include('partials.sysadmin-nav', ['activePage' => 'profile'])
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
            <h1>Session &amp; account</h1>
            <p>Change your password independently from your profile details.</p>
        </div>



        <section class="card profile-settings-card">
            <form method="POST" action="{{ route('profile.password.update') }}" class="profile-settings-form">
                @csrf
                @method('PUT')
                <div class="card-header"><span><i class="fa fa-shield-halved"></i> Password</span></div>
                <div class="card-body">
                    <p class="profile-section-intro">Use a strong password you have not used elsewhere.</p>
                    <div class="form-group">
                        <label for="current_password">Current password <span class="field-requirement">Required</span></label>
                        <input id="current_password" type="password" name="current_password" autocomplete="current-password" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="flex:1">
                            <label for="password">New password <span class="field-requirement">Required</span></label>
                            <input id="password" type="password" name="password" autocomplete="new-password" required>
                            <small class="field-hint">At least 8 characters, with uppercase and lowercase letters, a number, and a symbol.</small>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label for="password_confirmation">Confirm new password <span class="field-requirement">Required</span></label>
                            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                        </div>
                    </div>
                </div>
                <div class="profile-save-bar">
                    <a class="btn btn-outline" href="{{ route('profile.edit') }}">Back to profile</a>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-key"></i> Update password</button>
                </div>
            </form>
        </section>
    </div>
</x-shell>

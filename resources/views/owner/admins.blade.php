<x-shell title="Staff Management | MCTBS">
    <x-slot:nav>@include('partials.owner-nav', ['activePage' => 'staff'])</x-slot:nav>

    <div class="container workspace-page staff-management-page">
        <div class="page-header-row page-header">
            <div>
                <h1>Staff &amp; access</h1>
                <p>Invite trusted teammates and manage which property they can help you operate.</p>
            </div>
            <a href="#invite-staff" class="btn btn-primary"><i class="fa fa-user-plus"></i> Invite staff</a>
        </div>



        @if (session('invite_link'))
            <div class="alert alert-info invitation-link-alert mb-3">
                <strong>[Dev Mode] Invitation link (no mail sending yet):</strong>
                <a href="{{ session('invite_link') }}">{{ session('invite_link') }}</a>
            </div>
        @endif

        <div class="card mb-3 staff-management-card" id="invite-staff">
            <div class="card-header"><span><i class="fa fa-paper-plane"></i> Invite a staff member</span></div>
            <div class="card-body">
                @if ($houses->isEmpty())
                    <p class="text-muted">You need a transient house before inviting staff.</p>
                @else
                    <form method="POST" action="{{ route('owner.admins.invite') }}">
                        @csrf
                        <div class="form-row">
                            <div class="form-group" style="flex:2">
                                <label class="required">Staff's email address</label>
                                <input type="email" name="email" value="{{ old('email') }}" required>
                            </div>
                            <div class="form-group" style="flex:1">
                                <label class="required">Assign to house</label>
                                <select name="house_id" required>
                                    <option value="">— Select a house —</option>
                                    @foreach ($houses as $house)
                                        <option value="{{ $house->house_id }}" @selected(old('house_id') == $house->house_id)>{{ $house->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Invite</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card staff-management-card">
            <div class="card-header"><span><i class="fa fa-users"></i> Your staff</span></div>
            <div class="card-body">
                <div class="staff-list">
                @forelse ($admins as $entry)
                    <div class="staff-card">
                        <div class="staff-card-avatar" aria-hidden="true">
                            {{ $entry->invitee?->initials() ?? 'ST' }}
                        </div>
                        <div class="staff-card-info">
                            <strong>{{ trim(($entry->invitee?->first_name ?? '').' '.($entry->invitee?->last_name ?? '')) ?: 'Staff member' }}</strong>
                            <span>{{ $entry->email }}</span>
                        </div>
                        <div class="staff-card-house">
                            <i class="fa fa-building"></i>
                            {{ $entry->house->name ?? 'No house assigned' }}
                        </div>
                        <form class="staff-card-actions" method="POST" action="{{ route('owner.admins.remove', $entry) }}" onsubmit="return confirm('Remove this staff member?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-user-minus"></i> Remove</button>
                        </form>
                    </div>
                @empty
                    <p class="text-muted">No staff yet.</p>
                @endforelse
                </div>
            </div>
        </div>
    </div>
</x-shell>

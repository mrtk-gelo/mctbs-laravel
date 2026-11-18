<x-shell title="Owner Management | MCTBS">
    <x-slot:nav>@include('partials.sysadmin-nav', ['activePage' => 'owners'])</x-slot:nav>

    <div class="container workspace-page">
        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="page-header-row page-header">
            <div>
                <h1>Owner Management</h1>
                <p>Manage owner accounts and invitations. Account Managers do not manage property operations.</p>
            </div>
            <a class="btn btn-primary" href="#invite-owner"><i class="fa fa-user-plus"></i> Invite an Owner</a>
        </div>

        @if (session('invite_link'))
            <div class="alert alert-info invitation-link-alert mb-3">
                <div><strong>Invitation link ready</strong><span>Development mode: share this link with the invited owner.</span></div>
                <a href="{{ session('invite_link') }}">{{ session('invite_link') }}</a>
            </div>
        @endif

        <section class="card invite-owner-card mb-3" id="invite-owner">
            <div class="card-header"><span><i class="fa fa-paper-plane"></i> Invite an owner</span></div>
            <div class="card-body">
                <form method="POST" action="{{ route('sysadmin.owners.invite') }}" class="owner-invite-form">
                    @csrf
                    <div class="form-group">
                        <label class="required" for="owner-email">Email address</label>
                        <input id="owner-email" type="email" name="email" value="{{ old('email') }}" placeholder="owner@example.com" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Create invitation</button>
                </form>
            </div>
        </section>

        <section class="card personnel-table-card mb-3">
            <div class="card-header">
                <span>Registered owners</span>
            </div>
            <div class="personnel-table-toolbar">
                <div class="personnel-owner-actions">
                    <form method="GET" action="{{ route('sysadmin.owners.index') }}" class="personnel-search" role="search" data-ajax-search data-results="#owner-results">
                        <i class="fa fa-search"></i>
                        <input type="text" name="q" value="{{ $search }}" maxlength="100" placeholder="Search owners" aria-label="Search owners">
                        <p class="sr-only" data-search-status aria-live="polite"></p>
                        <p class="alert alert-danger mt-2" data-search-error hidden></p>
                    </form>
                    <form id="bulk-owner-delete-form" method="POST" action="{{ route('sysadmin.owners.bulk-destroy') }}" onsubmit="return confirm('Delete the selected owner accounts? Accounts linked to protected records cannot be deleted.')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" type="submit"><i class="fa fa-trash"></i> Delete selected</button>
                    </form>
                </div>
            </div>
            @include('sysadmin.partials.owner-results')
        </section>

        <section class="card personnel-table-card" id="owner-invitations">
            <div class="card-header">
                <span>Pending owner invitations</span>
                <span class="text-muted fs-sm">{{ $invitations->count() }} awaiting registration</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Email</th><th>Expires</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($invitations as $invitation)
                            <tr>
                                <td><strong>{{ $invitation->email }}</strong></td>
                                <td>{{ $invitation->expires_at->format('M j, Y g:i A') }}</td>
                                <td><span class="badge badge-pending">Awaiting registration</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty-table-cell">No pending invitations.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-shell>

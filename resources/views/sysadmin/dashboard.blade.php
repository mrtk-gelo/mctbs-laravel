<x-shell title="Account Manager Dashboard | MCTBS">
    <x-slot:nav>@include('partials.sysadmin-nav', ['activePage' => 'dashboard'])</x-slot:nav>

    <div class="container workspace-page">
        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        <section class="sysadmin-welcome">
            <div>
                <h1>Account Manager Dashboard</h1>
                <p>Manage owner accounts and invitations. Property listings, bookings, and daily operations are handled by owners and their staff.</p>
            </div>
            <a href="{{ route('sysadmin.owners.index') }}" class="btn btn-primary">
                <i class="fa fa-users"></i> Manage Owners
            </a>
        </section>

        @if ($pendingOwnerInvitations > 0)
            <section class="sysadmin-action-card" aria-label="Owner invitations needing attention">
                <span class="sysadmin-action-icon"><i class="fa fa-envelope-open-text"></i></span>
                <div>
                    <strong>{{ $pendingOwnerInvitations }} owner {{ \Illuminate\Support\Str::plural('invitation', $pendingOwnerInvitations) }} awaiting registration</strong>
                    <p>Review outstanding owner invitations.</p>
                </div>
                <a href="{{ route('sysadmin.owners.index') }}#owner-invitations" class="btn btn-outline">
                    Review invitations <i class="fa fa-arrow-right"></i>
                </a>
            </section>
        @endif

        <section class="card personnel-table-card sysadmin-dashboard-section">
            <div class="card-header"><span>Registered Owners</span></div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Houses</th>
                            <th scope="col">Status</th>
                            <th scope="col">Date registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($owners as $owner)
                            <tr>
                                <td><strong>{{ $owner->first_name }} {{ $owner->last_name }}</strong></td>
                                <td><a href="mailto:{{ $owner->email }}">{{ $owner->email }}</a></td>
                                <td>{{ $owner->transient_houses_count }}</td>
                                <td>
                                    @if (! $owner->is_active)
                                        <span class="badge badge-cancelled">Inactive</span>
                                    @elseif (! $owner->email_verified_at)
                                        <span class="badge badge-pending">Unverified</span>
                                    @else
                                        <span class="badge badge-accepted">Active</span>
                                    @endif
                                </td>
                                <td>{{ $owner->registered_at?->format('M j, Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-table-cell">No owners registered yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card personnel-table-card sysadmin-dashboard-section">
            <div class="card-header">
                <span>Database backups</span>
                <form method="POST" action="{{ route('sysadmin.backups.store') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary"><i class="fa fa-database"></i> Backup</button>
                </form>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th scope="col">Date</th><th scope="col">Size</th><th scope="col">Action</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($backups as $backup)
                            <tr>
                                <td>{{ $backup['created_at']->format('M j, Y g:i A') }}</td>
                                <td>{{ $backup['size'] }}</td>
                                <td><a class="btn btn-outline btn-sm" href="{{ route('sysadmin.backups.download', $backup['filename']) }}">Download</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty-table-cell">No backups yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-shell>

<section class="card personnel-table-card" id="owner-results">
    <div class="card-header"><span>Registered owners</span><span class="text-muted fs-sm">{{ $owners->count() }} matching</span></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th scope="col">Select</th><th>Name</th><th>Email</th><th>Phone</th><th>Properties</th><th>Date registered</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse ($owners as $person)
                    <tr>
                        <td><input type="checkbox" name="owner_ids[]" value="{{ $person->user_id }}" form="bulk-owner-deactivate-form" aria-label="Select {{ $person->first_name }} {{ $person->last_name }} for deactivation"></td>
                        <td><div class="person-cell">
                            <span class="avatar-initials">{{ $person->initials() }}</span>
                            <strong>{{ $person->first_name }} {{ $person->last_name }}</strong>
                        </div></td>
                        <td>{{ $person->email }}</td>
                        <td>{{ $person->phone ?: '—' }}</td>
                        <td>{{ $person->transient_houses_count }}</td>
                        <td>{{ $person->registered_at?->format('M j, Y') ?? '—' }}</td>
                        <td>
                            @if (! $person->is_active)
                                <span class="badge badge-cancelled">Inactive</span>
                            @elseif (! $person->email_verified_at)
                                <span class="badge badge-pending">Unverified</span>
                            @else
                                <span class="badge badge-accepted">Active</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('sysadmin.owners.deactivate', $person->user_id) }}" onsubmit="return confirm('Deactivate this owner account? Existing properties and records will be preserved.')">
                                @csrf
                                <button type="submit" class="btn btn-outline btn-danger btn-sm" aria-label="Deactivate {{ $person->first_name }} {{ $person->last_name }}">
                                    <i class="fa fa-user-slash" aria-hidden="true"></i> Deactivate
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty-table-cell">No matching owners found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

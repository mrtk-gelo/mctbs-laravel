<section class="card personnel-table-card" id="owner-results">
    <div class="card-header"><span>Deactivated owners</span><span class="text-muted fs-sm">{{ $owners->count() }} matching</span></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Select</th><th>Name</th><th>Email</th><th>Phone</th><th>Properties</th><th>Date Deactivated</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse ($owners as $person)
                    <tr>
                        <td><input type="checkbox" name="owner_ids[]" value="{{ $person->user_id }}" form="bulk-owner-reactivate-form" aria-label="Select {{ $person->first_name }} {{ $person->last_name }}"></td>
                        <td><div class="person-cell"><span class="avatar-initials">{{ $person->initials() }}</span><strong>{{ $person->first_name }} {{ $person->last_name }}</strong></div></td>
                        <td>{{ $person->email }}</td>
                        <td>{{ $person->phone ?: '—' }}</td>
                        <td>{{ $person->transient_houses_count }}</td>
                        <td>{{ $person->deactivated_at?->format('M j, Y g:i A') ?? '—' }}</td>
                        <td>
                            <div class="personnel-row-actions">
                                <form method="POST" action="{{ route('sysadmin.owners.reactivate', $person->user_id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm">Reactivate</button>
                                </form>
                                <form method="POST" action="{{ route('sysadmin.owners.destroy', $person->user_id) }}" onsubmit="return confirm('Permanently delete this owner account? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete Permanently</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-table-cell">No deactivated owners found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

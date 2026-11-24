<section class="card property-archive-records account-lifecycle-records" id="owner-results" aria-label="Deactivated owners">
    <div class="property-archive-table-wrap">
        <table class="property-archive-table">
            <thead><tr><th class="property-archive-checkbox-column"><input type="checkbox" id="select-all-deactivated-owners" aria-label="Select all owners on this page"></th><th>Name</th><th>Email</th><th>Phone</th><th>Properties</th><th>Date Deactivated</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse ($owners as $person)
                    <tr>
                        <td class="property-archive-checkbox-column"><input type="checkbox" name="owner_ids[]" value="{{ $person->user_id }}" form="bulk-owner-reactivate-form" data-deactivated-owner-select aria-label="Select {{ $person->first_name }} {{ $person->last_name }}"></td>
                        <td><strong>{{ $person->first_name }} {{ $person->last_name }}</strong></td>
                        <td>{{ $person->email }}</td>
                        <td>{{ $person->phone ?: '—' }}</td>
                        <td>{{ $person->transient_houses_count }}</td>
                        <td>{{ $person->deactivated_at?->format('M j, Y g:i A') ?? '—' }}</td>
                        <td>
                            <div class="personnel-row-actions">
                                <form method="POST" action="{{                                 route('accadmin.owners.reactivate', $person->user_id) }}" data-owner-row-reactivate data-account-name="{{ $person->first_name }} {{ $person->last_name }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm">Reactivate</button>
                                </form>
                                <form method="POST" action="{{                                 route('accadmin.owners.destroy', $person->user_id) }}" onsubmit="return confirm('Permanently delete this owner account? This cannot be undone.')">
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

<section class="card property-archive-records account-lifecycle-records" id="owner-results" aria-label="Registered owners">
    <div class="property-archive-table-wrap">
        <table class="property-archive-table">
            <thead>
                <tr>
                    <th class="property-archive-checkbox-column"><input type="checkbox" id="select-all-owners" aria-label="Select all owners on this page"></th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Properties</th>
                    <th>Date Registered</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($owners as $person)
                    <tr>
                        <td class="property-archive-checkbox-column">
                            <input type="checkbox" name="owner_ids[]" value="{{ $person->user_id }}" form="bulk-owner-deactivate-form" data-owner-select aria-label="Select {{ $person->first_name }} {{ $person->last_name }}">
                        </td>
                        <td><strong>{{ $person->first_name }} {{ $person->last_name }}</strong></td>
                        <td>{{ $person->email }}</td>
                        <td>{{ $person->phone ?: '—' }}</td>
                        <td>{{ $person->transient_houses_count }}</td>
                        <td>{{ $person->registered_at?->format('M j, Y') ?? '—' }}</td>
                        <td><span class="property-archive-resource property-archive-resource--house">{{ $person->email_verified_at ? 'Active' : 'Unverified' }}</span></td>
                        <td>
                            <form method="POST" action="{{                             route('accadmin.owners.deactivate', $person->user_id) }}" data-account-deactivate data-account-name="{{ $person->first_name }} {{ $person->last_name }}">
                                @csrf
                                <button type="submit" class="btn btn-outline btn-sm">Deactivate</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="property-archive-empty">No registered owners match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

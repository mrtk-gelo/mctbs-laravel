<x-shell title="Deactivated Staff | MCTBS">
    <x-slot:nav>@include('partials.owner-nav', ['activePage' => 'staff'])</x-slot:nav>
    <main class="container workspace-page account-lifecycle-page">
        <a href="{{ route('owner.admins.index') }}" class="text-muted fs-sm account-lifecycle-back">
            <i class="fa fa-arrow-left"></i> Back to staff management
        </a>
        <header class="page-header-row page-header">
            <div><h1>Deactivated Staff</h1><p>Reactivate staff or permanently delete only eligible accounts.</p></div>
        </header>
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        <form method="GET" action="{{ route('owner.admins.deactivated') }}" class="card account-lifecycle-filters" role="search">
            <div class="booking-list-filter-field"><label for="staff-search">Search</label><div class="property-archive-search"><i class="fa fa-search"></i><input id="staff-search" type="search" name="q" value="{{ $search }}" placeholder="Search deactivated staff"></div></div>
        </form>
        <section class="card personnel-table-card">
            <form id="bulk-staff-reactivate-form" method="POST" action="{{ route('owner.admins.bulk-reactivate') }}" class="personnel-table-toolbar">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm">Reactivate Selected</button>
                <button type="submit" class="btn btn-danger btn-sm" form="bulk-staff-delete-form">Delete Permanently Selected</button>
            </form>
            <form id="bulk-staff-delete-form" method="POST" action="{{ route('owner.admins.bulk-permanent-delete') }}" onsubmit="return confirm('Permanently delete selected eligible staff accounts? This cannot be undone.')">
                @csrf
            </form>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Select</th><th>Name</th><th>Email</th><th>House</th><th>Date Deactivated</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($admins as $entry)
                            @php $staff = $entry->invitee; @endphp
                            <tr>
                                <td><input type="checkbox" name="staff_ids[]" value="{{ $staff?->user_id }}" form="bulk-staff-reactivate-form" aria-label="Select {{ $staff?->first_name }} {{ $staff?->last_name }}"></td>
                                <td><strong>{{ trim(($staff?->first_name ?? '').' '.($staff?->last_name ?? '')) ?: 'Staff member' }}</strong></td>
                                <td>{{ $entry->email }}</td>
                                <td>{{ $entry->house->name ?? '—' }}</td>
                                <td>{{ $staff?->deactivated_at?->format('M j, Y g:i A') ?? '—' }}</td>
                                <td>
                                    <div class="personnel-row-actions">
                                        <form method="POST" action="{{ route('owner.admins.reactivate', $entry) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline btn-sm">Reactivate</button>
                                        </form>
                                        <form method="POST" action="{{ route('owner.admins.permanent-delete', $entry) }}" onsubmit="return confirm('Permanently delete this staff account? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete Permanently</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="empty-table-cell">No deactivated staff found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <script>
            document.getElementById('bulk-staff-delete-form').addEventListener('submit', (event) => {
                const form = event.currentTarget;
                form.querySelectorAll('[data-selected-staff]').forEach((input) => input.remove());
                document.querySelectorAll('input[name="staff_ids[]"][form="bulk-staff-reactivate-form"]:checked').forEach((checkbox) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'staff_ids[]';
                    input.value = checkbox.value;
                    input.dataset.selectedStaff = 'true';
                    form.appendChild(input);
                });
            });
        </script>
    </main>
</x-shell>

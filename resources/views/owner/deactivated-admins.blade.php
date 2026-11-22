<x-shell title="Deactivated Staff | MCTBS">
    <x-slot:nav>@include('partials.owner-nav', ['activePage' => 'staff'])</x-slot:nav>
    <main class="container workspace-page account-lifecycle-page">
        <a href="{{ route('owner.admins.index') }}" class="text-muted fs-sm account-lifecycle-back">
            <i class="fa fa-arrow-left"></i> Back to staff management
        </a>
        <header class="page-header-row page-header account-lifecycle-header">
            <div><h1>Deactivated Staff</h1><p>Reactivate staff or permanently delete only eligible accounts.</p></div>
        </header>
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        <form method="GET" action="{{ route('owner.admins.deactivated') }}" class="card property-archive-filters account-lifecycle-filters account-lifecycle-filter-grid" role="search">
            <div class="booking-list-filter-field booking-list-filter-search">
                <label for="staff-search">Search</label>
                <div class="property-archive-search"><i class="fa fa-search" aria-hidden="true"></i><input id="staff-search" type="search" name="q" value="{{ $search }}" placeholder="Search deactivated staff"></div>
            </div>
            <div class="booking-list-filter-field"><label for="staff-deactivated-after">Deactivated after</label><input id="staff-deactivated-after" type="date" name="deactivated_after" value="{{ request('deactivated_after') }}"></div>
            <div class="booking-list-filter-field"><label for="staff-deactivated-before">Deactivated before</label><input id="staff-deactivated-before" type="date" name="deactivated_before" value="{{ request('deactivated_before') }}"></div>
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            <button type="submit" class="sr-only">Apply filters</button>
        </form>

        <form id="bulk-staff-reactivate-form" method="POST" action="{{ route('owner.admins.bulk-reactivate') }}">@csrf</form>
        <form id="bulk-staff-delete-form" method="POST" action="{{ route('owner.admins.bulk-permanent-delete') }}">@csrf</form>
        <section class="account-lifecycle-table-section" aria-label="Deactivated staff">
            <div class="card property-archive-records account-lifecycle-records">
                <div class="property-archive-table-wrap">
                    <table class="property-archive-table">
                        <thead>
                            <tr>
                                <th class="property-archive-checkbox-column"><input type="checkbox" id="select-all-deactivated-staff" aria-label="Select all staff on this page"></th>
                                <th>Name</th><th>Email</th><th>House</th><th>Date Deactivated</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($admins as $entry)
                                @php $staff = $entry->invitee; @endphp
                                <tr>
                                    <td class="property-archive-checkbox-column"><input type="checkbox" name="staff_ids[]" value="{{ $staff->user_id }}" form="bulk-staff-reactivate-form" data-deactivated-staff-select aria-label="Select {{ $staff->first_name }} {{ $staff->last_name }}"></td>
                                    <td><strong>{{ trim($staff->first_name.' '.$staff->last_name) ?: 'Staff member' }}</strong></td>
                                    <td>{{ $entry->email }}</td>
                                    <td>{{ $entry->house->name ?? '—' }}</td>
                                    <td>{{ $staff->deactivated_at?->format('M j, Y g:i A') ?? '—' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('owner.admins.reactivate', $entry) }}" data-staff-row-reactivate>
                                            @csrf
                                            <button type="submit" class="btn btn-outline btn-sm">Reactivate</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="property-archive-empty">No deactivated staff match these filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @include('partials.account-lifecycle-pagination', ['records' => $admins, 'paginationId' => 'deactivated-staff'])
        </section>

        <div class="property-archive-bulk-bar" id="deactivated-staff-bulk-bar" role="region" aria-label="Selected staff actions" hidden>
            <span class="property-archive-selected-count" id="deactivated-staff-selected-count" aria-live="polite">0 selected</span>
            <button type="button" class="btn btn-outline" id="bulk-staff-reactivate">Reactivate</button>
            <button type="button" class="btn btn-danger" id="bulk-staff-delete">Delete Permanently</button>
            <button type="button" class="property-archive-clear-selection" id="clear-deactivated-staff-selection">Clear selection</button>
        </div>

        <div class="modal-overlay account-lifecycle-modal" id="staff-reactivate-confirmation" aria-hidden="true">
            <section class="modal-box">
                <header class="account-lifecycle-modal-header">
                    <strong>Confirm staff reactivation</strong>
                    <button type="button" class="btn btn-outline btn-sm" data-staff-reactivate-close>Cancel</button>
                </header>
                <div class="account-lifecycle-modal-content">
                    <p>Staff access will be restored under your active Owner account. Historical booking, payment, checkout, and other records will remain unchanged.</p>
                    <div class="account-lifecycle-modal-actions">
                        <button type="button" class="btn btn-outline" data-staff-reactivate-close>Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirm-staff-reactivation">Reactivate</button>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <script>
        const deactivatedStaffChecks = Array.from(document.querySelectorAll('[data-deactivated-staff-select]'));
        const selectAllDeactivatedStaff = document.getElementById('select-all-deactivated-staff');
        const deactivatedStaffBulkBar = document.getElementById('deactivated-staff-bulk-bar');
        const staffReactivateModal = document.getElementById('staff-reactivate-confirmation');
        let pendingStaffReactivation = null;

        function updateDeactivatedStaffSelection() {
            const count = deactivatedStaffChecks.filter((checkbox) => checkbox.checked).length;
            document.getElementById('deactivated-staff-selected-count').textContent = `${count} selected`;
            deactivatedStaffBulkBar.hidden = count === 0;
            document.querySelector('.account-lifecycle-page').classList.toggle('account-lifecycle-page--selected', count > 0);
            selectAllDeactivatedStaff.checked = deactivatedStaffChecks.length > 0 && count === deactivatedStaffChecks.length;
            selectAllDeactivatedStaff.indeterminate = count > 0 && count < deactivatedStaffChecks.length;
        }

        function openStaffReactivation(form) {
            pendingStaffReactivation = form;
            staffReactivateModal.classList.add('open');
            staffReactivateModal.setAttribute('aria-hidden', 'false');
        }

        deactivatedStaffChecks.forEach((checkbox) => checkbox.addEventListener('change', updateDeactivatedStaffSelection));
        selectAllDeactivatedStaff.addEventListener('change', () => {
            deactivatedStaffChecks.forEach((checkbox) => { checkbox.checked = selectAllDeactivatedStaff.checked; });
            updateDeactivatedStaffSelection();
        });
        document.getElementById('clear-deactivated-staff-selection').addEventListener('click', () => {
            deactivatedStaffChecks.forEach((checkbox) => { checkbox.checked = false; });
            updateDeactivatedStaffSelection();
        });
        document.getElementById('bulk-staff-reactivate').addEventListener('click', () => openStaffReactivation(document.getElementById('bulk-staff-reactivate-form')));
        document.querySelectorAll('[data-staff-row-reactivate]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (form.dataset.confirmed === 'true') {
                    delete form.dataset.confirmed;
                    return;
                }
                event.preventDefault();
                openStaffReactivation(form);
            });
        });
        document.getElementById('confirm-staff-reactivation').addEventListener('click', () => {
            if (pendingStaffReactivation) {
                pendingStaffReactivation.dataset.confirmed = 'true';
                pendingStaffReactivation.requestSubmit();
            }
            staffReactivateModal.classList.remove('open');
            staffReactivateModal.setAttribute('aria-hidden', 'true');
        });
        document.querySelectorAll('[data-staff-reactivate-close]').forEach((button) => {
            button.addEventListener('click', () => {
                pendingStaffReactivation = null;
                staffReactivateModal.classList.remove('open');
                staffReactivateModal.setAttribute('aria-hidden', 'true');
            });
        });
        document.getElementById('bulk-staff-delete').addEventListener('click', () => {
            const selected = deactivatedStaffChecks.filter((checkbox) => checkbox.checked);
            if (selected.length === 0 || !window.confirm(`Permanently delete ${selected.length} selected staff account(s)? Protected historical references will be preserved.`)) {
                return;
            }
            const form = document.getElementById('bulk-staff-delete-form');
            form.querySelectorAll('[data-selected-staff]').forEach((input) => input.remove());
            selected.forEach((checkbox) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'staff_ids[]';
                input.value = checkbox.value;
                input.dataset.selectedStaff = 'true';
                form.appendChild(input);
            });
            form.requestSubmit();
        });
        document.querySelectorAll('.account-lifecycle-filters input[type="date"]').forEach((input) => {
            input.addEventListener('change', () => input.form.requestSubmit());
        });
        const staffSearch = document.getElementById('staff-search');
        let staffSearchTimer;
        staffSearch.addEventListener('input', () => {
            window.clearTimeout(staffSearchTimer);
            staffSearchTimer = window.setTimeout(() => staffSearch.form.requestSubmit(), 350);
        });
    </script>
</x-shell>

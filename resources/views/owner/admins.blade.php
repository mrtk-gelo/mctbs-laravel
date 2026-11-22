<x-shell title="Staff Management | MCTBS">
    <x-slot:nav>@include('partials.owner-nav', ['activePage' => 'staff'])</x-slot:nav>

    <main class="container workspace-page account-lifecycle-page">
        @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif

        <header class="page-header-row page-header account-lifecycle-header">
            <div><h1>Staff &amp; access</h1><p>Manage your staff access and invitations.</p></div>
            <div class="account-lifecycle-header-actions">
                <button type="button" class="btn btn-outline" data-account-modal-open="pending-staff-invitations">
                    <i class="fa fa-envelope"></i> Pending Invitations <span class="account-lifecycle-count">{{ $invitations->count() }}</span>
                </button>
                <button type="button" class="btn btn-primary" data-account-modal-open="invite-staff-modal">
                    <i class="fa fa-user-plus"></i> Invite Staff
                </button>
            </div>
        </header>

        @if (session('invite_link'))
            <div class="alert alert-info invitation-link-alert mb-3">
                <strong>Invitation link ready (development mode):</strong>
                <a href="{{ session('invite_link') }}">{{ session('invite_link') }}</a>
            </div>
        @endif

        <form method="GET" action="{{ route('owner.admins.index') }}" class="card property-archive-filters account-lifecycle-filters account-lifecycle-filter-grid" role="search">
            <div class="booking-list-filter-field booking-list-filter-search">
                <label for="staff-search">Search staff</label>
                <div class="property-archive-search"><i class="fa fa-search" aria-hidden="true"></i><input id="staff-search" type="search" name="q" value="{{ $search }}" placeholder="Search by name or email"></div>
            </div>
            <div class="booking-list-filter-field"><label for="staff-registered-after">Registered after</label><input id="staff-registered-after" type="date" name="registered_after" value="{{ $filters['registered_after'] }}"></div>
            <div class="booking-list-filter-field"><label for="staff-registered-before">Registered before</label><input id="staff-registered-before" type="date" name="registered_before" value="{{ $filters['registered_before'] }}"></div>
            <input type="hidden" name="per_page" value="{{ $filters['per_page'] }}">
            <button type="submit" class="sr-only">Apply filters</button>
        </form>

        <form id="bulk-staff-deactivate-form" method="POST" action="{{ route('owner.admins.bulk-deactivate') }}">@csrf</form>
        <section class="account-lifecycle-table-section" aria-label="Active staff">
            <div class="card property-archive-records account-lifecycle-records">
                <div class="property-archive-table-wrap">
                    <table class="property-archive-table">
                        <thead>
                            <tr>
                                <th class="property-archive-checkbox-column"><input type="checkbox" id="select-all-staff" aria-label="Select all staff on this page"></th>
                                <th>Name</th><th>Email</th><th>House</th><th>Date Registered</th><th>Status</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($admins as $entry)
                                @php $staff = $entry->invitee; @endphp
                                <tr>
                                    <td class="property-archive-checkbox-column"><input type="checkbox" name="staff_ids[]" value="{{ $staff->user_id }}" form="bulk-staff-deactivate-form" data-active-staff-select aria-label="Select {{ $staff->first_name }} {{ $staff->last_name }}"></td>
                                    <td><strong>{{ trim($staff->first_name.' '.$staff->last_name) ?: 'Staff member' }}</strong></td>
                                    <td>{{ $entry->email }}</td>
                                    <td>{{ $entry->house->name ?? '—' }}</td>
                                    <td>{{ $staff->registered_at?->format('M j, Y') ?? '—' }}</td>
                                    <td><span class="property-archive-resource">Active</span></td>
                                    <td>
                                        <form method="POST" action="{{ route('owner.admins.deactivate', $entry) }}" data-account-deactivate data-account-name="{{ $staff->first_name }} {{ $staff->last_name }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline btn-sm">Deactivate</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="property-archive-empty">No active staff match these filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @include('partials.account-lifecycle-pagination', ['records' => $admins, 'paginationId' => 'active-staff'])
        </section>

        <div class="property-archive-bulk-bar" id="staff-bulk-bar" role="region" aria-label="Selected staff actions" hidden>
            <span class="property-archive-selected-count" id="staff-selected-count" aria-live="polite">0 selected</span>
            <button type="button" class="btn btn-danger" id="bulk-staff-deactivate">Deactivate</button>
            <button type="button" class="property-archive-clear-selection" id="clear-staff-selection">Clear selection</button>
        </div>

        <div class="account-lifecycle-secondary-navigation account-lifecycle-secondary-navigation--outer">
            <a href="{{ route('owner.admins.deactivated') }}" class="btn btn-outline">
                <i class="fa fa-user-slash"></i> View Deactivated Staff
            </a>
        </div>

        <div class="modal-overlay account-lifecycle-modal" id="invite-staff-modal" aria-hidden="true">
            <section class="modal-box">
                <header class="account-lifecycle-modal-header">
                    <strong>Invite Staff</strong>
                    <button type="button" class="btn btn-outline btn-sm" data-account-modal-close>Close</button>
                </header>
                <div class="account-lifecycle-modal-content">
                    @if ($houses->isEmpty())
                        <p class="text-muted">You need an active transient house before inviting staff.</p>
                    @else
                        <form method="POST" action="{{ route('owner.admins.invite') }}">
                            @csrf
                            <div class="form-group">
                                <label class="required" for="staff-email">Staff email address</label>
                                <input id="staff-email" type="email" name="email" value="{{ old('email') }}" required>
                            </div>
                            <div class="form-group">
                                <label class="required" for="staff-house">Assign to house</label>
                                <select id="staff-house" name="house_id" required>
                                    <option value="">Select a house</option>
                                    @foreach ($houses as $house)
                                        <option value="{{ $house->house_id }}" @selected(old('house_id') == $house->house_id)>{{ $house->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="account-lifecycle-modal-actions">
                                <button type="button" class="btn btn-outline" data-account-modal-close>Cancel</button>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Create Invitation</button>
                            </div>
                        </form>
                    @endif
                </div>
            </section>
        </div>

        <div class="modal-overlay account-lifecycle-modal" id="pending-staff-invitations" aria-hidden="true">
            <section class="modal-box account-lifecycle-pending-modal">
                <header class="account-lifecycle-modal-header">
                    <strong>Pending Staff Invitations</strong>
                    <button type="button" class="btn btn-outline btn-sm" data-account-modal-close>Close</button>
                </header>
                <div class="account-lifecycle-modal-content">
                    @if ($invitations->isEmpty())
                        <p class="text-muted mb-0">No pending staff invitations.</p>
                    @else
                        <div class="table-wrap">
                            <table class="property-archive-table account-lifecycle-modal-table">
                                <thead><tr><th>Email</th><th>House</th><th>Expires</th><th>Status</th></tr></thead>
                                <tbody>
                                    @foreach ($invitations as $invitation)
                                        <tr>
                                            <td><strong>{{ $invitation->email }}</strong></td>
                                            <td>{{ $invitation->house->name ?? '—' }}</td>
                                            <td>{{ $invitation->expires_at->format('M j, Y g:i A') }}</td>
                                            <td><span class="property-archive-resource">Awaiting registration</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <div class="modal-overlay account-lifecycle-modal" id="staff-deactivate-confirmation" aria-hidden="true">
            <section class="modal-box">
                <header class="account-lifecycle-modal-header">
                    <strong>Deactivate selected staff?</strong>
                    <button type="button" class="btn btn-outline btn-sm" data-staff-confirm-close>Cancel</button>
                </header>
                <div class="account-lifecycle-modal-content">
                    <p>Selected staff will lose access immediately. Booking, payment, checkout, and other historical records remain unchanged.</p>
                    <div class="account-lifecycle-modal-actions">
                        <button type="button" class="btn btn-outline" data-staff-confirm-close>Cancel</button>
                        <button type="button" class="btn btn-danger" id="staff-deactivate-confirm">Deactivate</button>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script>
        const staffChecks = Array.from(document.querySelectorAll('[data-active-staff-select]'));
        const staffSelectAll = document.getElementById('select-all-staff');
        const staffBulkBar = document.getElementById('staff-bulk-bar');
        const staffConfirmModal = document.getElementById('staff-deactivate-confirmation');
        let pendingStaffDeactivation = null;

        function updateStaffSelection() {
            const count = staffChecks.filter((checkbox) => checkbox.checked).length;
            document.getElementById('staff-selected-count').textContent = `${count} selected`;
            staffBulkBar.hidden = count === 0;
            document.querySelector('.account-lifecycle-page').classList.toggle('account-lifecycle-page--selected', count > 0);
            staffSelectAll.checked = staffChecks.length > 0 && count === staffChecks.length;
            staffSelectAll.indeterminate = count > 0 && count < staffChecks.length;
        }

        staffChecks.forEach((checkbox) => checkbox.addEventListener('change', updateStaffSelection));
        staffSelectAll.addEventListener('change', () => {
            staffChecks.forEach((checkbox) => { checkbox.checked = staffSelectAll.checked; });
            updateStaffSelection();
        });
        document.getElementById('clear-staff-selection').addEventListener('click', () => {
            staffChecks.forEach((checkbox) => { checkbox.checked = false; });
            updateStaffSelection();
        });
        document.getElementById('bulk-staff-deactivate').addEventListener('click', () => {
            pendingStaffDeactivation = document.getElementById('bulk-staff-deactivate-form');
            staffConfirmModal.classList.add('open');
            staffConfirmModal.setAttribute('aria-hidden', 'false');
        });
        document.querySelectorAll('[data-account-deactivate]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (form.dataset.confirmed === 'true') {
                    delete form.dataset.confirmed;
                    return;
                }
                event.preventDefault();
                pendingStaffDeactivation = form;
                staffConfirmModal.classList.add('open');
                staffConfirmModal.setAttribute('aria-hidden', 'false');
            });
        });
        document.getElementById('staff-deactivate-confirm').addEventListener('click', () => {
            if (pendingStaffDeactivation) {
                pendingStaffDeactivation.dataset.confirmed = 'true';
                pendingStaffDeactivation.requestSubmit();
            }
            staffConfirmModal.classList.remove('open');
            staffConfirmModal.setAttribute('aria-hidden', 'true');
        });
        document.querySelectorAll('[data-account-modal-open]').forEach((button) => {
            button.addEventListener('click', () => {
                pendingStaffDeactivation = null;
                const modal = document.getElementById(button.dataset.accountModalOpen);
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
            });
        });
        document.querySelectorAll('[data-account-modal-close]').forEach((button) => {
            button.addEventListener('click', () => {
                pendingStaffDeactivation = null;
                const modal = button.closest('.account-lifecycle-modal');
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
            });
        });
        document.querySelectorAll('[data-staff-confirm-close]').forEach((button) => {
            button.addEventListener('click', () => {
                staffConfirmModal.classList.remove('open');
                staffConfirmModal.setAttribute('aria-hidden', 'true');
                pendingStaffDeactivation = null;
            });
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

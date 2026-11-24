<x-shell title="Owner Management | MCTBS">
    <x-slot:nav>@include('partials.accadmin-nav', ['activePage' => 'owners'])</x-slot:nav>

    <main class="container workspace-page account-lifecycle-page">
        @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif

        <header class="page-header-row page-header account-lifecycle-header">
            <div>
                <h1>Owner Management</h1>
                <p>Manage registered owners and account invitations.</p>
            </div>
            <div class="account-lifecycle-header-actions">
                <button type="button" class="btn btn-outline" data-account-modal-open="pending-owner-invitations">
                    <i class="fa fa-envelope"></i> Pending Invitations <span class="account-lifecycle-count">{{ $invitations->count() }}</span>
                </button>
                <button type="button" class="btn btn-primary" data-account-modal-open="invite-owner-modal">
                    <i class="fa fa-user-plus"></i> Invite an Owner
                </button>
            </div>
        </header>

        <form method="GET" action="{{ route('accadmin.owners.index') }}" class="card property-archive-filters account-lifecycle-filters account-lifecycle-filter-grid account-lifecycle-owner-filter-grid" role="search">
            <div class="booking-list-filter-field booking-list-filter-search">
                <label for="owner-search">Search owners</label>
                <div class="property-archive-search"><i class="fa fa-search" aria-hidden="true"></i><input id="owner-search" type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Search by name, email, or phone"></div>
            </div>
            <div class="booking-list-filter-field">
                <label for="owner-registered-after">Registered after</label>
                <input id="owner-registered-after" type="date" name="registered_after" value="{{ request('registered_after') }}">
            </div>
            <div class="booking-list-filter-field">
                <label for="owner-registered-before">Registered before</label>
                <input id="owner-registered-before" type="date" name="registered_before" value="{{ request('registered_before') }}">
            </div>
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            <button type="submit" class="sr-only">Apply filters</button>
        </form>

        <form id="bulk-owner-deactivate-form" method="POST" action="{{ route('accadmin.owners.bulk-deactivate') }}">
            @csrf
        </form>
        <section class="account-lifecycle-table-section" aria-label="Registered owners">
            @include('accadmin.partials.owner-results')
            @include('partials.account-lifecycle-pagination', ['records' => $owners, 'paginationId' => 'owners'])
        </section>

        <div class="account-lifecycle-secondary-navigation account-lifecycle-secondary-navigation--outer">
            <a href="{{ route('accadmin.owners.deactivated') }}" class="btn btn-outline">
                <i class="fa fa-user-slash"></i> View Deactivated Owners
            </a>
        </div>

        <div class="property-archive-bulk-bar" id="owner-bulk-bar" role="region" aria-label="Selected owner actions" hidden>
            <span class="property-archive-selected-count" id="owner-selected-count" aria-live="polite">0 selected</span>
            <button type="button" class="btn btn-danger" id="owner-bulk-deactivate">Deactivate</button>
            <button type="button" class="property-archive-clear-selection" id="owner-clear-selection">Clear selection</button>
        </div>

        <div class="modal-overlay account-lifecycle-modal" id="invite-owner-modal" aria-hidden="true">
            <section class="modal-box">
                <header class="account-lifecycle-modal-header">
                    <strong>Invite an Owner</strong>
                    <button type="button" class="btn btn-outline btn-sm" data-account-modal-close>Close</button>
                </header>
                <div class="account-lifecycle-modal-content">
                    <form method="POST" action="{{ route('accadmin.owners.invite') }}">
                        @csrf
                        <div class="form-group">
                            <label class="required" for="owner-email">Email address</label>
                            <input id="owner-email" type="email" name="email" value="{{ old('email') }}" placeholder="owner@example.com" required>
                        </div>
                        <div class="account-lifecycle-modal-actions">
                            <button type="button" class="btn btn-outline" data-account-modal-close>Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Create Invitation</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>

        <div class="modal-overlay account-lifecycle-modal" id="pending-owner-invitations" aria-hidden="true">
            <section class="modal-box account-lifecycle-pending-modal">
                <header class="account-lifecycle-modal-header">
                    <strong>Pending Owner Invitations</strong>
                    <button type="button" class="btn btn-outline btn-sm" data-account-modal-close>Close</button>
                </header>
                <div class="account-lifecycle-modal-content">
                    @if ($invitations->isEmpty())
                        <p class="text-muted mb-0">No pending owner invitations.</p>
                    @else
                        <div class="table-wrap">
                            <table class="property-archive-table account-lifecycle-modal-table">
                                <thead><tr><th>Email</th><th>Expires</th><th>Status</th><th>Action</th></tr></thead>
                                <tbody>
                                    @foreach ($invitations as $invitation)
                                        <tr>
                                            <td><strong>{{ $invitation->email }}</strong></td>
                                            <td>{{ $invitation->expires_at->format('M j, Y g:i A') }}</td>
                                            <td><span class="property-archive-resource">Awaiting registration</span></td>
                                            <td>
                                                <form method="POST" action="{{ route('accadmin.owners.invitations.resend', $invitation) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline btn-sm">Resend email</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <div class="modal-overlay account-lifecycle-modal" id="owner-deactivate-confirmation" aria-hidden="true">
            <section class="modal-box">
                <header class="account-lifecycle-modal-header">
                    <strong>Deactivate selected owners?</strong>
                    <button type="button" class="btn btn-outline btn-sm" data-account-confirm-close>Cancel</button>
                </header>
                <div class="account-lifecycle-modal-content">
                    <p>Owners will lose access immediately. Their properties, units, bookings, payments, and historical records will be preserved.</p>
                    <div class="account-lifecycle-modal-actions">
                        <button type="button" class="btn btn-outline" data-account-confirm-close>Cancel</button>
                        <button type="button" class="btn btn-danger" id="owner-deactivate-confirm">Deactivate</button>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script>
        const ownerPage = document.querySelector('.account-lifecycle-page');
        const ownerChecks = Array.from(document.querySelectorAll('[data-owner-select]'));
        const ownerSelectAll = document.getElementById('select-all-owners');
        const ownerBulkBar = document.getElementById('owner-bulk-bar');
        const ownerDeactivateConfirmation = document.getElementById('owner-deactivate-confirmation');
        let pendingOwnerDeactivation = null;

        function updateOwnerSelection() {
            const selectedCount = ownerChecks.filter((checkbox) => checkbox.checked).length;
            document.getElementById('owner-selected-count').textContent = `${selectedCount} selected`;
            ownerBulkBar.hidden = selectedCount === 0;
            ownerPage.classList.toggle('account-lifecycle-page--selected', selectedCount > 0);
            ownerSelectAll.checked = ownerChecks.length > 0 && selectedCount === ownerChecks.length;
            ownerSelectAll.indeterminate = selectedCount > 0 && selectedCount < ownerChecks.length;
        }

        ownerChecks.forEach((checkbox) => checkbox.addEventListener('change', updateOwnerSelection));
        ownerSelectAll.addEventListener('change', () => {
            ownerChecks.forEach((checkbox) => { checkbox.checked = ownerSelectAll.checked; });
            updateOwnerSelection();
        });
        document.getElementById('owner-clear-selection').addEventListener('click', () => {
            ownerChecks.forEach((checkbox) => { checkbox.checked = false; });
            updateOwnerSelection();
        });
        document.getElementById('owner-bulk-deactivate').addEventListener('click', () => {
            pendingOwnerDeactivation = document.getElementById('bulk-owner-deactivate-form');
            ownerDeactivateConfirmation.classList.add('open');
            ownerDeactivateConfirmation.setAttribute('aria-hidden', 'false');
        });
        document.querySelectorAll('[data-account-deactivate]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (form.dataset.confirmed === 'true') {
                    delete form.dataset.confirmed;
                    return;
                }
                event.preventDefault();
                pendingOwnerDeactivation = form;
                ownerDeactivateConfirmation.classList.add('open');
                ownerDeactivateConfirmation.setAttribute('aria-hidden', 'false');
            });
        });
        document.getElementById('owner-deactivate-confirm').addEventListener('click', () => {
            if (pendingOwnerDeactivation) {
                pendingOwnerDeactivation.dataset.confirmed = 'true';
                pendingOwnerDeactivation.requestSubmit();
            }
            ownerDeactivateConfirmation.classList.remove('open');
            ownerDeactivateConfirmation.setAttribute('aria-hidden', 'true');
        });
        document.querySelectorAll('[data-account-modal-open]').forEach((button) => {
            button.addEventListener('click', () => {
                pendingOwnerDeactivation = null;
                const modal = document.getElementById(button.dataset.accountModalOpen);
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
            });
        });
        document.querySelectorAll('[data-account-modal-close]').forEach((button) => {
            button.addEventListener('click', () => {
                const modal = button.closest('.account-lifecycle-modal');
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
            });
        });
        document.querySelectorAll('[data-account-confirm-close]').forEach((button) => {
            button.addEventListener('click', () => {
                ownerDeactivateConfirmation.classList.remove('open');
                ownerDeactivateConfirmation.setAttribute('aria-hidden', 'true');
                pendingOwnerDeactivation = null;
            });
        });
        document.querySelectorAll('.account-lifecycle-filters input[type="date"]').forEach((input) => {
            input.addEventListener('change', () => input.form.requestSubmit());
        });
        const ownerSearch = document.getElementById('owner-search');
        let ownerSearchTimer;
        ownerSearch.addEventListener('input', () => {
            window.clearTimeout(ownerSearchTimer);
            ownerSearchTimer = window.setTimeout(() => ownerSearch.form.requestSubmit(), 350);
        });
    </script>
</x-shell>

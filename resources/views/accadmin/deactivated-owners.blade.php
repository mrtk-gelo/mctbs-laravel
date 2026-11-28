<x-accadmin-shell title="Deactivated Owners | MCTBS" heading="Deactivated owners">
    <main class="container workspace-page account-lifecycle-page">
        <a href="{{ route('accadmin.owners.index') }}" class="text-muted fs-sm account-lifecycle-back">
            <i class="fa fa-arrow-left"></i> Back to owner management
        </a>
        <header class="page-header-row page-header account-lifecycle-header">
            <div><h1>Deactivated Owners</h1><p>Reactivate accounts or permanently delete only eligible owners.</p></div>
        </header>
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        <form method="GET" action="{{ route('accadmin.owners.deactivated') }}" class="card property-archive-filters account-lifecycle-filters account-lifecycle-filter-grid account-lifecycle-owner-filter-grid" role="search">
            <div class="booking-list-filter-field booking-list-filter-search">
                <label for="owner-search">Search</label>
                <div class="property-archive-search"><i class="fa fa-search" aria-hidden="true"></i><input id="owner-search" type="search" name="q" value="{{ $search }}" placeholder="Search deactivated owners"></div>
            </div>
            <div class="booking-list-filter-field"><label for="owner-deactivated-after">Deactivated after</label><input id="owner-deactivated-after" type="date" name="deactivated_after" value="{{ request('deactivated_after') }}"></div>
            <div class="booking-list-filter-field"><label for="owner-deactivated-before">Deactivated before</label><input id="owner-deactivated-before" type="date" name="deactivated_before" value="{{ request('deactivated_before') }}"></div>
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            <button type="submit" class="sr-only">Apply filters</button>
        </form>

        <form id="bulk-owner-reactivate-form" method="POST" action="{{ route('accadmin.owners.bulk-reactivate') }}">@csrf</form>
        <form id="bulk-owner-destroy-form" method="POST" action="{{ route('accadmin.owners.bulk-destroy') }}" data-owner-bulk-delete>
            @csrf
            @method('DELETE')
        </form>
        <section class="account-lifecycle-table-section" aria-label="Deactivated owners">
            @include('accadmin.partials.deactivated-owner-results')
            @include('partials.account-lifecycle-pagination', ['records' => $owners, 'paginationId' => 'deactivated-owners'])
        </section>

        <div class="property-archive-bulk-bar" id="deactivated-owner-bulk-bar" role="region" aria-label="Selected owner actions" hidden>
            <span class="property-archive-selected-count" id="deactivated-owner-selected-count" aria-live="polite">0 selected</span>
            <button type="button" class="btn btn-outline" id="bulk-owner-reactivate">Reactivate</button>
            <button type="button" class="btn btn-danger" id="bulk-owner-delete">Delete Permanently</button>
            <button type="button" class="property-archive-clear-selection" id="clear-deactivated-owner-selection">Clear selection</button>
        </div>

        <div class="modal-overlay account-lifecycle-modal" id="owner-reactivate-confirmation" aria-hidden="true">
            <section class="modal-box">
                <header class="account-lifecycle-modal-header">
                    <strong>Confirm owner reactivation</strong>
                    <button type="button" class="btn btn-outline btn-sm" data-owner-confirm-close>Cancel</button>
                </header>
                <div class="account-lifecycle-modal-content">
                    <p>Owner login access will be restored. Previously archived properties and units will remain archived; booking and financial history will not change.</p>
                    <div class="account-lifecycle-modal-actions">
                        <button type="button" class="btn btn-outline" data-owner-confirm-close>Cancel</button>
                        <button type="button" class="btn btn-primary" id="owner-reactivate-confirm">Reactivate</button>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <script>
        const ownerSelects = Array.from(document.querySelectorAll('[data-deactivated-owner-select]'));
        const ownerSelectAll = document.getElementById('select-all-deactivated-owners');
        const ownerBulkBar = document.getElementById('deactivated-owner-bulk-bar');
        const ownerReactivateModal = document.getElementById('owner-reactivate-confirmation');
        let pendingOwnerReactivation = null;

        function updateDeactivatedOwnerSelection() {
            const count = ownerSelects.filter((checkbox) => checkbox.checked).length;
            document.getElementById('deactivated-owner-selected-count').textContent = `${count} selected`;
            ownerBulkBar.hidden = count === 0;
            document.querySelector('.account-lifecycle-page').classList.toggle('account-lifecycle-page--selected', count > 0);
            ownerSelectAll.checked = ownerSelects.length > 0 && count === ownerSelects.length;
            ownerSelectAll.indeterminate = count > 0 && count < ownerSelects.length;
        }

        ownerSelects.forEach((checkbox) => checkbox.addEventListener('change', updateDeactivatedOwnerSelection));
        ownerSelectAll.addEventListener('change', () => {
            ownerSelects.forEach((checkbox) => { checkbox.checked = ownerSelectAll.checked; });
            updateDeactivatedOwnerSelection();
        });
        document.getElementById('clear-deactivated-owner-selection').addEventListener('click', () => {
            ownerSelects.forEach((checkbox) => { checkbox.checked = false; });
            updateDeactivatedOwnerSelection();
        });
        function openOwnerReactivation(form) {
            pendingOwnerReactivation = form;
            ownerReactivateModal.classList.add('open');
            ownerReactivateModal.setAttribute('aria-hidden', 'false');
        }

        document.querySelectorAll('[data-owner-row-reactivate]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (form.dataset.confirmed === 'true') {
                    delete form.dataset.confirmed;
                    return;
                }
                event.preventDefault();
                openOwnerReactivation(form);
            });
        });
        document.getElementById('bulk-owner-reactivate').addEventListener('click', () => {
            openOwnerReactivation(document.getElementById('bulk-owner-reactivate-form'));
        });
        document.getElementById('owner-reactivate-confirm').addEventListener('click', () => {
            if (pendingOwnerReactivation) {
                pendingOwnerReactivation.dataset.confirmed = 'true';
                pendingOwnerReactivation.requestSubmit();
            }
            ownerReactivateModal.classList.remove('open');
            ownerReactivateModal.setAttribute('aria-hidden', 'true');
        });
        document.querySelectorAll('[data-owner-confirm-close]').forEach((button) => {
            button.addEventListener('click', () => {
                pendingOwnerReactivation = null;
                ownerReactivateModal.classList.remove('open');
                ownerReactivateModal.setAttribute('aria-hidden', 'true');
            });
        });
        document.getElementById('bulk-owner-delete').addEventListener('click', () => {
            if (window.confirm(`Permanently delete ${ownerSelects.filter((checkbox) => checkbox.checked).length} selected owner(s)? Eligible accounts only; protected records are retained.`)) {
                const form = document.getElementById('bulk-owner-destroy-form');
                form.querySelectorAll('[data-selected-owner]').forEach((input) => input.remove());
                ownerSelects.filter((checkbox) => checkbox.checked).forEach((checkbox) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'owner_ids[]';
                    input.value = checkbox.value;
                    input.dataset.selectedOwner = 'true';
                    form.appendChild(input);
                });
                form.requestSubmit();
            }
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
</x-accadmin-shell>

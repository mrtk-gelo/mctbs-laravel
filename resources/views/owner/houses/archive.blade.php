<x-shell title="Archives | MCTBS">
    <x-slot:nav>
        @if (auth()->user()->role->name === 'Staff')
            @include('partials.staff-nav', ['activePage' => 'houses'])
        @else
            @include('partials.owner-nav', ['activePage' => 'houses'])
        @endif
    </x-slot:nav>

    <main class="container property-archive" id="property-archive">
        <a href="{{ route($dashboardPrefix.'.houses.index') }}" class="text-muted fs-sm archive-back-link">
            <i class="fa fa-arrow-left" aria-hidden="true"></i> Back to properties
        </a>

        <header class="property-archive-header">
            <h1>Archives</h1>
            <a href="{{ route($dashboardPrefix.'.houses.archive.export', request()->query()) }}" class="btn btn-outline">
                <i class="fa fa-download" aria-hidden="true"></i>
                Export
            </a>
        </header>

        @if (session('archiveResults'))
            <div class="alert alert-info" role="status">
                <ul class="mb-0">
                    @foreach (session('archiveResults') as $result)
                        <li>{{ $result }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="GET"
            action="{{ route($dashboardPrefix.'.houses.archive.index') }}"
            class="card property-archive-filters booking-list-filters"
            id="archive-filters"
        >
            <div class="booking-list-filter-field">
                <label for="archive-resource">Resource</label>
                <select id="archive-resource" name="resource">
                    <option value="all" @selected($filters['resource'] === 'all')>All resources</option>
                    <option value="house" @selected($filters['resource'] === 'house')>Houses</option>
                    <option value="unit" @selected($filters['resource'] === 'unit')>Units</option>
                </select>
            </div>
            <div class="booking-list-filter-field booking-list-filter-search">
                <label for="archive-search">Search</label>
                <div class="property-archive-search">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <input
                        id="archive-search"
                        type="search"
                        name="q"
                        value="{{ $filters['q'] }}"
                        maxlength="100"
                        placeholder="Search houses and units"
                        autocomplete="off"
                    >
                </div>
            </div>
            <div class="booking-list-filter-field">
                <label for="archive-from">Archived after</label>
                <input id="archive-from" type="date" name="from" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="booking-list-filter-field">
                <label for="archive-to">Archived before</label>
                <input id="archive-to" type="date" name="to" value="{{ $filters['to'] ?? '' }}">
            </div>
            <input type="hidden" name="per_page" value="{{ $filters['per_page'] }}">
        </form>

        <section class="card property-archive-records" aria-label="Archived records">
            <div class="property-archive-table-wrap">
                <table class="property-archive-table">
                    <thead>
                        <tr>
                            <th class="property-archive-checkbox-column">
                                <input id="archive-select-all" type="checkbox" aria-label="Select all records on this page">
                            </th>
                            <th>Resource</th>
                            <th>Record / House Name</th>
                            <th>Archived At</th>
                            <th>Archived By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            @php
                                $recordName = $record->entity_type === 'house'
                                    ? $record->record_name
                                    : $record->record_name.' at '.$record->house_name;
                                $archivedBy = trim(($record->archived_by_first_name ?? '').' '.($record->archived_by_last_name ?? ''));
                            @endphp
                            <tr>
                                <td class="property-archive-checkbox-column">
                                    <input
                                        type="checkbox"
                                        name="records[]"
                                        value="{{ $record->entity_type }}:{{ $record->entity_id }}"
                                        form="archive-bulk-actions"
                                        data-archive-record-check
                                        data-record-name="{{ $recordName }}"
                                        aria-label="Select {{ $recordName }}"
                                    >
                                </td>
                                <td>
                                    <span class="property-archive-resource property-archive-resource--{{ $record->entity_type }}">
                                        {{ $record->resource }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $record->record_name }}</strong>
                                    @if ($record->entity_type === 'unit')
                                        <span class="property-archive-house-name">{{ $record->house_name }}</span>
                                    @endif
                                </td>
                                <td>{{ \Illuminate\Support\Carbon::parse($record->archived_at)->format('M j, Y g:i A') }}</td>
                                <td>{{ $archivedBy !== '' ? $archivedBy : 'Former user' }}</td>
                                <td class="property-archive-actions-column">
                                    <div class="property-archive-row-actions">
                                        @if ($record->entity_type === 'house')
                                            <form method="POST" action="{{ route($dashboardPrefix.'.houses.restore', $record->entity_id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline btn-sm">Restore</button>
                                            </form>
                                        @elseif ($record->house_archived_at !== null)
                                            <span class="property-archive-restore-note">Restore the house first</span>
                                        @else
                                            <form method="POST" action="{{ route($dashboardPrefix.'.houses.units.restore', [$record->house_id, $record->entity_id]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline btn-sm">Restore</button>
                                            </form>
                                        @endif

                                        @if ($record->entity_type === 'house')
                                            <form
                                                method="POST"
                                                action="{{ route($dashboardPrefix.'.houses.permanently-delete', $record->entity_id) }}"
                                                data-archive-delete
                                                data-record-name="{{ $recordName }}"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete Permanently</button>
                                            </form>
                                        @else
                                            <form
                                                method="POST"
                                                action="{{ route($dashboardPrefix.'.houses.units.permanently-delete', [$record->house_id, $record->entity_id]) }}"
                                                data-archive-delete
                                                data-record-name="{{ $recordName }}"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete Permanently</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="property-archive-empty">
                                    No archived records match these filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <footer class="property-archive-pagination">
            <form method="GET" action="{{ route($dashboardPrefix.'.houses.archive.index') }}" id="archive-page-size-form">
                <input type="hidden" name="resource" value="{{ $filters['resource'] }}">
                <input type="hidden" name="q" value="{{ $filters['q'] }}">
                <input type="hidden" name="from" value="{{ $filters['from'] }}">
                <input type="hidden" name="to" value="{{ $filters['to'] }}">
                <label for="archive-page-size">Rows per page</label>
                <select id="archive-page-size" name="per_page">
                    @foreach ([10, 25, 50] as $pageSize)
                        <option value="{{ $pageSize }}" @selected($filters['per_page'] === $pageSize)>{{ $pageSize }}</option>
                    @endforeach
                </select>
            </form>

            <span class="property-archive-record-count">
                {{ $records->firstItem() ?? 0 }}–{{ $records->lastItem() ?? 0 }} of {{ $records->total() }} records
            </span>

            <nav class="property-archive-page-navigation" aria-label="Archive pages">
                @if ($records->onFirstPage())
                    <button type="button" class="btn btn-outline btn-sm" aria-label="Previous page" disabled>
                        <i class="fa fa-chevron-left" aria-hidden="true"></i>
                    </button>
                @else
                    <a href="{{ $records->previousPageUrl() }}" class="btn btn-outline btn-sm" aria-label="Previous page">
                        <i class="fa fa-chevron-left" aria-hidden="true"></i>
                    </a>
                @endif

                <span>Page <strong>{{ $records->currentPage() }}</strong> of {{ max(1, $records->lastPage()) }}</span>

                @if ($records->hasMorePages())
                    <a href="{{ $records->nextPageUrl() }}" class="btn btn-outline btn-sm" aria-label="Next page">
                        <i class="fa fa-chevron-right" aria-hidden="true"></i>
                    </a>
                @else
                    <button type="button" class="btn btn-outline btn-sm" aria-label="Next page" disabled>
                        <i class="fa fa-chevron-right" aria-hidden="true"></i>
                    </button>
                @endif
            </nav>
        </footer>

        <form id="archive-bulk-actions" method="POST" action="{{ route($dashboardPrefix.'.houses.archive.bulk') }}">
            @csrf
            <input type="hidden" name="action" value="">
        </form>

        <div class="property-archive-bulk-bar" id="archive-bulk-bar" role="region" aria-label="Bulk archive actions" hidden>
            <span class="property-archive-selected-count" id="archive-selected-count" aria-live="polite">0 selected</span>
            <button type="button" class="btn btn-outline" id="archive-bulk-restore">Restore</button>
            <button type="button" class="btn btn-danger" id="archive-bulk-delete">Delete Permanently</button>
            <button type="button" class="property-archive-clear-selection" id="archive-clear-selection">Clear selection</button>
        </div>

        <div id="archive-delete-modal" class="modal-overlay" aria-hidden="true">
            <div class="modal-box property-archive-delete-dialog" role="dialog" aria-modal="true" aria-labelledby="archive-delete-title">
                <div class="property-archive-delete-header">
                    <strong id="archive-delete-title">Confirm permanent deletion</strong>
                    <button type="button" class="btn btn-outline btn-sm" data-archive-modal-close aria-label="Close">Close</button>
                </div>
                <div class="property-archive-delete-content">
                    <p id="archive-delete-message" data-archive-delete-message></p>
                    <p data-archive-delete-final hidden>
                        This cannot be undone. Records with protected history will be kept.
                    </p>
                    <div class="property-archive-delete-actions">
                        <button type="button" class="btn btn-outline" data-archive-modal-close>Cancel</button>
                        <button type="button" class="btn btn-danger" id="archive-delete-confirm">Continue</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const archiveFilters = document.getElementById('archive-filters');
        const archiveSearch = document.getElementById('archive-search');
        let archiveSearchTimer;

        archiveFilters.querySelectorAll('select, input[type="date"]').forEach((input) => {
            input.addEventListener('change', () => archiveFilters.requestSubmit());
        });
        archiveSearch.addEventListener('input', () => {
            window.clearTimeout(archiveSearchTimer);
            archiveSearchTimer = window.setTimeout(() => archiveFilters.requestSubmit(), 350);
        });
        archiveFilters.addEventListener('submit', () => window.clearTimeout(archiveSearchTimer));

        document.getElementById('archive-page-size').addEventListener('change', (event) => {
            event.currentTarget.form.requestSubmit();
        });

        const archiveCheckboxes = Array.from(document.querySelectorAll('[data-archive-record-check]'));
        const selectAllArchiveRecords = document.getElementById('archive-select-all');
        const archiveBulkBar = document.getElementById('archive-bulk-bar');
        const archiveSelectedCount = document.getElementById('archive-selected-count');
        const archiveBulkForm = document.getElementById('archive-bulk-actions');
        const archiveBulkAction = archiveBulkForm.querySelector('input[name="action"]');
        const archiveDeleteModal = document.getElementById('archive-delete-modal');
        const archiveDeleteMessage = document.getElementById('archive-delete-message');
        const archiveDeleteFinal = archiveDeleteModal.querySelector('[data-archive-delete-final]');
        const archiveDeleteConfirm = document.getElementById('archive-delete-confirm');
        let pendingArchiveDeleteForm = null;
        let archiveDeleteStep = 1;

        function selectedArchiveRecords() {
            return archiveCheckboxes.filter((checkbox) => checkbox.checked);
        }

        function updateArchiveSelection() {
            const selectedCount = selectedArchiveRecords().length;
            archiveSelectedCount.textContent = `${selectedCount} selected`;
            archiveBulkBar.hidden = selectedCount === 0;
            document.getElementById('property-archive').classList.toggle('property-archive--selected', selectedCount > 0);

            if (selectAllArchiveRecords) {
                selectAllArchiveRecords.checked = archiveCheckboxes.length > 0 && selectedCount === archiveCheckboxes.length;
                selectAllArchiveRecords.indeterminate = selectedCount > 0 && selectedCount < archiveCheckboxes.length;
            }
        }

        function closeArchiveDeleteModal() {
            archiveDeleteModal.classList.remove('open');
            archiveDeleteModal.setAttribute('aria-hidden', 'true');
            pendingArchiveDeleteForm = null;
        }

        function openArchiveDeleteModal(form, records) {
            pendingArchiveDeleteForm = form;
            archiveDeleteStep = 1;
            archiveDeleteMessage.textContent = form === archiveBulkForm
                ? `Permanently delete ${records.length} selected record(s)?`
                : `Permanently delete "${records[0]}"?`;
            archiveDeleteFinal.hidden = true;
            archiveDeleteConfirm.textContent = 'Continue';
            archiveDeleteModal.classList.add('open');
            archiveDeleteModal.setAttribute('aria-hidden', 'false');
            archiveDeleteConfirm.focus();
        }

        archiveCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', updateArchiveSelection));

        selectAllArchiveRecords.addEventListener('change', () => {
            archiveCheckboxes.forEach((checkbox) => {
                checkbox.checked = selectAllArchiveRecords.checked;
            });
            updateArchiveSelection();
        });

        document.getElementById('archive-clear-selection').addEventListener('click', () => {
            archiveCheckboxes.forEach((checkbox) => {
                checkbox.checked = false;
            });
            updateArchiveSelection();
        });

        document.getElementById('archive-bulk-restore').addEventListener('click', () => {
            archiveBulkAction.value = 'restore';
            archiveBulkForm.requestSubmit();
        });

        document.getElementById('archive-bulk-delete').addEventListener('click', () => {
            const selected = selectedArchiveRecords();
            if (selected.length > 0) {
                archiveBulkAction.value = 'delete';
                openArchiveDeleteModal(archiveBulkForm, selected.map((checkbox) => checkbox.dataset.recordName));
            }
        });

        archiveBulkForm.addEventListener('submit', (event) => {
            if (archiveBulkAction.value !== 'delete' || archiveBulkForm.dataset.archiveConfirmed === 'true') {
                delete archiveBulkForm.dataset.archiveConfirmed;
                return;
            }

            event.preventDefault();
            openArchiveDeleteModal(
                archiveBulkForm,
                selectedArchiveRecords().map((checkbox) => checkbox.dataset.recordName)
            );
        });

        document.querySelectorAll('[data-archive-delete]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (form.dataset.archiveConfirmed === 'true') {
                    delete form.dataset.archiveConfirmed;
                    return;
                }

                event.preventDefault();
                openArchiveDeleteModal(form, [form.dataset.recordName]);
            });
        });

        archiveDeleteConfirm.addEventListener('click', () => {
            if (archiveDeleteStep === 1) {
                archiveDeleteStep = 2;
                archiveDeleteMessage.textContent = 'Please confirm permanent deletion.';
                archiveDeleteFinal.hidden = false;
                archiveDeleteConfirm.textContent = 'Delete Permanently';
                archiveDeleteConfirm.focus();
                return;
            }

            if (pendingArchiveDeleteForm) {
                pendingArchiveDeleteForm.dataset.archiveConfirmed = 'true';
                pendingArchiveDeleteForm.requestSubmit();
            }
            closeArchiveDeleteModal();
        });

        document.querySelectorAll('[data-archive-modal-close]').forEach((button) => {
            button.addEventListener('click', closeArchiveDeleteModal);
        });

        archiveDeleteModal.addEventListener('click', (event) => {
            if (event.target === archiveDeleteModal) {
                closeArchiveDeleteModal();
            }
        });

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && archiveDeleteModal.classList.contains('open')) {
                closeArchiveDeleteModal();
            }
        });
    </script>
</x-shell>

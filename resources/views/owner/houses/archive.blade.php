<x-shell title="Archived Properties | MCTBS">
    <x-slot:nav>
        @if (auth()->user()->role->name === 'Staff')
            @include('partials.staff-nav', ['activePage' => 'houses'])
        @else
            @include('partials.owner-nav', ['activePage' => 'houses'])
        @endif
    </x-slot:nav>

    <div class="container" style="margin-top:24px">
        <div class="page-header-row page-header mb-3">
            <div>
                <h1>Archived Properties</h1>
                <p>Restore properties or permanently delete archived items with no protected history.</p>
            </div>
            <a href="{{ route($dashboardPrefix.'.houses.index') }}" class="btn btn-outline">
                <i class="fa fa-arrow-left"></i> Back to Properties
            </a>
        </div>

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

        <form method="GET" action="{{ route($dashboardPrefix.'.houses.archive.index') }}" class="card mb-4">
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group" style="flex:2">
                        <label for="archive-search">Search houses and units</label>
                        <input id="archive-search" type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Property or unit name">
                    </div>
                    <div class="form-group" style="flex:1">
                        <label for="archive-from">Archived from</label>
                        <input id="archive-from" type="date" name="from" value="{{ $filters['from'] ?? '' }}">
                    </div>
                    <div class="form-group" style="flex:1">
                        <label for="archive-to">Archived through</label>
                        <input id="archive-to" type="date" name="to" value="{{ $filters['to'] ?? '' }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa fa-magnifying-glass"></i> Apply Filters</button>
                <a href="{{ route($dashboardPrefix.'.houses.archive.index') }}" class="btn btn-outline">Clear</a>
            </div>
        </form>

        <section class="mb-5">
            <div class="flex-between mb-2">
                <div>
                    <h2 class="management-section-title mb-1">Archived Houses</h2>
                    <p class="text-muted">Restoring a house also restores only the units archived with it.</p>
                </div>
                <form id="bulk-house-actions" method="POST" action="{{ route($dashboardPrefix.'.houses.archive.houses') }}" data-bulk-confirm>
                    @csrf
                    <button type="submit" name="action" value="restore" class="btn btn-outline btn-sm">Restore Selected</button>
                    <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm">Permanently Delete Selected</button>
                </form>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th><span class="sr-only">Select</span></th>
                            <th>House</th>
                            <th>Archived</th>
                            <th>Units</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($houses as $house)
                            <tr>
                                <td><input type="checkbox" name="house_ids[]" value="{{ $house->house_id }}" form="bulk-house-actions" aria-label="Select {{ $house->name }}"></td>
                                <td>
                                    <strong>{{ $house->name }}</strong><br>
                                    <span class="text-muted">{{ $house->address }}, {{ $house->city }}</span>
                                </td>
                                <td>
                                    {{ $house->archived_at->format('M j, Y g:i A') }}<br>
                                    <span class="text-muted">{{ $house->archivedBy ? $house->archivedBy->first_name.' '.$house->archivedBy->last_name : 'Former user' }}</span>
                                </td>
                                <td>{{ $house->units_count }}</td>
                                <td>
                                    <div class="management-property-actions">
                                        <form method="POST" action="{{ route($dashboardPrefix.'.houses.restore', $house) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline btn-sm">Restore</button>
                                        </form>
                                        <form
                                            method="POST"
                                            action="{{ route($dashboardPrefix.'.houses.permanently-delete', $house) }}"
                                            data-permanent-confirm
                                            data-record-name="{{ $house->name }}"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Permanently Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted">No archived houses match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $houses->links() }}
        </section>

        <section>
            <div class="flex-between mb-2">
                <div>
                    <h2 class="management-section-title mb-1">Archived Units</h2>
                    <p class="text-muted">A unit inside an archived house is restored when its house is restored.</p>
                </div>
                <form id="bulk-unit-actions" method="POST" action="{{ route($dashboardPrefix.'.houses.archive.units') }}" data-bulk-confirm>
                    @csrf
                    <button type="submit" name="action" value="restore" class="btn btn-outline btn-sm">Restore Selected</button>
                    <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm">Permanently Delete Selected</button>
                </form>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th><span class="sr-only">Select</span></th>
                            <th>Unit</th>
                            <th>House</th>
                            <th>Archived</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($units as $unit)
                            <tr>
                                <td><input type="checkbox" name="unit_ids[]" value="{{ $unit->unit_id }}" form="bulk-unit-actions" aria-label="Select {{ $unit->unit_type }} at {{ $unit->house->name }}"></td>
                                <td><strong>{{ $unit->unit_type }}</strong></td>
                                <td>{{ $unit->house->name }}</td>
                                <td>
                                    {{ $unit->archived_at->format('M j, Y g:i A') }}<br>
                                    <span class="text-muted">{{ $unit->archivedBy ? $unit->archivedBy->first_name.' '.$unit->archivedBy->last_name : 'Former user' }}</span>
                                </td>
                                <td>
                                    <div class="management-property-actions">
                                        @if ($unit->house->archived_at)
                                            <span class="text-muted">Restore the house first</span>
                                        @else
                                            <form method="POST" action="{{ route($dashboardPrefix.'.houses.units.restore', [$unit->house, $unit]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline btn-sm">Restore</button>
                                            </form>
                                        @endif
                                        <form
                                            method="POST"
                                            action="{{ route($dashboardPrefix.'.houses.units.permanently-delete', [$unit->house, $unit]) }}"
                                            data-permanent-confirm
                                            data-record-name="{{ $unit->unit_type }} at {{ $unit->house->name }}"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Permanently Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted">No archived units match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $units->links() }}
        </section>
    </div>

    <script>
        document.querySelectorAll('[data-permanent-confirm]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                const name = form.dataset.recordName;
                if (!window.confirm('Permanently delete "' + name + '"?')) {
                    event.preventDefault();
                    return;
                }
                if (!window.confirm('This cannot be undone. Confirm permanent deletion?')) {
                    event.preventDefault();
                }
            });
        });

        document.querySelectorAll('[data-bulk-confirm]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!event.submitter || event.submitter.value !== 'delete') {
                    return;
                }
                const count = document.querySelectorAll('input[form="' + form.id + '"]:checked').length;
                if (count === 0 || !window.confirm('Permanently delete ' + count + ' selected item(s)?')) {
                    event.preventDefault();
                    return;
                }
                if (!window.confirm('This cannot be undone. Items with booking, review, report, or invitation history will be kept. Continue?')) {
                    event.preventDefault();
                }
            });
        });
    </script>
</x-shell>

<x-shell title="Deactivated Owners | MCTBS">
    <x-slot:nav>@include('partials.sysadmin-nav', ['activePage' => 'owners'])</x-slot:nav>
    <main class="container workspace-page account-lifecycle-page">
        <a href="{{ route('sysadmin.owners.index') }}" class="text-muted fs-sm account-lifecycle-back">
            <i class="fa fa-arrow-left"></i> Back to owner management
        </a>
        <header class="page-header-row page-header">
            <div><h1>Deactivated Owners</h1><p>Reactivate accounts or permanently delete only eligible owners.</p></div>
        </header>
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        <form method="GET" action="{{ route('sysadmin.owners.deactivated') }}" class="card account-lifecycle-filters" role="search">
            <div class="booking-list-filter-field"><label for="owner-search">Search</label><div class="property-archive-search"><i class="fa fa-search"></i><input id="owner-search" type="search" name="q" value="{{ $search }}" placeholder="Search deactivated owners"></div></div>
        </form>
        <section class="card personnel-table-card">
            <div class="personnel-table-toolbar">
                <form id="bulk-owner-reactivate-form" method="POST" action="{{ route('sysadmin.owners.bulk-reactivate') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">Reactivate Selected</button>
                </form>
                <form id="bulk-owner-destroy-form" method="POST" action="{{ route('sysadmin.owners.bulk-destroy') }}" onsubmit="return confirm('Permanently delete selected eligible owners? Protected accounts will be retained.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Delete Permanently Selected</button>
                </form>
            </div>
            @include('sysadmin.partials.deactivated-owner-results')
        </section>
        <script>
            document.getElementById('bulk-owner-destroy-form').addEventListener('submit', (event) => {
                const form = event.currentTarget;
                form.querySelectorAll('[data-selected-owner]').forEach((input) => input.remove());
                document.querySelectorAll('input[name="owner_ids[]"][form="bulk-owner-reactivate-form"]:checked').forEach((checkbox) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'owner_ids[]';
                    input.value = checkbox.value;
                    input.dataset.selectedOwner = 'true';
                    form.appendChild(input);
                });
            });
        </script>
    </main>
</x-shell>

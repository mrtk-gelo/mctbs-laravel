<x-accadmin-shell title="Database Backups | MCTBS">
    <main class="container workspace-page">
        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        <header class="page-header-row page-header">
            <div>
                <h1>Database backups</h1>
                <p>Create and download private copies of the system database.</p>
            </div>
            <form method="POST" action="{{ route('accadmin.backups.store') }}">
                @csrf
                <button type="submit" class="btn btn-primary"><i class="fa fa-database"></i> Create backup</button>
            </form>
        </header>

        <section class="card personnel-table-card">
            <div class="card-header"><span>Recent backups</span></div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Date</th>
                            <th scope="col">Size</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($backups as $backup)
                            <tr>
                                <td>{{ $backup['created_at']->format('M j, Y g:i A') }}</td>
                                <td>{{ $backup['size'] }}</td>
                                <td>
                                    <a class="btn btn-outline btn-sm" href="{{ route('accadmin.backups.download', $backup['filename']) }}">Download</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty-table-cell">No backups yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</x-accadmin-shell>

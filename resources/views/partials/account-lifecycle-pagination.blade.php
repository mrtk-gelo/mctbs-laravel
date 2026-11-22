<footer class="property-archive-pagination account-lifecycle-pagination">
    <form method="GET" action="{{ request()->url() }}">
        @foreach (request()->query() as $key => $value)
            @if (! in_array($key, ['per_page', 'page'], true) && is_scalar($value))
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <label for="{{ $paginationId }}-page-size">Rows per page</label>
        <select id="{{ $paginationId }}-page-size" name="per_page" onchange="this.form.requestSubmit()">
            @foreach ([10, 25, 50] as $pageSize)
                <option value="{{ $pageSize }}" @selected($records->perPage() === $pageSize)>{{ $pageSize }}</option>
            @endforeach
        </select>
    </form>

    <span class="property-archive-record-count">
        {{ $records->firstItem() ?? 0 }}–{{ $records->lastItem() ?? 0 }} of {{ $records->total() }} records
    </span>

    <nav class="property-archive-page-navigation" aria-label="Account pages">
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

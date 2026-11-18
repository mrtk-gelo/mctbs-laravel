<x-shell title="Browse Transients | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'transients'])</x-slot:nav>

    <section class="container transients-section transients-page" id="transients">
        <div class="transients-hero">
            <h1>Browse transient homes</h1>
            <p>Find comfortable stays across Baguio with a refined search experience and beautifully presented listings.</p>
        </div>
        <div class="transients-search-card">
            <form method="GET" action="{{ route('public.transients') }}" class="google-search-bar" data-ajax-search data-results="#transient-results">
                <div class="search-input-wrapper">
                    <input type="text" name="q" value="{{ $search }}" maxlength="100" placeholder="Search transient houses, locations, or descriptions..." aria-label="Search transient houses, locations, or descriptions">
                    <i class="fa fa-search search-icon"></i>
                </div>
            </form>
            <p class="sr-only" data-search-status aria-live="polite"></p>
            <p class="alert alert-danger mt-2" data-search-error hidden></p>
        </div>
        @include('public.partials.transient-results')
    </section>
</x-shell>

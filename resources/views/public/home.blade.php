<x-shell title="Welcome | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'home'])</x-slot:nav>

    <section class="hero hero-home" id="home" data-scroll-section="home">
        <h1>Your Home In The <br>City of Pines</h1>
        <p>Escape to the cool breeze of Baguio City with a cozy, comfortable stay made for peaceful and memorable moments.</p>
        <div class="hero-actions">
            @auth
                <a href="{{ auth()->user()->role?->name === 'Guest' ? '#transients' : auth()->user()->dashboardPath() }}" class="btn btn-primary">Book your escape</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">Book your escape</a>
            @endauth
            <a href="#transients" class="btn btn-explore">Explore transient homes</a>
        </div>
    </section>

    <section class="container transients-section" id="transients" data-scroll-section="transients">
        <div class="transients-hero">
            <h2>Browse transient homes</h2>
            <p>Find comfortable stays across Baguio with a refined search experience and beautifully presented listings.</p>
        </div>
        <div class="transients-search-card">
            <form method="GET" action="{{ route('home') }}" class="google-search-bar" data-ajax-search data-results="#transient-results">
                <div class="search-input-wrapper">
                    <input type="text" name="q" value="{{ $search }}" maxlength="100" placeholder="Search houses, locations, or descriptions..." aria-label="Search transient houses, locations, or descriptions">
                    <i class="fa fa-search search-icon"></i>
                </div>
            </form>
            <p class="sr-only" data-search-status aria-live="polite"></p>
            <p class="alert alert-danger mt-2" data-search-error hidden></p>
        </div>
        <div class="transient-home-results">
            @include('public.partials.transient-results')
        </div>
    </section>
</x-shell>

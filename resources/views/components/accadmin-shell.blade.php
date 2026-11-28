@props(['title' => 'Account Admin | MCTBS', 'heading' => 'Account Admin'])
@php($navUser = auth()->user())
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('img/mctbs-icon-512.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite(['resources/css/accadmin.css', 'resources/js/app.js'])
</head>
<body>
<a href="#aa-content" class="aa-skip-link">Skip to content</a>

<div class="aa-app" id="aa-app">
    @include('accadmin.partials.sidebar')
    <div class="aa-backdrop" id="aa-backdrop" aria-hidden="true"></div>

    <div class="aa-main-col">
        <header class="aa-topbar">
            <button type="button" class="aa-menu-button" id="aa-menu-button" aria-controls="aa-sidebar" aria-expanded="false" aria-label="Open navigation">
                <i class="fa fa-bars" aria-hidden="true"></i>
            </button>
            <span class="aa-topbar-title">{{ $heading }}</span>
            <div class="aa-topbar-right">
                @include('partials.notification-bell')
                @include('partials.profile-menu')
            </div>
        </header>

        <div class="aa-content" id="aa-content">
            {{ $slot }}
        </div>
    </div>
</div>

@include('partials.system-interface')

<script>
    (function () {
        const app = document.getElementById('aa-app');
        const button = document.getElementById('aa-menu-button');
        const backdrop = document.getElementById('aa-backdrop');
        const setOpen = (open) => {
            app.classList.toggle('aa-nav-open', open);
            button.setAttribute('aria-expanded', String(open));
            button.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
        };
        button.addEventListener('click', () => setOpen(! app.classList.contains('aa-nav-open')));
        backdrop.addEventListener('click', () => setOpen(false));
        document.addEventListener('keydown', (event) => { if (event.key === 'Escape') setOpen(false); });
        document.querySelectorAll('#aa-sidebar a').forEach((link) => link.addEventListener('click', () => setOpen(false)));
    })();
</script>
</body>
</html>

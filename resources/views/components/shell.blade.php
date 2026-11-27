@props(['title' => 'MCTBS', 'footer' => false, 'chatUnit' => null])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

{{ $nav }}

<div class="page-wrapper">
    {{ $slot }}
</div>

@include('partials.system-interface')

@if (auth()->user()?->role?->name === 'Guest' || (! auth()->check() && $chatUnit))
    @include('partials.guest-chat-launcher', ['unit' => $chatUnit])
@endif

@if ($footer)
<footer class="site-footer" id="contact">
    <div class="container site-footer-grid">
        <div class="site-footer-col site-footer-about">
            <a href="{{ route('home') }}" class="site-footer-brand" aria-label="MCTBS home">
                <span class="brand-icon"></span>
                <span>MCTBS</span>
            </a>
            <p>Motal and Conteros Transient Booking System. Comfortable, well-kept transient homes in Baguio City, with simple online booking and clear confirmations.</p>
            {{-- TODO: replace with the real MCTBS social pages --}}
            <ul class="site-footer-social" aria-label="Social media">
                <li><a href="https://facebook.com/yourpage" target="_blank" rel="noopener noreferrer" aria-label="MCTBS on Facebook"><i class="fa-brands fa-facebook-f"></i></a></li>
            </ul>
        </div>

        <div class="site-footer-col">
            <h3>Contact us</h3>
            {{-- TODO: replace the placeholder email and phone number --}}
            <ul class="site-footer-contact">
                <li><i class="fa fa-location-dot" aria-hidden="true"></i><span>Baguio City, Benguet, Philippines</span></li>
                <li><i class="fa fa-envelope" aria-hidden="true"></i><a href="mailto:hello@example.com">hello@example.com</a></li>
                <li><i class="fa fa-phone" aria-hidden="true"></i><a href="tel:+639000000000">+63 900 000 0000</a></li>
                <li><i class="fa fa-clock" aria-hidden="true"></i><span>Daily, 8:00 AM to 8:00 PM</span></li>
            </ul>
        </div>

        <div class="site-footer-col">
            @guest
                <h3>Own or manage a property?</h3>
                <p>Sign in to manage your listings, bookings and reports.</p>
                <a href="{{ route('login') }}" class="site-footer-cta">Owner and staff login <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
            @else
                <h3>Need help?</h3>
                <p>For questions about a booking or your account, reach us using the contact details on this page.</p>
            @endguest
        </div>
    </div>

    <div class="site-footer-bottom">
        <div class="container site-footer-bottom-inner">
            <span>&copy; {{ date('Y') }} Motal and Conteros Transient Booking System. All rights reserved.</span>
            <a href="#home" class="site-footer-top">Back to top <i class="fa fa-arrow-up" aria-hidden="true"></i></a>
        </div>
    </div>
</footer>
@endif

</body>
</html>
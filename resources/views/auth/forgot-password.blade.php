@extends('layouts.auth')

@section('title', 'Forgot Password | MCTBS')

@section('content')
<div class="auth-wrapper">
    <div class="auth-left">
        <div class="auth-left-noise"></div>
        <div class="deco-ring deco-ring-1"></div>
        <div class="deco-ring deco-ring-2"></div>
        <div class="deco-ring deco-ring-3"></div>
        <div class="left-content">
            <div class="brand-logo"><img src="{{ asset('img/mctbs.png') }}" alt="MCTBS logo"></div>
            <h1 class="left-heading">Motal and Conteros<br>Transient Booking System</h1>
            <span class="heading-accent"></span>
            <p class="left-subheader">A modern booking system designed for Baguio transient house services.</p>
        </div>
    </div>

    <div class="auth-right">
        <div class="auth-card">
            <div class="card-eyebrow"><span class="card-eyebrow-dot"></span> Account recovery</div>
            <h2 class="card-title">Forgot your password?</h2>
            <p class="card-subtitle">Enter the email address linked to your MCTBS account. If an active account matches, we’ll send a secure reset link.</p>

            @if (session('status'))
                <div class="auth-alert success" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="auth-alert error" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" data-auth-submit>
                @csrf
                <div class="form-field">
                    <label for="email">Email address <span class="field-requirement">Required</span></label>
                    <div class="input-wrap">
                        <i class="fa fa-envelope input-icon" aria-hidden="true"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@email.com" autocomplete="email" required autofocus>
                    </div>
                    @error('email')<div class="field-error" role="alert">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn-signin"><span data-auth-submit-label>Send reset link</span></button>
            </form>

            <p class="auth-link auth-recovery-return"><a href="{{ route('login') }}"><i class="fa fa-arrow-left" aria-hidden="true"></i> Return to Login</a></p>
        </div>
    </div>
</div>
<script>
    document.querySelectorAll('[data-auth-submit]').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');
            button.disabled = true;
            button.querySelector('[data-auth-submit-label]').textContent = 'Sending…';
        });
    });
</script>
@endsection

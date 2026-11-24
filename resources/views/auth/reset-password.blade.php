@extends('layouts.auth')

@section('title', 'Reset Password | MCTBS')

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
            <div class="card-eyebrow"><span class="card-eyebrow-dot"></span> Secure account recovery</div>
            <h2 class="card-title">Choose a new password</h2>
            <p class="card-subtitle">Set a strong password you have not used for this account before.</p>

            @if ($errors->any())
                <div class="auth-alert error" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" data-auth-submit>
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="form-field">
                    <label for="email">Email address <span class="field-requirement">Required</span></label>
                    <div class="input-wrap">
                        <i class="fa fa-envelope input-icon" aria-hidden="true"></i>
                        <input id="email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required>
                    </div>
                    @error('email')<div class="field-error" role="alert">{{ $message }}</div>@enderror
                </div>

                <div class="form-field">
                    <label for="password">New password <span class="field-requirement">Required</span></label>
                    <div class="input-wrap">
                        <i class="fa fa-lock input-icon" aria-hidden="true"></i>
                        <input id="password" type="password" name="password" autocomplete="new-password" required>
                        <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show new password">
                            <i class="fa fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    <span class="field-hint">At least 8 characters, with uppercase (A-Z) and lowercase letters (a-z), a number (0-9), and a symbol (!@#$%^&*()-+).</span>
                    @error('password')<div class="field-error" role="alert">{{ $message }}</div>@enderror
                </div>

                <div class="form-field">
                    <label for="password_confirmation">Confirm new password <span class="field-requirement">Required</span></label>
                    <div class="input-wrap">
                        <i class="fa fa-lock input-icon" aria-hidden="true"></i>
                        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                        <button type="button" class="password-toggle" data-password-toggle="password_confirmation" aria-label="Show password confirmation">
                            <i class="fa fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-signin"><span data-auth-submit-label>Reset password</span></button>
            </form>

            <p class="auth-link auth-recovery-return"><a href="{{ route('login') }}"><i class="fa fa-arrow-left" aria-hidden="true"></i> Return to Login</a></p>
        </div>
    </div>
</div>
<script>
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            button.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
            button.innerHTML = `<i class="fa fa-eye${visible ? '-slash' : ''}" aria-hidden="true"></i>`;
        });
    });

    document.querySelectorAll('[data-auth-submit]').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');
            button.disabled = true;
            button.querySelector('[data-auth-submit-label]').textContent = 'Updating…';
        });
    });
</script>
@endsection

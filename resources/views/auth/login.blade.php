@extends('layouts.auth')

@php($systemAdminLogin = $systemAdminLogin ?? false)

@section('title', $systemAdminLogin ? 'System Admin Login | MCTBS' : 'Login | MCTBS')

@section('content')
<div class="auth-wrapper">
    <div class="auth-left">
        <div class="auth-left-noise"></div>
        <div class="deco-ring deco-ring-1"></div>
        <div class="deco-ring deco-ring-2"></div>
        <div class="deco-ring deco-ring-3"></div>

        <div class="left-content">
            <div class="brand-logo">
                <img src="{{ asset('img/mctbs.png') }}" alt="MCTBS logo">
            </div>

            <h1 class="left-heading">Motal and Conteros<br>Transient Booking System</h1>
            <span class="heading-accent"></span>
            <p class="left-subheader">A modern booking system designed for Baguio transient house services.</p>
        </div>
    </div>

    <div class="auth-right">
        <div class="auth-card">
            <div class="card-eyebrow">
                <span class="card-eyebrow-dot"></span>
                {{ $systemAdminLogin ? 'Welcome back, Admin' : 'Welcome back' }}
            </div>
            <h2 class="card-title">{{ $systemAdminLogin ? 'System Admin sign in' : 'Sign in to your account' }}</h2>
            <p class="card-subtitle">
                {{ $systemAdminLogin ? 'Sign in with your Account Admin credentials to open the Account Admin dashboard.' : 'Enter your credentials to access your dashboard.' }}
            </p>

            @if (session('status'))
                <div class="auth-alert success" role="status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route($systemAdminLogin ? 'sysadmin.login.store' : 'login') }}" autocomplete="on">
                @csrf

                <div class="form-field">
                    <label for="email">Email Address <span class="field-requirement">Required</span></label>
                    <div class="input-wrap">
                        <i class="fa fa-envelope input-icon"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@email.com" required autofocus>
                    </div>
                    @error('email')
                        <div class="field-error">{{ $message }}</div>
                                @if (str_contains($message, 'Account Deactivated'))
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-outline btn-sm" onclick="alert('Please contact an Account Admin through your available MCTBS communication channel to request account reactivation.')">
                                            Contact Account Admin
                                        </button>
                                    </div>
                                @endif
                    @enderror
                </div>

                <div class="form-field">
                    <label for="password">Password <span class="field-requirement">Required</span></label>
                    <div class="auth-password-recovery"><a href="{{ route('password.request') }}">Forgot password?</a></div>
                    <div class="input-wrap">
                        <i class="fa fa-lock input-icon"></i>
                        <input id="password" type="password" name="password" placeholder="••••••••" required>
                        <button type="button" class="password-toggle" aria-label="Toggle password visibility" onclick="const input = document.getElementById('password'); const value = input.type === 'password'; input.type = value ? 'text' : 'password'; this.innerHTML = value ? '<i class=\'fa fa-eye-slash\'></i>' : '<i class=\'fa fa-eye\'></i>';">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-signin">
                    <i class="fa fa-arrow-right-to-bracket"></i>
                    Sign In
                </button>
            </form>

            <div class="divider">or</div>

            @if ($systemAdminLogin)
                <p class="auth-link">
                    Not a System Admin?&nbsp;
                    <a href="{{ route('login') }}">Return to regular sign in</a>
                </p>
            @else
                <p class="auth-link">
                    Don't have an account?&nbsp;
                    <a href="{{ route('register') }}">Register as Guest</a>
                </p>
            @endif
        </div>
    </div>
</div>
@endsection

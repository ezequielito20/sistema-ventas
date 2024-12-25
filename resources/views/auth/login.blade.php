@extends('layouts.app')

@section('content')
<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center bg-primary bg-gradient">
    <div class="card shadow-lg" style="max-width: 400px; width: 100%;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <h2 class="fw-bold mb-2">{{ __('Welcome Back') }}</h2>
                <p class="text-muted">{{ __('Please sign in to your account') }}</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="form-label">{{ __('Email Address') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input id="email" type="email" 
                            class="form-control @error('email') is-invalid @enderror" 
                            name="email" 
                            value="{{ old('email') }}" 
                            required 
                            autocomplete="email" 
                            placeholder="Enter your email"
                            autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input id="password" type="password" 
                            class="form-control @error('password') is-invalid @enderror" 
                            name="password" 
                            required 
                            autocomplete="current-password"
                            placeholder="Enter your password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" 
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                {{ __('Remember Me') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-6 text-end">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-decoration-none">
                                {{ __('Forgot Password?') }}
                            </a>
                        @endif
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3 position-relative">
                    <span class="position-absolute start-0 ms-3">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </span>
                    {{ __('Sign in') }}
                </button>

                <div class="text-center">
                    <span class="text-muted">Don't have an account?</span>
                    <a href="{{ route('register') }}" class="text-decoration-none">Register here</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    .bg-primary.bg-gradient {
        background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%) !important;
    }
    .card {
        border: none;
        border-radius: 1rem;
    }
    .input-group-text {
        background-color: transparent;
    }
    .btn-primary {
        padding: 0.8rem;
        border-radius: 0.5rem;
    }
</style>
@endpush

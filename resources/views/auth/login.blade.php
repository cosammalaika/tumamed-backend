@extends('layouts.auth')

@section('content')
    <h2 class="auth-title">Login to your account</h2>
    <p class="auth-subtitle">Manage TumaMed requests, delivery updates, and pharmacy operations from anywhere.</p>

    @if (session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="auth-form-grid">
        @csrf

        <div class="auth-field">
            <label class="form-label auth-label" for="email">Email</label>
            <div class="position-relative auth-input-wrap">
                <i class="mdi mdi-email-outline auth-input-icon"></i>
                <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control auth-control ps-5 @error('email') is-invalid @enderror" required autofocus autocomplete="email" placeholder="Enter Email Address">
            </div>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="auth-field">
            <label class="form-label auth-label" for="password">Password</label>
            <div class="position-relative auth-input-wrap">
                <i class="mdi mdi-lock-outline auth-input-icon"></i>
                <input id="password" name="password" type="password" class="form-control auth-control ps-5 pe-5 @error('password') is-invalid @enderror" required autocomplete="current-password" placeholder="Enter Password">
                <button type="button" class="btn btn-link auth-password-toggle" data-target="password" aria-label="Show password">
                    <i class="mdi mdi-eye-outline"></i>
                </button>
            </div>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="auth-field d-flex justify-content-between align-items-center auth-remember-row">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember" name="remember" @checked(old('remember'))>
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="auth-link">Forgot Password?</a>
            @endif
        </div>

        <button type="submit" class="btn auth-btn w-100">Login</button>
    </form>

    @if (Route::has('register'))
        <p class="text-center auth-bottom mb-0">
            Don’t have an account? <a href="{{ route('register') }}" class="auth-link fw-semibold">Create an account</a>
        </p>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('click', function (event) {
            const toggle = event.target.closest('.auth-password-toggle');
            if (!toggle) return;
            const input = document.getElementById(toggle.dataset.target);
            if (!input) return;

            const icon = toggle.querySelector('i');
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            if (icon) {
                icon.className = showing ? 'mdi mdi-eye-outline' : 'mdi mdi-eye-off-outline';
            }
        });
    </script>
@endpush

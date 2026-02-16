@extends('layouts.auth')

@section('content')
        <h2 class="auth-title">Create your account</h2>

        @if (session('status'))
            <div class="alert alert-success py-2">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="auth-form-grid">
            @csrf

            <div class="auth-field">
                <label class="form-label" for="name">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" class="form-control auth-control @error('name') is-invalid @enderror" required autofocus autocomplete="name" placeholder="Enter Full Name">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="auth-field">
                <label class="form-label" for="email">Email</label>
                <div class="position-relative auth-input-wrap">
                    <i class="mdi mdi-email-outline auth-input-icon"></i>
                <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control auth-control ps-5 @error('email') is-invalid @enderror" required autocomplete="email" placeholder="Enter Email Address">
                </div>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="auth-field">
                <label class="form-label" for="password">Password</label>
                <div class="position-relative auth-input-wrap">
                    <i class="mdi mdi-lock-outline auth-input-icon"></i>
                <input id="password" name="password" type="password" class="form-control auth-control ps-5 pe-5 @error('password') is-invalid @enderror" required autocomplete="new-password" placeholder="Enter Password">
                    <button type="button" class="btn btn-link auth-password-toggle" data-target="password" aria-label="Show password">
                        <i class="mdi mdi-eye-outline"></i>
                    </button>
                </div>
                <small class="text-muted d-block mt-1">Use at least 8 characters.</small>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="auth-field">
                <label class="form-label" for="password_confirmation">Confirm Password</label>
                <div class="position-relative auth-input-wrap">
                    <i class="mdi mdi-shield-lock-outline auth-input-icon"></i>
                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control auth-control ps-5 pe-5 @error('password_confirmation') is-invalid @enderror" required autocomplete="new-password" placeholder="Confirm Password">
                    <button type="button" class="btn btn-link auth-password-toggle" data-target="password_confirmation" aria-label="Show password">
                        <i class="mdi mdi-eye-outline"></i>
                    </button>
                </div>
                @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn auth-btn w-100">Create account</button>
        </form>

        <p class="text-center auth-bottom mb-0">
            Already have an account? <a href="{{ route('login') }}" class="auth-link fw-semibold">Login</a>
        </p>
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

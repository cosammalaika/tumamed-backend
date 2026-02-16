<x-layouts.auth>
    <div>
        <div class="text-center mb-4">
            <h4 class="mb-1">{{ __('Forgot password') }}</h4>
            <p class="text-muted mb-0">{{ __('Enter your email to receive a password reset link') }}</p>
        </div>
        <x-auth-session-status class="alert alert-success py-2" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label" for="email">{{ __('Email Address') }}</label>
                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" required autofocus placeholder="email@example.com">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary w-100" data-test="email-password-reset-link-button">{{ __('Email password reset link') }}</button>
        </form>

        <div class="text-center text-muted mt-4">
            <span>{{ __('Or, return to') }}</span>
            <a href="{{ route('login') }}">{{ __('log in') }}</a>
        </div>
    </div>
</x-layouts.auth>

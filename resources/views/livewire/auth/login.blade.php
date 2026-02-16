<x-layouts.auth>
    <div>
        <div class="text-center mb-4">
            <h4 class="mb-1">{{ __('Log in to your account') }}</h4>
            <p class="text-muted mb-0">{{ __('Enter your email and password below to log in') }}</p>
        </div>
        <x-auth-session-status class="alert alert-success py-2" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="email">{{ __('Email address') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="email" placeholder="email@example.com">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">{{ __('Password') }}</label>
                <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password" placeholder="{{ __('Password') }}">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember" @checked(old('remember'))>
                    <label class="form-check-label" for="remember">{{ __('Remember me') }}</label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
                @endif
            </div>

            <button type="submit" class="btn btn-primary w-100" data-test="login-button">{{ __('Log in') }}</button>
        </form>

        @if (Route::has('register'))
            <div class="text-center text-muted mt-4">
                <span>{{ __('Don\'t have an account?') }}</span>
                <a href="{{ route('register') }}">{{ __('Sign up') }}</a>
            </div>
        @endif
    </div>
</x-layouts.auth>

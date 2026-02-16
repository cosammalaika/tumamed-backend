<x-layouts.auth>
    <div>
        <div class="text-center mb-4">
            <h4 class="mb-1">{{ __('Create an account') }}</h4>
            <p class="text-muted mb-0">{{ __('Enter your details below to create your account') }}</p>
        </div>
        <x-auth-session-status class="alert alert-success py-2" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="name">{{ __('Name') }}</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required autofocus autocomplete="name" placeholder="{{ __('Full name') }}">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="email">{{ __('Email address') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autocomplete="email" placeholder="email@example.com">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">{{ __('Password') }}</label>
                <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password" placeholder="{{ __('Password') }}">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="password_confirmation">{{ __('Confirm password') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation') is-invalid @enderror" required autocomplete="new-password" placeholder="{{ __('Confirm password') }}">
                @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary w-100" data-test="register-user-button">{{ __('Create account') }}</button>
        </form>

        <div class="text-center text-muted mt-4">
            <span>{{ __('Already have an account?') }}</span>
            <a href="{{ route('login') }}">{{ __('Log in') }}</a>
        </div>
    </div>
</x-layouts.auth>

<x-layouts.auth>
    <div>
        <div class="text-center mb-4">
            <h4 class="mb-1">{{ __('Reset password') }}</h4>
            <p class="text-muted mb-0">{{ __('Please enter your new password below') }}</p>
        </div>
        <x-auth-session-status class="alert alert-success py-2" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <div class="mb-3">
                <label class="form-label" for="email">{{ __('Email') }}</label>
                <input id="email" name="email" type="email" value="{{ request('email') }}" class="form-control @error('email') is-invalid @enderror" required autocomplete="email">
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

            <button type="submit" class="btn btn-primary w-100" data-test="reset-password-button">{{ __('Reset password') }}</button>
        </form>
    </div>
</x-layouts.auth>

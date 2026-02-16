<x-layouts.app.sidebar :title="__('Profile')">
    @push('styles')
        <link href="{{ asset('admin/css/tumamed-admin.css') }}?v={{ filemtime(public_path('admin/css/tumamed-admin.css')) }}" rel="stylesheet" type="text/css" />
    @endpush

    <x-admin.page title="{{ __('Profile') }}" subtitle="{{ __('Manage your account information and security settings.') }}">
        <div class="tm-page tm-profile-page">
            @php
                $nameParts = preg_split('/\s+/', trim($user->name)) ?: [];
                $initials = collect($nameParts)->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
                $initials = $initials !== '' ? $initials : 'U';

                $lastLoginAt = data_get($user, 'last_login_at');
                $passwordUpdatedAt = data_get($user, 'password_updated_at') ?? data_get($user, 'password_changed_at');
            @endphp

            <div class="row g-4 align-items-start">
                <aside class="col-12 col-md-5 col-lg-4">
                    <div class="card profile-summary-card tm-card mb-3">
                        <div class="card-body tm-profile-summary">
                            <div class="text-center">
                                <div class="tm-avatar-wrap mx-auto mb-3">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle profile-avatar-gradient text-white fw-semibold tm-avatar-inner">
                                        {{ $initials }}
                                    </div>
                                </div>
                                <h5 class="mb-1 tm-profile-name">{{ $user->name }}</h5>
                                <p class="text-muted mb-3">{{ $user->email }}</p>
                                <div class="d-flex justify-content-center gap-2 flex-wrap mb-4">
                                    <span class="badge rounded-pill tm-role-badge px-3 py-2">{{ strtoupper($user->role) }}</span>
                                    <span class="badge rounded-pill tm-status-badge px-3 py-2">{{ __('Active') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card tm-card tm-compact-card mb-3">
                        <div class="card-body">
                            <h6 class="tm-section-title mb-2">{{ __('Account Details') }}</h6>
                            <div class="profile-meta-list text-start">
                                <div class="d-flex justify-content-between align-items-center py-2 border-top">
                                    <span class="text-muted">{{ __('Role') }}</span>
                                    <span class="fw-medium">{{ strtoupper($user->role) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2 border-top">
                                    <span class="text-muted">{{ __('Status') }}</span>
                                    <span class="badge tm-status-badge">{{ __('Active') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2 border-top border-bottom">
                                    <span class="text-muted">{{ __('Member since') }}</span>
                                    <span class="fw-medium">{{ $user->created_at?->format('M Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card tm-card tm-compact-card">
                        <div class="card-body">
                            <h6 class="tm-section-title mb-2">{{ __('Security Status') }}</h6>
                            <div class="tm-mini-panel">
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span class="text-muted">{{ __('Last login') }}</span>
                                    <span class="fw-medium text-end">{{ $lastLoginAt ? \Illuminate\Support\Carbon::parse($lastLoginAt)->format('M d, Y h:i A') : __('Not available yet') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2">
                                    <span class="text-muted">{{ __('Password updated') }}</span>
                                    <span class="fw-medium text-end">{{ $passwordUpdatedAt ? \Illuminate\Support\Carbon::parse($passwordUpdatedAt)->format('M d, Y') : __('Not available yet') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <main class="col-12 col-md-7 col-lg-8 tm-main">
                    @if (session('success'))
                        <div class="tm-inline-toast alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-circle-outline me-1"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card profile-card tm-card" id="profile-info">
                        <div class="card-header profile-card-header">
                            <h5 class="mb-1">{{ __('Profile Information') }}</h5>
                            <p class="text-muted small mb-0">{{ __('Update your personal details used in system notifications and account records.') }}</p>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.profile.update-info') }}" onsubmit="this.querySelector('button[type=submit]').disabled = true;">
                                @csrf
                                @method('PUT')
                                <div class="row g-3">
                                    <div class="col-12 col-lg-6">
                                        <label class="form-label">{{ __('Name') }}</label>
                                        <input type="text" name="name" class="form-control tm-profile-input @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label class="form-label">{{ __('Email') }}</label>
                                        <input type="email" name="email" class="form-control tm-profile-input @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">{{ __('Role') }}</label>
                                    <input type="text" class="form-control tm-profile-input" value="{{ strtoupper($user->role) }}" disabled>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn tm-btn-gradient">{{ __('Save changes') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card profile-card tm-card" id="security">
                        <div class="card-header profile-card-header">
                            <h5 class="mb-1">{{ __('Security') }}</h5>
                            <p class="text-muted small mb-0">{{ __('Change your password to keep your account protected.') }}</p>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.profile.update-password') }}" onsubmit="this.querySelector('button[type=submit]').disabled = true;">
                                @csrf
                                @method('PUT')
                                <div class="row g-3">
                                    <div class="col-12 col-lg-6">
                                        <label class="form-label">{{ __('Current password') }}</label>
                                        <input type="password" name="current_password" class="form-control tm-profile-input password-field @error('current_password') is-invalid @enderror" required>
                                        @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label class="form-label">{{ __('New password') }}</label>
                                        <input type="password" name="password" class="form-control tm-profile-input password-field @error('password') is-invalid @enderror" required>
                                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">{{ __('Confirm new password') }}</label>
                                    <input type="password" name="password_confirmation" class="form-control tm-profile-input password-field" required>
                                    <small class="text-muted d-block mt-1">{{ __('Use at least 8 characters.') }}</small>
                                </div>
                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                    <div>
                                        <div class="form-check mb-1">
                                            <input class="form-check-input" type="checkbox" id="toggle-password-visibility">
                                            <label class="form-check-label" for="toggle-password-visibility">
                                                {{ __('Show passwords') }}
                                            </label>
                                        </div>
                                        <p class="text-muted mb-0 small">
                                            {{ __('You will stay signed in here, but other devices may require sign-in again.') }}
                                        </p>
                                    </div>
                                    <button type="submit" class="btn tm-btn-gradient">{{ __('Update password') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </x-admin.page>

    @push('scripts')
        <script>
            document.addEventListener('change', function (event) {
                if (event.target.id !== 'toggle-password-visibility') return;
                document.querySelectorAll('.password-field').forEach(function (field) {
                    field.type = event.target.checked ? 'text' : 'password';
                });
            });
        </script>
    @endpush
</x-layouts.app.sidebar>

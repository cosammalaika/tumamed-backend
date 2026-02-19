<x-layouts.app.sidebar :title="__('Create User')">
    <x-admin.page title="{{ __('Create New User') }}" subtitle="{{ __('Add a new account and assign role access.') }}">
        <x-admin.section>
            <form method="POST" action="{{ route('admin.users.store') }}" class="row g-3">
                @csrf
                <input type="hidden" name="return_to" value="{{ old('return_to', $returnTo) }}">

                @if ($errors->any())
                    <div class="col-12">
                        <div class="alert alert-danger mb-0">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="col-md-6">
                    <label for="name" class="form-label">{{ __('Name') }}</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required maxlength="255">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">{{ __('Email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required minlength="8">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required minlength="8">
                </div>

                <div class="col-md-6">
                    <label for="role" class="form-label">{{ __('Role') }}</label>
                    <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="">{{ __('Select role') }}</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
                        @endforeach
                    </select>
                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div id="pharmacyField" class="col-md-6" style="display: none;">
                    <label for="pharmacy_id" class="form-label">{{ __('Pharmacy') }}</label>
                    <select id="pharmacy_id" name="pharmacy_id" class="form-select @error('pharmacy_id') is-invalid @enderror">
                        <option value="">{{ __('Select pharmacy') }}</option>
                        @foreach ($pharmacies as $pharmacy)
                            <option value="{{ $pharmacy->id }}" @selected(old('pharmacy_id') === $pharmacy->id)>
                                {{ $pharmacy->name }}{{ $pharmacy->is_active ? '' : ' (Inactive)' }}
                            </option>
                        @endforeach
                    </select>
                    @error('pharmacy_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input id="is_active" type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', '1') == '1')>
                        <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ $returnTo }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('Create User') }}</button>
                </div>
            </form>
        </x-admin.section>
    </x-admin.page>

    @push('scripts')
        <script>
            (function () {
                const roleInput = document.getElementById('role');
                const pharmacyField = document.getElementById('pharmacyField');
                const pharmacySelect = document.getElementById('pharmacy_id');

                function updatePharmacyField() {
                    const isPharmacyRole = roleInput && roleInput.value === 'PHARMACY';
                    pharmacyField.style.display = isPharmacyRole ? '' : 'none';
                    pharmacySelect.required = isPharmacyRole;
                    if (!isPharmacyRole) {
                        pharmacySelect.value = '';
                    }
                }

                if (roleInput) {
                    roleInput.addEventListener('change', updatePharmacyField);
                    updatePharmacyField();
                }
            })();
        </script>
    @endpush
</x-layouts.app.sidebar>

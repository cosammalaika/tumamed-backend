<x-admin.page title="{{ __('Users') }}" subtitle="{{ __('Manage user access and account activity.') }}">
    <x-slot:actions>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
            {{ __('Add User') }}
        </button>
    </x-slot:actions>

    <x-admin.section>
        <x-admin.table>
            <table class="table table-bordered table-hover align-middle table-nowrap w-100 js-datatable">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Created') }}</th>
                        <th class="no-sort table-actions-column"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ strtoupper($user->role) }}</td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-soft-success text-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-light text-muted">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td>{{ optional($user->created_at)->format('Y-m-d') }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.access.index') }}" class="btn btn-sm btn-outline-primary">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="m-0">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-sm {{ $user->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                            onclick="return confirm('{{ $user->is_active ? __('Disable this user?') : __('Activate this user?') }}')">
                                            {{ $user->is_active ? __('Disable') : __('Activate') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.table>
    </x-admin.section>

    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content border-0 rounded-3 shadow-sm">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">{{ __('Create New User') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('admin.users.store') }}" id="createUserForm">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ request()->fullUrlWithQuery(['openCreateUser' => null]) }}">

                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="createUserName" class="form-label">{{ __('Name') }}</label>
                                <input id="createUserName" name="name" type="text" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" maxlength="255" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="createUserEmail" class="form-label">{{ __('Email') }}</label>
                                <input id="createUserEmail" name="email" type="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="createUserPassword" class="form-label">{{ __('Password') }}</label>
                                <input id="createUserPassword" name="password" type="password" class="form-control @error('password') is-invalid @enderror" minlength="8" required>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="createUserPasswordConfirmation" class="form-label">{{ __('Confirm Password') }}</label>
                                <input id="createUserPasswordConfirmation" name="password_confirmation" type="password" class="form-control" minlength="8" required>
                            </div>

                            <div class="col-md-6">
                                <label for="createUserRole" class="form-label">{{ __('Role') }}</label>
                                <select id="createUserRole" name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="">{{ __('Select role') }}</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
                                    @endforeach
                                </select>
                                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div id="createUserPharmacyField" class="col-md-6 d-none">
                                <label for="createUserPharmacyId" class="form-label">{{ __('Pharmacy') }}</label>
                                <select id="createUserPharmacyId" name="pharmacy_id" class="form-select @error('pharmacy_id') is-invalid @enderror">
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
                                    <input id="createUserIsActive" type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', '1') == '1')>
                                    <label class="form-check-label" for="createUserIsActive">{{ __('Active') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" id="createUserSubmitBtn">{{ __('Create User') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                const modalEl = document.getElementById('createUserModal');
                const formEl = document.getElementById('createUserForm');
                const submitBtn = document.getElementById('createUserSubmitBtn');
                const roleEl = document.getElementById('createUserRole');
                const pharmacyFieldEl = document.getElementById('createUserPharmacyField');
                const pharmacySelectEl = document.getElementById('createUserPharmacyId');
                if (!modalEl || !formEl || !roleEl || !pharmacyFieldEl || !pharmacySelectEl) return;

                const modal = new bootstrap.Modal(modalEl);

                const togglePharmacy = function () {
                    const isPharmacy = roleEl.value === 'PHARMACY';
                    pharmacyFieldEl.classList.toggle('d-none', !isPharmacy);
                    pharmacySelectEl.required = isPharmacy;
                    if (!isPharmacy) {
                        pharmacySelectEl.value = '';
                    }
                };

                roleEl.addEventListener('change', togglePharmacy);
                togglePharmacy();

                formEl.addEventListener('submit', function () {
                    submitBtn.disabled = true;
                    submitBtn.innerText = '{{ __('Creating...') }}';
                });

                const shouldOpen = @json($errors->any() || request()->boolean('openCreateUser'));
                if (shouldOpen) {
                    modal.show();
                }
            })();
        </script>
    @endpush
</x-admin.page>

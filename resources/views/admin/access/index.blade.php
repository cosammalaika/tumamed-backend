<x-layouts.app.sidebar :title="__('Access Control')">
    <x-admin.page title="{{ __('Access Control') }}" subtitle="{{ __('Manage roles, permissions, and user access.') }}">
        <x-admin.section>
            <ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#access-roles" role="tab">{{ __('Roles') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#access-permissions" role="tab">{{ __('Permissions') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#access-assignment" role="tab">{{ __('Assignments') }}</a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="access-roles" role="tabpanel">
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createRoleModal">{{ __('New Role') }}</button>
                    </div>
                    <x-admin.table>
                        <table class="table table-bordered table-hover align-middle table-nowrap w-100 js-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Role') }}</th>
                                    <th>{{ __('Users') }}</th>
                                    <th>{{ __('Permissions') }}</th>
                                    <th class="no-sort text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                    <tr>
                                        <td>{{ $role->name }}</td>
                                        <td>{{ $role->users_count }}</td>
                                        <td>{{ $role->permissions_count }}</td>
                                        <td class="text-end table-actions">
                                            <div class="d-inline-flex gap-1">
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editRoleModal-{{ $role->id }}">{{ __('Edit') }}</button>
                                                <form method="POST" action="{{ route('admin.access.roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-admin.table>
                </div>

                <div class="tab-pane" id="access-permissions" role="tabpanel">
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPermissionModal">{{ __('New Permission') }}</button>
                    </div>
                    <x-admin.table>
                        <table class="table table-bordered table-hover align-middle table-nowrap w-100 js-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Permission') }}</th>
                                    <th>{{ __('Roles') }}</th>
                                    <th class="no-sort text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permissions as $permission)
                                    <tr>
                                        <td>{{ $permission->name }}</td>
                                        <td>{{ $permission->roles_count }}</td>
                                        <td class="text-end table-actions">
                                            <div class="d-inline-flex gap-1">
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPermissionModal-{{ $permission->id }}">{{ __('Edit') }}</button>
                                                <form method="POST" action="{{ route('admin.access.permissions.destroy', $permission) }}" onsubmit="return confirm('Delete this permission?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-admin.table>
                </div>

                <div class="tab-pane" id="access-assignment" role="tabpanel">
                    <form method="GET" action="{{ route('admin.access.index') }}" class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Select Role') }}</label>
                            <select class="form-select" name="role_id" onchange="this.form.submit()">
                                <option value="">{{ __('Choose role') }}</option>
                                @foreach ($allRoles as $roleOption)
                                    <option value="{{ $roleOption->id }}" @selected($selectedRoleId === $roleOption->id)>{{ $roleOption->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    @if ($selectedRole)
                        @php
                            $selectedPermissionIds = $selectedRole->permissions->pluck('id')->all();
                            $permissionGroups = $allPermissions->groupBy(function ($permission) {
                                $parts = explode('_', $permission->name, 2);
                                return strtoupper($parts[0] ?? 'misc');
                            });
                        @endphp
                        <form method="POST" action="{{ route('admin.access.roles.sync-permissions', $selectedRole) }}">
                            @csrf
                            <div class="d-flex gap-2 mb-3">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-check-all>{{ __('Select all') }}</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-clear-all>{{ __('Clear') }}</button>
                                <button type="submit" class="btn btn-sm btn-primary">{{ __('Save changes') }}</button>
                            </div>

                            <div class="row g-3 mb-4">
                                @foreach ($permissionGroups as $group => $groupPermissions)
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 h-100">
                                            <h6 class="mb-2">{{ $group }}</h6>
                                            @foreach ($groupPermissions as $permission)
                                                <div class="form-check mb-1">
                                                    <input class="form-check-input access-permission-checkbox" type="checkbox" name="permission_ids[]" id="perm-{{ $permission->id }}" value="{{ $permission->id }}" @checked(in_array($permission->id, $selectedPermissionIds, true))>
                                                    <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </form>
                    @endif

                    <h5 class="mb-3">{{ __('Assign Role to User') }}</h5>
                    <x-admin.table>
                        <table class="table table-bordered table-hover align-middle table-nowrap w-100 js-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Current Role') }}</th>
                                    <th class="no-sort">{{ __('Assign') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->roles->pluck('name')->join(', ') ?: $user->role }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.access.users.assign-role', $user) }}" class="d-flex gap-2 align-items-center">
                                                @csrf
                                                <select class="form-select form-select-sm" name="role_id" style="max-width: 220px;">
                                                    @foreach ($allRoles as $roleOption)
                                                        <option value="{{ $roleOption->id }}" @selected($user->roles->pluck('id')->contains($roleOption->id))>{{ $roleOption->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-outline-primary">{{ __('Save') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-admin.table>
                </div>
            </div>
        </x-admin.section>
    </x-admin.page>

    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('admin.access.roles.store') }}" class="modal-content">
                @csrf
                <div class="modal-header"><h5 class="modal-title">{{ __('New Role') }}</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="form-label">{{ __('Role name') }}</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('Create') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="createPermissionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('admin.access.permissions.store') }}" class="modal-content">
                @csrf
                <div class="modal-header"><h5 class="modal-title">{{ __('New Permission') }}</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="form-label">{{ __('Permission name') }}</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('Create') }}</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($roles as $role)
        <div class="modal fade" id="editRoleModal-{{ $role->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('admin.access.roles.update', $role) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header"><h5 class="modal-title">{{ __('Edit Role') }}</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <label class="form-label">{{ __('Role name') }}</label>
                        <input type="text" name="name" value="{{ $role->name }}" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary btn-sm">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    @foreach ($permissions as $permission)
        <div class="modal fade" id="editPermissionModal-{{ $permission->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('admin.access.permissions.update', $permission) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header"><h5 class="modal-title">{{ __('Edit Permission') }}</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <label class="form-label">{{ __('Permission name') }}</label>
                        <input type="text" name="name" value="{{ $permission->name }}" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary btn-sm">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    @push('scripts')
        <script>
            document.addEventListener('click', function (event) {
                if (event.target.matches('[data-check-all]')) {
                    document.querySelectorAll('.access-permission-checkbox').forEach((checkbox) => checkbox.checked = true);
                }

                if (event.target.matches('[data-clear-all]')) {
                    document.querySelectorAll('.access-permission-checkbox').forEach((checkbox) => checkbox.checked = false);
                }
            });
        </script>
    @endpush
</x-layouts.app.sidebar>


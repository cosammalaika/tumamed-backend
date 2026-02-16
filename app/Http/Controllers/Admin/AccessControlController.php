<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccessControlController extends Controller
{
    private const CORE_PERMISSIONS = [
        'view_dashboard',
        'manage_hospitals',
        'manage_pharmacies',
        'view_requests',
        'view_reports',
        'manage_requests',
        'view_audit_logs',
        'manage_users',
        'manage_roles_permissions',
    ];

    public function index(Request $request)
    {
        $selectedRole = null;
        $selectedRoleId = $request->string('role_id')->toString();
        if ($selectedRoleId !== '') {
            $selectedRole = Role::query()->with('permissions:id,name')->find($selectedRoleId);
        }

        return view('admin.access.index', [
            'roles' => Role::query()->withCount(['users', 'permissions'])->orderBy('name')->get(),
            'permissions' => Permission::query()->withCount('roles')->orderBy('name')->get(),
            'users' => User::query()->with('roles:id,name')->orderBy('name')->get(['id', 'name', 'email', 'role']),
            'allPermissions' => Permission::query()->orderBy('name')->get(['id', 'name']),
            'allRoles' => Role::query()->orderBy('name')->get(['id', 'name']),
            'selectedRole' => $selectedRole,
            'selectedRoleId' => $selectedRoleId,
        ]);
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')],
        ]);

        Role::create(['name' => $data['name'], 'guard_name' => 'web']);

        return back()->with('success', 'Role created successfully.');
    }

    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
        ]);

        $role->update(['name' => $data['name']]);

        return back()->with('success', 'Role updated successfully.');
    }

    public function destroyRole(Role $role): RedirectResponse
    {
        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'Cannot delete a role that is assigned to users.']);
        }

        if (strtoupper($role->name) === User::ROLE_ADMIN && $this->countAdminUsers() <= 1) {
            return back()->withErrors(['role' => 'Cannot delete the last Admin role in use.']);
        }

        $role->delete();

        return back()->with('success', 'Role deleted successfully.');
    }

    public function storePermission(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')],
        ]);

        Permission::create(['name' => $data['name'], 'guard_name' => 'web']);

        return back()->with('success', 'Permission created successfully.');
    }

    public function updatePermission(Request $request, Permission $permission): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($permission->id)],
        ]);

        $permission->update(['name' => $data['name']]);

        return back()->with('success', 'Permission updated successfully.');
    }

    public function destroyPermission(Permission $permission): RedirectResponse
    {
        if (in_array($permission->name, self::CORE_PERMISSIONS, true)) {
            return back()->withErrors(['permission' => 'Cannot delete a core system permission.']);
        }

        $permission->delete();

        return back()->with('success', 'Permission deleted successfully.');
    }

    public function syncRolePermissions(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['string', 'exists:permissions,id'],
        ]);

        $role->syncPermissions($data['permission_ids'] ?? []);

        return back()->with('success', 'Role permissions updated successfully.');
    }

    public function assignUserRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role_id' => ['required', 'string', 'exists:roles,id'],
        ]);

        $role = Role::query()->findOrFail($data['role_id']);

        if (strtoupper((string) $user->role) === User::ROLE_ADMIN
            && strtoupper($role->name) !== User::ROLE_ADMIN
            && $this->countAdminUsers() <= 1) {
            return back()->withErrors(['user_role' => 'Cannot remove the last Admin user role.']);
        }

        $user->syncRoles([$role->name]);
        $user->update(['role' => strtoupper($role->name)]);

        return back()->with('success', 'User role updated successfully.');
    }

    private function countAdminUsers(): int
    {
        return User::role(User::ROLE_ADMIN)->count();
    }
}

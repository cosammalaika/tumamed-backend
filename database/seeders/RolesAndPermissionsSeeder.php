<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'view_dashboard',
            'manage_hospitals',
            'manage_pharmacies',
            'view_requests',
            'view_reports',
            'manage_requests',
            'view_audit_logs',
            'manage_users',
            'manage_roles_permissions',
            'view_assigned_requests',
            'accept_request',
            'decline_request',
            'update_request_status',
            'toggle_pharmacy_open',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => User::ROLE_ADMIN, 'guard_name' => 'web']);
        $adminRole->syncPermissions($permissions);

        $pharmacyRole = Role::firstOrCreate(['name' => User::ROLE_PHARMACY, 'guard_name' => 'web']);
        $pharmacyRole->syncPermissions([
            'view_assigned_requests',
            'accept_request',
            'decline_request',
            'update_request_status',
            'toggle_pharmacy_open',
        ]);

        Role::firstOrCreate(['name' => User::ROLE_SUPPORT, 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => User::ROLE_PATIENT, 'guard_name' => 'web']);

        User::query()
            ->whereRaw('LOWER(role) = ?', [strtolower(User::ROLE_ADMIN)])
            ->get()
            ->each(function (User $user): void {
                $user->update(['role' => User::ROLE_ADMIN]);
                $user->syncRoles([User::ROLE_ADMIN]);
            });

        $superAdminRole = Role::query()->whereRaw('LOWER(name) = ?', ['super_admin'])->first();
        if ($superAdminRole) {
            $superAdminRole->syncPermissions($permissions);
        }
    }
}

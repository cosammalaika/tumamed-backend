<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('admin can access access-control page', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $user->syncRoles([User::ROLE_ADMIN]);

    $this->actingAs($user)
        ->get('/admin/access')
        ->assertOk()
        ->assertSee('Access Control');
});

test('non-admin without permission gets forbidden on access-control page', function () {
    $user = User::factory()->create(['role' => 'SUPPORT']);
    $user->syncRoles(['SUPPORT']);

    $this->actingAs($user)
        ->get('/admin/access')
        ->assertForbidden();
});

test('can create role from access control', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $user->syncRoles([User::ROLE_ADMIN]);

    $this->actingAs($user)
        ->post('/admin/access/roles', ['name' => 'DISPATCHER'])
        ->assertRedirect();

    $this->assertDatabaseHas('roles', ['name' => 'DISPATCHER']);
});

test('can create permission and sync it to a role', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $user->syncRoles([User::ROLE_ADMIN]);

    $this->actingAs($user)
        ->post('/admin/access/permissions', ['name' => 'view_finance'])
        ->assertRedirect();

    $role = Role::where('name', 'SUPPORT')->firstOrFail();
    $permission = Permission::where('name', 'view_finance')->firstOrFail();

    $this->actingAs($user)
        ->post("/admin/access/roles/{$role->id}/sync-permissions", [
            'permission_ids' => [$permission->id],
        ])
        ->assertRedirect();

    expect($role->fresh()->permissions->pluck('id')->contains($permission->id))->toBeTrue();
});

test('cannot delete role in use', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $user->syncRoles([User::ROLE_ADMIN]);

    $supportUser = User::factory()->create(['role' => 'SUPPORT']);
    $supportUser->syncRoles(['SUPPORT']);
    $supportRole = Role::where('name', 'SUPPORT')->firstOrFail();

    $this->actingAs($user)
        ->delete("/admin/access/roles/{$supportRole->id}")
        ->assertRedirect();

    $this->assertDatabaseHas('roles', ['id' => $supportRole->id]);
});

test('cannot remove last admin role from last admin user', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $admin->syncRoles([User::ROLE_ADMIN]);
    $supportRole = Role::where('name', 'SUPPORT')->firstOrFail();

    $this->actingAs($admin)
        ->post("/admin/access/users/{$admin->id}/assign-role", [
            'role_id' => $supportRole->id,
        ])
        ->assertRedirect();

    expect($admin->fresh()->role)->toBe(User::ROLE_ADMIN);
});


<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('admin user can access admin dashboard', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $admin->syncRoles([User::ROLE_ADMIN]);
    $admin->syncPermissions(['view_dashboard']);

    $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertOk();
});

test('non-admin gets forbidden on admin dashboard', function () {
    $user = User::factory()->create(['role' => 'SUPPORT']);
    $user->syncRoles(['SUPPORT']);

    $this->actingAs($user)
        ->get('/admin/dashboard')
        ->assertForbidden();
});

test('authenticated admin can view profile page', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $admin->syncRoles([User::ROLE_ADMIN]);

    $this->actingAs($admin)
        ->get('/admin/profile')
        ->assertOk();
});

test('admin can change password with correct current password', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'password' => 'old-password',
    ]);
    $admin->syncRoles([User::ROLE_ADMIN]);

    $this->actingAs($admin)
        ->put('/admin/profile/password', [
            'current_password' => 'old-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertRedirect();

    expect(Hash::check('new-password-123', $admin->fresh()->password))->toBeTrue();
});

test('admin password change fails with wrong current password', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'password' => 'old-password',
    ]);
    $admin->syncRoles([User::ROLE_ADMIN]);

    $this->actingAs($admin)
        ->from('/admin/profile')
        ->put('/admin/profile/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertRedirect('/admin/profile')
        ->assertSessionHasErrors('current_password');
});


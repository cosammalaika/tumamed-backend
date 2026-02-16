<?php

use App\Models\Hospital;
use App\Models\Pharmacy;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('disabled user cannot login', function () {
    $user = User::factory()->create([
        'email' => 'inactive@example.com',
        'password' => 'password',
        'is_active' => false,
    ]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('disabled authenticated user is logged out from admin route', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => false,
    ]);
    $user->syncRoles([User::ROLE_ADMIN]);
    $user->syncPermissions(['view_dashboard']);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('admin can toggle hospital and pharmacy active status', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $admin->syncRoles([User::ROLE_ADMIN]);
    $admin->syncPermissions(['manage_hospitals', 'manage_pharmacies']);

    $hospital = Hospital::create(['name' => 'Toggle Hospital', 'is_active' => true]);
    $pharmacy = Pharmacy::create(['name' => 'Toggle Pharmacy', 'is_active' => true]);

    $this->actingAs($admin)
        ->post(route('admin.hospitals.toggle-active', $hospital))
        ->assertRedirect();

    expect($hospital->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)
        ->post(route('admin.pharmacies.toggle-active', $pharmacy))
        ->assertRedirect();

    expect($pharmacy->fresh()->is_active)->toBeFalse();
});


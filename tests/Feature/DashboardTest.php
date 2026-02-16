<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $user->syncRoles([User::ROLE_ADMIN]);
    $user->syncPermissions(['view_dashboard']);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
});

test('non admin without dashboard permission is forbidden', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create(['role' => 'SUPPORT']);
    $user->syncRoles(['SUPPORT']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertForbidden();
});

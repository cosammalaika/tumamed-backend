<?php

use App\Models\Hospital;
use App\Models\MedicineRequest;
use App\Models\Patient;
use App\Models\Pharmacy;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('admin with permission can access reports page', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $admin->syncRoles([User::ROLE_ADMIN]);
    $admin->givePermissionTo('view_reports');

    $this->actingAs($admin)
        ->get(route('admin.reports'))
        ->assertOk()
        ->assertSee('Reports');
});

test('user without permission gets forbidden on reports page', function () {
    $support = User::factory()->create(['role' => 'SUPPORT']);
    $support->syncRoles(['SUPPORT']);

    $this->actingAs($support)
        ->get(route('admin.reports'))
        ->assertForbidden();
});

test('reports export returns downloadable file', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $admin->syncRoles([User::ROLE_ADMIN]);
    $admin->givePermissionTo('view_reports');

    $hospital = Hospital::create(['name' => 'General Hospital']);
    $pharmacy = Pharmacy::create(['name' => 'Main Pharmacy', 'is_open' => true, 'is_verified' => true]);
    $patient = Patient::create(['name' => 'Jane Doe', 'phone' => '5551234567']);

    MedicineRequest::create([
        'patient_id' => $patient->id,
        'hospital_id' => $hospital->id,
        'current_pharmacy_id' => $pharmacy->id,
        'status' => MedicineRequest::STATUS_SENT,
        'request_text' => 'Need refill',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reports.export', ['format' => 'csv']))
        ->assertOk()
        ->assertHeader('content-disposition');
});


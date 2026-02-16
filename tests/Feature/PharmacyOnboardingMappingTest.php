<?php

use App\Models\Hospital;
use App\Models\HospitalPharmacy;
use App\Models\Pharmacy;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('admin pharmacy create auto-creates mapping', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $admin->syncPermissions(['manage_pharmacies']);
    Sanctum::actingAs($admin);

    $hospital = Hospital::create([
        'name' => 'Central Hospital',
        'latitude' => -2.5164,
        'longitude' => 32.9175,
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/admin/pharmacies', [
        'name' => 'New Care Pharmacy',
        'latitude' => -2.5165,
        'longitude' => 32.9174,
        'hospital_id' => $hospital->id,
        'radius_km' => 3,
        'is_open' => true,
    ]);

    $response->assertCreated();
    $pharmacyId = $response->json('id');

    $this->assertDatabaseHas('hospital_pharmacies', [
        'pharmacy_id' => $pharmacyId,
        'hospital_id' => $hospital->id,
        'priority' => 1,
        'is_active' => true,
    ]);
});

test('mapping upsert does not duplicate for same pharmacy and hospital', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $admin->syncPermissions(['manage_pharmacies']);
    Sanctum::actingAs($admin);

    $hospital = Hospital::create([
        'name' => 'Referral Hospital',
        'is_active' => true,
    ]);

    $createResponse = $this->postJson('/api/admin/pharmacies', [
        'name' => 'Dup Check Pharmacy',
        'hospital_id' => $hospital->id,
        'radius_km' => 5,
    ])->assertCreated();

    $pharmacyId = $createResponse->json('id');
    $pharmacy = Pharmacy::findOrFail($pharmacyId);

    $this->putJson("/api/admin/pharmacies/{$pharmacy->id}", [
        'name' => 'Dup Check Pharmacy Updated',
        'hospital_id' => $hospital->id,
        'radius_km' => 2,
    ])->assertOk();

    $mappingCount = HospitalPharmacy::query()
        ->where('pharmacy_id', $pharmacy->id)
        ->where('hospital_id', $hospital->id)
        ->count();

    expect($mappingCount)->toBe(1);
});

test('pharmacy role cannot access admin hospitals page', function () {
    $pharmacyUser = User::factory()->create([
        'role' => User::ROLE_PHARMACY,
        'pharmacy_id' => Pharmacy::create(['name' => 'Assigned Pharmacy'])->id,
    ]);
    $pharmacyUser->syncRoles([User::ROLE_PHARMACY]);

    $this->actingAs($pharmacyUser)
        ->get('/admin/hospitals')
        ->assertForbidden();
});


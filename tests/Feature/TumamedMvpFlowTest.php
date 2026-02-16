<?php

use App\Jobs\DispatchToNextPharmacyJob;
use App\Jobs\RequestTimeoutForwardJob;
use App\Models\Hospital;
use App\Models\HospitalPharmacy;
use App\Models\MedicineRequest;
use App\Models\Patient;
use App\Models\RequestAssignment;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('patient create request queues dispatch job', function () {
    Queue::fake();

    $hospital = Hospital::create([
        'name' => 'Mwanza General',
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/requests', [
        'name' => 'Jane Patient',
        'phone' => '255700000001',
        'hospital_id' => $hospital->id,
        'request_text' => 'Need medicine',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('medicine_requests', [
        'hospital_id' => $hospital->id,
        'status' => MedicineRequest::STATUS_PENDING,
    ]);
    Queue::assertPushed(DispatchToNextPharmacyJob::class);
});

test('patient show and cancel require ownership proof', function () {
    $patient = Patient::create([
        'name' => 'Owner',
        'phone' => '255700000010',
    ]);
    $hospital = Hospital::create([
        'name' => 'Bugando',
        'is_active' => true,
    ]);
    $request = MedicineRequest::create([
        'patient_id' => $patient->id,
        'hospital_id' => $hospital->id,
        'status' => MedicineRequest::STATUS_PENDING,
        'request_text' => 'Painkillers',
    ]);

    $this->getJson("/api/requests/{$request->id}")
        ->assertForbidden();
    $this->postJson("/api/requests/{$request->id}/cancel")
        ->assertForbidden();

    $this->getJson("/api/requests/{$request->id}?phone=255700000010")
        ->assertOk();
    $this->postJson("/api/requests/{$request->id}/cancel", ['phone' => '255700000010'])
        ->assertOk()
        ->assertJsonPath('status', MedicineRequest::STATUS_CANCELLED);
});

test('patient rate limiter returns 429 when exceeded', function () {
    RateLimiter::for('patient', fn (Request $request) => \Illuminate\Cache\RateLimiting\Limit::perMinute(2)->by($request->ip()));

    $patient = Patient::create([
        'name' => 'Owner',
        'phone' => '255700000011',
    ]);
    $hospital = Hospital::create([
        'name' => 'Sekou Toure',
        'is_active' => true,
    ]);
    $request = MedicineRequest::create([
        'patient_id' => $patient->id,
        'hospital_id' => $hospital->id,
        'status' => MedicineRequest::STATUS_PENDING,
        'request_text' => 'Antibiotics',
    ]);

    $this->getJson("/api/requests/{$request->id}?phone=255700000011")->assertOk();
    $this->getJson("/api/requests/{$request->id}?phone=255700000011")->assertOk();
    $this->getJson("/api/requests/{$request->id}?phone=255700000011")->assertStatus(429);
});

test('pharmacy can accept an assigned request', function () {
    $hospital = Hospital::create([
        'name' => 'Mawenzi',
        'is_active' => true,
    ]);
    $pharmacy = \App\Models\Pharmacy::create([
        'name' => 'Afya Pharmacy',
        'is_verified' => true,
        'is_open' => true,
    ]);
    $patient = Patient::create([
        'name' => 'Client',
        'phone' => '255700000012',
    ]);
    $request = MedicineRequest::create([
        'patient_id' => $patient->id,
        'hospital_id' => $hospital->id,
        'current_pharmacy_id' => $pharmacy->id,
        'status' => MedicineRequest::STATUS_SENT,
        'request_text' => 'Vitamin C',
    ]);
    RequestAssignment::create([
        'medicine_request_id' => $request->id,
        'pharmacy_id' => $pharmacy->id,
        'attempt_no' => 1,
        'status' => MedicineRequest::STATUS_SENT,
        'sent_at' => now(),
    ]);
    Cache::put(DispatchToNextPharmacyJob::timeoutCacheKey($request->id, 1), true, now()->addMinutes(5));

    $user = User::factory()->create([
        'role' => User::ROLE_PHARMACY,
        'pharmacy_id' => $pharmacy->id,
    ]);
    $user->syncRoles([User::ROLE_PHARMACY]);

    Sanctum::actingAs($user, [$user->role]);

    $this->postJson("/api/pharmacy/requests/{$request->id}/accept")
        ->assertOk()
        ->assertJsonPath('status', MedicineRequest::STATUS_ACCEPTED);

    $this->assertDatabaseHas('request_events', [
        'medicine_request_id' => $request->id,
        'type' => MedicineRequest::STATUS_ACCEPTED,
    ]);
});

test('timeout forwarding marks current assignment and queues next dispatch', function () {
    Queue::fake();

    $hospital = Hospital::create([
        'name' => 'KCMC',
        'is_active' => true,
    ]);
    $pharmacyA = \App\Models\Pharmacy::create([
        'name' => 'Pharmacy A',
        'is_verified' => true,
        'is_open' => true,
    ]);
    $pharmacyB = \App\Models\Pharmacy::create([
        'name' => 'Pharmacy B',
        'is_verified' => true,
        'is_open' => true,
    ]);

    HospitalPharmacy::create([
        'hospital_id' => $hospital->id,
        'pharmacy_id' => $pharmacyA->id,
        'priority' => 2,
        'is_active' => true,
    ]);
    HospitalPharmacy::create([
        'hospital_id' => $hospital->id,
        'pharmacy_id' => $pharmacyB->id,
        'priority' => 1,
        'is_active' => true,
    ]);

    $patient = Patient::create([
        'name' => 'Timeout User',
        'phone' => '255700000013',
    ]);
    $request = MedicineRequest::create([
        'patient_id' => $patient->id,
        'hospital_id' => $hospital->id,
        'current_pharmacy_id' => $pharmacyA->id,
        'status' => MedicineRequest::STATUS_SENT,
        'request_text' => 'Cough syrup',
    ]);

    RequestAssignment::create([
        'medicine_request_id' => $request->id,
        'pharmacy_id' => $pharmacyA->id,
        'attempt_no' => 1,
        'status' => MedicineRequest::STATUS_SENT,
        'sent_at' => now()->subMinutes(6),
    ]);

    Cache::put(DispatchToNextPharmacyJob::timeoutCacheKey($request->id, 1), true, now()->addMinute());

    (new RequestTimeoutForwardJob($request->id, 1))->handle();

    $request->refresh();
    expect($request->status)->toBe(MedicineRequest::STATUS_FORWARDED);
    expect($request->current_pharmacy_id)->toBeNull();
    Queue::assertPushed(DispatchToNextPharmacyJob::class);
});

test('hospital search is case insensitive on sqlite compatible query', function () {
    Hospital::create([
        'name' => 'Alpha Health',
        'is_active' => true,
    ]);

    $this->getJson('/api/hospitals?search=ALPHA')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Alpha Health');
});

test('dashboard nav is visible for a user with seeded permission key', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $user->syncPermissions(['view_dashboard', 'manage_hospitals']);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('id="topnav-dashboard"', false);
});

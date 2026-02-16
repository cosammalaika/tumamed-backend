<?php

use App\Models\Hospital;
use App\Models\Order;
use App\Models\OrderPharmacyRequest;
use App\Models\Pharmacy;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('authenticated user can create order and it dispatches to nearby pharmacies', function () {
    $hospital = Hospital::create([
        'name' => 'Bugando Medical Centre',
        'town' => 'Mwanza',
        'latitude' => -2.5167,
        'longitude' => 32.9000,
        'is_active' => true,
    ]);

    Pharmacy::create([
        'name' => 'Near Pharmacy',
        'latitude' => -2.5170,
        'longitude' => 32.9005,
        'rating_avg' => 4.5,
        'is_active' => true,
        'is_open' => true,
    ]);

    Pharmacy::create([
        'name' => 'Far Pharmacy',
        'latitude' => -3.0000,
        'longitude' => 33.3000,
        'rating_avg' => 4.9,
        'is_active' => true,
        'is_open' => true,
    ]);

    $user = User::factory()->create();
    Sanctum::actingAs($user, [$user->role]);

    $response = $this->postJson('/api/orders', [
        'hospital_id' => $hospital->id,
        'is_self_patient' => true,
        'patient_name' => 'Jane Patient',
        'patient_phone' => '255700000101',
        'search_radius_km' => 5,
    ]);

    $response->assertCreated();

    $orderId = $response->json('id');
    $this->assertDatabaseHas('orders', [
        'id' => $orderId,
        'status' => Order::STATUS_AWAITING_RESPONSES,
    ]);

    $this->assertDatabaseCount('order_pharmacy_requests', 1);
});

test('pharmacy in stock response matches order and expires pending requests', function () {
    $hospital = Hospital::create([
        'name' => 'Sekou Toure Regional',
        'town' => 'Mwanza',
        'latitude' => -2.5200,
        'longitude' => 32.9100,
        'is_active' => true,
    ]);

    $pharmacyA = Pharmacy::create([
        'name' => 'Alpha Pharmacy',
        'latitude' => -2.5202,
        'longitude' => 32.9102,
        'rating_avg' => 4.0,
        'is_active' => true,
        'is_open' => true,
    ]);

    $pharmacyB = Pharmacy::create([
        'name' => 'Beta Pharmacy',
        'latitude' => -2.5204,
        'longitude' => 32.9104,
        'rating_avg' => 4.1,
        'is_active' => true,
        'is_open' => true,
    ]);

    $user = User::factory()->create();
    Sanctum::actingAs($user, [$user->role]);

    $order = Order::create([
        'user_id' => $user->id,
        'hospital_id' => $hospital->id,
        'is_self_patient' => true,
        'patient_name' => 'Order Owner',
        'patient_phone' => '255700000102',
        'status' => Order::STATUS_AWAITING_RESPONSES,
        'search_radius_km' => 5,
    ]);

    $reqA = OrderPharmacyRequest::create([
        'order_id' => $order->id,
        'pharmacy_id' => $pharmacyA->id,
        'score' => 0.8,
        'status' => OrderPharmacyRequest::STATUS_SENT,
    ]);

    OrderPharmacyRequest::create([
        'order_id' => $order->id,
        'pharmacy_id' => $pharmacyB->id,
        'score' => 0.7,
        'status' => OrderPharmacyRequest::STATUS_SENT,
    ]);

    $pharmacyUser = User::factory()->create([
        'role' => User::ROLE_PHARMACY,
        'pharmacy_id' => $pharmacyA->id,
    ]);
    $pharmacyUser->syncRoles([User::ROLE_PHARMACY]);

    Sanctum::actingAs($pharmacyUser, [$pharmacyUser->role]);

    $this->postJson("/api/pharmacy/order-requests/{$reqA->id}/respond", [
        'status' => 'in_stock',
    ])->assertOk();

    $order->refresh();
    expect($order->status)->toBe(Order::STATUS_MATCHED);
    expect($order->matched_pharmacy_id)->toBe($pharmacyA->id);

    $this->assertDatabaseHas('order_pharmacy_requests', [
        'order_id' => $order->id,
        'pharmacy_id' => $pharmacyB->id,
        'status' => OrderPharmacyRequest::STATUS_EXPIRED,
    ]);
});

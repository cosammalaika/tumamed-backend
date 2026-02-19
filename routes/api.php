<?php

use App\Http\Controllers\Api\Admin\HospitalController as AdminHospitalController;
use App\Http\Controllers\Api\Admin\PharmacyController as AdminPharmacyController;
use App\Http\Controllers\Api\Admin\RequestController as AdminRequestController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HospitalController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PatientRequestController;
use App\Http\Controllers\Api\Pharmacy\OrderPharmacyRequestController;
use App\Http\Controllers\Api\Pharmacy\PharmacyRequestController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/patient/register', [AuthController::class, 'patientRegister']);
Route::post('/patient/login', [AuthController::class, 'patientLogin']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/hospitals', [HospitalController::class, 'index']);
Route::middleware('throttle:patient')->group(function () {
    Route::post('/requests', [PatientRequestController::class, 'store']);
    Route::get('/requests/{medicineRequest}', [PatientRequestController::class, 'show']);
    Route::post('/requests/{medicineRequest}/cancel', [PatientRequestController::class, 'cancel']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/patient/logout', [AuthController::class, 'logout']);

    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders/{order}/prescriptions', [OrderController::class, 'uploadPrescriptions']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::get('/orders/{order}/requests', [OrderController::class, 'requests']);
    Route::post('/orders/{order}/expand-search', [OrderController::class, 'expandSearch']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);

    Route::prefix('pharmacy')->middleware(['role:PHARMACY'])->group(function () {
        Route::get('/order-requests', [OrderPharmacyRequestController::class, 'index'])->middleware('permission:view_assigned_requests');
        Route::post('/order-requests/{orderPharmacyRequest}/respond', [OrderPharmacyRequestController::class, 'respond'])->middleware('permission:update_request_status');

        Route::get('/requests', [PharmacyRequestController::class, 'index'])->middleware('permission:view_assigned_requests');
        Route::post('/requests/{medicineRequest}/accept', [PharmacyRequestController::class, 'accept'])->middleware('permission:accept_request');
        Route::post('/requests/{medicineRequest}/decline', [PharmacyRequestController::class, 'decline'])->middleware('permission:decline_request');
        Route::post('/requests/{medicineRequest}/status', [PharmacyRequestController::class, 'updateStatus'])->middleware('permission:update_request_status');
        Route::post('/toggle-open', [PharmacyRequestController::class, 'toggleOpen'])->middleware('permission:toggle_pharmacy_open');
    });

    Route::prefix('admin')->middleware(['role:ADMIN'])->group(function () {
        Route::apiResource('hospitals', AdminHospitalController::class)->middleware('permission:manage_hospitals');
        Route::apiResource('pharmacies', AdminPharmacyController::class)->middleware('permission:manage_pharmacies');
        Route::get('requests', [AdminRequestController::class, 'index'])->middleware('permission:view_requests');
        Route::post('users', [AdminUserController::class, 'store'])->middleware('permission:manage_users');
    });
});

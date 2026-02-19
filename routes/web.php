<?php

use App\Http\Controllers\Admin\AccessControlController;
use App\Http\Controllers\Admin\ActiveStatusController;
use App\Http\Controllers\Admin\HospitalController as AdminHospitalTableController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReportsExportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('dashboard', \App\Livewire\Admin\DashboardPage::class)
    ->middleware(['auth', 'verified', 'no-cache', 'active-user', 'dashboard-access'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'no-cache', 'active-user', 'role:ADMIN'])->prefix('admin')->group(function () {
    Route::get('dashboard', \App\Livewire\Admin\DashboardPage::class)->middleware('dashboard-access')->name('admin.dashboard');
    Route::get('users', \App\Livewire\Admin\UsersPage::class)->middleware('permission:manage_users')->name('admin.users');
    Route::get('users/create', [AdminUserController::class, 'create'])->middleware('permission:manage_users')->name('admin.users.create');
    Route::post('users', [AdminUserController::class, 'store'])->middleware('permission:manage_users')->name('admin.users.store');
    Route::get('hospitals', \App\Livewire\Admin\HospitalsPage::class)->middleware('permission:manage_hospitals')->name('admin.hospitals');
    Route::get('hospitals/datatables', [AdminHospitalTableController::class, 'datatables'])->middleware('permission:manage_hospitals')->name('admin.hospitals.datatables');
    Route::get('pharmacies', \App\Livewire\Admin\PharmaciesPage::class)->middleware('permission:manage_pharmacies')->name('admin.pharmacies');
    Route::get('requests', \App\Livewire\Admin\RequestsPage::class)->middleware('permission:view_requests')->name('admin.requests');
    Route::get('reports', \App\Livewire\Admin\ReportsPage::class)->middleware('permission:view_reports')->name('admin.reports');
    Route::get('reports/export', ReportsExportController::class)->middleware('permission:view_reports')->name('admin.reports.export');
    Route::get('audit-logs', \App\Livewire\Admin\AuditLogsPage::class)->middleware('permission:view_audit_logs')->name('admin.audit-logs');
    Route::get('profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('profile', [ProfileController::class, 'updateInfo'])->name('admin.profile.update-info');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('admin.profile.update-password');
    Route::post('users/{user}/toggle-active', [ActiveStatusController::class, 'toggleUser'])->middleware('permission:manage_users')->name('admin.users.toggle-active');
    Route::post('hospitals/{hospital}/toggle-active', [ActiveStatusController::class, 'toggleHospital'])->middleware('permission:manage_hospitals')->name('admin.hospitals.toggle-active');
    Route::post('pharmacies/{pharmacy}/toggle-active', [ActiveStatusController::class, 'togglePharmacy'])->middleware('permission:manage_pharmacies')->name('admin.pharmacies.toggle-active');
});

Route::middleware(['auth', 'verified', 'no-cache', 'active-user', 'access-control'])->prefix('admin/access')->group(function () {
    Route::get('/', [AccessControlController::class, 'index'])->name('admin.access.index');
    Route::post('/roles', [AccessControlController::class, 'storeRole'])->name('admin.access.roles.store');
    Route::put('/roles/{role}', [AccessControlController::class, 'updateRole'])->name('admin.access.roles.update');
    Route::delete('/roles/{role}', [AccessControlController::class, 'destroyRole'])->name('admin.access.roles.destroy');
    Route::post('/permissions', [AccessControlController::class, 'storePermission'])->name('admin.access.permissions.store');
    Route::put('/permissions/{permission}', [AccessControlController::class, 'updatePermission'])->name('admin.access.permissions.update');
    Route::delete('/permissions/{permission}', [AccessControlController::class, 'destroyPermission'])->name('admin.access.permissions.destroy');
    Route::post('/roles/{role}/sync-permissions', [AccessControlController::class, 'syncRolePermissions'])->name('admin.access.roles.sync-permissions');
    Route::post('/users/{user}/assign-role', [AccessControlController::class, 'assignUserRole'])->name('admin.access.users.assign-role');
});

require __DIR__.'/settings.php';

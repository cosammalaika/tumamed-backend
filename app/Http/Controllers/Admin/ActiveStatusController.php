<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\Pharmacy;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;

class ActiveStatusController extends Controller
{
    public function toggleUser(User $user, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless(auth()->user()?->can('manage_users'), 403);

        if ($user->is_active && strtoupper((string) $user->role) === User::ROLE_ADMIN && User::query()->where('role', User::ROLE_ADMIN)->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['user' => 'Cannot deactivate the last active admin user.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        $auditLogger->log('ADMIN_TOGGLE_USER_ACTIVE', [
            'user_id' => $user->id,
            'is_active' => $user->is_active,
        ], $user);

        return back()->with('success', 'User status updated.');
    }

    public function toggleHospital(Hospital $hospital, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless(auth()->user()?->can('manage_hospitals'), 403);

        $hospital->update(['is_active' => ! $hospital->is_active]);

        $auditLogger->log('ADMIN_TOGGLE_HOSPITAL_ACTIVE', [
            'hospital_id' => $hospital->id,
            'is_active' => $hospital->is_active,
        ], $hospital);

        return back()->with('success', 'Hospital status updated.');
    }

    public function togglePharmacy(Pharmacy $pharmacy, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless(auth()->user()?->can('manage_pharmacies'), 403);

        $newActive = ! $pharmacy->is_active;
        $pharmacy->update([
            'is_active' => $newActive,
            'is_open' => $newActive ? $pharmacy->is_open : false,
        ]);

        $auditLogger->log('ADMIN_TOGGLE_PHARMACY_ACTIVE', [
            'pharmacy_id' => $pharmacy->id,
            'is_active' => $pharmacy->is_active,
        ], $pharmacy);

        return back()->with('success', 'Pharmacy status updated.');
    }
}


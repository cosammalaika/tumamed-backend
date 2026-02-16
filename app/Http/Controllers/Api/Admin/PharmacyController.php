<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PharmacyRequest;
use App\Models\Pharmacy;
use App\Services\AuditLogger;
use App\Services\PharmacyOnboardingService;
use Illuminate\Http\Request;

class PharmacyController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->toString();

        $pharmacies = Pharmacy::query()
            ->when($search, function ($query) use ($search) {
                $needle = '%'.strtolower($search).'%';
                $query->where(function ($subQuery) use ($needle) {
                    $subQuery->whereRaw('LOWER(name) LIKE ?', [$needle])
                        ->orWhereRaw('LOWER(town) LIKE ?', [$needle])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$needle]);
                });
            })
            ->orderBy('name')
            ->paginate(20);

        return response()->json($pharmacies);
    }

    public function store(PharmacyRequest $request, AuditLogger $auditLogger, PharmacyOnboardingService $onboarding)
    {
        $validated = $request->validated();
        $hospitalId = $validated['hospital_id'] ?? null;
        $radiusKm = $validated['radius_km'] ?? 5;
        unset($validated['hospital_id'], $validated['radius_km']);

        $pharmacy = Pharmacy::create($validated);
        $onboarding->syncPrimaryMapping($pharmacy, $hospitalId, (int) $radiusKm);

        $auditLogger->log('ADMIN_CREATE_PHARMACY', $pharmacy->toArray(), $pharmacy, null, $request);

        return response()->json($pharmacy, 201);
    }

    public function show(Pharmacy $pharmacy)
    {
        return response()->json($pharmacy);
    }

    public function update(PharmacyRequest $request, Pharmacy $pharmacy, AuditLogger $auditLogger, PharmacyOnboardingService $onboarding)
    {
        $data = $request->validated();
        $hospitalId = $data['hospital_id'] ?? null;
        $radiusKm = $data['radius_km'] ?? 5;
        unset($data['hospital_id'], $data['radius_km']);

        $pharmacy->update($data);
        $onboarding->syncPrimaryMapping($pharmacy, $hospitalId, (int) $radiusKm);

        $auditLogger->log('ADMIN_UPDATE_PHARMACY', $data, $pharmacy, null, $request);

        return response()->json($pharmacy);
    }

    public function destroy(Pharmacy $pharmacy, AuditLogger $auditLogger, Request $request)
    {
        $auditLogger->log('ADMIN_DELETE_PHARMACY', $pharmacy->toArray(), $pharmacy, null, $request);
        $pharmacy->delete();

        return response()->noContent();
    }
}

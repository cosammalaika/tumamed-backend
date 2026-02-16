<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HospitalPharmacyRequest;
use App\Models\HospitalPharmacy;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class HospitalPharmacyController extends Controller
{
    public function store(HospitalPharmacyRequest $request, AuditLogger $auditLogger)
    {
        $data = $request->validated();

        $mapping = HospitalPharmacy::updateOrCreate(
            [
                'hospital_id' => $data['hospital_id'],
                'pharmacy_id' => $data['pharmacy_id'],
            ],
            [
                'priority' => $data['priority'],
                'is_active' => $data['is_active'],
            ]
        );

        $auditLogger->log('ADMIN_UPSERT_MAPPING', $data, $mapping, null, $request);

        return response()->json($mapping, 201);
    }

    public function destroy(Request $request, AuditLogger $auditLogger)
    {
        $data = $request->validate([
            'hospital_id' => ['required', 'string'],
            'pharmacy_id' => ['required', 'string'],
        ]);

        HospitalPharmacy::where('hospital_id', $data['hospital_id'])
            ->where('pharmacy_id', $data['pharmacy_id'])
            ->delete();

        $auditLogger->log('ADMIN_DELETE_MAPPING', $data, null, null, $request);

        return response()->noContent();
    }
}

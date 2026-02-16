<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicineRequest;
use App\Jobs\DispatchToNextPharmacyJob;
use App\Models\MedicineRequest;
use App\Models\Patient;
use App\Models\RequestAssignment;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PatientRequestController extends Controller
{
    public function store(StoreMedicineRequest $request, AuditLogger $auditLogger)
    {
        $data = $request->validated();

        /** @var Patient $patient */
        $patient = Patient::updateOrCreate(
            ['phone' => $data['phone']],
            ['name' => $data['name']],
        );

        $prescriptionPath = $request->hasFile('prescription_image')
            ? $request->file('prescription_image')->store('prescriptions', 'public')
            : null;

        $medicineRequest = MedicineRequest::create([
            'patient_id' => $patient->id,
            'hospital_id' => $data['hospital_id'],
            'status' => MedicineRequest::STATUS_PENDING,
            'request_text' => $data['request_text'],
            'prescription_image_path' => $prescriptionPath,
        ]);

        $medicineRequest->events()->create([
            'type' => 'CREATED',
        ]);

        $auditLogger->log(
            'PATIENT_CREATE_REQUEST',
            ['request_id' => $medicineRequest->id, 'hospital_id' => $data['hospital_id']],
            $medicineRequest,
            [
                'type' => 'PATIENT',
                'id' => $patient->id,
                'name' => $patient->name,
                'phone' => $patient->phone,
            ],
            $request
        );

        dispatch(new DispatchToNextPharmacyJob($medicineRequest->id));

        return response()->json(
            $medicineRequest->load(['patient', 'hospital', 'currentPharmacy']),
            201
        );
    }

    public function show(Request $request, MedicineRequest $medicineRequest)
    {
        if (! $this->ownsRequest($request, $medicineRequest)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json(
            $medicineRequest->load(['patient', 'hospital', 'currentPharmacy', 'assignments', 'events'])
        );
    }

    public function cancel(Request $request, MedicineRequest $medicineRequest)
    {
        if (! $this->ownsRequest($request, $medicineRequest)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $updated = DB::transaction(function () use ($medicineRequest) {
            /** @var MedicineRequest $locked */
            $locked = MedicineRequest::query()->lockForUpdate()->findOrFail($medicineRequest->id);

            if (! in_array($locked->status, [MedicineRequest::STATUS_PENDING, MedicineRequest::STATUS_SENT], true)) {
                return null;
            }

            $assignment = RequestAssignment::query()
                ->where('medicine_request_id', $locked->id)
                ->where('status', MedicineRequest::STATUS_SENT)
                ->orderByDesc('attempt_no')
                ->lockForUpdate()
                ->first();

            if ($assignment) {
                Cache::forget(DispatchToNextPharmacyJob::timeoutCacheKey($locked->id, $assignment->attempt_no));
                $assignment->update([
                    'status' => MedicineRequest::STATUS_CANCELLED,
                    'responded_at' => now(),
                ]);
            }

            $locked->update([
                'status' => MedicineRequest::STATUS_CANCELLED,
                'current_pharmacy_id' => null,
                'cancelled_at' => now(),
            ]);

            $locked->events()->create([
                'type' => MedicineRequest::STATUS_CANCELLED,
            ]);

            return $locked;
        });

        if (! $updated) {
            return response()->json([
                'message' => 'Request cannot be cancelled in its current state.',
            ], 422);
        }

        app(AuditLogger::class)->log(
            'PATIENT_CANCEL_REQUEST',
            ['request_id' => $medicineRequest->id],
            $medicineRequest,
            [
                'type' => 'PATIENT',
                'id' => $updated->patient_id,
                'name' => $updated->patient->name,
                'phone' => $updated->patient->phone,
            ],
            $request
        );

        return response()->json($updated->load(['patient', 'hospital', 'currentPharmacy']));
    }

    private function ownsRequest(Request $request, MedicineRequest $medicineRequest): bool
    {
        $providedPhone = trim((string) ($request->input('phone') ?? $request->header('X-Patient-Phone', '')));
        if ($providedPhone === '') {
            return false;
        }

        $ownerPhone = (string) optional($medicineRequest->loadMissing('patient')->patient)->phone;

        return hash_equals($ownerPhone, $providedPhone);
    }
}

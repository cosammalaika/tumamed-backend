<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Http\Requests\PharmacyDeclineRequest;
use App\Http\Requests\PharmacyStatusRequest;
use App\Jobs\DispatchToNextPharmacyJob;
use App\Models\MedicineRequest;
use App\Models\RequestAssignment;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PharmacyRequestController extends Controller
{
    public function index(Request $request)
    {
        $pharmacyId = $request->user()->pharmacy_id;

        $requests = MedicineRequest::query()
            ->where('current_pharmacy_id', $pharmacyId)
            ->with(['patient', 'hospital'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($requests);
    }

    public function accept(Request $request, MedicineRequest $medicineRequest, AuditLogger $auditLogger)
    {
        $user = $request->user();
        $lock = Cache::lock("request:accept:{$medicineRequest->id}", 5);

        return $lock->block(5, function () use ($medicineRequest, $user, $auditLogger, $request) {
            $updated = DB::transaction(function () use ($medicineRequest, $user) {
                /** @var MedicineRequest|null $locked */
                $locked = MedicineRequest::query()->lockForUpdate()->find($medicineRequest->id);

                if (! $locked || $locked->current_pharmacy_id !== $user->pharmacy_id || $locked->status !== MedicineRequest::STATUS_SENT) {
                    return null;
                }

                $assignment = RequestAssignment::query()
                    ->where('medicine_request_id', $locked->id)
                    ->where('pharmacy_id', $user->pharmacy_id)
                    ->where('status', MedicineRequest::STATUS_SENT)
                    ->lockForUpdate()
                    ->orderByDesc('attempt_no')
                    ->first();

                if (! $assignment) {
                    return null;
                }

                Cache::forget(DispatchToNextPharmacyJob::timeoutCacheKey($locked->id, $assignment->attempt_no));

                $assignment->update([
                    'status' => MedicineRequest::STATUS_ACCEPTED,
                    'responded_at' => now(),
                ]);

                $locked->update([
                    'status' => MedicineRequest::STATUS_ACCEPTED,
                    'accepted_at' => now(),
                ]);

                $locked->events()->create([
                    'type' => MedicineRequest::STATUS_ACCEPTED,
                    'actor_user_id' => $user->id,
                    'to_pharmacy_id' => $locked->current_pharmacy_id,
                ]);

                return $locked;
            });

            if (! $updated) {
                return response()->json(['message' => 'Request cannot be accepted.'], 422);
            }

            $auditLogger->log('PHARMACY_ACCEPT_REQUEST', [
                'request_id' => $medicineRequest->id,
                'pharmacy_id' => $user->pharmacy_id,
            ], $medicineRequest, null, $request);

            return response()->json($updated->load(['patient', 'hospital']));
        });
    }

    public function decline(PharmacyDeclineRequest $request, MedicineRequest $medicineRequest, AuditLogger $auditLogger)
    {
        $user = $request->user();
        $reason = $request->validated('reason');
        $shouldDispatch = false;

        $updated = DB::transaction(function () use ($medicineRequest, $user, $reason, &$shouldDispatch) {
            /** @var MedicineRequest|null $locked */
            $locked = MedicineRequest::query()->lockForUpdate()->find($medicineRequest->id);

            if (! $locked || $locked->current_pharmacy_id !== $user->pharmacy_id || $locked->status !== MedicineRequest::STATUS_SENT) {
                return null;
            }

            $assignment = RequestAssignment::query()
                ->where('medicine_request_id', $locked->id)
                ->where('pharmacy_id', $user->pharmacy_id)
                ->where('status', MedicineRequest::STATUS_SENT)
                ->lockForUpdate()
                ->orderByDesc('attempt_no')
                ->first();

            if (! $assignment) {
                return null;
            }

            Cache::forget(DispatchToNextPharmacyJob::timeoutCacheKey($locked->id, $assignment->attempt_no));

            $assignment->update([
                'status' => MedicineRequest::STATUS_DECLINED,
                'reason' => $reason,
                'responded_at' => now(),
            ]);

            $locked->events()->create([
                'type' => MedicineRequest::STATUS_DECLINED,
                'details' => $reason,
                'actor_user_id' => $user->id,
                'from_pharmacy_id' => $user->pharmacy_id,
            ]);

            $locked->update([
                'status' => MedicineRequest::STATUS_FORWARDED,
                'current_pharmacy_id' => null,
            ]);

            $shouldDispatch = true;

            return $locked;
        });

        if ($shouldDispatch) {
            dispatch(new DispatchToNextPharmacyJob($medicineRequest->id));
        }

        if (! $updated) {
            return response()->json(['message' => 'Request cannot be declined.'], 422);
        }

        $auditLogger->log('PHARMACY_DECLINE_REQUEST', [
            'request_id' => $medicineRequest->id,
            'reason' => $reason,
        ], $medicineRequest, null, $request);

        return response()->json($updated->load(['patient', 'hospital']));
    }

    public function updateStatus(PharmacyStatusRequest $request, MedicineRequest $medicineRequest, AuditLogger $auditLogger)
    {
        $user = $request->user();
        $newStatus = $request->validated('status');

        $updated = DB::transaction(function () use ($medicineRequest, $user, $newStatus) {
            /** @var MedicineRequest|null $locked */
            $locked = MedicineRequest::query()->lockForUpdate()->find($medicineRequest->id);

            if (! $locked || $locked->current_pharmacy_id !== $user->pharmacy_id) {
                return null;
            }

            $assignment = RequestAssignment::query()
                ->where('medicine_request_id', $locked->id)
                ->where('pharmacy_id', $user->pharmacy_id)
                ->orderByDesc('attempt_no')
                ->lockForUpdate()
                ->first();

            if ($newStatus === MedicineRequest::STATUS_DELIVERING) {
                if ($locked->status !== MedicineRequest::STATUS_ACCEPTED) {
                    return null;
                }

                $locked->update(['status' => MedicineRequest::STATUS_DELIVERING]);
                if ($assignment) {
                    $assignment->update(['status' => MedicineRequest::STATUS_DELIVERING]);
                }

                $locked->events()->create([
                    'type' => MedicineRequest::STATUS_DELIVERING,
                    'actor_user_id' => $user->id,
                ]);
            } elseif ($newStatus === MedicineRequest::STATUS_DELIVERED) {
                if (! in_array($locked->status, [MedicineRequest::STATUS_ACCEPTED, MedicineRequest::STATUS_DELIVERING], true)) {
                    return null;
                }

                $locked->update([
                    'status' => MedicineRequest::STATUS_DELIVERED,
                    'delivered_at' => now(),
                ]);

                if ($assignment) {
                    $assignment->update([
                        'status' => MedicineRequest::STATUS_DELIVERED,
                        'responded_at' => $assignment->responded_at ?? now(),
                    ]);
                }

                $locked->events()->create([
                    'type' => MedicineRequest::STATUS_DELIVERED,
                    'actor_user_id' => $user->id,
                ]);
            }

            return $locked;
        });

        if (! $updated) {
            return response()->json(['message' => 'Unable to update status.'], 422);
        }

        $auditLogger->log('PHARMACY_UPDATE_STATUS', [
            'request_id' => $medicineRequest->id,
            'status' => $newStatus,
        ], $medicineRequest, null, $request);

        return response()->json($updated->load(['patient', 'hospital']));
    }

    public function toggleOpen(Request $request, AuditLogger $auditLogger)
    {
        $pharmacy = $request->user()->pharmacy;

        if (! $pharmacy) {
            return response()->json(['message' => 'No pharmacy assigned.'], 422);
        }

        $pharmacy->update([
            'is_open' => ! $pharmacy->is_open,
        ]);

        $auditLogger->log('PHARMACY_TOGGLE_OPEN', [
            'pharmacy_id' => $pharmacy->id,
            'is_open' => $pharmacy->is_open,
        ], $pharmacy, null, $request);

        return response()->json([
            'pharmacy_id' => $pharmacy->id,
            'is_open' => $pharmacy->is_open,
        ]);
    }
}

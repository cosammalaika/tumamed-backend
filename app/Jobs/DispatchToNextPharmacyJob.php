<?php

namespace App\Jobs;

use App\Models\MedicineRequest;
use App\Models\Pharmacy;
use App\Models\RequestAssignment;
use App\Services\AuditLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DispatchToNextPharmacyJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];
    public int $timeout = 30;

    public function __construct(private readonly string $requestId)
    {
        //
    }

    public static function timeoutCacheKey(string $requestId, int $attemptNo): string
    {
        return "request:timeout:{$requestId}:{$attemptNo}";
    }

    /**
     * Execute the job by moving the request to the next eligible pharmacy.
     */
    public function handle(): void
    {
        $timeoutJob = null;

        DB::transaction(function () use (&$timeoutJob): void {
            /** @var MedicineRequest|null $request */
            $request = MedicineRequest::query()->lockForUpdate()->find($this->requestId);

            if (! $request) {
                return;
            }

            if (in_array($request->status, [
                MedicineRequest::STATUS_ACCEPTED,
                MedicineRequest::STATUS_DELIVERED,
                MedicineRequest::STATUS_CANCELLED,
                MedicineRequest::STATUS_EXPIRED,
            ], true)) {
                return;
            }

            $attempted = RequestAssignment::query()
                ->where('medicine_request_id', $request->id)
                ->pluck('pharmacy_id')
                ->all();

            /** @var Pharmacy|null $nextPharmacy */
            $nextPharmacy = Pharmacy::query()
                ->select('pharmacies.*', 'hospital_pharmacies.priority')
                ->join('hospital_pharmacies', 'pharmacies.id', '=', 'hospital_pharmacies.pharmacy_id')
                ->join('hospitals', 'hospitals.id', '=', 'hospital_pharmacies.hospital_id')
                ->where('hospital_pharmacies.hospital_id', $request->hospital_id)
                ->where('hospital_pharmacies.is_active', true)
                ->where('hospitals.is_active', true)
                ->where('pharmacies.is_active', true)
                ->where('pharmacies.is_verified', true)
                ->where('pharmacies.is_open', true)
                ->when($attempted, fn ($query) => $query->whereNotIn('pharmacies.id', $attempted))
                ->orderByDesc('hospital_pharmacies.priority')
                ->orderBy('pharmacies.name')
                ->first();

            if (! $nextPharmacy) {
                $request->update([
                    'status' => MedicineRequest::STATUS_EXPIRED,
                    'current_pharmacy_id' => null,
                ]);

                $request->events()->create([
                    'type' => MedicineRequest::STATUS_EXPIRED,
                ]);

                app(AuditLogger::class)->log('SYSTEM_EXPIRE_REQUEST', [
                    'request_id' => $request->id,
                ], $request, ['type' => 'SYSTEM', 'name' => 'DispatchJob']);

                return;
            }

            $attemptNo = (RequestAssignment::where('medicine_request_id', $request->id)->max('attempt_no') ?? 0) + 1;

            RequestAssignment::create([
                'medicine_request_id' => $request->id,
                'pharmacy_id' => $nextPharmacy->id,
                'attempt_no' => $attemptNo,
                'status' => MedicineRequest::STATUS_SENT,
                'sent_at' => now(),
            ]);

            $request->update([
                'status' => MedicineRequest::STATUS_SENT,
                'current_pharmacy_id' => $nextPharmacy->id,
            ]);

            $request->events()->create([
                'type' => 'SENT_TO_PHARMACY',
                'to_pharmacy_id' => $nextPharmacy->id,
            ]);

            app(AuditLogger::class)->log('SYSTEM_DISPATCH_TO_PHARMACY', [
                'request_id' => $request->id,
                'pharmacy_id' => $nextPharmacy->id,
                'attempt_no' => $attemptNo,
            ], $request, ['type' => 'SYSTEM', 'name' => 'DispatchJob']);

            Cache::put(self::timeoutCacheKey($request->id, $attemptNo), true, now()->addMinutes(5));
            $timeoutJob = new RequestTimeoutForwardJob($request->id, $attemptNo);
        });

        if ($timeoutJob) {
            dispatch($timeoutJob)->delay(now()->addMinutes(5));
        }
    }
}

<?php

namespace App\Jobs;

use App\Models\MedicineRequest;
use App\Models\RequestAssignment;
use App\Services\AuditLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RequestTimeoutForwardJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];
    public int $timeout = 30;

    public function __construct(
        private readonly string $requestId,
        private readonly int $attemptNo,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! Cache::has(DispatchToNextPharmacyJob::timeoutCacheKey($this->requestId, $this->attemptNo))) {
            return;
        }

        $shouldDispatch = false;

        DB::transaction(function () use (&$shouldDispatch): void {
            /** @var MedicineRequest|null $request */
            $request = MedicineRequest::query()->lockForUpdate()->find($this->requestId);

            if (! $request || $request->status !== MedicineRequest::STATUS_SENT || ! $request->current_pharmacy_id) {
                return;
            }

            /** @var RequestAssignment|null $assignment */
            $assignment = RequestAssignment::query()
                ->where('medicine_request_id', $request->id)
                ->where('attempt_no', $this->attemptNo)
                ->lockForUpdate()
                ->first();

            if (! $assignment || $assignment->status !== MedicineRequest::STATUS_SENT) {
                return;
            }

            $assignment->update([
                'status' => 'TIMED_OUT',
                'responded_at' => now(),
            ]);

            $request->events()->create([
                'type' => 'FORWARDED',
                'from_pharmacy_id' => $request->current_pharmacy_id,
                'details' => 'Timed out',
            ]);

            $request->update([
                'status' => MedicineRequest::STATUS_FORWARDED,
                'current_pharmacy_id' => null,
            ]);

            Cache::forget(DispatchToNextPharmacyJob::timeoutCacheKey($request->id, $this->attemptNo));
            $shouldDispatch = true;

            app(AuditLogger::class)->log('SYSTEM_TIMEOUT_FORWARD', [
                'request_id' => $request->id,
                'attempt_no' => $this->attemptNo,
                'from_pharmacy_id' => $assignment->pharmacy_id,
            ], $request, ['type' => 'SYSTEM', 'name' => 'TimeoutJob']);
        });

        if ($shouldDispatch) {
            dispatch(new DispatchToNextPharmacyJob($this->requestId));
        }
    }
}

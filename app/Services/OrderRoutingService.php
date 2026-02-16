<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderPharmacyRequest;
use App\Models\Pharmacy;
use Illuminate\Support\Facades\DB;

class OrderRoutingService
{
    public function dispatch(Order $order, int $limit = 10): int
    {
        $order->loadMissing('hospital');

        [$originLat, $originLng] = $this->resolveOrigin($order);
        if ($originLat === null || $originLng === null) {
            return 0;
        }

        $radiusKm = max(1, (int) $order->search_radius_km);

        $activeStatuses = [
            Order::STATUS_SUBMITTED,
            Order::STATUS_DISPATCHED,
            Order::STATUS_AWAITING_RESPONSES,
            Order::STATUS_EXPANDED_SEARCH,
            Order::STATUS_MATCHED,
        ];

        $workloadByPharmacy = DB::table('orders')
            ->selectRaw('matched_pharmacy_id, COUNT(*) AS total')
            ->whereIn('status', $activeStatuses)
            ->whereNotNull('matched_pharmacy_id')
            ->groupBy('matched_pharmacy_id')
            ->pluck('total', 'matched_pharmacy_id');

        $alreadyRequested = $order->pharmacyRequests()->pluck('pharmacy_id')->all();

        $candidates = Pharmacy::query()
            ->where('is_active', true)
            ->where('is_open', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($alreadyRequested, fn ($query) => $query->whereNotIn('id', $alreadyRequested))
            ->get()
            ->map(function (Pharmacy $pharmacy) use ($originLat, $originLng, $radiusKm, $workloadByPharmacy) {
                $distanceKm = $this->haversine($originLat, $originLng, (float) $pharmacy->latitude, (float) $pharmacy->longitude);
                if ($distanceKm > $radiusKm) {
                    return null;
                }

                $workload = (int) ($workloadByPharmacy[$pharmacy->id] ?? 0);
                $distanceScore = 1 - min(max($distanceKm / $radiusKm, 0), 1);
                $ratingScore = min(max(((float) $pharmacy->rating_avg) / 5, 0), 1);
                $workloadScore = 1 - min(max($workload / 20, 0), 1);

                $score = (0.50 * $distanceScore) + (0.30 * $ratingScore) + (0.20 * $workloadScore);

                return [
                    'pharmacy' => $pharmacy,
                    'score' => $score,
                ];
            })
            ->filter()
            ->sortByDesc('score')
            ->take($limit)
            ->values();

        if ($candidates->isEmpty()) {
            $order->update(['status' => Order::STATUS_UNAVAILABLE_NEARBY]);

            return 0;
        }

        DB::transaction(function () use ($order, $candidates) {
            foreach ($candidates as $candidate) {
                OrderPharmacyRequest::create([
                    'order_id' => $order->id,
                    'pharmacy_id' => $candidate['pharmacy']->id,
                    'score' => $candidate['score'],
                    'status' => OrderPharmacyRequest::STATUS_SENT,
                ]);
            }

            $order->update(['status' => Order::STATUS_AWAITING_RESPONSES]);
        });

        return $candidates->count();
    }

    public function markTimedOutRequests(Order $order, int $timeoutMinutes = 10): void
    {
        $cutoff = now()->subMinutes($timeoutMinutes);

        $order->pharmacyRequests()
            ->where('status', OrderPharmacyRequest::STATUS_SENT)
            ->where('created_at', '<=', $cutoff)
            ->update([
                'status' => OrderPharmacyRequest::STATUS_NO_RESPONSE,
                'responded_at' => now(),
            ]);
    }

    public function refreshAggregateStatus(Order $order): void
    {
        if ($order->status === Order::STATUS_MATCHED || $order->status === Order::STATUS_CANCELLED) {
            return;
        }

        $counts = $order->pharmacyRequests()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $sentCount = (int) ($counts[OrderPharmacyRequest::STATUS_SENT] ?? 0);
        $inStockCount = (int) ($counts[OrderPharmacyRequest::STATUS_IN_STOCK] ?? 0);

        if ($inStockCount > 0) {
            return;
        }

        if ($sentCount === 0 && $order->pharmacyRequests()->exists()) {
            $order->update(['status' => Order::STATUS_UNAVAILABLE_NEARBY]);
        }
    }

    /**
     * @return array{0: float|null, 1: float|null}
     */
    private function resolveOrigin(Order $order): array
    {
        $facilityLat = $order->hospital?->latitude;
        $facilityLng = $order->hospital?->longitude;

        if ($facilityLat !== null && $facilityLng !== null) {
            return [(float) $facilityLat, (float) $facilityLng];
        }

        if ($order->user_lat !== null && $order->user_lng !== null) {
            return [(float) $order->user_lat, (float) $order->user_lng];
        }

        return [null, null];
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $earthRadius * asin(min(1, sqrt($a)));
    }
}

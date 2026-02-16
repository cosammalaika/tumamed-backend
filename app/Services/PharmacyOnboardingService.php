<?php

namespace App\Services;

use App\Models\Hospital;
use App\Models\HospitalPharmacy;
use App\Models\Pharmacy;
use Illuminate\Support\Collection;

class PharmacyOnboardingService
{
    public function syncPrimaryMapping(Pharmacy $pharmacy, ?string $hospitalId = null, ?int $radiusKm = 5): ?HospitalPharmacy
    {
        $radius = max(1, min(5, (int) ($radiusKm ?? 5)));
        $hospital = $this->resolveHospital($pharmacy, $hospitalId, $radius);

        if (! $hospital) {
            return null;
        }

        return HospitalPharmacy::updateOrCreate(
            [
                'hospital_id' => $hospital->id,
                'pharmacy_id' => $pharmacy->id,
            ],
            [
                'priority' => 1,
                'is_active' => true,
            ],
        );
    }

    public function findNearbyHospitals(?float $latitude, ?float $longitude, ?int $radiusKm = 5): Collection
    {
        $radius = max(1, min(5, (int) ($radiusKm ?? 5)));

        return Hospital::query()
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->get(['id', 'name', 'latitude', 'longitude'])
            ->map(function (Hospital $hospital) use ($latitude, $longitude) {
                $distance = $this->distanceKm($latitude, $longitude, $hospital->latitude, $hospital->longitude);
                $hospital->distance_km = $distance;

                return $hospital;
            })
            ->filter(fn (Hospital $hospital) => $hospital->distance_km !== null && $hospital->distance_km <= $radius)
            ->sortBy('distance_km')
            ->values();
    }

    private function resolveHospital(Pharmacy $pharmacy, ?string $hospitalId, int $radiusKm): ?Hospital
    {
        if ($hospitalId) {
            return Hospital::query()
                ->whereKey($hospitalId)
                ->where('is_active', true)
                ->first();
        }

        if ($pharmacy->latitude === null || $pharmacy->longitude === null) {
            return null;
        }

        return $this->findNearbyHospitals($pharmacy->latitude, $pharmacy->longitude, $radiusKm)->first();
    }

    private function distanceKm(?float $lat1, ?float $lon1, ?float $lat2, ?float $lon2): ?float
    {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return null;
        }

        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earthRadiusKm * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}


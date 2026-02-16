<?php

namespace App\Livewire\Admin;

use App\Models\Hospital;
use App\Models\MedicineRequest;
use App\Models\Pharmacy;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardPage extends Component
{
    private function monthBucketExpression(): string
    {
        return match (DB::getDriverName()) {
            'pgsql' => "date_trunc('month', created_at)",
            'sqlite' => "strftime('%Y-%m-01', created_at)",
            default => "DATE_FORMAT(created_at, '%Y-%m-01')",
        };
    }

    public function render()
    {
        $hospitalCount = Hospital::count();
        $pharmacyCount = Pharmacy::count();

        $activeStatuses = [
            MedicineRequest::STATUS_PENDING,
            MedicineRequest::STATUS_SENT,
            MedicineRequest::STATUS_ACCEPTED,
            MedicineRequest::STATUS_DELIVERING,
            MedicineRequest::STATUS_FORWARDED,
        ];

        $activeRequests = MedicineRequest::whereIn('status', $activeStatuses)->count();
        $deliveredLast30 = MedicineRequest::where('delivered_at', '>=', now()->subDays(30))->count();

        $monthBucket = $this->monthBucketExpression();

        $requestsByMonth = MedicineRequest::selectRaw("{$monthBucket} as month, count(*) as total")
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'label' => \Illuminate\Support\Carbon::parse($row->month)->format('M Y'),
                'value' => (int) $row->total,
            ])
            ->values();

        $statusCounts = MedicineRequest::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $pharmacyActivity = [
            'open' => Pharmacy::where('is_open', true)->count(),
            'closed' => Pharmacy::where('is_open', false)->count(),
            'verified' => Pharmacy::where('is_verified', true)->count(),
            'unverified' => Pharmacy::where('is_verified', false)->count(),
        ];

        $recentRequests = MedicineRequest::with(['patient', 'hospital', 'currentPharmacy'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('livewire.admin.dashboard-page', [
            'hospitalCount' => $hospitalCount,
            'pharmacyCount' => $pharmacyCount,
            'activeRequests' => $activeRequests,
            'deliveredLast30' => $deliveredLast30,
            'requestsByMonth' => $requestsByMonth,
            'statusCounts' => $statusCounts,
            'pharmacyActivity' => $pharmacyActivity,
            'recentRequests' => $recentRequests,
        ])->layout('components.layouts.app.sidebar', ['title' => __('TumaMed Dashboard')]);
    }
}

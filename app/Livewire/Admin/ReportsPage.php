<?php

namespace App\Livewire\Admin;

use App\Models\Hospital;
use App\Models\MedicineRequest;
use App\Models\Pharmacy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReportsPage extends Component
{
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public ?string $hospitalId = null;
    public ?string $pharmacyId = null;
    public ?string $status = null;

    protected $queryString = [
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'hospitalId' => ['except' => ''],
        'pharmacyId' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('view_reports'), 403);
    }

    public function applyFilters(): void
    {
        // Query-string bound filters are applied automatically on render.
    }

    public function resetFilters(): void
    {
        $this->reset(['dateFrom', 'dateTo', 'hospitalId', 'pharmacyId', 'status']);
    }

    private function baseQuery(): Builder
    {
        return MedicineRequest::query()
            ->when($this->dateFrom, fn (Builder $q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn (Builder $q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->hospitalId, fn (Builder $q) => $q->where('hospital_id', $this->hospitalId))
            ->when(
                $this->pharmacyId,
                fn (Builder $q) => $q->where(function (Builder $sub): void {
                    $sub->where('current_pharmacy_id', $this->pharmacyId)
                        ->orWhereHas('assignments', fn (Builder $a) => $a->where('pharmacy_id', $this->pharmacyId));
                })
            )
            ->when($this->status, fn (Builder $q) => $q->where('status', $this->status));
    }

    public function render()
    {
        $query = $this->baseQuery();

        $rows = (clone $query)
            ->with(['patient', 'hospital', 'currentPharmacy'])
            ->orderByDesc('created_at')
            ->limit(2000)
            ->get();

        $totalRequests = (clone $query)->count();
        $delivered30 = (clone $query)
            ->where('status', MedicineRequest::STATUS_DELIVERED)
            ->where('delivered_at', '>=', now()->subDays(30))
            ->count();
        $activeRequests = (clone $query)
            ->whereIn('status', [
                MedicineRequest::STATUS_PENDING,
                MedicineRequest::STATUS_SENT,
                MedicineRequest::STATUS_ACCEPTED,
                MedicineRequest::STATUS_DELIVERING,
                MedicineRequest::STATUS_FORWARDED,
            ])
            ->count();
        $failedExpired = (clone $query)
            ->whereIn('status', [MedicineRequest::STATUS_DECLINED, MedicineRequest::STATUS_EXPIRED])
            ->count();

        $dayExpression = match (DB::getDriverName()) {
            'pgsql' => "DATE(created_at)",
            default => "date(created_at)",
        };

        $requestsPerDay = (clone $query)
            ->selectRaw("{$dayExpression} as day, count(*) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn ($row) => [
                'label' => (string) $row->day,
                'value' => (int) $row->total,
            ])
            ->values();

        $statusBreakdown = (clone $query)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('livewire.admin.reports-page', [
            'rows' => $rows,
            'hospitals' => Hospital::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'pharmacies' => Pharmacy::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'statuses' => [
                MedicineRequest::STATUS_PENDING,
                MedicineRequest::STATUS_SENT,
                MedicineRequest::STATUS_ACCEPTED,
                MedicineRequest::STATUS_DECLINED,
                MedicineRequest::STATUS_FORWARDED,
                MedicineRequest::STATUS_DELIVERING,
                MedicineRequest::STATUS_DELIVERED,
                MedicineRequest::STATUS_CANCELLED,
                MedicineRequest::STATUS_EXPIRED,
            ],
            'summary' => [
                'totalRequests' => $totalRequests,
                'delivered30' => $delivered30,
                'activeRequests' => $activeRequests,
                'failedExpired' => $failedExpired,
            ],
            'chartData' => [
                'requestsPerDay' => $requestsPerDay,
                'statusBreakdown' => $statusBreakdown,
            ],
        ])->layout('components.layouts.app.sidebar', ['title' => __('Reports')]);
    }
}

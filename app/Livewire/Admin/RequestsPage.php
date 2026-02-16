<?php

namespace App\Livewire\Admin;

use App\Models\Hospital;
use App\Models\Order;
use App\Models\Pharmacy;
use Livewire\Component;
use Livewire\WithPagination;

class RequestsPage extends Component
{
    use WithPagination;

    public ?string $status = null;
    public ?string $hospitalId = null;
    public ?string $pharmacyId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('view_requests'), 403);
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingHospitalId(): void
    {
        $this->resetPage();
    }

    public function updatingPharmacyId(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $requests = Order::with(['hospital', 'matchedPharmacy', 'prescriptions', 'pharmacyRequests'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->hospitalId, fn ($q) => $q->where('hospital_id', $this->hospitalId))
            ->when(
                $this->pharmacyId,
                fn ($q) => $q->where(function ($sub) {
                    $sub->where('matched_pharmacy_id', $this->pharmacyId)
                        ->orWhereHas('pharmacyRequests', fn ($assignment) => $assignment->where('pharmacy_id', $this->pharmacyId));
                })
            )
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.admin.requests-page', [
            'requests' => $requests,
            'hospitals' => Hospital::orderBy('name')->get(['id', 'name']),
            'pharmacies' => Pharmacy::orderBy('name')->get(['id', 'name']),
        ])->layout('components.layouts.app.sidebar', ['title' => __('Requests')]);
    }
}

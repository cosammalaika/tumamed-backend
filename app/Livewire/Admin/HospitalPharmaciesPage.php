<?php

namespace App\Livewire\Admin;

use App\Models\Hospital;
use App\Models\HospitalPharmacy;
use App\Models\Pharmacy;
use App\Services\AuditLogger;
use Livewire\Component;
use Livewire\WithPagination;

class HospitalPharmaciesPage extends Component
{
    use WithPagination;

    public ?string $selectedHospitalId = null;
    public ?string $pharmacyId = null;
    public int $priority = 0;
    public bool $is_active = true;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('manage_mappings'), 403);
    }

    protected function rules(): array
    {
        return [
            'selectedHospitalId' => ['required', 'string', 'exists:hospitals,id'],
            'pharmacyId' => ['required', 'string', 'exists:pharmacies,id'],
            'priority' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function updatedSelectedHospitalId(): void
    {
        $this->resetPage();
    }

    public function save(): void
    {
        $data = $this->validate();

        HospitalPharmacy::updateOrCreate(
            [
                'hospital_id' => $data['selectedHospitalId'],
                'pharmacy_id' => $data['pharmacyId'],
            ],
            [
                'priority' => $data['priority'],
                'is_active' => $data['is_active'],
            ]
        );

        app(AuditLogger::class)->log('ADMIN_UPSERT_MAPPING', $data);

        $this->pharmacyId = null;
        $this->priority = 0;
        $this->is_active = true;
    }

    public function remove(string $mappingId): void
    {
        HospitalPharmacy::whereKey($mappingId)->delete();
        app(AuditLogger::class)->log('ADMIN_DELETE_MAPPING', ['mapping_id' => $mappingId]);
    }

    public function render()
    {
        $hospitals = Hospital::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $pharmacies = Pharmacy::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $mappings = HospitalPharmacy::with('pharmacy')
            ->when($this->selectedHospitalId, fn ($q) => $q->where('hospital_id', $this->selectedHospitalId))
            ->orderByDesc('priority')
            ->get();

        return view('livewire.admin.hospital-pharmacies-page', [
            'hospitals' => $hospitals,
            'pharmacies' => $pharmacies,
            'mappings' => $mappings,
        ])->layout('components.layouts.app.sidebar', ['title' => __('Hospital ↔ Pharmacy')]);
    }
}

<?php

namespace App\Livewire\Admin;

use App\Models\Pharmacy;
use Illuminate\Validation\Rule;
use App\Services\AuditLogger;
use App\Services\PharmacyOnboardingService;
use Livewire\Component;
use Livewire\WithPagination;

class PharmaciesPage extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $editingId = null;
    public int $radiusKm = 5;
    public ?string $hospitalId = null;
    public array $form = [
        'name' => '',
        'email' => '',
        'phone' => '',
        'town' => '',
        'address' => '',
        'latitude' => null,
        'longitude' => null,
        'is_active' => true,
        'is_verified' => false,
        'is_open' => true,
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('manage_pharmacies'), 403);
    }

    protected function rules(): array
    {
        return [
            'form.name' => ['required', 'string', 'max:255'],
            'form.email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('pharmacies', 'email')->ignore($this->editingId),
            ],
            'form.phone' => ['nullable', 'string', 'max:50'],
            'form.town' => ['nullable', 'string', 'max:150'],
            'form.address' => ['nullable', 'string', 'max:255'],
            'form.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'form.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'form.is_active' => ['boolean'],
            'hospitalId' => ['required', 'string', 'exists:hospitals,id'],
            'radiusKm' => ['required', 'integer', 'min:1', 'max:5'],
            'form.is_verified' => ['boolean'],
            'form.is_open' => ['boolean'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function edit(string $pharmacyId): void
    {
        $pharmacy = Pharmacy::findOrFail($pharmacyId);
        $this->editingId = $pharmacy->id;
        $this->form = $pharmacy->only(array_keys($this->form));
        $this->radiusKm = 5;
        $this->hospitalId = $pharmacy->hospitals()->wherePivot('is_active', true)->orderByPivot('priority')->value('hospitals.id');
        $this->dispatch('open-pharmacy-modal');
    }

    public function resetForm(): void
    {
        $this->clearForm();
        $this->dispatch('open-pharmacy-modal');
    }

    public function save(PharmacyOnboardingService $onboarding): void
    {
        $data = $this->validate()['form'];
        $isUpdate = $this->editingId !== null;

        $pharmacy = Pharmacy::updateOrCreate(
            ['id' => $this->editingId],
            $data
        );

        $onboarding->syncPrimaryMapping($pharmacy, $this->hospitalId, $this->radiusKm);

        app(AuditLogger::class)->log(
            $isUpdate ? 'ADMIN_UPDATE_PHARMACY' : 'ADMIN_CREATE_PHARMACY',
            $data,
            $pharmacy
        );

        $this->clearForm();

        $this->dispatch('close-pharmacy-modal');
    }

    public function delete(string $pharmacyId): void
    {
        Pharmacy::whereKey($pharmacyId)->delete();
        app(AuditLogger::class)->log('ADMIN_DELETE_PHARMACY', ['pharmacy_id' => $pharmacyId]);
        $this->clearForm();
    }

    private function clearForm(): void
    {
        $this->editingId = null;
        $this->radiusKm = 5;
        $this->hospitalId = null;
        $this->form = [
            'name' => '',
            'email' => '',
            'phone' => '',
            'town' => '',
            'address' => '',
            'latitude' => null,
            'longitude' => null,
            'is_active' => true,
            'is_verified' => false,
            'is_open' => true,
        ];
    }

    public function render()
    {
        $hospitals = app(PharmacyOnboardingService::class)->findNearbyHospitals(
            $this->form['latitude'] !== null ? (float) $this->form['latitude'] : null,
            $this->form['longitude'] !== null ? (float) $this->form['longitude'] : null,
            $this->radiusKm
        );

        if ($hospitals->isEmpty()) {
            $hospitals = \App\Models\Hospital::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        if ($this->hospitalId === null && $hospitals->count() === 1) {
            $this->hospitalId = $hospitals->first()->id;
        }

        $pharmacies = Pharmacy::query()
            ->when($this->search, function ($query) {
                $needle = '%'.strtolower($this->search).'%';
                $query->where(function ($sub) use ($needle) {
                    $sub->whereRaw('LOWER(name) LIKE ?', [$needle])
                        ->orWhereRaw('LOWER(town) LIKE ?', [$needle])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$needle]);
                });
            })
            ->orderBy('name')
            ->get();

        return view('livewire.admin.pharmacies-page', [
            'pharmacies' => $pharmacies,
            'hospitals' => $hospitals,
        ])->layout('components.layouts.app.sidebar', ['title' => __('Pharmacies')]);
    }
}

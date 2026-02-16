<?php

namespace App\Livewire\Admin;

use App\Models\Hospital;
use App\Services\AuditLogger;
use Livewire\Component;

class HospitalsPage extends Component
{
    public string $search = '';
    public ?string $editingId = null;
    public array $form = [
        'name' => '',
        'type' => '',
        'town' => '',
        'address' => '',
        'latitude' => null,
        'longitude' => null,
        'is_active' => true,
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('manage_hospitals'), 403);
    }

    protected function rules(): array
    {
        return [
            'form.name' => ['required', 'string', 'max:255'],
            'form.type' => ['nullable', 'string', 'max:100'],
            'form.town' => ['nullable', 'string', 'max:150'],
            'form.address' => ['nullable', 'string', 'max:255'],
            'form.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'form.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'form.is_active' => ['boolean'],
        ];
    }

    public function updatingSearch(): void
    {
        // kept for backward compatibility; table search is server-side via DataTables.
    }

    public function edit(string $hospitalId): void
    {
        $hospital = Hospital::findOrFail($hospitalId);
        $this->editingId = $hospital->id;
        $this->form = $hospital->only(array_keys($this->form));
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->form = [
            'name' => '',
            'type' => '',
            'town' => '',
            'address' => '',
            'latitude' => null,
            'longitude' => null,
            'is_active' => true,
        ];
    }

    public function save(): void
    {
        $data = $this->validate()['form'];

        Hospital::updateOrCreate(
            ['id' => $this->editingId],
            $data,
        );

        app(AuditLogger::class)->log(
            $this->editingId ? 'ADMIN_UPDATE_HOSPITAL' : 'ADMIN_CREATE_HOSPITAL',
            $data,
            $this->editingId ? Hospital::find($this->editingId) : null
        );

        $this->resetForm();
    }

    public function delete(string $hospitalId): void
    {
        Hospital::whereKey($hospitalId)->delete();
        app(AuditLogger::class)->log('ADMIN_DELETE_HOSPITAL', ['hospital_id' => $hospitalId]);
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.admin.hospitals-page')
            ->layout('components.layouts.app.sidebar', ['title' => __('Hospitals')]);
    }
}

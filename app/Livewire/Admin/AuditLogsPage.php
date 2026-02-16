<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogsPage extends Component
{
    use WithPagination;

    public string $actionFilter = '';
    public string $actorFilter = '';
    public string $subjectTypeFilter = '';
    public ?string $from = null;
    public ?string $to = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('view_audit_logs'), 403);
    }

    public function updatingActionFilter(): void
    {
        $this->resetPage();
    }

    public function updatingActorFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSubjectTypeFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $actionNeedle = '%'.strtolower($this->actionFilter).'%';
        $actorNeedle = '%'.strtolower($this->actorFilter).'%';

        $logs = AuditLog::query()
            ->when($this->actionFilter, fn ($q) => $q->whereRaw('LOWER(action) LIKE ?', [$actionNeedle]))
            ->when($this->actorFilter, fn ($q) => $q->whereRaw('LOWER(actor_name) LIKE ?', [$actorNeedle]))
            ->when($this->subjectTypeFilter, fn ($q) => $q->where('subject_type', $this->subjectTypeFilter))
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.admin.audit-logs-page', [
            'logs' => $logs,
        ])->layout('components.layouts.app.sidebar', ['title' => __('Audit Logs')]);
    }
}

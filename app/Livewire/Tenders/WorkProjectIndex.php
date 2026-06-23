<?php

namespace App\Livewire\Tenders;

use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Control de Obra por Proyecto')]
class WorkProjectIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'activo';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $projects = Project::query()
            ->whereHas('branch', fn ($query) => $query->where('company_id', $companyId))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->search, function ($query) {
                $query->where(function ($inner) {
                    $inner->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('code', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->with(['customer', 'responsible'])
            ->withCount([
                'milestones',
                'employees as active_workers_count' => fn ($query) => $query->where('project_employees.is_active', true),
                'workPermits as active_permits_count' => fn ($query) => $query->where('status', 'activo'),
                'workReports',
                'workPhotoReports',
                'workIncidentReports',
            ])
            ->orderByRaw("CASE WHEN status = 'activo' THEN 0 WHEN status = 'pausado' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.tenders.work-project-index', [
            'projects' => $projects,
            'statuses' => [
                'activo' => 'Activos',
                'pausado' => 'Pausados',
                'borrador' => 'Borradores',
                'completado' => 'Completados',
                'cancelado' => 'Cancelados',
                '' => 'Todos',
            ],
        ]);
    }
}

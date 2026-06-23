<?php

namespace App\Livewire\Tenders;

use App\Models\Project;
use App\Models\Tender;
use App\Models\WorkIncidentReport;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Reportes de Incidencias')]
class WorkIncidentReportIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $filterProject = null;
    public ?int $contextProjectId = null;
    public bool $embedded = false;
    public bool $showModal = false;
    public ?int $editingId = null;

    public ?int $project_id = null;
    public ?int $tender_id = null;
    public string $incident_date = '';
    public string $title = '';
    public string $location = '';
    public string $description = '';
    public string $actions_taken = '';
    public string $responsible_name = '';
    public string $status = 'abierta';

    public array $statuses = [
        'abierta' => 'Abierta',
        'en_revision' => 'En revision',
        'cerrada' => 'Cerrada',
    ];

    public function mount(?Project $project = null, bool $embedded = false): void
    {
        $this->embedded = $embedded;
        $projectId = $project?->id ?: (request()->integer('project_id') ?: null);

        if ($projectId) {
            $companyId = auth()->user()->company_id;
            $exists = Project::whereKey($projectId)
                ->whereHas('branch', fn ($query) => $query->where('company_id', $companyId))
                ->exists();

            $this->filterProject = $exists ? $projectId : null;
            $this->contextProjectId = $project?->id && $exists ? $project->id : null;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openModal(?int $id = null): void
    {
        $this->resetForm();
        $this->incident_date = now()->format('Y-m-d');
        $this->project_id = $this->filterProject;

        if ($id) {
            $report = WorkIncidentReport::query()
                ->when($this->contextProjectId, fn ($query) => $query->where('project_id', $this->contextProjectId))
                ->findOrFail($id);

            $this->editingId = $id;
            $this->project_id = $report->project_id;
            $this->tender_id = $report->tender_id;
            $this->incident_date = $report->incident_date->format('Y-m-d');
            $this->title = $report->title;
            $this->location = $report->location ?? '';
            $this->description = $report->description;
            $this->actions_taken = $report->actions_taken ?? '';
            $this->responsible_name = $report->responsible_name ?? '';
            $this->status = $report->status;
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->contextProjectId) {
            $this->project_id = $this->contextProjectId;
        }

        $this->validate([
            'project_id' => 'required|exists:projects,id',
            'incident_date' => 'required|date',
            'title' => 'required|string|max:200',
            'location' => 'nullable|string|max:200',
            'description' => 'required|string',
            'actions_taken' => 'nullable|string',
            'responsible_name' => 'nullable|string|max:160',
            'status' => 'required|in:abierta,en_revision,cerrada',
        ]);

        $data = [
            'project_id' => $this->project_id,
            'tender_id' => $this->tender_id ?: null,
            'incident_date' => $this->incident_date,
            'title' => $this->title,
            'location' => $this->location ?: null,
            'description' => $this->description,
            'actions_taken' => $this->actions_taken ?: null,
            'responsible_name' => $this->responsible_name ?: null,
            'status' => $this->status,
            'created_by' => auth()->id(),
        ];

        if ($this->editingId) {
            WorkIncidentReport::query()
                ->when($this->contextProjectId, fn ($query) => $query->where('project_id', $this->contextProjectId))
                ->findOrFail($this->editingId)
                ->update($data);
        } else {
            WorkIncidentReport::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Reporte de incidencia guardado.');
    }

    public function delete(int $id): void
    {
        WorkIncidentReport::query()
            ->when($this->contextProjectId, fn ($query) => $query->where('project_id', $this->contextProjectId))
            ->findOrFail($id)
            ->delete();

        session()->flash('success', 'Reporte de incidencia eliminado.');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->project_id = null;
        $this->tender_id = null;
        $this->incident_date = '';
        $this->title = '';
        $this->location = '';
        $this->description = '';
        $this->actions_taken = '';
        $this->responsible_name = '';
        $this->status = 'abierta';
    }

    public function render()
    {
        if ($this->contextProjectId) {
            $this->filterProject = $this->contextProjectId;
        }

        $companyId = auth()->user()->company_id;
        $reports = WorkIncidentReport::whereHas('project', fn ($query) => $query->whereHas('branch', fn ($branch) => $branch->where('company_id', $companyId)))
            ->when($this->search, function ($query) {
                $query->where(function ($inner) {
                    $inner->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhere('location', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterProject, fn ($query) => $query->where('project_id', $this->filterProject))
            ->with(['project', 'tender', 'createdBy'])
            ->latest('incident_date')
            ->paginate(12, pageName: 'incidentReportsPage');

        return view('livewire.tenders.work-incident-report-index', [
            'reports' => $reports,
            'projects' => Project::whereHas('branch', fn ($query) => $query->where('company_id', $companyId))->orderBy('name')->get(),
            'tenders' => Tender::where('company_id', $companyId)->orderBy('name')->get(),
        ]);
    }
}

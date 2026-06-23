<?php

namespace App\Livewire\Tenders;

use App\Models\Project;
use App\Models\Tender;
use App\Models\WorkReport;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Reportes Semanales de Obra')]
class WorkReportIndex extends Component
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
    public string $week_start = '';
    public string $week_end = '';

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

    public function updatedSearch(): void { $this->resetPage(); }

    public function openModal(?int $id = null): void
    {
        $this->resetForm();
        $this->week_start = now()->startOfWeek()->format('Y-m-d');
        $this->week_end   = now()->endOfWeek()->format('Y-m-d');
        $this->project_id  = $this->filterProject;
        if ($id) {
            $r = WorkReport::query()
                ->when($this->contextProjectId, fn ($query) => $query->where('project_id', $this->contextProjectId))
                ->findOrFail($id);
            $this->editingId          = $id;
            $this->project_id         = $r->project_id;
            $this->tender_id          = $r->tender_id;
            $this->week_start         = $r->week_start->format('Y-m-d');
            $this->week_end           = $r->week_end->format('Y-m-d');
        }
        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->contextProjectId) {
            $this->project_id = $this->contextProjectId;
        }

        $this->validate([
            'project_id'   => 'required|exists:projects,id',
            'week_start'   => 'required|date',
            'week_end'     => 'required|date|after_or_equal:week_start',
        ]);

        $data = [
            'project_id'         => $this->project_id,
            'tender_id'          => $this->tender_id ?: null,
            'week_start'         => $this->week_start,
            'week_end'           => $this->week_end,
            'progress_pct'       => 0,
            'created_by'         => auth()->id(),
        ];

        if ($this->editingId) {
            WorkReport::query()
                ->when($this->contextProjectId, fn ($query) => $query->where('project_id', $this->contextProjectId))
                ->findOrFail($this->editingId)
                ->update($data);
        } else {
            WorkReport::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Reporte guardado.');
    }

    public function delete(int $id): void
    {
        WorkReport::query()
            ->when($this->contextProjectId, fn ($query) => $query->where('project_id', $this->contextProjectId))
            ->findOrFail($id)
            ->delete();
        session()->flash('success', 'Reporte eliminado.');
    }

    private function resetForm(): void
    {
        $this->editingId = null; $this->project_id = null; $this->tender_id = null;
        $this->week_start = ''; $this->week_end = '';
    }

    public function render()
    {
        if ($this->contextProjectId) {
            $this->filterProject = $this->contextProjectId;
        }

        $companyId = auth()->user()->company_id;
        $reports = WorkReport::whereHas('project', fn($q) => $q->whereHas('branch', fn($bq) => $bq->where('company_id', $companyId)))
            ->when($this->search, fn($q) => $q->whereHas('project', fn($q2) => $q2->where('name', 'like', '%' . $this->search . '%')))
            ->when($this->filterProject, fn($q) => $q->where('project_id', $this->filterProject))
            ->with(['project', 'tender', 'createdBy'])
            ->latest('week_start')
            ->paginate(15, pageName: 'weeklyReportsPage');

        return view('livewire.tenders.work-report-index', [
            'reports'  => $reports,
            'projects' => Project::whereHas('branch', fn($q) => $q->where('company_id', $companyId))->orderBy('name')->get(),
            'tenders'  => Tender::where('company_id', $companyId)->orderBy('name')->get(),
        ]);
    }
}

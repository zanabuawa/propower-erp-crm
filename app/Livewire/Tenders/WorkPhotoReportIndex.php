<?php

namespace App\Livewire\Tenders;

use App\Models\Project;
use App\Models\Tender;
use App\Models\WorkPhotoReport;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Reportes Fotográficos')]
class WorkPhotoReportIndex extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public ?int $filterProject = null;
    public ?int $contextProjectId = null;
    public bool $embedded = false;
    public bool $showModal = false;
    public ?int $editingId = null;
    public ?WorkPhotoReport $viewingReport = null;

    public ?int $project_id = null;
    public ?int $tender_id = null;
    public string $report_date = '';
    public string $week_start = '';
    public string $week_end = '';
    public string $title = '';
    public string $description = '';
    public string $location = '';
    public array $newPhotos = [];

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
        $today = now();
        $this->report_date = $today->format('Y-m-d');
        $this->week_start = $today->copy()->startOfWeek()->format('Y-m-d');
        $this->week_end = $today->copy()->endOfWeek()->format('Y-m-d');
        $this->project_id = $this->filterProject;
        if ($id) {
            $r = WorkPhotoReport::query()
                ->when($this->contextProjectId, fn ($query) => $query->where('project_id', $this->contextProjectId))
                ->findOrFail($id);
            $this->editingId   = $id;
            $this->project_id  = $r->project_id;
            $this->tender_id   = $r->tender_id;
            $this->report_date = $r->report_date->format('Y-m-d');
            $this->week_start  = ($r->week_start ?: $r->report_date?->copy()->startOfWeek())->format('Y-m-d');
            $this->week_end    = ($r->week_end ?: $r->report_date?->copy()->endOfWeek())->format('Y-m-d');
            $this->title       = $r->title;
            $this->description = $r->description ?? '';
            $this->location    = $r->location ?? '';
        }
        $this->showModal = true;
    }

    public function viewReport(int $id): void
    {
        $this->viewingReport = WorkPhotoReport::query()
            ->when($this->contextProjectId, fn ($query) => $query->where('project_id', $this->contextProjectId))
            ->findOrFail($id);
    }

    public function save(): void
    {
        if ($this->contextProjectId) {
            $this->project_id = $this->contextProjectId;
        }

        $this->validate([
            'project_id'  => 'required|exists:projects,id',
            'report_date' => 'required|date',
            'week_start'  => 'required|date',
            'week_end'    => 'required|date|after_or_equal:week_start',
            'title'       => 'required|string|max:200',
            'newPhotos'   => 'nullable|array',
            'newPhotos.*' => 'image|max:5120',
        ]);

        $existingPhotos = [];
        if ($this->editingId) {
            $existing = WorkPhotoReport::query()
                ->when($this->contextProjectId, fn ($query) => $query->where('project_id', $this->contextProjectId))
                ->find($this->editingId);
            $existingPhotos = $existing?->photos ?? [];
        }

        $photoPaths = $existingPhotos;
        foreach ($this->newPhotos as $photo) {
            $photoPaths[] = $photo->store('works/photos', 'public');
        }

        $data = [
            'project_id'  => $this->project_id,
            'tender_id'   => $this->tender_id ?: null,
            'report_date' => $this->report_date,
            'week_start'  => $this->week_start,
            'week_end'    => $this->week_end,
            'title'       => $this->title,
            'description' => $this->description ?: null,
            'location'    => $this->location ?: null,
            'photos'      => $photoPaths,
            'created_by'  => auth()->id(),
        ];

        if ($this->editingId) {
            WorkPhotoReport::query()
                ->when($this->contextProjectId, fn ($query) => $query->where('project_id', $this->contextProjectId))
                ->findOrFail($this->editingId)
                ->update($data);
        } else {
            WorkPhotoReport::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Reporte fotográfico guardado.');
    }

    public function delete(int $id): void
    {
        WorkPhotoReport::query()
            ->when($this->contextProjectId, fn ($query) => $query->where('project_id', $this->contextProjectId))
            ->findOrFail($id)
            ->delete();
        session()->flash('success', 'Reporte eliminado.');
    }

    private function resetForm(): void
    {
        $this->editingId = null; $this->project_id = null; $this->tender_id = null;
        $this->report_date = ''; $this->week_start = ''; $this->week_end = '';
        $this->title = ''; $this->description = '';
        $this->location = ''; $this->newPhotos = [];
    }

    public function render()
    {
        if ($this->contextProjectId) {
            $this->filterProject = $this->contextProjectId;
        }

        $companyId = auth()->user()->company_id;
        $reports = WorkPhotoReport::whereHas('project', fn($q) => $q->whereHas('branch', fn($bq) => $bq->where('company_id', $companyId)))
            ->when($this->search, fn($q) => $q->where('title', 'like', '%' . $this->search . '%'))
            ->when($this->filterProject, fn($q) => $q->where('project_id', $this->filterProject))
            ->with(['project', 'tender', 'createdBy'])
            ->latest('report_date')
            ->paginate(12, pageName: 'photoReportsPage');

        return view('livewire.tenders.work-photo-report-index', [
            'reports'  => $reports,
            'projects' => Project::whereHas('branch', fn($q) => $q->where('company_id', $companyId))->orderBy('name')->get(),
            'tenders'  => Tender::where('company_id', $companyId)->orderBy('name')->get(),
        ]);
    }
}

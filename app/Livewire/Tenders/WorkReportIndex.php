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
    public bool $showModal = false;
    public ?int $editingId = null;

    public ?int $project_id = null;
    public ?int $tender_id = null;
    public string $week_start = '';
    public string $week_end = '';
    public int $progress_pct = 0;
    public string $activities = '';
    public string $issues = '';
    public string $next_week_plan = '';
    public string $weather_conditions = '';
    public int $workers_count = 0;

    public function updatedSearch(): void { $this->resetPage(); }

    public function openModal(?int $id = null): void
    {
        $this->resetForm();
        $this->week_start = now()->startOfWeek()->format('Y-m-d');
        $this->week_end   = now()->endOfWeek()->format('Y-m-d');
        if ($id) {
            $r = WorkReport::findOrFail($id);
            $this->editingId          = $id;
            $this->project_id         = $r->project_id;
            $this->tender_id          = $r->tender_id;
            $this->week_start         = $r->week_start->format('Y-m-d');
            $this->week_end           = $r->week_end->format('Y-m-d');
            $this->progress_pct       = $r->progress_pct;
            $this->activities         = $r->activities ?? '';
            $this->issues             = $r->issues ?? '';
            $this->next_week_plan     = $r->next_week_plan ?? '';
            $this->weather_conditions = $r->weather_conditions ?? '';
            $this->workers_count      = $r->workers_count;
        }
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'project_id'   => 'required|exists:projects,id',
            'week_start'   => 'required|date',
            'week_end'     => 'required|date|after_or_equal:week_start',
            'progress_pct' => 'required|integer|min:0|max:100',
            'activities'   => 'nullable|string',
        ]);

        $data = [
            'project_id'         => $this->project_id,
            'tender_id'          => $this->tender_id ?: null,
            'week_start'         => $this->week_start,
            'week_end'           => $this->week_end,
            'progress_pct'       => $this->progress_pct,
            'activities'         => $this->activities ?: null,
            'issues'             => $this->issues ?: null,
            'next_week_plan'     => $this->next_week_plan ?: null,
            'weather_conditions' => $this->weather_conditions ?: null,
            'workers_count'      => $this->workers_count,
            'created_by'         => auth()->id(),
        ];

        if ($this->editingId) {
            WorkReport::findOrFail($this->editingId)->update($data);
        } else {
            WorkReport::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Reporte guardado.');
    }

    public function delete(int $id): void
    {
        WorkReport::findOrFail($id)->delete();
        session()->flash('success', 'Reporte eliminado.');
    }

    private function resetForm(): void
    {
        $this->editingId = null; $this->project_id = null; $this->tender_id = null;
        $this->week_start = ''; $this->week_end = ''; $this->progress_pct = 0;
        $this->activities = ''; $this->issues = ''; $this->next_week_plan = '';
        $this->weather_conditions = ''; $this->workers_count = 0;
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;
        $reports = WorkReport::whereHas('project', fn($q) => $q->where('company_id', $companyId))
            ->when($this->search, fn($q) => $q->whereHas('project', fn($q2) => $q2->where('name', 'like', '%' . $this->search . '%')))
            ->with(['project', 'tender', 'createdBy'])
            ->latest('week_start')
            ->paginate(15);

        return view('livewire.tenders.work-report-index', [
            'reports'  => $reports,
            'projects' => Project::where('company_id', $companyId)->orderBy('name')->get(),
            'tenders'  => Tender::where('company_id', $companyId)->orderBy('name')->get(),
        ]);
    }
}

<?php

namespace App\Livewire\Tenders;

use App\Models\Project;
use App\Models\WorkIncidentReport;
use App\Models\WorkPermit;
use App\Models\WorkPhotoReport;
use App\Models\WorkProgram;
use App\Models\WorkReport;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Control de Obra')]
class ProjectWorkControl extends Component
{
    public Project $project;
    public string $tab = 'overview';

    protected array $queryString = [
        'tab' => ['except' => 'overview'],
    ];

    public function mount(Project $project): void
    {
        $companyId = auth()->user()->company_id;

        $project->load('branch');
        abort_if($project->branch && $project->branch->company_id !== $companyId, 404);

        $this->project = $project;
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, $this->tabs(), true)) {
            $this->tab = $tab;
        }
    }

    private function tabs(): array
    {
        return ['overview', 'permits', 'program', 'weekly', 'photos', 'logbook'];
    }

    public function render()
    {
        if (! in_array($this->tab, $this->tabs(), true)) {
            $this->tab = 'overview';
        }

        $this->project->load([
            'customer',
            'responsible',
            'milestones',
            'employees' => fn ($query) => $query->withPivot('role', 'start_date', 'end_date', 'hours_assigned', 'is_active'),
        ]);

        $milestones = $this->project->milestones;
        $completedMilestones = $milestones->where('status', 'completado')->count();
        $milestoneProgress = $milestones->count() > 0
            ? (int) round(($completedMilestones / $milestones->count()) * 100)
            : (int) ($this->project->progress ?? 0);

        $program = WorkProgram::where('project_id', $this->project->id)
            ->where('status', 'vigente')
            ->with('allActivities')
            ->first();

        $programActivities = $program?->allActivities ?? collect();
        $programProgress = $programActivities->count() > 0
            ? (int) round($programActivities->avg('progress_pct'))
            : 0;

        $permits = WorkPermit::where('project_id', $this->project->id)
            ->with('issuedBy')
            ->latest()
            ->take(8)
            ->get();
        $activePermitsCount = WorkPermit::where('project_id', $this->project->id)
            ->where('status', 'activo')
            ->count();

        $weeklyReports = WorkReport::where('project_id', $this->project->id)
            ->with('createdBy')
            ->latest('week_start')
            ->take(8)
            ->get();
        $weeklyReportsCount = WorkReport::where('project_id', $this->project->id)->count();

        $photoReports = WorkPhotoReport::where('project_id', $this->project->id)
            ->with('createdBy')
            ->latest('report_date')
            ->take(8)
            ->get();
        $photoReportsCount = WorkPhotoReport::where('project_id', $this->project->id)->count();
        $incidentReportsCount = WorkIncidentReport::where('project_id', $this->project->id)->count();

        return view('livewire.tenders.project-work-control', [
            'activePermitsCount' => $activePermitsCount,
            'completedMilestones' => $completedMilestones,
            'milestoneProgress' => $milestoneProgress,
            'milestones' => $milestones,
            'permits' => $permits,
            'photoReports' => $photoReports,
            'program' => $program,
            'programActivities' => $programActivities,
            'programProgress' => $programProgress,
            'weeklyReports' => $weeklyReports,
            'weeklyReportsCount' => $weeklyReportsCount,
            'photoReportsCount' => $photoReportsCount,
            'incidentReportsCount' => $incidentReportsCount,
        ]);
    }
}

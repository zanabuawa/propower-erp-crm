<?php

namespace App\Livewire\Tenders;

use App\Models\Project;
use App\Models\WorkProgram;
use App\Models\WorkProgramActivity;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Programa de Obra')]
class WorkProgramIndex extends Component
{
    public Project $project;
    public ?WorkProgram $program = null;
    public bool $showActivityModal = false;
    public ?int $editingActivityId = null;
    public ?int $parentActivityId = null;

    public string $actName = '';
    public string $actUnit = '';
    public string $actQuantity = '';
    public string $actStartDate = '';
    public string $actEndDate = '';
    public int $actProgress = 0;

    public function mount(Project $project): void
    {
        $this->project = $project;
        $this->program = WorkProgram::where('project_id', $project->id)
            ->where('status', 'vigente')
            ->with('allActivities')
            ->first();
    }

    public function createProgram(): void
    {
        // Mark previous as historical
        WorkProgram::where('project_id', $this->project->id)->where('status', 'vigente')->update(['status' => 'historico']);
        $version = WorkProgram::where('project_id', $this->project->id)->max('version') + 1;
        $this->program = WorkProgram::create([
            'project_id' => $this->project->id,
            'name'       => 'Programa v' . $version,
            'version'    => $version,
            'status'     => 'vigente',
            'created_by' => auth()->id(),
        ]);
        session()->flash('success', 'Programa de obra creado.');
    }

    public function openActivityModal(?int $id = null, ?int $parentId = null): void
    {
        $this->resetActivityForm();
        $this->parentActivityId = $parentId;
        if ($id) {
            $a = WorkProgramActivity::findOrFail($id);
            $this->editingActivityId = $id;
            $this->actName      = $a->name;
            $this->actUnit      = $a->unit ?? '';
            $this->actQuantity  = (string) ($a->quantity ?? '');
            $this->actStartDate = $a->start_date?->format('Y-m-d') ?? '';
            $this->actEndDate   = $a->end_date?->format('Y-m-d') ?? '';
            $this->actProgress  = $a->progress_pct;
        }
        $this->showActivityModal = true;
    }

    public function saveActivity(): void
    {
        $this->validate([
            'actName'      => 'required|string|max:200',
            'actStartDate' => 'nullable|date',
            'actEndDate'   => 'nullable|date',
            'actProgress'  => 'integer|min:0|max:100',
        ]);

        $data = [
            'program_id'   => $this->program->id,
            'parent_id'    => $this->parentActivityId,
            'name'         => $this->actName,
            'unit'         => $this->actUnit ?: null,
            'quantity'     => $this->actQuantity ?: null,
            'start_date'   => $this->actStartDate ?: null,
            'end_date'     => $this->actEndDate ?: null,
            'progress_pct' => $this->actProgress,
        ];

        if ($this->editingActivityId) {
            WorkProgramActivity::findOrFail($this->editingActivityId)->update($data);
        } else {
            $lastOrder = WorkProgramActivity::where('program_id', $this->program->id)->max('sort_order') ?? 0;
            $data['sort_order'] = $lastOrder + 1;
            WorkProgramActivity::create($data);
        }

        $this->showActivityModal = false;
        $this->resetActivityForm();
        $this->program->load('allActivities');
        session()->flash('success', 'Actividad guardada.');
    }

    public function deleteActivity(int $id): void
    {
        WorkProgramActivity::findOrFail($id)->delete();
        $this->program->load('allActivities');
        session()->flash('success', 'Actividad eliminada.');
    }

    public function updateProgress(int $id, int $pct): void
    {
        WorkProgramActivity::findOrFail($id)->update(['progress_pct' => max(0, min(100, $pct))]);
        $this->program->load('allActivities');
    }

    private function resetActivityForm(): void
    {
        $this->editingActivityId = null; $this->parentActivityId = null;
        $this->actName = ''; $this->actUnit = ''; $this->actQuantity = '';
        $this->actStartDate = ''; $this->actEndDate = ''; $this->actProgress = 0;
    }

    public function render()
    {
        return view('livewire.tenders.work-program-index', [
            'project' => $this->project,
            'program' => $this->program,
        ]);
    }
}

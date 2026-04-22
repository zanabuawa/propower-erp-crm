<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectMilestone;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ProjectGantt extends Component
{
    public Project $project;
    public string $editTaskId = '';
    public bool $showEditModal = false;

    // Task edit fields
    public string $editTitle = '';
    public string $editStartDate = '';
    public string $editDueDate = '';
    public string $editStatus = '';
    public string $editPriority = '';
    public ?int $editAssigned = null;
    public string $editEstimatedHours = '';

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    public function openEdit(int $taskId): void
    {
        $task = ProjectTask::findOrFail($taskId);
        $this->editTaskId        = $taskId;
        $this->editTitle         = $task->title;
        $this->editStartDate     = $task->start_date?->format('Y-m-d') ?? '';
        $this->editDueDate       = $task->due_date?->format('Y-m-d') ?? '';
        $this->editStatus        = $task->status;
        $this->editPriority      = $task->priority;
        $this->editAssigned      = $task->assigned_to;
        $this->editEstimatedHours = $task->estimated_hours ?? '';
        $this->showEditModal     = true;
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editTitle'    => 'required|string|max:255',
            'editStartDate'=> 'nullable|date',
            'editDueDate'  => 'nullable|date',
            'editStatus'   => 'required|in:pendiente,en_progreso,revision,completada,cancelada',
            'editPriority' => 'required|in:baja,media,alta,urgente',
            'editEstimatedHours' => 'nullable|numeric|min:0',
        ]);

        ProjectTask::findOrFail($this->editTaskId)->update([
            'title'           => $this->editTitle,
            'start_date'      => $this->editStartDate ?: null,
            'due_date'        => $this->editDueDate ?: null,
            'status'          => $this->editStatus,
            'priority'        => $this->editPriority,
            'assigned_to'     => $this->editAssigned,
            'estimated_hours' => $this->editEstimatedHours ?: null,
        ]);

        $this->showEditModal = false;
    }

    public function render()
    {
        $this->project->load(['tasks.assignedTo', 'milestones', 'responsible']);

        $tasks      = $this->project->tasks()->whereNull('parent_task_id')->with('assignedTo')->orderBy('sort_order')->get();
        $milestones = $this->project->milestones;

        // Determine timeline range
        $allDates = collect();
        if ($this->project->start_date) $allDates->push($this->project->start_date);
        if ($this->project->end_date)   $allDates->push($this->project->end_date);

        foreach ($tasks as $task) {
            if ($task->start_date) $allDates->push($task->start_date);
            if ($task->due_date)   $allDates->push($task->due_date);
        }
        foreach ($milestones as $ms) {
            $allDates->push($ms->due_date);
        }

        $timelineStart = $allDates->min() ? Carbon::parse($allDates->min())->startOfMonth() : now()->startOfMonth();
        $timelineEnd   = $allDates->max() ? Carbon::parse($allDates->max())->endOfMonth()   : now()->addMonths(3)->endOfMonth();

        // Build months for header
        $months = [];
        $cursor = $timelineStart->copy()->startOfMonth();
        while ($cursor->lte($timelineEnd)) {
            $months[] = [
                'label' => $cursor->translatedFormat('M Y'),
                'days'  => $cursor->daysInMonth,
                'start' => $cursor->copy(),
            ];
            $cursor->addMonth();
        }

        $totalDays = $timelineStart->diffInDays($timelineEnd) + 1;

        // Helper: position/width as percentage
        $pct = function ($date, $width = false) use ($timelineStart, $totalDays) {
            if (!$date) return null;
            $d = Carbon::parse($date);
            if ($width) {
                return max(1, round(($d->diffInDays(Carbon::parse($width)) + 1) / $totalDays * 100, 2));
            }
            return max(0, round($d->diffInDays($timelineStart) / $totalDays * 100, 2));
        };

        // Build task rows with positioning
        $taskRows = $tasks->map(function ($task) use ($pct, $timelineStart, $timelineEnd) {
            $start = $task->start_date ?? $task->due_date ?? null;
            $end   = $task->due_date ?? $task->start_date ?? null;

            return [
                'task'   => $task,
                'left'   => $start ? $pct($start) : null,
                'width'  => ($start && $end) ? $pct($start, $end) : ($start ? 1 : null),
            ];
        });

        $milestoneRows = $milestones->map(fn($ms) => [
            'milestone' => $ms,
            'left'      => $pct($ms->due_date),
        ]);

        $users = \App\Models\User::where('company_id', auth()->user()->company_id)->orderBy('name')->get(['id', 'name']);

        return view('livewire.projects.project-gantt', compact(
            'tasks', 'milestones', 'months', 'taskRows', 'milestoneRows',
            'timelineStart', 'timelineEnd', 'totalDays', 'users'
        ));
    }
}

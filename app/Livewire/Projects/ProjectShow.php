<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ProjectShow extends Component
{
    public Project $project;

    // Nueva tarea rápida
    public string $newTaskTitle = '';
    public ?int $newTaskAssigned = null;
    public string $newTaskDue = '';
    public bool $showTaskForm = false;

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    public function addTask(): void
    {
        $this->validate([
            'newTaskTitle'    => 'required|string|max:255',
            'newTaskAssigned' => 'nullable|exists:users,id',
            'newTaskDue'      => 'nullable|date',
        ]);

        $this->project->tasks()->create([
            'title'       => $this->newTaskTitle,
            'assigned_to' => $this->newTaskAssigned,
            'due_date'    => $this->newTaskDue ?: null,
            'status'      => 'pendiente',
            'priority'    => 'media',
            'sort_order'  => $this->project->tasks()->max('sort_order') + 1,
        ]);

        $this->reset('newTaskTitle', 'newTaskAssigned', 'newTaskDue', 'showTaskForm');
        session()->flash('success', 'Tarea agregada.');
    }

    public function toggleTaskStatus(int $taskId): void
    {
        $task = ProjectTask::findOrFail($taskId);
        $task->update([
            'status'       => $task->status === 'completada' ? 'pendiente' : 'completada',
            'completed_at' => $task->status === 'completada' ? null : now(),
        ]);

        // Recalcular progreso del proyecto
        $total     = $this->project->tasks()->count();
        $completed = $this->project->tasks()->where('status', 'completada')->count();
        $this->project->update(['progress' => $total > 0 ? round(($completed / $total) * 100) : 0]);

        $this->project->refresh();
    }

    public function deleteTask(int $taskId): void
    {
        ProjectTask::findOrFail($taskId)->delete();
        $this->project->refresh();
    }

    public function render()
    {
        $this->project->load(['customer', 'branch', 'responsible', 'tasks.assignedTo', 'milestones', 'expenses']);
        $users = User::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        return view('livewire.projects.project-show', compact('users'));
    }
}

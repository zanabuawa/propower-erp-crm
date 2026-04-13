<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ProjectTaskBoard extends Component
{
    public Project $project;

    public bool $showForm = false;
    public ?int $editingId = null;
    public string $title = '';
    public string $priority = 'media';
    public ?int $assigned_to = null;
    public string $due_date = '';
    public string $newStatus = 'pendiente';

    public static array $columns = [
        'pendiente'   => 'Pendiente',
        'en_progreso' => 'En progreso',
        'revision'    => 'En revisión',
        'completada'  => 'Completada',
    ];

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    public function openForm(string $status = 'pendiente'): void
    {
        $this->reset('editingId', 'title', 'priority', 'assigned_to', 'due_date');
        $this->newStatus = $status;
        $this->showForm  = true;
    }

    public function editTask(int $id): void
    {
        $task = ProjectTask::findOrFail($id);
        $this->editingId   = $id;
        $this->title       = $task->title;
        $this->priority    = $task->priority;
        $this->assigned_to = $task->assigned_to;
        $this->due_date    = $task->due_date?->format('Y-m-d') ?? '';
        $this->newStatus   = $task->status;
        $this->showForm    = true;
    }

    public function save(): void
    {
        $this->validate([
            'title'       => 'required|string|max:255',
            'priority'    => 'required|in:baja,media,alta,urgente',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date'    => 'nullable|date',
            'newStatus'   => 'required|in:pendiente,en_progreso,revision,completada,cancelada',
        ]);

        $data = [
            'title'        => $this->title,
            'priority'     => $this->priority,
            'assigned_to'  => $this->assigned_to,
            'due_date'     => $this->due_date ?: null,
            'status'       => $this->newStatus,
            'completed_at' => $this->newStatus === 'completada' ? now() : null,
        ];

        if ($this->editingId) {
            ProjectTask::findOrFail($this->editingId)->update($data);
        } else {
            $data['project_id']  = $this->project->id;
            $data['sort_order']  = ProjectTask::where('project_id', $this->project->id)->max('sort_order') + 1;
            ProjectTask::create($data);
        }

        $this->recalculateProgress();
        $this->reset('showForm', 'editingId', 'title', 'priority', 'assigned_to', 'due_date');
    }

    public function moveTask(int $id, string $status): void
    {
        ProjectTask::findOrFail($id)->update([
            'status'       => $status,
            'completed_at' => $status === 'completada' ? now() : null,
        ]);
        $this->recalculateProgress();
    }

    public function deleteTask(int $id): void
    {
        ProjectTask::findOrFail($id)->delete();
        $this->recalculateProgress();
    }

    private function recalculateProgress(): void
    {
        $total     = $this->project->tasks()->count();
        $completed = $this->project->tasks()->where('status', 'completada')->count();
        $this->project->update(['progress' => $total > 0 ? round(($completed / $total) * 100) : 0]);
        $this->project->refresh();
    }

    public function render()
    {
        $tasks = ProjectTask::with('assignedTo')
            ->where('project_id', $this->project->id)
            ->whereNull('parent_task_id')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('status');

        $users = User::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        return view('livewire.projects.project-task-board', compact('tasks', 'users'));
    }
}

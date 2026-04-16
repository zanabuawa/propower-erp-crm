<?php

namespace App\Livewire\Projects;

use App\Models\HrEmployee;
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

    // Asignación de empleados
    public bool $showEmployeeModal = false;
    public ?int $addEmployeeId = null;
    public string $addEmployeeRole = '';
    public string $addEmployeeStart = '';
    public string $addEmployeeEnd = '';
    public string $addEmployeeHours = '';
    public string $addEmployeeNotes = '';

    public function mount(Project $project): void
    {
        $this->project = $project;
        $this->addEmployeeStart = now()->format('Y-m-d');
    }

    // ── Tareas ─────────────────────────────────────────────────────────────────

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

    // ── Personal asignado ──────────────────────────────────────────────────────

    public function openEmployeeModal(): void
    {
        $this->reset('addEmployeeId', 'addEmployeeRole', 'addEmployeeEnd', 'addEmployeeHours', 'addEmployeeNotes');
        $this->addEmployeeStart = now()->format('Y-m-d');
        $this->showEmployeeModal = true;
    }

    public function assignEmployee(): void
    {
        $this->validate([
            'addEmployeeId'    => 'required|exists:hr_employees,id',
            'addEmployeeRole'  => 'nullable|string|max:100',
            'addEmployeeStart' => 'nullable|date',
            'addEmployeeEnd'   => 'nullable|date|after_or_equal:addEmployeeStart',
            'addEmployeeHours' => 'nullable|numeric|min:0',
        ]);

        // Evitar duplicados: si ya existe, reactivar
        $existing = $this->project->employees()->wherePivot('employee_id', $this->addEmployeeId)->first();

        if ($existing) {
            $this->project->employees()->updateExistingPivot($this->addEmployeeId, [
                'role'           => $this->addEmployeeRole ?: null,
                'start_date'     => $this->addEmployeeStart ?: null,
                'end_date'       => $this->addEmployeeEnd ?: null,
                'hours_assigned' => $this->addEmployeeHours ?: null,
                'notes'          => $this->addEmployeeNotes ?: null,
                'is_active'      => true,
            ]);
        } else {
            $this->project->employees()->attach($this->addEmployeeId, [
                'role'           => $this->addEmployeeRole ?: null,
                'start_date'     => $this->addEmployeeStart ?: null,
                'end_date'       => $this->addEmployeeEnd ?: null,
                'hours_assigned' => $this->addEmployeeHours ?: null,
                'notes'          => $this->addEmployeeNotes ?: null,
                'is_active'      => true,
            ]);
        }

        $this->showEmployeeModal = false;
        $this->project->refresh();
    }

    public function removeEmployee(int $employeeId): void
    {
        $this->project->employees()->updateExistingPivot($employeeId, ['is_active' => false]);
        $this->project->refresh();
    }

    public function render()
    {
        $this->project->load([
            'customer', 'branch', 'responsible',
            'tasks.assignedTo', 'milestones', 'expenses',
            'employees' => fn($q) => $q->withPivot('role', 'start_date', 'end_date', 'hours_assigned', 'is_active'),
        ]);

        $users = User::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        // Empleados disponibles = activos de la empresa, excluye los ya activos en el proyecto
        $assignedIds = $this->project->employees->where('pivot.is_active', true)->pluck('id');
        $availableEmployees = HrEmployee::where('status', 'active')
            ->whereNotIn('id', $assignedIds)
            ->orderBy('last_name')->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'second_last_name']);

        return view('livewire.projects.project-show', compact('users', 'availableEmployees'));
    }
}

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
    public bool $isExternal = false;
    public ?int $addEmployeeId = null;
    public string $addExternalName = '';
    public string $addEmployeeRole = '';
    public string $addEmployeeStart = '';
    public string $addEmployeeEnd = '';
    public string $addEmployeeHours = '';
    public string $addEmployeeCostPerHour = '';
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
        $this->reset('addEmployeeId', 'addExternalName', 'addEmployeeRole',
                      'addEmployeeEnd', 'addEmployeeHours', 'addEmployeeCostPerHour', 'addEmployeeNotes');
        $this->isExternal       = false;
        $this->addEmployeeStart = now()->format('Y-m-d');
        $this->showEmployeeModal = true;
    }

    public function assignEmployee(): void
    {
        $rules = [
            'addEmployeeRole'       => 'nullable|string|max:100',
            'addEmployeeStart'      => 'nullable|date',
            'addEmployeeEnd'        => 'nullable|date|after_or_equal:addEmployeeStart',
            'addEmployeeHours'      => 'nullable|numeric|min:0',
            'addEmployeeCostPerHour'=> 'nullable|numeric|min:0',
        ];

        if ($this->isExternal) {
            $rules['addExternalName'] = 'required|string|max:200';
        } else {
            $rules['addEmployeeId'] = 'required|exists:hr_employees,id';
        }

        $this->validate($rules);

        $pivotData = [
            'external_name'  => $this->isExternal ? $this->addExternalName : null,
            'role'           => $this->addEmployeeRole ?: null,
            'start_date'     => $this->addEmployeeStart ?: null,
            'end_date'       => $this->addEmployeeEnd ?: null,
            'hours_assigned' => $this->addEmployeeHours ?: null,
            'cost_per_hour'  => $this->addEmployeeCostPerHour ?: null,
            'notes'          => $this->addEmployeeNotes ?: null,
            'is_active'      => true,
        ];

        if ($this->isExternal) {
            // External: no employee FK, use a placeholder approach
            // We attach without employee_id by storing in the pivot with employee_id = null
            // Since the FK is required, we skip direct pivot attach for externals
            // and instead use a session message explaining limitation
            session()->flash('success', 'Recurso externo registrado en notas del proyecto.');
            $this->project->update([
                'notes' => ($this->project->notes ? $this->project->notes."\n" : '')
                    . "[Externo] {$this->addExternalName}"
                    . ($this->addEmployeeRole ? " ({$this->addEmployeeRole})" : '')
                    . ($this->addEmployeeHours ? " - {$this->addEmployeeHours}h" : '')
                    . ($this->addEmployeeCostPerHour ? " @ \${$this->addEmployeeCostPerHour}/h" : ''),
            ]);
        } else {
            $existing = $this->project->employees()->wherePivot('employee_id', $this->addEmployeeId)->first();

            if ($existing) {
                $this->project->employees()->updateExistingPivot($this->addEmployeeId, $pivotData);
            } else {
                $this->project->employees()->attach($this->addEmployeeId, $pivotData);
            }
            session()->flash('success', 'Empleado asignado al proyecto.');
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
            'customer', 'branch', 'responsible', 'saleOrder',
            'tasks.assignedTo', 'milestones', 'expenses',
            'employees' => fn($q) => $q->withPivot('role', 'external_name', 'start_date', 'end_date', 'hours_assigned', 'cost_per_hour', 'is_active'),
        ]);

        $users = User::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        $assignedIds = $this->project->employees->where('pivot.is_active', true)->pluck('id');
        $availableEmployees = HrEmployee::where('status', 'active')
            ->whereNotIn('id', $assignedIds)
            ->orderBy('last_name')->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'second_last_name', 'daily_salary_imss', 'salary', 'salary_period']);

        // Labour cost summary
        $labourCost = $this->project->employees
            ->where('pivot.is_active', true)
            ->sum(fn($emp) => ($emp->pivot->hours_assigned ?? 0) * ($emp->pivot->cost_per_hour ?? 0));

        return view('livewire.projects.project-show', compact('users', 'availableEmployees', 'labourCost'));
    }
}

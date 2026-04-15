<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\ProjectMilestone;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ProjectMilestones extends Component
{
    public Project $project;

    public bool $showForm  = false;
    public ?int $editingId = null;

    public string $name           = '';
    public string $description    = '';
    public string $due_date       = '';
    public string $status         = 'pendiente';
    public string $payment_amount = '0';
    public string $sort_order     = '0';

    public function mount(Project $project): void
    {
        $this->project    = $project;
        $this->sort_order = (string) ($project->milestones()->max('sort_order') + 1);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->sort_order = (string) ($this->project->milestones()->max('sort_order') + 1);
        $this->showForm   = true;
    }

    public function edit(int $id): void
    {
        $milestone = ProjectMilestone::findOrFail($id);

        $this->editingId      = $id;
        $this->name           = $milestone->name;
        $this->description    = $milestone->description ?? '';
        $this->due_date       = $milestone->due_date?->format('Y-m-d') ?? '';
        $this->status         = $milestone->status;
        $this->payment_amount = (string) $milestone->payment_amount;
        $this->sort_order     = (string) $milestone->sort_order;
        $this->showForm       = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string|max:1000',
            'due_date'       => 'nullable|date',
            'status'         => 'required|in:pendiente,en_progreso,completado,cancelado',
            'payment_amount' => 'nullable|numeric|min:0',
            'sort_order'     => 'nullable|integer|min:0',
        ]);

        $data = [
            'project_id'     => $this->project->id,
            'name'           => $this->name,
            'description'    => $this->description ?: null,
            'due_date'       => $this->due_date ?: null,
            'status'         => $this->status,
            'payment_amount' => $this->payment_amount ?: 0,
            'sort_order'     => $this->sort_order ?: 0,
        ];

        if ($this->editingId) {
            $milestone = ProjectMilestone::findOrFail($this->editingId);

            // Si se está marcando como completado ahora
            if ($milestone->status !== 'completado' && $this->status === 'completado') {
                $data['completed_at'] = now();
            } elseif ($this->status !== 'completado') {
                $data['completed_at'] = null;
            }

            $milestone->update($data);
            session()->flash('success', 'Hito actualizado.');
        } else {
            ProjectMilestone::create($data);
            session()->flash('success', 'Hito creado.');
        }

        $this->resetForm();
    }

    public function complete(int $id): void
    {
        $milestone = ProjectMilestone::findOrFail($id);
        $milestone->update([
            'status'       => 'completado',
            'completed_at' => now(),
        ]);
        session()->flash('success', 'Hito marcado como completado.');
    }

    public function delete(int $id): void
    {
        ProjectMilestone::findOrFail($id)->delete();
        session()->flash('success', 'Hito eliminado.');
    }

    public function resetForm(): void
    {
        $this->reset('name', 'description', 'due_date', 'editingId', 'showForm');
        $this->status         = 'pendiente';
        $this->payment_amount = '0';
        $this->sort_order     = '0';
    }

    public function render()
    {
        $milestones = $this->project->milestones()->get();

        $totalPago      = $milestones->sum('payment_amount');
        $totalCompletado = $milestones->where('status', 'completado')->sum('payment_amount');

        return view('livewire.projects.project-milestones', compact('milestones', 'totalPago', 'totalCompletado'));
    }
}

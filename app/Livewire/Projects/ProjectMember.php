<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ProjectMemberComponent extends Component
{
    public Project $project;

    // Formulario
    public bool $showForm = false;
    public ?int $editingId = null;
    public ?int $user_id = null;
    public string $role = 'otro';
    public bool $is_active = true;
    public string $joined_at = '';
    public string $left_at = '';
    public string $notes = '';

    public function mount(Project $project): void
    {
        $this->project   = $project;
        $this->joined_at = now()->format('Y-m-d');
    }

    public function openForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $member = ProjectMember::findOrFail($id);

        $this->editingId = $id;
        $this->user_id   = $member->user_id;
        $this->role      = $member->role;
        $this->is_active = $member->is_active;
        $this->joined_at = $member->joined_at?->format('Y-m-d') ?? '';
        $this->left_at   = $member->left_at?->format('Y-m-d') ?? '';
        $this->notes     = $member->notes ?? '';
        $this->showForm  = true;
    }

    public function save(): void
    {
        $this->validate([
            'user_id'   => [
                'required',
                'exists:users,id',
                // Evitar duplicado al crear
                $this->editingId
                    ? null
                    : \Illuminate\Validation\Rule::unique('project_members')
                        ->where('project_id', $this->project->id),
            ],
            'role'      => 'required|in:lider,desarrollador,diseñador,qa,observador,otro',
            'is_active' => 'boolean',
            'joined_at' => 'nullable|date',
            'left_at'   => 'nullable|date|after_or_equal:joined_at',
            'notes'     => 'nullable|string|max:500',
        ]);

        $data = [
            'project_id' => $this->project->id,
            'user_id'    => $this->user_id,
            'role'       => $this->role,
            'is_active'  => $this->is_active,
            'joined_at'  => $this->joined_at ?: null,
            'left_at'    => $this->left_at ?: null,
            'notes'      => $this->notes ?: null,
        ];

        if ($this->editingId) {
            ProjectMember::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Miembro actualizado.');
        } else {
            ProjectMember::create($data);
            session()->flash('success', 'Miembro agregado al proyecto.');
        }

        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $member = ProjectMember::findOrFail($id);
        $member->update([
            'is_active' => !$member->is_active,
            'left_at'   => !$member->is_active ? null : now()->format('Y-m-d'),
        ]);
    }

    public function delete(int $id): void
    {
        ProjectMember::findOrFail($id)->delete();
        session()->flash('success', 'Miembro eliminado.');
    }

    public function resetForm(): void
    {
        $this->reset('editingId', 'user_id', 'role', 'is_active', 'left_at', 'notes', 'showForm');
        $this->joined_at = now()->format('Y-m-d');
        $this->role      = 'otro';
        $this->is_active = true;
    }

    public function render()
    {
        $members = ProjectMember::with('user')
            ->where('project_id', $this->project->id)
            ->orderByDesc('is_active')
            ->orderBy('joined_at')
            ->get();

        // Solo usuarios de la empresa que aún no son miembros del proyecto
        $existingUserIds = $members->pluck('user_id');

        $availableUsers = User::where('company_id', auth()->user()->company_id)
            ->when(!$this->editingId, fn($q) => $q->whereNotIn('id', $existingUserIds))
            ->orderBy('name')
            ->get();

        return view('livewire.projects.project-member', compact('members', 'availableUsers'));
    }
}
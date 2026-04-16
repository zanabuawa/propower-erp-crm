<?php

namespace App\Livewire\HR;

use App\Models\HrDepartment;
use App\Models\HrEmployee;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Departamentos')]
class DepartmentIndex extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $code = '';
    public string $description = '';
    public ?int $manager_id = null;
    public bool $is_active = true;

    public string $search = '';

    public function openCreate(): void
    {
        $this->reset(['editingId','name','code','description','manager_id','is_active']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $dept = HrDepartment::findOrFail($id);
        $this->editingId   = $id;
        $this->name        = $dept->name;
        $this->code        = $dept->code ?? '';
        $this->description = $dept->description ?? '';
        $this->manager_id  = $dept->manager_id;
        $this->is_active   = $dept->is_active;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20',
        ]);

        $data = [
            'company_id'  => auth()->user()->company_id,
            'name'        => $this->name,
            'code'        => $this->code ?: null,
            'description' => $this->description ?: null,
            'manager_id'  => $this->manager_id,
            'is_active'   => $this->is_active,
        ];

        if ($this->editingId) {
            HrDepartment::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Departamento actualizado.');
        } else {
            HrDepartment::create($data);
            session()->flash('success', 'Departamento creado.');
        }

        $this->showModal = false;
    }

    public function toggleActive(int $id): void
    {
        $dept = HrDepartment::findOrFail($id);
        $dept->update(['is_active' => !$dept->is_active]);
    }

    public function render()
    {
        $departments = HrDepartment::withCount('employees')
            ->with('manager')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();

        $employees = HrEmployee::where('status', 'active')
            ->orderBy('last_name')
            ->get(['id','first_name','last_name','second_last_name']);

        return view('livewire.hr.department-index', compact('departments', 'employees'));
    }
}

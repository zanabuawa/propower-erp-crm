<?php

namespace App\Livewire\HR;

use App\Models\HrDepartment;
use App\Models\HrEmployee;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Formulario de Departamento')]
class DepartmentForm extends Component
{
    public ?HrDepartment $department = null;

    public string $name = '';
    public string $code = '';
    public string $description = '';
    public ?int $manager_id = null;
    public bool $is_active = true;

    public function mount(?HrDepartment $department = null): void
    {
        if ($department && $department->exists) {
            $this->department  = $department;
            $this->name        = $department->name;
            $this->code        = $department->code ?? '';
            $this->description = $department->description ?? '';
            $this->manager_id  = $department->manager_id;
            $this->is_active   = $department->is_active;
        }
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100',
            'code'        => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'manager_id'  => 'nullable|exists:hr_employees,id',
            'is_active'   => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'company_id'  => auth()->user()->company_id,
            'name'        => $this->name,
            'code'        => $this->code ?: null,
            'description' => $this->description ?: null,
            'manager_id'  => $this->manager_id,
            'is_active'   => $this->is_active,
        ];

        if ($this->department && $this->department->exists) {
            $this->department->update($data);
            session()->flash('success', 'Departamento actualizado correctamente.');
        } else {
            HrDepartment::create($data);
            session()->flash('success', 'Departamento creado correctamente.');
        }

        $this->redirect(route('hr.departments.index'), navigate: true);
    }

    public function render()
    {
        $employees = HrEmployee::where('status', 'active')
            ->orderBy('last_name')
            ->get(['id','first_name','last_name','second_last_name']);

        return view('livewire.hr.department-form', compact('employees'));
    }
}

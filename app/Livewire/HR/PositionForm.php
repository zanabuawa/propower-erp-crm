<?php

namespace App\Livewire\HR;

use App\Models\HrDepartment;
use App\Models\HrPosition;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Formulario de Puesto')]
class PositionForm extends Component
{
    public ?HrPosition $position = null;

    public string $name = '';
    public string $code = '';
    public string $description       = '';
    public string $responsibilities  = '';
    public string $requirements      = '';
    public int    $authorized_headcount = 1;
    public ?int   $department_id     = null;
    public string $salary_type       = 'monthly';
    public string $min_salary        = '';
    public string $max_salary        = '';
    public bool   $is_active         = true;

    public function mount(?HrPosition $position = null): void
    {
        if ($position && $position->exists) {
            $this->position             = $position;
            $this->name                 = $position->name;
            $this->code                 = $position->code ?? '';
            $this->description          = $position->description ?? '';
            $this->responsibilities     = $position->responsibilities ?? '';
            $this->requirements         = $position->requirements ?? '';
            $this->authorized_headcount = $position->authorized_headcount ?? 1;
            $this->department_id        = $position->department_id;
            $this->salary_type          = $position->salary_type;
            $this->min_salary           = (string) ($position->min_salary ?? '');
            $this->max_salary           = (string) ($position->max_salary ?? '');
            $this->is_active            = $position->is_active;
        }
    }

    public function rules(): array
    {
        return [
            'name'                  => 'required|string|max:100',
            'department_id'         => 'required|exists:hr_departments,id',
            'salary_type'           => 'required|in:' . implode(',', array_keys(HrPosition::SALARY_TYPES)),
            'min_salary'            => 'nullable|numeric|min:0',
            'max_salary'            => 'nullable|numeric|min:0',
            'authorized_headcount'  => 'required|integer|min:0',
            'description'           => 'nullable|string',
            'responsibilities'      => 'nullable|string',
            'requirements'          => 'nullable|string',
            'is_active'             => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'company_id'            => auth()->user()->company_id,
            'name'                  => $this->name,
            'code'                  => $this->code ?: null,
            'description'           => $this->description ?: null,
            'responsibilities'      => $this->responsibilities ?: null,
            'requirements'          => $this->requirements ?: null,
            'authorized_headcount'  => $this->authorized_headcount,
            'department_id'         => $this->department_id,
            'salary_type'           => $this->salary_type,
            'min_salary'            => $this->min_salary ?: null,
            'max_salary'            => $this->max_salary ?: null,
            'is_active'             => $this->is_active,
        ];

        if ($this->position && $this->position->exists) {
            $this->position->update($data);
            session()->flash('success', 'Puesto actualizado correctamente.');
        } else {
            HrPosition::create($data);
            session()->flash('success', 'Puesto creado correctamente.');
        }

        $this->redirect(route('hr.positions.index'), navigate: true);
    }

    public function render()
    {
        $departments = HrDepartment::where('is_active', true)->orderBy('name')->get();
        return view('livewire.hr.position-form', compact('departments'));
    }
}

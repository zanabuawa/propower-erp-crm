<?php

namespace App\Livewire\HR;

use App\Models\HrDepartment;
use App\Models\HrPosition;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Puestos Laborales')]
class PositionIndex extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $code = '';
    public string $description = '';
    public ?int $department_id = null;
    public string $salary_type = 'monthly';
    public string $min_salary = '';
    public string $max_salary = '';
    public bool $is_active = true;

    public string $search = '';
    public string $filterDepartment = '';

    public function openCreate(): void
    {
        $this->reset(['editingId','name','code','description','department_id','salary_type','min_salary','max_salary','is_active']);
        $this->salary_type = 'monthly';
        $this->is_active   = true;
        $this->showModal   = true;
    }

    public function openEdit(int $id): void
    {
        $pos = HrPosition::findOrFail($id);
        $this->editingId     = $id;
        $this->name          = $pos->name;
        $this->code          = $pos->code ?? '';
        $this->description   = $pos->description ?? '';
        $this->department_id = $pos->department_id;
        $this->salary_type   = $pos->salary_type;
        $this->min_salary    = (string) ($pos->min_salary ?? '');
        $this->max_salary    = (string) ($pos->max_salary ?? '');
        $this->is_active     = $pos->is_active;
        $this->showModal     = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'          => 'required|string|max:100',
            'department_id' => 'required|exists:hr_departments,id',
            'salary_type'   => 'required|in:' . implode(',', array_keys(HrPosition::SALARY_TYPES)),
            'min_salary'    => 'nullable|numeric|min:0',
            'max_salary'    => 'nullable|numeric|min:0',
        ]);

        $data = [
            'company_id'    => auth()->user()->company_id,
            'name'          => $this->name,
            'code'          => $this->code ?: null,
            'description'   => $this->description ?: null,
            'department_id' => $this->department_id,
            'salary_type'   => $this->salary_type,
            'min_salary'    => $this->min_salary ?: null,
            'max_salary'    => $this->max_salary ?: null,
            'is_active'     => $this->is_active,
        ];

        if ($this->editingId) {
            HrPosition::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Puesto actualizado.');
        } else {
            HrPosition::create($data);
            session()->flash('success', 'Puesto creado.');
        }

        $this->showModal = false;
    }

    public function render()
    {
        $positions = HrPosition::withCount('employees')
            ->with('department')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filterDepartment, fn($q) => $q->where('department_id', $this->filterDepartment))
            ->orderBy('name')
            ->get();

        $departments = HrDepartment::where('is_active', true)->orderBy('name')->get();

        return view('livewire.hr.position-index', compact('positions', 'departments'));
    }
}

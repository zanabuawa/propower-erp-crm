<?php

namespace App\Livewire\HR;

use App\Models\HrDepartment;
use App\Models\HrEmployee;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Empleados')]
class EmployeeIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterDepartment = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterDepartment(): void { $this->resetPage(); }

    public function render()
    {
        $employees = HrEmployee::query()
            ->with(['department', 'position'])
            ->when($this->search, fn($q) => $q->where(fn($q2) =>
                $q2->where('first_name', 'like', "%{$this->search}%")
                   ->orWhere('last_name', 'like', "%{$this->search}%")
                   ->orWhere('second_last_name', 'like', "%{$this->search}%")
                   ->orWhere('employee_number', 'like', "%{$this->search}%")
                   ->orWhere('email', 'like', "%{$this->search}%")
                   ->orWhere('rfc', 'like', "%{$this->search}%")
            ))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterDepartment, fn($q) => $q->where('department_id', $this->filterDepartment))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15);

        $departments = HrDepartment::where('is_active', true)->orderBy('name')->get();

        return view('livewire.hr.employee-index', compact('employees', 'departments'));
    }
}

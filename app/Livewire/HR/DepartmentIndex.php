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
    public string $search = '';

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

        return view('livewire.hr.department-index', compact('departments'));
    }
}

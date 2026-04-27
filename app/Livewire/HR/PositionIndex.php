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
    public string $search = '';
    public string $filterDepartment = '';

    public function toggleActive(int $id): void
    {
        $pos = HrPosition::findOrFail($id);
        $pos->update(['is_active' => !$pos->is_active]);
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

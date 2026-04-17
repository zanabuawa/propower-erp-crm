<?php

namespace App\Livewire\HR;

use App\Models\HrEmployee;
use App\Models\HrLeave;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Expediente de Empleado')]
class EmployeeShow extends Component
{
    public HrEmployee $employee;

    public string $activeTab = 'info';

    protected $listeners = ['refreshEmployeeShow' => '$refresh'];

    public function mount(HrEmployee $employee): void
    {
        $this->employee = $employee->load([
            'department', 'position', 'branch', 'supervisor',
            'activeContract', 'contracts',
            'leaves' => fn($q) => $q->latest()->limit(10),
            'incidents' => fn($q) => $q->latest()->limit(10),
            'evaluations' => fn($q) => $q->latest()->limit(5),
            'vacationBalances' => fn($q) => $q->where('year', now()->year),
            'projects',
            'education' => fn($q) => $q->latest(),
            'trainings.course',
            'documents' => fn($q) => $q->latest(),
        ]);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $recentPayrolls = $this->employee->payrollItems()
            ->with('payroll')
            ->latest()
            ->limit(6)
            ->get();

        return view('livewire.hr.employee-show', compact('recentPayrolls'));
    }
}

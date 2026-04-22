<?php

namespace App\Livewire\HR;

use App\Models\HrDepartment;
use App\Models\HrEmployee;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Organigrama')]
class OrgChart extends Component
{
    public string $viewMode     = 'departments'; // departments | supervisors
    public bool   $showEmployees = true;

    public function render()
    {
        $companyId = auth()->user()->company_id;

        if ($this->viewMode === 'departments') {
            $roots = HrDepartment::with($this->deptRelations())
                ->where('company_id', $companyId)
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            // Employees without department
            $noDept = $this->showEmployees
                ? HrEmployee::where('company_id', $companyId)
                    ->where('status', 'active')
                    ->whereNull('department_id')
                    ->with('position')
                    ->orderBy('last_name')
                    ->get()
                : collect();

            return view('livewire.hr.org-chart', compact('roots', 'noDept'));
        }

        // Supervisor view: employees without supervisor = root nodes
        $rootEmployees = HrEmployee::with($this->empRelations())
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->whereNull('supervisor_id')
            ->orderBy('last_name')
            ->get();

        return view('livewire.hr.org-chart', ['roots' => collect(), 'noDept' => collect(), 'rootEmployees' => $rootEmployees]);
    }

    private function deptRelations(): array
    {
        $empWith = fn($q) => $q->where('status', 'active')->with('position')->orderBy('last_name');

        return [
            'manager.position',
            'employees'         => $empWith,
            'children' => fn($q) => $q->where('is_active', true)->orderBy('name')->with([
                'manager.position',
                'employees'     => $empWith,
                'children' => fn($q2) => $q2->where('is_active', true)->orderBy('name')->with([
                    'manager.position',
                    'employees' => $empWith,
                    'children' => fn($q3) => $q3->where('is_active', true)->orderBy('name')->with([
                        'manager.position',
                        'employees' => $empWith,
                    ]),
                ]),
            ]),
        ];
    }

    private function empRelations(): array
    {
        $rel = fn($q) => $q->where('status', 'active')->with('position')->orderBy('last_name');
        return [
            'position', 'department',
            'subordinates' => fn($q) => $q->where('status', 'active')->orderBy('last_name')->with([
                'position', 'department',
                'subordinates' => fn($q2) => $q2->where('status', 'active')->orderBy('last_name')->with([
                    'position', 'department',
                    'subordinates' => fn($q3) => $q3->where('status', 'active')->with(['position']),
                ]),
            ]),
        ];
    }
}

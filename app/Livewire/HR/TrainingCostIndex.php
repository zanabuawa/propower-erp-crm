<?php

namespace App\Livewire\HR;

use App\Models\HrCourse;
use App\Models\HrDepartment;
use App\Models\HrEmployee;
use App\Models\HrEmployeeTraining;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Costos de Capacitación')]
class TrainingCostIndex extends Component
{
    public string $filterYear       = '';
    public string $filterDepartment = '';
    public string $filterType       = ''; // internal | external

    public function mount(): void
    {
        $this->filterYear = (string) now()->year;
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        // Base query: trainings de esta empresa con su curso y empleado
        $query = HrEmployeeTraining::query()
            ->join('hr_employees', 'hr_employee_training.employee_id', '=', 'hr_employees.id')
            ->join('hr_courses', 'hr_employee_training.course_id', '=', 'hr_courses.id')
            ->where('hr_employees.company_id', $companyId)
            ->where('hr_employee_training.status', 'completed')
            ->when($this->filterYear, fn($q) =>
                $q->whereYear('hr_employee_training.completion_date', $this->filterYear)
            )
            ->when($this->filterType, fn($q) =>
                $q->where('hr_courses.type', $this->filterType)
            )
            ->when($this->filterDepartment, fn($q) =>
                $q->where('hr_employees.department_id', $this->filterDepartment)
            );

        // KPIs globales
        $totalCost       = (clone $query)->sum('hr_courses.cost');
        $totalTrainings  = (clone $query)->count();
        $totalHours      = (clone $query)->sum('hr_courses.duration_hours');
        $totalEmployees  = (clone $query)->distinct('hr_employee_training.employee_id')->count('hr_employee_training.employee_id');

        // Costos por tipo (interno/externo)
        $costByType = (clone $query)
            ->selectRaw('hr_courses.type, SUM(hr_courses.cost) as total, COUNT(*) as count')
            ->groupBy('hr_courses.type')
            ->get()
            ->keyBy('type');

        // Top cursos por costo total
        $topCourses = (clone $query)
            ->selectRaw('hr_courses.id, hr_courses.name, hr_courses.type, hr_courses.provider, hr_courses.cost, hr_courses.duration_hours, COUNT(*) as participants, SUM(hr_courses.cost) as total_cost')
            ->groupBy('hr_courses.id', 'hr_courses.name', 'hr_courses.type', 'hr_courses.provider', 'hr_courses.cost', 'hr_courses.duration_hours')
            ->orderByDesc('total_cost')
            ->limit(10)
            ->get();

        // Costos por empleado
        $byEmployee = (clone $query)
            ->selectRaw('hr_employees.id, hr_employees.first_name, hr_employees.last_name, hr_employees.second_last_name, COUNT(*) as courses_count, SUM(hr_courses.cost) as total_cost, SUM(hr_courses.duration_hours) as total_hours')
            ->groupBy('hr_employees.id', 'hr_employees.first_name', 'hr_employees.last_name', 'hr_employees.second_last_name')
            ->orderByDesc('total_cost')
            ->limit(20)
            ->get();

        // Costos por departamento
        $byDepartment = (clone $query)
            ->join('hr_departments', 'hr_employees.department_id', '=', 'hr_departments.id')
            ->selectRaw('hr_departments.name as department_name, COUNT(*) as courses_count, SUM(hr_courses.cost) as total_cost, SUM(hr_courses.duration_hours) as total_hours')
            ->groupBy('hr_departments.name')
            ->orderByDesc('total_cost')
            ->get();

        $departments = HrDepartment::where('company_id', $companyId)->orderBy('name')->get(['id', 'name']);
        $years       = range(now()->year, now()->year - 4);

        return view('livewire.hr.training-cost-index', compact(
            'totalCost', 'totalTrainings', 'totalHours', 'totalEmployees',
            'costByType', 'topCourses', 'byEmployee', 'byDepartment',
            'departments', 'years'
        ));
    }
}

<?php

namespace App\Livewire\HR;

use App\Models\HrEmployee;
use App\Models\HrPerformanceEvaluation;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Evaluaciones de Desempeño')]
class EvaluationIndex extends Component
{
    use WithPagination;

    public string $filterStatus = '';
    public string $filterEmployee = '';

    public function submit(int $id): void
    {
        HrPerformanceEvaluation::findOrFail($id)->update(['status' => 'submitted']);
        session()->flash('success', 'Evaluación enviada al empleado.');
    }

    public function render()
    {
        $evaluations = HrPerformanceEvaluation::with(['employee', 'evaluator'])
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterEmployee, fn($q) => $q->where('employee_id', $this->filterEmployee))
            ->latest('evaluation_date')
            ->paginate(15);

        $employees = HrEmployee::where('status', 'active')
            ->orderBy('last_name')
            ->get(['id','first_name','last_name','second_last_name']);

        return view('livewire.hr.evaluation-index', compact('evaluations', 'employees'));
    }
}

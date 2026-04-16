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

    // Modal
    public bool $showModal = false;
    public ?int $editingId = null;
    public ?int $employee_id = null;
    public string $period = '';
    public string $evaluation_date = '';
    public array $categories = [
        'attendance'    => 80,
        'performance'   => 80,
        'teamwork'      => 80,
        'initiative'    => 80,
        'communication' => 80,
        'quality'       => 80,
    ];
    public string $strengths = '';
    public string $areas_for_improvement = '';
    public string $goals_next_period = '';
    public string $status = 'draft';

    public function mount(): void
    {
        $this->period          = now()->format('Y') . '-Q' . (int) ceil(now()->month / 3);
        $this->evaluation_date = now()->format('Y-m-d');
    }

    public function openCreate(): void
    {
        $this->reset(['editingId','employee_id','strengths','areas_for_improvement','goals_next_period']);
        $this->categories      = array_fill_keys(array_keys($this->categories), 80);
        $this->status          = 'draft';
        $this->period          = now()->format('Y') . '-Q' . (int) ceil(now()->month / 3);
        $this->evaluation_date = now()->format('Y-m-d');
        $this->showModal       = true;
    }

    public function openEdit(int $id): void
    {
        $ev = HrPerformanceEvaluation::findOrFail($id);
        $this->editingId              = $id;
        $this->employee_id            = $ev->employee_id;
        $this->period                 = $ev->period;
        $this->evaluation_date        = $ev->evaluation_date->format('Y-m-d');
        $this->categories             = array_merge($this->categories, $ev->categories ?? []);
        $this->strengths              = $ev->strengths ?? '';
        $this->areas_for_improvement  = $ev->areas_for_improvement ?? '';
        $this->goals_next_period      = $ev->goals_next_period ?? '';
        $this->status                 = $ev->status;
        $this->showModal              = true;
    }

    public function save(): void
    {
        $this->validate([
            'employee_id'     => 'required|exists:hr_employees,id',
            'period'          => 'required|string|max:20',
            'evaluation_date' => 'required|date',
        ]);

        $overall = round(array_sum($this->categories) / count($this->categories), 2);

        $data = [
            'company_id'           => auth()->user()->company_id,
            'employee_id'          => $this->employee_id,
            'evaluator_id'         => auth()->id(),
            'period'               => $this->period,
            'evaluation_date'      => $this->evaluation_date,
            'categories'           => $this->categories,
            'overall_score'        => $overall,
            'strengths'            => $this->strengths ?: null,
            'areas_for_improvement'=> $this->areas_for_improvement ?: null,
            'goals_next_period'    => $this->goals_next_period ?: null,
            'status'               => $this->status,
        ];

        if ($this->editingId) {
            HrPerformanceEvaluation::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Evaluación actualizada.');
        } else {
            HrPerformanceEvaluation::create($data);
            session()->flash('success', 'Evaluación creada.');
        }

        $this->showModal = false;
    }

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

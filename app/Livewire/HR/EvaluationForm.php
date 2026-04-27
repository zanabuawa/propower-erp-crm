<?php

namespace App\Livewire\HR;

use App\Models\HrEmployee;
use App\Models\HrPerformanceEvaluation;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Formulario de Evaluación')]
class EvaluationForm extends Component
{
    public ?HrPerformanceEvaluation $evaluation = null;

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

    public function mount(?HrPerformanceEvaluation $evaluation = null): void
    {
        $this->period          = now()->format('Y') . '-Q' . (int) ceil(now()->month / 3);
        $this->evaluation_date = now()->format('Y-m-d');

        if ($evaluation && $evaluation->exists) {
            $this->evaluation             = $evaluation;
            $this->employee_id            = $evaluation->employee_id;
            $this->period                 = $evaluation->period;
            $this->evaluation_date        = $evaluation->evaluation_date->format('Y-m-d');
            $this->categories             = array_merge($this->categories, $evaluation->categories ?? []);
            $this->strengths              = $evaluation->strengths ?? '';
            $this->areas_for_improvement  = $evaluation->areas_for_improvement ?? '';
            $this->goals_next_period      = $evaluation->goals_next_period ?? '';
            $this->status                 = $evaluation->status;
        }
    }

    public function rules(): array
    {
        return [
            'employee_id'     => 'required|exists:hr_employees,id',
            'period'          => 'required|string|max:20',
            'evaluation_date' => 'required|date',
            'categories'      => 'required|array',
            'categories.*'    => 'numeric|min:0|max:100',
            'strengths'       => 'nullable|string',
            'areas_for_improvement'=> 'nullable|string',
            'goals_next_period'    => 'nullable|string',
            'status'          => 'required|in:draft,submitted,completed',
        ];
    }

    public function save(): void
    {
        $this->validate();

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

        if ($this->evaluation && $this->evaluation->exists) {
            $this->evaluation->update($data);
            session()->flash('success', 'Evaluación actualizada correctamente.');
        } else {
            HrPerformanceEvaluation::create($data);
            session()->flash('success', 'Evaluación registrada correctamente.');
        }

        $this->redirect(route('hr.evaluations.index'), navigate: true);
    }

    public function render()
    {
        $employees = HrEmployee::where('status', 'active')
            ->orderBy('last_name')
            ->get(['id','first_name','last_name','second_last_name']);

        return view('livewire.hr.evaluation-form', compact('employees'));
    }
}

<?php

namespace App\Livewire\Finance;

use App\Models\Branch;
use App\Models\FinanceBudget;
use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class FinanceBudgetForm extends Component
{
    public ?FinanceBudget $budget = null;

    public string $name = '';
    public string $period_type = 'mensual';
    public string $year = '';
    public string $period_number = '';
    public string $category = 'otro';
    public string $amount_planned = '0';
    public string $currency = 'MXN';
    public string $status = 'borrador';
    public string $notes = '';
    public ?int $branch_id = null;
    public ?int $project_id = null;

    public function mount(?FinanceBudget $budget = null): void
    {
        $this->year = now()->year;

        if ($budget && $budget->exists) {
            $this->budget          = $budget;
            $this->name            = $budget->name;
            $this->period_type     = $budget->period_type;
            $this->year            = $budget->year;
            $this->period_number   = $budget->period_number ?? '';
            $this->category        = $budget->category;
            $this->amount_planned  = $budget->amount_planned;
            $this->currency        = $budget->currency;
            $this->status          = $budget->status;
            $this->notes           = $budget->notes ?? '';
            $this->branch_id       = $budget->branch_id;
            $this->project_id      = $budget->project_id;
        }
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'period_type'    => 'required|in:mensual,trimestral,semestral,anual',
            'year'           => 'required|integer|min:2000|max:2100',
            'period_number'  => 'nullable|integer|min:1|max:12',
            'category'       => 'required|in:ingresos,egresos,proyecto,departamento,otro',
            'amount_planned' => 'required|numeric|min:0',
            'currency'       => 'required|string|size:3',
            'status'         => 'required|in:borrador,aprobado,cerrado',
            'notes'          => 'nullable|string|max:500',
            'branch_id'      => 'nullable|exists:branches,id',
            'project_id'     => 'nullable|exists:projects,id',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'company_id'     => auth()->user()->company_id,
            'branch_id'      => $this->branch_id,
            'project_id'     => $this->project_id,
            'name'           => $this->name,
            'period_type'    => $this->period_type,
            'year'           => $this->year,
            'period_number'  => $this->period_number ?: null,
            'category'       => $this->category,
            'amount_planned' => $this->amount_planned,
            'currency'       => $this->currency,
            'status'         => $this->status,
            'notes'          => $this->notes ?: null,
        ];

        if ($this->budget && $this->budget->exists) {
            $this->budget->update($data);
            session()->flash('success', 'Presupuesto actualizado correctamente.');
        } else {
            FinanceBudget::create($data);
            session()->flash('success', 'Presupuesto creado correctamente.');
        }

        $this->redirect(route('finance.budgets.index'), navigate: true);
    }

    public function render()
    {
        $branches = Branch::where('company_id', auth()->user()->company_id)->orderBy('name')->get();
        $projects = Project::whereIn('status', ['borrador', 'activo'])->orderBy('name')->get();

        return view('livewire.finance.finance-budget-form', compact('branches', 'projects'));
    }
}

<?php

namespace App\Livewire\Finance;

use App\Models\FinanceAccount;
use App\Models\FinanceBudget;
use App\Models\FinanceCashflow;
use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class FinanceCashflowForm extends Component
{
    public ?FinanceCashflow $cashflow = null;

    public ?int $account_id = null;
    public ?int $project_id = null;
    public ?int $budget_id = null;
    public string $concept = '';
    public string $type = 'proyectado';
    public string $flow = 'entrada';
    public string $category = 'operacion';
    public string $amount = '';
    public string $currency = 'MXN';
    public string $expected_date = '';
    public string $realized_date = '';
    public bool $is_realized = false;
    public string $notes = '';

    public function mount(?FinanceCashflow $cashflow = null): void
    {
        $this->expected_date = now()->format('Y-m-d');

        if ($cashflow && $cashflow->exists) {
            $this->cashflow       = $cashflow;
            $this->account_id     = $cashflow->account_id;
            $this->project_id     = $cashflow->project_id;
            $this->budget_id      = $cashflow->budget_id;
            $this->concept        = $cashflow->concept;
            $this->type           = $cashflow->type;
            $this->flow           = $cashflow->flow;
            $this->category       = $cashflow->category;
            $this->amount         = $cashflow->amount;
            $this->currency       = $cashflow->currency;
            $this->expected_date  = $cashflow->expected_date->format('Y-m-d');
            $this->realized_date  = $cashflow->realized_date?->format('Y-m-d') ?? '';
            $this->is_realized    = $cashflow->is_realized;
            $this->notes          = $cashflow->notes ?? '';
        }
    }

    public function rules(): array
    {
        return [
            'account_id'    => 'required|exists:finance_accounts,id',
            'project_id'    => 'nullable|exists:projects,id',
            'budget_id'     => 'nullable|exists:finance_budgets,id',
            'concept'       => 'required|string|max:255',
            'type'          => 'required|in:proyectado,real',
            'flow'          => 'required|in:entrada,salida',
            'category'      => 'required|in:operacion,inversion,financiamiento',
            'amount'        => 'required|numeric|min:0.01',
            'currency'      => 'required|string|size:3',
            'expected_date' => 'required|date',
            'realized_date' => 'nullable|date',
            'is_realized'   => 'boolean',
            'notes'         => 'nullable|string|max:500',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'account_id'    => $this->account_id,
            'project_id'    => $this->project_id,
            'budget_id'     => $this->budget_id,
            'concept'       => $this->concept,
            'type'          => $this->type,
            'flow'          => $this->flow,
            'category'      => $this->category,
            'amount'        => $this->amount,
            'currency'      => $this->currency,
            'expected_date' => $this->expected_date,
            'realized_date' => $this->realized_date ?: null,
            'is_realized'   => $this->is_realized,
            'notes'         => $this->notes ?: null,
        ];

        if ($this->cashflow && $this->cashflow->exists) {
            $this->cashflow->update($data);
            session()->flash('success', 'Movimiento actualizado correctamente.');
        } else {
            FinanceCashflow::create($data);
            session()->flash('success', 'Movimiento registrado correctamente.');
        }

        $this->redirect(route('finance.cashflow.index'), navigate: true);
    }

    public function render()
    {
        $accounts = FinanceAccount::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)->orderBy('name')->get();
        $projects = Project::whereIn('status', ['borrador', 'activo'])->orderBy('name')->get();
        $budgets  = FinanceBudget::where('company_id', auth()->user()->company_id)
            ->where('status', '!=', 'cerrado')->orderByDesc('year')->get();

        return view('livewire.finance.finance-cashflow-form', compact('accounts', 'projects', 'budgets'));
    }
}

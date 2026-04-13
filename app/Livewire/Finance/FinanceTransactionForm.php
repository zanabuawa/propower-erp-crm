<?php

namespace App\Livewire\Finance;

use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class FinanceTransactionForm extends Component
{
    public ?FinanceTransaction $transaction = null;

    public ?int $account_id = null;
    public ?int $transfer_to_account_id = null;
    public ?int $project_id = null;
    public string $type = 'ingreso';
    public string $concept = '';
    public string $category = 'otro';
    public string $amount = '';
    public string $currency = 'MXN';
    public string $exchange_rate = '1';
    public string $transaction_date = '';
    public string $reference = '';
    public string $status = 'confirmado';
    public string $notes = '';

    public function mount(?FinanceTransaction $transaction = null): void
    {
        $this->transaction_date = now()->format('Y-m-d');

        if ($transaction && $transaction->exists) {
            $this->transaction            = $transaction;
            $this->account_id             = $transaction->account_id;
            $this->transfer_to_account_id = $transaction->transfer_to_account_id;
            $this->project_id             = $transaction->project_id;
            $this->type                   = $transaction->type;
            $this->concept                = $transaction->concept;
            $this->category               = $transaction->category;
            $this->amount                 = $transaction->amount;
            $this->currency               = $transaction->currency;
            $this->exchange_rate          = $transaction->exchange_rate;
            $this->transaction_date       = $transaction->transaction_date->format('Y-m-d');
            $this->reference              = $transaction->reference ?? '';
            $this->status                 = $transaction->status;
            $this->notes                  = $transaction->notes ?? '';
        }
    }

    public function rules(): array
    {
        return [
            'account_id'             => 'required|exists:finance_accounts,id',
            'transfer_to_account_id' => 'nullable|exists:finance_accounts,id|different:account_id',
            'project_id'             => 'nullable|exists:projects,id',
            'type'                   => 'required|in:ingreso,egreso,transferencia',
            'concept'                => 'required|string|max:255',
            'category'               => 'required|in:venta,compra,nomina,impuesto,prestamo,inversion,proyecto,otro',
            'amount'                 => 'required|numeric|min:0.01',
            'currency'               => 'required|string|size:3',
            'exchange_rate'          => 'required|numeric|min:0.000001',
            'transaction_date'       => 'required|date',
            'reference'              => 'nullable|string|max:100',
            'status'                 => 'required|in:pendiente,confirmado,cancelado',
            'notes'                  => 'nullable|string|max:500',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'account_id'             => $this->account_id,
            'transfer_to_account_id' => $this->type === 'transferencia' ? $this->transfer_to_account_id : null,
            'project_id'             => $this->project_id,
            'registered_by'          => auth()->id(),
            'type'                   => $this->type,
            'concept'                => $this->concept,
            'category'               => $this->category,
            'amount'                 => $this->amount,
            'currency'               => $this->currency,
            'exchange_rate'          => $this->exchange_rate,
            'transaction_date'       => $this->transaction_date,
            'reference'              => $this->reference ?: null,
            'status'                 => $this->status,
            'notes'                  => $this->notes ?: null,
        ];

        if ($this->transaction && $this->transaction->exists) {
            $this->transaction->update($data);
            session()->flash('success', 'Transacción actualizada correctamente.');
        } else {
            $data['folio'] = 'TXN-' . strtoupper(uniqid());
            FinanceTransaction::create($data);
            session()->flash('success', 'Transacción registrada correctamente.');
        }

        $this->redirect(route('finance.transactions.index'), navigate: true);
    }

    public function render()
    {
        $accounts = FinanceAccount::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)->orderBy('name')->get();
        $projects = Project::where('status', 'activo')->orderBy('name')->get();

        return view('livewire.finance.finance-transaction-form', compact('accounts', 'projects'));
    }
}

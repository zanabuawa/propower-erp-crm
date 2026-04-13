<?php

namespace App\Livewire\Finance;

use App\Models\Branch;
use App\Models\FinanceAccount;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class FinanceAccountForm extends Component
{
    public ?FinanceAccount $account = null;

    public string $code = '';
    public string $name = '';
    public string $type = 'banco';
    public string $bank_name = '';
    public string $account_number = '';
    public string $clabe = '';
    public string $currency = 'MXN';
    public string $opening_balance = '0';
    public bool $is_active = true;
    public string $notes = '';
    public ?int $branch_id = null;

    public function mount(?FinanceAccount $account = null): void
    {
        if ($account && $account->exists) {
            $this->account        = $account;
            $this->code           = $account->code;
            $this->name           = $account->name;
            $this->type           = $account->type;
            $this->bank_name      = $account->bank_name ?? '';
            $this->account_number = $account->account_number ?? '';
            $this->clabe          = $account->clabe ?? '';
            $this->currency       = $account->currency;
            $this->opening_balance = $account->opening_balance;
            $this->is_active      = $account->is_active;
            $this->notes          = $account->notes ?? '';
            $this->branch_id      = $account->branch_id;
        }
    }

    public function rules(): array
    {
        return [
            'code'            => 'required|string|max:50|unique:finance_accounts,code,' . ($this->account?->id ?? 'NULL'),
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:banco,caja,credito,inversion,otro',
            'bank_name'       => 'nullable|string|max:100',
            'account_number'  => 'nullable|string|max:50',
            'clabe'           => 'nullable|string|max:18',
            'currency'        => 'required|string|size:3',
            'opening_balance' => 'nullable|numeric|min:0',
            'is_active'       => 'boolean',
            'notes'           => 'nullable|string|max:500',
            'branch_id'       => 'nullable|exists:branches,id',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'company_id'      => auth()->user()->company_id,
            'branch_id'       => $this->branch_id,
            'code'            => $this->code,
            'name'            => $this->name,
            'type'            => $this->type,
            'bank_name'       => $this->bank_name ?: null,
            'account_number'  => $this->account_number ?: null,
            'clabe'           => $this->clabe ?: null,
            'currency'        => $this->currency,
            'opening_balance' => $this->opening_balance ?: 0,
            'is_active'       => $this->is_active,
            'notes'           => $this->notes ?: null,
        ];

        if ($this->account && $this->account->exists) {
            $this->account->update($data);
            session()->flash('success', 'Cuenta actualizada correctamente.');
        } else {
            $data['current_balance'] = $data['opening_balance'];
            FinanceAccount::create($data);
            session()->flash('success', 'Cuenta creada correctamente.');
        }

        $this->redirect(route('finance.accounts.index'), navigate: true);
    }

    public function render()
    {
        $branches = Branch::where('company_id', auth()->user()->company_id)->orderBy('name')->get();
        return view('livewire.finance.finance-account-form', compact('branches'));
    }
}

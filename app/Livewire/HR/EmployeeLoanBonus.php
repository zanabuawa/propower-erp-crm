<?php

namespace App\Livewire\HR;

use App\Models\HrEmployee;
use App\Models\HrEmployeeBonus;
use App\Models\HrEmployeeLoan;
use App\Models\HrPayrollConcept;
use Livewire\Component;

class EmployeeLoanBonus extends Component
{
    public HrEmployee $employee;

    // Modal control
    public string $modalType = ''; // loan | bonus

    // Préstamo
    public string $loanAmount      = '';
    public string $loanInstallment = '';
    public string $loanReason      = '';
    public string $loanDate        = '';

    // Bono
    public string $bonusAmount  = '';
    public string $bonusReason  = '';
    public string $bonusApplyAt = '';
    public ?int   $bonusConcept = null;

    protected $listeners = ['refreshLoanBonus' => '$refresh'];

    public function mount(HrEmployee $employee): void
    {
        $this->employee   = $employee;
        $this->loanDate   = now()->toDateString();
        $this->bonusApplyAt = now()->toDateString();
    }

    public function openLoan(): void
    {
        $this->reset(['loanAmount', 'loanInstallment', 'loanReason']);
        $this->loanDate  = now()->toDateString();
        $this->modalType = 'loan';
    }

    public function openBonus(): void
    {
        $this->reset(['bonusAmount', 'bonusReason', 'bonusConcept']);
        $this->bonusApplyAt = now()->toDateString();
        $this->modalType    = 'bonus';
    }

    public function saveLoan(): void
    {
        $this->validate([
            'loanAmount'      => 'required|numeric|min:1',
            'loanInstallment' => 'required|numeric|min:1',
            'loanDate'        => 'required|date',
        ], [
            'loanAmount.required'      => 'El monto del préstamo es obligatorio.',
            'loanInstallment.required' => 'La cuota por período es obligatoria.',
        ]);

        HrEmployeeLoan::create([
            'company_id'         => auth()->user()->company_id,
            'employee_id'        => $this->employee->id,
            'amount'             => $this->loanAmount,
            'balance'            => $this->loanAmount,
            'installment_amount' => $this->loanInstallment,
            'reason'             => $this->loanReason ?: null,
            'loan_date'          => $this->loanDate,
            'status'             => 'active',
        ]);

        $this->modalType = '';
        session()->flash('success', 'Préstamo registrado.');
        $this->dispatch('refreshLoanBonus');
    }

    public function saveBonus(): void
    {
        $this->validate([
            'bonusAmount'  => 'required|numeric|min:0.01',
            'bonusApplyAt' => 'required|date',
        ], [
            'bonusAmount.required' => 'El monto del bono es obligatorio.',
        ]);

        HrEmployeeBonus::create([
            'company_id'  => auth()->user()->company_id,
            'employee_id' => $this->employee->id,
            'concept_id'  => $this->bonusConcept ?: null,
            'amount'      => $this->bonusAmount,
            'reason'      => $this->bonusReason ?: null,
            'apply_at'    => $this->bonusApplyAt,
            'is_applied'  => false,
        ]);

        $this->modalType = '';
        session()->flash('success', 'Bono registrado. Se aplicará en la siguiente nómina.');
        $this->dispatch('refreshLoanBonus');
    }

    public function cancelLoan(HrEmployeeLoan $loan): void
    {
        $loan->update(['status' => 'cancelled']);
        session()->flash('success', 'Préstamo cancelado.');
    }

    public function deleteBonus(HrEmployeeBonus $bonus): void
    {
        if ($bonus->is_applied) {
            session()->flash('error', 'No se puede eliminar un bono ya aplicado.');
            return;
        }
        $bonus->delete();
        session()->flash('success', 'Bono eliminado.');
    }

    public function render()
    {
        $loans = HrEmployeeLoan::where('employee_id', $this->employee->id)
            ->orderByDesc('loan_date')->get();

        $bonuses = HrEmployeeBonus::where('employee_id', $this->employee->id)
            ->orderByDesc('apply_at')->get();

        $concepts = HrPayrollConcept::where('company_id', auth()->user()->company_id)
            ->where('type', 'perception')->where('is_active', true)
            ->orderBy('name')->get(['id', 'name']);

        return view('livewire.hr.employee-loan-bonus', compact('loans', 'bonuses', 'concepts'));
    }
}

<?php

namespace App\Livewire\HR;

use App\Models\HrEmployeeBonus;
use App\Models\HrEmployeeLoan;
use App\Models\HrPayrollConcept;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Complementos de Nomina')]
class PayrollComplementIndex extends Component
{
    public function render()
    {
        $companyId = auth()->user()->company_id;

        $conceptStats = [
            'total' => HrPayrollConcept::where('company_id', $companyId)->count(),
            'perceptions' => HrPayrollConcept::where('company_id', $companyId)->where('type', 'perception')->count(),
            'deductions' => HrPayrollConcept::where('company_id', $companyId)->where('type', 'deduction')->count(),
            'active' => HrPayrollConcept::where('company_id', $companyId)->where('is_active', true)->count(),
        ];

        $pendingBonuses = HrEmployeeBonus::with(['employee', 'concept'])
            ->where('company_id', $companyId)
            ->where('is_applied', false)
            ->latest('apply_at')
            ->take(6)
            ->get();

        $activeLoans = HrEmployeeLoan::with('employee')
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->latest('loan_date')
            ->take(6)
            ->get();

        $totals = [
            'pending_bonus_amount' => HrEmployeeBonus::where('company_id', $companyId)
                ->where('is_applied', false)
                ->sum('amount'),
            'active_loan_balance' => HrEmployeeLoan::where('company_id', $companyId)
                ->where('status', 'active')
                ->sum('balance'),
        ];

        return view('livewire.hr.payroll-complement-index', compact(
            'conceptStats',
            'pendingBonuses',
            'activeLoans',
            'totals',
        ));
    }
}

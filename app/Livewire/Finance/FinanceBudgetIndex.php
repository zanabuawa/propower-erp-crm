<?php

namespace App\Livewire\Finance;

use App\Models\FinanceBudget;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class FinanceBudgetIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterPeriodType = '';
    public string $filterYear = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterPeriodType(): void { $this->resetPage(); }
    public function updatingFilterYear(): void { $this->resetPage(); }

    public function render()
    {
        $budgets = FinanceBudget::with(['branch', 'project'])
            ->where('company_id', auth()->user()->company_id)
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPeriodType, fn($q) => $q->where('period_type', $this->filterPeriodType))
            ->when($this->filterYear, fn($q) => $q->where('year', $this->filterYear))
            ->orderByDesc('year')
            ->orderBy('period_number')
            ->paginate(20);

        $years = FinanceBudget::where('company_id', auth()->user()->company_id)
            ->distinct()->orderByDesc('year')->pluck('year');

        return view('livewire.finance.finance-budget-index', compact('budgets', 'years'));
    }
}

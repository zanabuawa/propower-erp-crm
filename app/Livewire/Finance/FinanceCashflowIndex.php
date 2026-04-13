<?php

namespace App\Livewire\Finance;

use App\Models\FinanceAccount;
use App\Models\FinanceCashflow;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class FinanceCashflowIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterFlow = '';
    public string $filterType = '';
    public string $filterCategory = '';
    public ?int $filterAccount = null;
    public string $filterDateFrom = '';
    public string $filterDateTo = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterFlow(): void { $this->resetPage(); }
    public function updatingFilterType(): void { $this->resetPage(); }
    public function updatingFilterAccount(): void { $this->resetPage(); }

    public function render()
    {
        $cashflows = FinanceCashflow::with(['account', 'project', 'budget'])
            ->whereHas('account', fn($q) => $q->where('company_id', auth()->user()->company_id))
            ->when($this->search, fn($q) => $q->where('concept', 'like', "%{$this->search}%"))
            ->when($this->filterFlow, fn($q) => $q->where('flow', $this->filterFlow))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterCategory, fn($q) => $q->where('category', $this->filterCategory))
            ->when($this->filterAccount, fn($q) => $q->where('account_id', $this->filterAccount))
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('expected_date', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn($q) => $q->whereDate('expected_date', '<=', $this->filterDateTo))
            ->orderBy('expected_date')
            ->paginate(25);

        $accounts = FinanceAccount::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)->orderBy('name')->get();

        // Totales del resultado filtrado (sin paginar)
        $totalsQuery = FinanceCashflow::whereHas('account', fn($q) => $q->where('company_id', auth()->user()->company_id))
            ->when($this->filterFlow, fn($q) => $q->where('flow', $this->filterFlow))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterAccount, fn($q) => $q->where('account_id', $this->filterAccount))
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('expected_date', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn($q) => $q->whereDate('expected_date', '<=', $this->filterDateTo));

        $totalEntradas = (clone $totalsQuery)->where('flow', 'entrada')->sum('amount');
        $totalSalidas  = (clone $totalsQuery)->where('flow', 'salida')->sum('amount');

        return view('livewire.finance.finance-cashflow-index', compact('cashflows', 'accounts', 'totalEntradas', 'totalSalidas'));
    }
}

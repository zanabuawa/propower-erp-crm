<?php

namespace App\Livewire\Finance;

use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class FinanceTransactionIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterType = '';
    public string $filterCategory = '';
    public ?int $filterAccount = null;
    public string $filterDateFrom = '';
    public string $filterDateTo = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterType(): void { $this->resetPage(); }
    public function updatingFilterCategory(): void { $this->resetPage(); }
    public function updatingFilterAccount(): void { $this->resetPage(); }

    public function render()
    {
        $transactions = FinanceTransaction::with(['account', 'project', 'registeredBy'])
            ->whereHas('account', fn($q) => $q->where('company_id', auth()->user()->company_id))
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('folio', 'like', "%{$this->search}%")
                  ->orWhere('concept', 'like', "%{$this->search}%")
                  ->orWhere('reference', 'like', "%{$this->search}%");
            }))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterCategory, fn($q) => $q->where('category', $this->filterCategory))
            ->when($this->filterAccount, fn($q) => $q->where('account_id', $this->filterAccount))
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('transaction_date', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn($q) => $q->whereDate('transaction_date', '<=', $this->filterDateTo))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(25);

        $accounts = FinanceAccount::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)->orderBy('name')->get();

        return view('livewire.finance.finance-transaction-index', compact('transactions', 'accounts'));
    }
}

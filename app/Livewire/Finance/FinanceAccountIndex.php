<?php

namespace App\Livewire\Finance;

use App\Models\Branch;
use App\Models\FinanceAccount;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class FinanceAccountIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterType = '';
    public ?int $filterBranch = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterType(): void { $this->resetPage(); }
    public function updatingFilterBranch(): void { $this->resetPage(); }

    public function render()
    {
        $accounts = FinanceAccount::with(['branch'])
            ->where('company_id', auth()->user()->company_id)
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                  ->orWhere('name', 'like', "%{$this->search}%")
                  ->orWhere('bank_name', 'like', "%{$this->search}%");
            }))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterBranch, fn($q) => $q->where('branch_id', $this->filterBranch))
            ->orderBy('code')
            ->paginate(20);

        $branches = Branch::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        return view('livewire.finance.finance-account-index', compact('accounts', 'branches'));
    }
}

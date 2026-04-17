<?php

namespace App\Livewire\Assets;

use App\Models\AssetLoan;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AssetLoanIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    public bool $showReturnModal = false;
    public ?int $returningLoanId = null;
    public string $conditionOnReturn = 'good';
    public string $returnNotes = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function openReturnModal(int $loanId): void
    {
        $this->returningLoanId  = $loanId;
        $this->conditionOnReturn = 'good';
        $this->returnNotes       = '';
        $this->showReturnModal   = true;
    }

    public function closeReturnModal(): void
    {
        $this->showReturnModal  = false;
        $this->returningLoanId  = null;
    }

    public function confirmReturn(): void
    {
        $loan = AssetLoan::where('company_id', auth()->user()->company_id)
            ->where('status', 'active')
            ->findOrFail($this->returningLoanId);

        $status = match ($this->conditionOnReturn) {
            'lost'    => 'lost',
            'damaged' => 'damaged',
            default   => 'returned',
        };

        $loan->update([
            'status'             => $status,
            'condition_on_return'=> $this->conditionOnReturn,
            'actual_return_date' => now()->toDateString(),
            'return_notes'       => $this->returnNotes ?: null,
            'returned_by'        => auth()->id(),
        ]);

        // Liberar activo si se devolvió en buen estado
        if ($status === 'returned') {
            $loan->asset->update(['status' => 'active']);
        } elseif ($status === 'lost') {
            $loan->asset->update(['status' => 'retired']);
        }

        $this->closeReturnModal();
        session()->flash('success', 'Devolución registrada correctamente.');
    }

    public function markLost(int $loanId): void
    {
        $loan = AssetLoan::where('company_id', auth()->user()->company_id)
            ->where('status', 'active')
            ->findOrFail($loanId);

        $loan->update([
            'status'             => 'lost',
            'condition_on_return'=> 'lost',
            'actual_return_date' => now()->toDateString(),
            'returned_by'        => auth()->id(),
        ]);

        $loan->asset->update(['status' => 'retired']);
        session()->flash('success', 'Activo marcado como pérdida.');
    }

    public function render()
    {
        $loans = AssetLoan::with(['asset', 'loanedToUser', 'createdBy'])
            ->where('company_id', auth()->user()->company_id)
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('folio', 'like', "%{$this->search}%")
                  ->orWhere('loaned_to_name', 'like', "%{$this->search}%")
                  ->orWhereHas('loanedToUser', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                  ->orWhereHas('asset', fn($q) => $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('folio', 'like', "%{$this->search}%"));
            }))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderByDesc('id')
            ->paginate(20);

        $overdueCount = AssetLoan::where('company_id', auth()->user()->company_id)
            ->where('status', 'active')
            ->whereNotNull('expected_return_date')
            ->where('expected_return_date', '<', now()->toDateString())
            ->count();

        return view('livewire.assets.asset-loan-index', compact('loans', 'overdueCount'));
    }
}

<?php

namespace App\Livewire\Purchases;

use App\Models\StockMovement;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class GoodsReceiptIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void { $this->resetPage(); }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $movements = StockMovement::with(['warehouse', 'user', 'items.product'])
            ->where('company_id', $companyId)
            ->where('type', 'entry')
            ->where('notes', 'Recepción de mercancías')
            ->when($this->search, fn($q) => $q->where(fn($s) => $s
                ->where('folio', 'like', "%{$this->search}%")
                ->orWhere('reference', 'like', "%{$this->search}%")))
            ->latest('moved_at')
            ->paginate(15);

        return view('livewire.purchases.goods-receipt-index', [
            'movements' => $movements,
        ]);
    }
}

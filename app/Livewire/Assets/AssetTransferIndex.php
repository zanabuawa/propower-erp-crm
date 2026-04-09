<?php

namespace App\Livewire\Assets;

use App\Models\AssetTransfer;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AssetTransferIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function updatingSearch(): void { $this->resetPage(); }

    public function render()
    {
        $transfers = AssetTransfer::with(['asset', 'fromBranch', 'toBranch', 'requestedBy'])
            ->where('company_id', auth()->user()->company_id)
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('folio', 'like', "%{$this->search}%")
                  ->orWhereHas('asset', fn($q) => $q
                      ->where('name', 'like', "%{$this->search}%")
                      ->orWhere('folio', 'like', "%{$this->search}%"));
            }))
            ->when($this->dateFrom, fn($q) => $q->whereDate('transferred_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('transferred_at', '<=', $this->dateTo))
            ->orderByDesc('transferred_at')
            ->paginate(20);

        return view('livewire.assets.asset-transfer-index', compact('transfers'));
    }
}

<?php

namespace App\Livewire\Assets;

use App\Models\Branch;
use App\Models\FixedAsset;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AssetIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterCategory = '';
    public ?int $filterBranch = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterCategory(): void { $this->resetPage(); }
    public function updatingFilterBranch(): void { $this->resetPage(); }

    public function render()
    {
        $assets = FixedAsset::with(['branch', 'warehouse', 'assignedUser'])
            ->where('company_id', auth()->user()->company_id)
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('folio', 'like', "%{$this->search}%")
                  ->orWhere('name', 'like', "%{$this->search}%")
                  ->orWhere('serial_number', 'like', "%{$this->search}%")
                  ->orWhere('brand', 'like', "%{$this->search}%")
                  ->orWhere('model', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterCategory, fn($q) => $q->where('category', $this->filterCategory))
            ->when($this->filterBranch, fn($q) => $q->where('branch_id', $this->filterBranch))
            ->orderByDesc('id')
            ->paginate(20);

        $branches = Branch::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        return view('livewire.assets.asset-index', compact('assets', 'branches'));
    }
}

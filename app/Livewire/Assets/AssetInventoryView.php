<?php

namespace App\Livewire\Assets;

use App\Models\Branch;
use App\Models\FixedAsset;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AssetInventoryView extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterCategory = '';
    public ?int $filterBranch = null;
    public string $groupBy = 'branch'; // branch | category | status

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterCategory(): void { $this->resetPage(); }
    public function updatingFilterBranch(): void { $this->resetPage(); }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        // Summary stats
        $totalAssets    = FixedAsset::where('company_id', $companyId)->where('is_active', true)->count();
        $activeAssets   = FixedAsset::where('company_id', $companyId)->where('status', 'active')->count();
        $inMaintenance  = FixedAsset::where('company_id', $companyId)->where('status', 'in_maintenance')->count();
        $retired        = FixedAsset::where('company_id', $companyId)->where('status', 'retired')->count();

        // By branch summary
        $byBranch = FixedAsset::where('company_id', $companyId)
            ->where('is_active', true)
            ->selectRaw('branch_id, COUNT(*) as total,
                SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN status = "in_maintenance" THEN 1 ELSE 0 END) as maintenance_count,
                SUM(acquisition_cost) as total_cost')
            ->groupBy('branch_id')
            ->with('branch')
            ->get();

        // By category summary
        $byCategory = FixedAsset::where('company_id', $companyId)
            ->where('is_active', true)
            ->selectRaw('category, COUNT(*) as total, SUM(acquisition_cost) as total_cost')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // Detailed list
        $assets = FixedAsset::with(['branch', 'warehouse', 'assignedUser'])
            ->where('company_id', $companyId)
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('folio', 'like', "%{$this->search}%")
                  ->orWhere('name', 'like', "%{$this->search}%")
                  ->orWhere('serial_number', 'like', "%{$this->search}%")
                  ->orWhere('brand', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterCategory, fn($q) => $q->where('category', $this->filterCategory))
            ->when($this->filterBranch, fn($q) => $q->where('branch_id', $this->filterBranch))
            ->orderBy('branch_id')
            ->orderBy('name')
            ->paginate(25);

        $branches = Branch::where('company_id', $companyId)->orderBy('name')->get();

        return view('livewire.assets.asset-inventory-view', compact(
            'assets', 'branches',
            'totalAssets', 'activeAssets', 'inMaintenance', 'retired',
            'byBranch', 'byCategory'
        ));
    }
}

<?php

namespace App\Livewire\Assets;

use App\Models\AssetDepreciation;
use App\Models\FixedAsset;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AssetDepreciationIndex extends Component
{
    public ?int $assetId = null;
    public string $filterType = 'contable'; // contable | fiscal
    public int $filterYear;

    public function mount(?int $assetId = null): void
    {
        $this->assetId    = $assetId;
        $this->filterYear = now()->year;
    }

    public function runDepreciation(): void
    {
        if (! $this->assetId) {
            return;
        }

        $asset = FixedAsset::where('company_id', auth()->user()->company_id)->findOrFail($this->assetId);
        $now   = now();

        $fiscal  = $this->filterType === 'fiscal';
        $created = $fiscal
            ? $asset->runMonthlyFiscalDepreciation($now->year, $now->month)
            : $asset->runMonthlyDepreciation($now->year, $now->month);

        if ($created) {
            session()->flash('success', 'Depreciación del período ' . $now->format('m/Y') . ' registrada.');
        } else {
            session()->flash('info', 'Ya existe depreciación para este período o el activo no aplica.');
        }
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $assetsQuery = FixedAsset::where('company_id', $companyId)
            ->whereNotNull('depreciation_method')
            ->orderBy('name');

        $assets = $assetsQuery->get();

        $depreciations = AssetDepreciation::with('asset')
            ->whereHas('asset', fn($q) => $q->where('company_id', $companyId))
            ->where('is_fiscal', $this->filterType === 'fiscal')
            ->when($this->assetId, fn($q) => $q->where('fixed_asset_id', $this->assetId))
            ->where('year', $this->filterYear)
            ->orderBy('year')->orderBy('month')
            ->get();

        $totalDepreciation = $depreciations->sum('depreciation_amount');

        $selectedAsset = $this->assetId
            ? $assets->firstWhere('id', $this->assetId)
            : null;

        return view('livewire.assets.asset-depreciation-index', compact(
            'assets', 'depreciations', 'totalDepreciation', 'selectedAsset'
        ));
    }
}

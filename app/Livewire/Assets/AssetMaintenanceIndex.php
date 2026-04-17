<?php

namespace App\Livewire\Assets;

use App\Models\AssetMaintenance;
use App\Models\FixedAsset;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AssetMaintenanceIndex extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $filterStatus = '';
    public string $filterType   = '';
    public ?int   $filterAsset  = null;

    public function updatingSearch(): void      { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterType(): void   { $this->resetPage(); }
    public function updatingFilterAsset(): void  { $this->resetPage(); }

    public function complete(int $id): void
    {
        $m = AssetMaintenance::where('company_id', auth()->user()->company_id)->findOrFail($id);
        $m->update([
            'status'         => 'completed',
            'completed_date' => now()->toDateString(),
        ]);

        // Programar el siguiente si tiene intervalo definido
        if ($m->interval_months && $m->type === 'preventive') {
            AssetMaintenance::create([
                'company_id'      => $m->company_id,
                'fixed_asset_id'  => $m->fixed_asset_id,
                'created_by'      => auth()->id(),
                'folio'           => AssetMaintenance::generateFolio($m->company_id),
                'type'            => 'preventive',
                'status'          => 'scheduled',
                'scheduled_date'  => now()->addMonths($m->interval_months)->toDateString(),
                'interval_months' => $m->interval_months,
                'technician_name' => $m->technician_name,
                'provider'        => $m->provider,
            ]);
        }

        // Actualizar estado del activo a 'active' si estaba en mantenimiento
        $asset = $m->asset;
        if ($asset->status === 'in_maintenance') {
            $asset->update(['status' => 'active']);
        }

        session()->flash('success', 'Mantenimiento completado. ' . ($m->interval_months ? 'Próximo programado automáticamente.' : ''));
    }

    public function cancel(int $id): void
    {
        AssetMaintenance::where('company_id', auth()->user()->company_id)
            ->findOrFail($id)
            ->update(['status' => 'cancelled']);

        session()->flash('success', 'Mantenimiento cancelado.');
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $maintenances = AssetMaintenance::with(['asset', 'createdBy', 'technician'])
            ->where('company_id', $companyId)
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('folio', 'like', "%{$this->search}%")
                  ->orWhere('technician_name', 'like', "%{$this->search}%")
                  ->orWhere('provider', 'like', "%{$this->search}%")
                  ->orWhereHas('asset', fn($q) => $q
                      ->where('name', 'like', "%{$this->search}%")
                      ->orWhere('folio', 'like', "%{$this->search}%"));
            }))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType,   fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterAsset,  fn($q) => $q->where('fixed_asset_id', $this->filterAsset))
            ->orderBy('scheduled_date')
            ->paginate(20);

        $overdueCount = AssetMaintenance::where('company_id', $companyId)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->where('scheduled_date', '<', now()->toDateString())
            ->count();

        $assets = FixedAsset::where('company_id', $companyId)->orderBy('name')->get(['id', 'folio', 'name']);

        return view('livewire.assets.asset-maintenance-index',
            compact('maintenances', 'overdueCount', 'assets'));
    }
}

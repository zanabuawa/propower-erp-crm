<?php

namespace App\Livewire\Assets;

use App\Models\AssetMaintenance;
use App\Models\FixedAsset;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AssetMaintenanceForm extends Component
{
    public ?AssetMaintenance $maintenance = null;

    public ?int    $fixed_asset_id       = null;
    public string  $type                 = 'preventive';
    public string  $status               = 'scheduled';
    public string  $scheduled_date       = '';
    public string  $completed_date       = '';
    public ?int    $technician_user_id   = null;
    public string  $technician_name      = '';
    public string  $provider             = '';
    public string  $cost                 = '';
    public string  $next_scheduled_date  = '';
    public string  $interval_months      = '';
    public string  $work_performed       = '';
    public string  $parts_replaced       = '';
    public string  $observations         = '';
    public string  $technicianType       = 'internal'; // internal | external

    public function mount(?AssetMaintenance $maintenance = null, ?int $assetId = null): void
    {
        $this->scheduled_date = now()->toDateString();

        if ($assetId) {
            $this->fixed_asset_id = $assetId;
        }

        if ($maintenance && $maintenance->exists) {
            $this->maintenance          = $maintenance;
            $this->fixed_asset_id       = $maintenance->fixed_asset_id;
            $this->type                 = $maintenance->type;
            $this->status               = $maintenance->status;
            $this->scheduled_date       = $maintenance->scheduled_date->format('Y-m-d');
            $this->completed_date       = $maintenance->completed_date?->format('Y-m-d') ?? '';
            $this->technician_user_id   = $maintenance->technician_user_id;
            $this->technician_name      = $maintenance->technician_name ?? '';
            $this->provider             = $maintenance->provider ?? '';
            $this->cost                 = $maintenance->cost ?? '';
            $this->next_scheduled_date  = $maintenance->next_scheduled_date?->format('Y-m-d') ?? '';
            $this->interval_months      = $maintenance->interval_months ?? '';
            $this->work_performed       = $maintenance->work_performed ?? '';
            $this->parts_replaced       = $maintenance->parts_replaced ?? '';
            $this->observations         = $maintenance->observations ?? '';
            $this->technicianType       = $maintenance->technician_user_id ? 'internal' : 'external';
        }
    }

    public function rules(): array
    {
        return [
            'fixed_asset_id'     => 'required|exists:fixed_assets,id',
            'type'               => 'required|in:preventive,corrective,calibration,inspection',
            'status'             => 'required|in:scheduled,in_progress,completed,cancelled',
            'scheduled_date'     => 'required|date',
            'completed_date'     => 'nullable|date|after_or_equal:scheduled_date',
            'technician_user_id' => 'nullable|exists:users,id',
            'technician_name'    => 'nullable|string|max:255',
            'provider'           => 'nullable|string|max:255',
            'cost'               => 'nullable|numeric|min:0',
            'next_scheduled_date'=> 'nullable|date|after:scheduled_date',
            'interval_months'    => 'nullable|integer|min:1|max:60',
            'work_performed'     => 'nullable|string',
            'parts_replaced'     => 'nullable|string',
            'observations'       => 'nullable|string',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $companyId = auth()->user()->company_id;

        $data = [
            'company_id'          => $companyId,
            'fixed_asset_id'      => $this->fixed_asset_id,
            'created_by'          => auth()->id(),
            'type'                => $this->type,
            'status'              => $this->status,
            'scheduled_date'      => $this->scheduled_date,
            'completed_date'      => $this->completed_date ?: null,
            'technician_user_id'  => $this->technicianType === 'internal' ? $this->technician_user_id : null,
            'technician_name'     => $this->technicianType === 'external' ? ($this->technician_name ?: null) : null,
            'provider'            => $this->provider ?: null,
            'cost'                => $this->cost ?: null,
            'next_scheduled_date' => $this->next_scheduled_date ?: null,
            'interval_months'     => $this->interval_months ?: null,
            'work_performed'      => $this->work_performed ?: null,
            'parts_replaced'      => $this->parts_replaced ?: null,
            'observations'        => $this->observations ?: null,
        ];

        if ($this->maintenance && $this->maintenance->exists) {
            $this->maintenance->update($data);
        } else {
            $data['folio'] = AssetMaintenance::generateFolio($companyId);
            AssetMaintenance::create($data);
        }

        // Si el mantenimiento está en proceso, marcar el activo como in_maintenance
        if ($this->status === 'in_progress') {
            FixedAsset::where('id', $this->fixed_asset_id)->update(['status' => 'in_maintenance']);
        }

        session()->flash('success', 'Mantenimiento guardado correctamente.');
        $this->redirect(route('assets.maintenance.index'), navigate: true);
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;
        $assets    = FixedAsset::where('company_id', $companyId)->orderBy('name')->get(['id', 'folio', 'name', 'category']);
        $users     = User::where('company_id', $companyId)->orderBy('name')->get(['id', 'name']);

        return view('livewire.assets.asset-maintenance-form', compact('assets', 'users'));
    }
}

<?php

namespace App\Livewire\Assets;

use App\Models\Branch;
use App\Models\FixedAsset;
use App\Models\User;
use App\Models\Warehouse;
use App\Notifications\AssetStatusChangedNotification;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AssetForm extends Component
{
    public ?FixedAsset $asset = null;

    public string $name = '';
    public string $category = '';
    public string $brand = '';
    public string $model = '';
    public string $serial_number = '';
    public string $description = '';
    public string $acquisition_date = '';
    public string $acquisition_cost = '';
    public string $status = 'active';
    public string $notes = '';
    public ?int $branch_id = null;
    public ?int $warehouse_id = null;
    public ?int $assigned_to = null;

    public function mount(?FixedAsset $asset = null): void
    {
        if ($asset && $asset->exists) {
            $this->asset            = $asset;
            $this->name             = $asset->name;
            $this->category         = $asset->category ?? '';
            $this->brand            = $asset->brand ?? '';
            $this->model            = $asset->model ?? '';
            $this->serial_number    = $asset->serial_number ?? '';
            $this->description      = $asset->description ?? '';
            $this->acquisition_date = $asset->acquisition_date?->format('Y-m-d') ?? '';
            $this->acquisition_cost = $asset->acquisition_cost ?? '';
            $this->status           = $asset->status;
            $this->notes            = $asset->notes ?? '';
            $this->branch_id        = $asset->branch_id;
            $this->warehouse_id     = $asset->warehouse_id;
            $this->assigned_to      = $asset->assigned_to;
        }
    }

    public function updatedBranchId(): void
    {
        $this->warehouse_id = null;
    }

    public function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'category'         => 'nullable|string|max:100',
            'brand'            => 'nullable|string|max:100',
            'model'            => 'nullable|string|max:100',
            'serial_number'    => 'nullable|string|max:100',
            'description'      => 'nullable|string',
            'acquisition_date' => 'nullable|date',
            'acquisition_cost' => 'nullable|numeric|min:0',
            'status'           => 'required|in:active,in_maintenance,transferred,retired',
            'notes'            => 'nullable|string|max:500',
            'branch_id'        => 'nullable|exists:branches,id',
            'warehouse_id'     => 'nullable|exists:warehouses,id',
            'assigned_to'      => 'nullable|exists:users,id',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'company_id'       => auth()->user()->company_id,
            'name'             => $this->name,
            'category'         => $this->category ?: null,
            'brand'            => $this->brand ?: null,
            'model'            => $this->model ?: null,
            'serial_number'    => $this->serial_number ?: null,
            'description'      => $this->description ?: null,
            'acquisition_date' => $this->acquisition_date ?: null,
            'acquisition_cost' => $this->acquisition_cost ?: null,
            'status'           => $this->status,
            'notes'            => $this->notes ?: null,
            'branch_id'        => $this->branch_id,
            'warehouse_id'     => $this->warehouse_id,
            'assigned_to'      => $this->assigned_to,
        ];

        if ($this->asset && $this->asset->exists) {
            $previousStatus = $this->asset->status;
            $this->asset->update($data);

            if ($previousStatus !== $this->status) {
                $this->notifyAssetManagers($this->asset, 'status_changed', $previousStatus);
            }

            session()->flash('success', 'Activo actualizado correctamente.');
        } else {
            $data['folio'] = FixedAsset::generateFolio(auth()->user()->company_id);
            $asset = FixedAsset::create($data);
            $this->notifyAssetManagers($asset, 'created', null);
            session()->flash('success', 'Activo creado correctamente.');
        }

        $this->redirect(route('assets.index'), navigate: true);
    }

    private function notifyAssetManagers(FixedAsset $asset, string $eventType, ?string $previousStatus): void
    {
        $statuses   = \App\Models\FixedAsset::STATUSES;
        $fromLabel  = $statuses[$previousStatus] ?? $previousStatus ?? '—';
        $toLabel    = $statuses[$asset->status] ?? $asset->status;
        $titles = [
            'created'        => 'Nuevo activo registrado',
            'status_changed' => 'Cambio de estado en activo',
        ];
        $messages = [
            'created'        => "{$asset->folio}: {$asset->name} registrado como activo.",
            'status_changed' => "{$asset->folio}: {$asset->name} cambió de \"{$fromLabel}\" a \"{$toLabel}\".",
        ];

        $notification = new AssetStatusChangedNotification(
            title: $titles[$eventType] ?? 'Activo actualizado',
            message: $messages[$eventType] ?? '',
            type: $eventType,
            assetId: $asset->id,
        );

        User::where('company_id', auth()->user()->company_id)
            ->where('id', '!=', auth()->id())
            ->get()
            ->filter(fn($u) => $u->can('view assets'))
            ->each(fn($u) => $u->notify($notification));
    }

    public function render()
    {
        $branches   = Branch::where('company_id', auth()->user()->company_id)->orderBy('name')->get();
        $warehouses = $this->branch_id
            ? Warehouse::where('branch_id', $this->branch_id)->where('is_active', true)->orderBy('name')->get()
            : collect();
        $users = User::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        return view('livewire.assets.asset-form', compact('branches', 'warehouses', 'users'));
    }
}

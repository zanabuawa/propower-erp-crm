<?php

namespace App\Livewire\Assets;

use App\Models\AssetTransfer;
use App\Models\Branch;
use App\Models\FixedAsset;
use App\Models\User;
use App\Models\Warehouse;
use App\Notifications\AssetStatusChangedNotification;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class AssetTransferForm extends Component
{
    public string $assetSearch = '';
    public array $assetResults = [];
    public ?int $asset_id = null;
    public ?FixedAsset $selectedAsset = null;

    public ?int $to_branch_id = null;
    public ?int $to_warehouse_id = null;
    public ?int $to_user_id = null;
    public string $reason = '';
    public string $notes = '';
    public string $transferred_at = '';

    public function mount(?int $asset = null): void
    {
        $this->transferred_at = now()->format('Y-m-d\TH:i');

        if ($asset) {
            $this->selectAsset($asset);
        }
    }

    public function updatedAssetSearch(): void
    {
        if (strlen($this->assetSearch) < 2) {
            $this->assetResults = [];
            return;
        }

        $this->assetResults = FixedAsset::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->whereNotIn('status', ['retired'])
            ->where(fn($q) => $q
                ->where('name', 'like', "%{$this->assetSearch}%")
                ->orWhere('folio', 'like', "%{$this->assetSearch}%")
                ->orWhere('serial_number', 'like', "%{$this->assetSearch}%"))
            ->with(['branch', 'assignedUser'])
            ->limit(8)
            ->get()
            ->toArray();
    }

    public function selectAsset(int $assetId): void
    {
        $this->selectedAsset = FixedAsset::with(['branch', 'warehouse', 'assignedUser'])->find($assetId);
        $this->asset_id = $assetId;
        $this->assetSearch = '';
        $this->assetResults = [];
    }

    public function clearAsset(): void
    {
        $this->selectedAsset = null;
        $this->asset_id = null;
    }

    public function updatedToBranchId(): void
    {
        $this->to_warehouse_id = null;
    }

    public function rules(): array
    {
        return [
            'asset_id'       => 'required|exists:fixed_assets,id',
            'to_branch_id'   => 'nullable|exists:branches,id',
            'to_warehouse_id'=> 'nullable|exists:warehouses,id',
            'to_user_id'     => 'nullable|exists:users,id',
            'reason'         => 'nullable|string|max:500',
            'notes'          => 'nullable|string',
            'transferred_at' => 'required|date',
        ];
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $asset = FixedAsset::findOrFail($this->asset_id);

            $folio = AssetTransfer::generateFolio(auth()->user()->company_id);

            AssetTransfer::create([
                'company_id'       => auth()->user()->company_id,
                'asset_id'         => $asset->id,
                'from_branch_id'   => $asset->branch_id,
                'to_branch_id'     => $this->to_branch_id,
                'from_warehouse_id'=> $asset->warehouse_id,
                'to_warehouse_id'  => $this->to_warehouse_id,
                'from_user_id'     => $asset->assigned_to,
                'to_user_id'       => $this->to_user_id,
                'requested_by'     => auth()->id(),
                'folio'            => $folio,
                'status'           => 'completed',
                'reason'           => $this->reason ?: null,
                'notes'            => $this->notes ?: null,
                'transferred_at'   => $this->transferred_at,
            ]);

            // Update asset location and assignment
            $asset->update([
                'branch_id'   => $this->to_branch_id,
                'warehouse_id'=> $this->to_warehouse_id,
                'assigned_to' => $this->to_user_id,
                'status'      => 'active',
            ]);
        });

        $asset = FixedAsset::with(['branch'])->find($this->asset_id);
        $toBranch = $this->to_branch_id ? \App\Models\Branch::find($this->to_branch_id) : null;
        $toUserName = $this->to_user_id ? User::find($this->to_user_id)?->name : null;

        $fromDesc = $asset->branch?->name ?? 'sin sucursal';
        $toDesc   = $toBranch?->name ?? 'sin sucursal';
        $toDesc  .= $toUserName ? " ({$toUserName})" : '';

        $notification = new AssetStatusChangedNotification(
            title: 'Activo transferido',
            message: "{$asset->folio}: {$asset->name} transferido de {$fromDesc} a {$toDesc}.",
            type: 'transferred',
            assetId: $asset->id,
        );

        User::where('company_id', auth()->user()->company_id)
            ->where('id', '!=', auth()->id())
            ->get()
            ->filter(fn($u) => $u->can('view assets'))
            ->each(fn($u) => $u->notify($notification));

        session()->flash('success', 'Transferencia de activo registrada correctamente.');
        $this->redirect(route('assets.transfers.index'), navigate: true);
    }

    public function render()
    {
        $branches   = Branch::where('company_id', auth()->user()->company_id)->orderBy('name')->get();
        $warehouses = $this->to_branch_id
            ? Warehouse::where('branch_id', $this->to_branch_id)->where('is_active', true)->orderBy('name')->get()
            : collect();
        $users = User::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        return view('livewire.assets.asset-transfer-form', compact('branches', 'warehouses', 'users'));
    }
}

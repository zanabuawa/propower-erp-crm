<?php

namespace App\Livewire\Inventory;

use App\Models\Branch;
use App\Models\Warehouse;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class WarehouseForm extends Component
{
    public ?Warehouse $warehouse = null;
    public ?int $branch_id = null;
    public string $name = '';
    public string $code = '';
    public string $location = '';
    public bool $is_active    = true;
    public bool $is_defective = false;

    public function mount($warehouse = null): void
    {
        if ($warehouse) {
            $this->warehouse    = $warehouse instanceof Warehouse ? $warehouse : Warehouse::findOrFail($warehouse);
            $this->branch_id    = $this->warehouse->branch_id;
            $this->name         = $this->warehouse->name;
            $this->code         = $this->warehouse->code ?? '';
            $this->location     = $this->warehouse->location ?? '';
            $this->is_active    = $this->warehouse->is_active;
            $this->is_defective = $this->warehouse->is_defective;
        }
    }

    public function rules(): array
    {
        return [
            'branch_id'    => 'required|exists:branches,id',
            'name'         => 'required|string|max:255',
            'code'         => 'nullable|string|max:20',
            'location'     => 'nullable|string|max:255',
            'is_active'    => 'boolean',
            'is_defective' => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'company_id'   => auth()->user()->company_id,
            'branch_id'    => $this->branch_id,
            'name'         => $this->name,
            'code'         => $this->code,
            'location'     => $this->location,
            'is_active'    => $this->is_active,
            'is_defective' => $this->is_defective,
        ];

        if ($this->warehouse?->exists) {
            $this->warehouse->update($data);
            session()->flash('success', 'Almacén actualizado correctamente.');
        } else {
            Warehouse::create($data);
            session()->flash('success', 'Almacén creado correctamente.');
        }

        $this->redirect(route('inventory.warehouses.index'));
    }

    public function render()
    {
        return view('livewire.inventory.warehouse-form', [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
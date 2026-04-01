<?php

namespace App\Livewire\Inventory;

use App\Models\UnitOfMeasure;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class UnitForm extends Component
{
    public ?UnitOfMeasure $unitOfMeasure = null;
    public string $name = '';
    public string $abbreviation = '';
    public bool $is_active = true;

    public function mount($unitOfMeasure = null): void
    {
        if ($unitOfMeasure) {
            $this->unitOfMeasure  = $unitOfMeasure instanceof UnitOfMeasure ? $unitOfMeasure : UnitOfMeasure::findOrFail($unitOfMeasure);
            $this->name           = $this->unitOfMeasure->name;
            $this->abbreviation   = $this->unitOfMeasure->abbreviation;
            $this->is_active      = $this->unitOfMeasure->is_active;
        }
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'abbreviation' => 'required|string|max:10',
            'is_active'    => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'company_id'   => auth()->user()->company_id,
            'name'         => $this->name,
            'abbreviation' => $this->abbreviation,
            'is_active'    => $this->is_active,
        ];

        if ($this->unitOfMeasure?->exists) {
            $this->unitOfMeasure->update($data);
            session()->flash('success', 'Unidad actualizada correctamente.');
        } else {
            UnitOfMeasure::create($data);
            session()->flash('success', 'Unidad creada correctamente.');
        }

        $this->redirect(route('inventory.units.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.inventory.unit-form');
    }
}
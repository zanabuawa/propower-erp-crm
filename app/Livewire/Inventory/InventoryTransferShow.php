<?php

namespace App\Livewire\Inventory;

use App\Models\StockMovement;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class InventoryTransferShow extends Component
{
    public StockMovement $stockMovement;

    public function mount(StockMovement $stockMovement): void
    {
        $this->stockMovement = $stockMovement->load([
            'warehouse.branch',
            'warehouseDestination.branch',
            'user',
            'dispatchedBy',
            'items.product',
            'events.user',
        ]);
    }

    public function render()
    {
        return view('livewire.inventory.inventory-transfer-show');
    }
}

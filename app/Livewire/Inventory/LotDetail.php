<?php

namespace App\Livewire\Inventory;

use App\Models\ProductLot;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class LotDetail extends Component
{
    public ProductLot $lot;

    public function mount(ProductLot $lot): void
    {
        if (! $lot->barcode) {
            $lot->update([
                'barcode' => ProductLot::generateBarcode($lot->company_id, $lot->product_id),
            ]);
        }

        $this->lot = $lot->load([
            'product',
            'warehouse',
            'movementItems.movement',
            'deliveryItems.delivery.order',
        ]);
    }

    public function render()
    {
        return view('livewire.inventory.lot-detail');
    }
}

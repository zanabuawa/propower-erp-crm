<?php

namespace App\Livewire\Sales;

use App\Models\SaleOrder;
use App\Models\Stock;
use App\Models\Warehouse;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class OrderShow extends Component
{
    public SaleOrder $order;
    public string $activeTab = 'items';

    public function mount($order): void
    {
        $this->order = $order instanceof SaleOrder
            ? $order
            : SaleOrder::with([
                'items.product',
                'customer',
                'createdBy',
                'deliveries.items.product',
                'invoice',
                'quotation',
            ])->findOrFail($order);
    }

    public function cancel(): void
    {
        // Liberar stock comprometido
        $branchId = $this->order->branch_id ?? auth()->user()->branch_id;
        $warehouseId = $branchId
            ? Warehouse::where('branch_id', $branchId)->where('is_active', true)
                ->where('is_transit', false)->where('is_defective', false)->first()?->id
            : null;

        if ($warehouseId) {
            foreach ($this->order->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $warehouseId)->first();
                $stock?->release((float) $item->quantity);
            }
        }

        $this->order->update(['status' => 'cancelled']);
        $this->order->refresh();
        session()->flash('success', 'Orden cancelada.');
    }

    public function render()
    {
        return view('livewire.sales.order-show');
    }
}
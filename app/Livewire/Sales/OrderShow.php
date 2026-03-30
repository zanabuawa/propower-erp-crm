<?php

namespace App\Livewire\Sales;

use App\Models\SaleOrder;
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
        $this->order->update(['status' => 'cancelled']);
        $this->order->refresh();
        session()->flash('success', 'Orden cancelada.');
    }

    public function render()
    {
        return view('livewire.sales.order-show');
    }
}
<?php

namespace App\Livewire\Purchases;

use App\Models\PurchaseOrder;
use App\Models\PurchaseReceipt;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Notifications\PurchaseNotification;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class OrderShow extends Component
{
    public PurchaseOrder $order;
    public string $activeTab = 'items';

    public function mount($order): void
    {
        $this->order = $order instanceof PurchaseOrder
            ? $order
            : PurchaseOrder::with([
                'items.product',
                'supplier',
                'branch',
                'createdBy',
                'receipts.items.product',
                'receipts.items.warehouse',
                'receipts.receivedBy',
                'invoice',
                'requisition',
                'supplierBankAccount',
            ])->findOrFail($order);
    }

    public function markAsSent(): void
    {
        $this->order->update(['status' => 'sent']);
        $this->order->refresh();
        session()->flash('success', 'Orden marcada como enviada al proveedor.');
    }

    public function markAsWaitingDelivery(): void
    {
        $this->order->update(['status' => 'waiting_delivery']);
        $this->order->refresh();
        session()->flash('success', 'Orden marcada como esperando mercancía.');
    }

    public function cancel(): void
    {
        $this->order->update(['status' => 'cancelled']);
        $this->order->refresh();
        session()->flash('success', 'Orden cancelada.');
    }

    public function render()
    {
        return view('livewire.purchases.order-show');
    }
}
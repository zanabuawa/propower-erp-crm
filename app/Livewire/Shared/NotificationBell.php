<?php

namespace App\Livewire\Shared;

use Livewire\Component;

class NotificationBell extends Component
{
    public int  $count = 0;
    public bool $open  = false;

    public function mount(): void
    {
        $this->refreshCount();
    }

    public function refreshCount(): void
    {
        $this->count = auth()->user()->unreadNotifications()->count();
    }

    public function toggle(): void
    {
        $this->open = !$this->open;
        if ($this->open) {
            $this->refreshCount();
        }
    }

    public function markAsRead(string $id): void
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();
        if (!$notification) return;

        $notification->update(['read_at' => now()]);
        $this->refreshCount();

        $data = $notification->data;
        if (!empty($data['requisition_id'])) {
            $this->redirect(route('purchases.requisitions.show', $data['requisition_id']), navigate: true);
        } elseif (!empty($data['order_id'])) {
            $this->redirect(route('purchases.orders.show', $data['order_id']), navigate: true);
        } elseif (!empty($data['transfer_id'])) {
            $this->redirect(route('inventory.transfers.show', $data['transfer_id']), navigate: true);
        } elseif (!empty($data['asset_id'])) {
            $this->redirect(route('assets.edit', $data['asset_id']), navigate: true);
        } elseif (!empty($data['product_id'])) {
            $this->redirect(route('inventory.products.edit', $data['product_id']), navigate: true);
        } elseif (!empty($data['customer_id'])) {
            $this->redirect(route('contacts.show', $data['customer_id']), navigate: true);
        } elseif (!empty($data['role_name'])) {
            $this->redirect(route('users.index'), navigate: true);
        } elseif (!empty($data['approval_id'])) {
            $this->redirect(route('sales.discount-approvals.index'), navigate: true);
        }
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->count = 0;
    }

    public function render()
    {
        return view('livewire.shared.notification-bell', [
            'notifications' => $this->open
                ? auth()->user()->notifications()->latest()->take(15)->get()
                : collect(),
        ]);
    }
}

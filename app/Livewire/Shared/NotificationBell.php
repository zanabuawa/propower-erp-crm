<?php

namespace App\Livewire\Shared;

use Livewire\Component;
use Livewire\Attributes\On;

class NotificationBell extends Component
{
    public int  $count  = 0;
    public bool $open   = false;
    public int  $userId = 0;

    public function mount(): void
    {
        $this->userId = auth()->id();
        $this->refreshCount();
    }

    public function refreshCount(): void
    {
        $this->count = auth()->user()->unreadNotifications()->count();
    }

    // Escucha el evento de broadcast en el canal privado del usuario
    #[On('echo-private:App.Models.User.{userId},Illuminate\\Notifications\\Events\\BroadcastNotificationCreated')]
    public function handleNewNotification(): void
    {
        $this->refreshCount();
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

<?php

namespace App\Livewire\Shared;

use Livewire\Component;
use Livewire\Attributes\Layout;

class NotificationBell extends Component
{
    public int $count = 0;
    public bool $open = false;

    public function mount(): void
    {
        $this->count = auth()->user()->unreadNotifications()->count();
    }

    public function toggle(): void
    {
        $this->open = !$this->open;
        if ($this->open) {
            $this->count = auth()->user()->unreadNotifications()->count();
        }
    }

    public function markAsRead(string $id): void
    {
        auth()->user()->notifications()->where('id', $id)->update(['read_at' => now()]);
        $this->count = auth()->user()->unreadNotifications()->count();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->count = 0;
    }

    public function render()
    {
        return view('livewire.shared.notification-bell', [
            'notifications' => auth()->user()->notifications()->latest()->take(10)->get(),
        ]);
    }
}
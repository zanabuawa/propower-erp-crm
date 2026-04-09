<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class IncompleteProductNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public int    $productId,
        public string $productName,
        public string $productType,   // 'product' | 'service'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'        => $this->title,
            'message'      => $this->message,
            'type'         => 'incomplete_product',
            'product_id'   => $this->productId,
            'product_name' => $this->productName,
            'product_type' => $this->productType,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}

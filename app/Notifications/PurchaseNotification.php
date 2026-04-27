<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PurchaseNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $type,
        public ?int $requisitionId = null,
        public ?int $orderId = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'          => $this->title,
            'message'        => $this->message,
            'type'           => $this->type,
            'requisition_id' => $this->requisitionId,
            'order_id'       => $this->orderId,
        ];
    }
}
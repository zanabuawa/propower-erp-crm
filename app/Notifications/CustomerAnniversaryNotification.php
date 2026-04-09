<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CustomerAnniversaryNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public int    $customerId,
        public string $customerName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'         => $this->title,
            'message'       => $this->message,
            'type'          => 'customer_anniversary',
            'customer_id'   => $this->customerId,
            'customer_name' => $this->customerName,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class InventoryTransferNotification extends Notification
{
    use Queueable;

    /**
     * @param string $title
     * @param string $message
     * @param string $type       created | dispatched_complete | dispatched_partial | rejected | in_transit | partial | completed | cancelled
     * @param int    $transferId
     * @param array  $extraData  Optional structured data (items summary, dispatch_notes, etc.)
     */
    public function __construct(
        public string $title,
        public string $message,
        public string $type,
        public int    $transferId,
        public array  $extraData = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'title'       => $this->title,
            'message'     => $this->message,
            'type'        => $this->type,
            'transfer_id' => $this->transferId,
        ], $this->extraData);
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}

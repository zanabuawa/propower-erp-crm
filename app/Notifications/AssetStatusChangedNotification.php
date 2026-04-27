<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssetStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $type,      // transferred | status_changed | created
        public int $assetId,
        public ?int $transferId = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'       => $this->title,
            'message'     => $this->message,
            'type'        => $this->type,
            'asset_id'    => $this->assetId,
            'transfer_id' => $this->transferId,
        ];
    }
}

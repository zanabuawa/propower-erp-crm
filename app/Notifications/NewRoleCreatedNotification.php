<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewRoleCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $roleName,
        public string $employeeName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'         => 'Nuevo rol sin permisos',
            'message'       => "Se creó el rol \"{$this->roleName}\" al registrar a {$this->employeeName}. Asigna los permisos correspondientes.",
            'type'          => 'new_role',
            'role_name'     => $this->roleName,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}

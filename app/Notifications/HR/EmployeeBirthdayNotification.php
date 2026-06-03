<?php

namespace App\Notifications\HR;

use App\Models\HrEmployee;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeBirthdayNotification extends Notification
{
    use Queueable;

    public function __construct(
        public HrEmployee $employee,
        public int $daysRemaining = 0
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match($this->daysRemaining) {
            0 => '¡Hoy es el cumpleaños de!: ' . $this->employee->full_name,
            1 => 'Próximo Cumpleaños (Mañana): ' . $this->employee->full_name,
            3 => 'Próximo Cumpleaños (3 días): ' . $this->employee->full_name,
            default => 'Recordatorio de Cumpleaños: ' . $this->employee->full_name,
        };

        $message = match($this->daysRemaining) {
            0 => "Hoy es el cumpleaños de {$this->employee->full_name}.",
            1 => "Mañana será el cumpleaños de {$this->employee->full_name}.",
            3 => "En 3 días será el cumpleaños de {$this->employee->full_name}.",
            default => "Se acerca el cumpleaños de {$this->employee->full_name}.",
        };

        return (new MailMessage)
            ->subject($subject)
            ->line($message)
            ->line('Puesto: ' . ($this->employee->position?->name ?? 'N/A'))
            ->line('Departamento: ' . ($this->employee->department?->name ?? 'N/A'))
            ->action('Ver Perfil del Empleado', route('hr.employees.show', $this->employee))
            ->line('¡No olvides felicitarle!');
    }

    public function toArray(object $notifiable): array
    {
        $title = match($this->daysRemaining) {
            0 => 'Cumpleaños hoy',
            1 => 'Cumpleaños mañana',
            3 => 'Cumpleaños en 3 días',
            default => 'Recordatorio de cumpleaños',
        };

        $message = match($this->daysRemaining) {
            0 => "Hoy es el cumpleaños de {$this->employee->full_name}.",
            1 => "Mañana será el cumpleaños de {$this->employee->full_name}.",
            3 => "En 3 días será el cumpleaños de {$this->employee->full_name}.",
            default => "Se acerca el cumpleaños de {$this->employee->full_name}.",
        };

        return [
            'employee_id' => $this->employee->id,
            'title'       => $title,
            'message'     => $message,
            'type'        => 'birthday',
            'days_remaining' => $this->daysRemaining,
            'url'         => route('hr.employees.show', $this->employee),
        ];
    }
}

<?php

namespace App\Notifications\HR;

use App\Models\HrProspect;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public HrProspect $prospect,
        public string $timeframe // '24h' or '1h'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $timeStr = $this->timeframe === '24h' ? 'mañana' : 'en 1 hora';
        
        return (new MailMessage)
            ->subject('Recordatorio de Entrevista: ' . $this->prospect->full_name)
            ->line('Tienes una entrevista agendada con ' . $this->prospect->full_name . ' para ' . $timeStr . '.')
            ->line('Fecha y Hora: ' . $this->prospect->interview_date->format('d/m/Y H:i'))
            ->line('Tipo: ' . $this->prospect->interview_type_label)
            ->action('Ver Prospecto', route('hr.prospects.edit', $this->prospect))
            ->line('¡Que tengas una excelente entrevista!');
    }

    public function toArray(object $notifiable): array
    {
        $timeStr = $this->timeframe === '24h' ? '24 horas' : '1 hora';
        return [
            'prospect_id' => $this->prospect->id,
            'title'       => 'Recordatorio de entrevista',
            'message'     => "Entrevista con {$this->prospect->full_name} en {$timeStr}.",
            'date'        => $this->prospect->interview_date->format('Y-m-d H:i:s'),
            'url'         => route('hr.prospects.edit', $this->prospect),
        ];
    }
}

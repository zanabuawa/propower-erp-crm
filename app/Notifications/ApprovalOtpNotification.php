<?php

namespace App\Notifications;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $code = '',
        public string $context = '',
    ) {
        if (empty($this->context)) {
            $this->context = $this->title;
        }
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $company = Company::first();

        if ($this->code) {
            return (new MailMessage)->view('mail.approval-otp', [
                'user'    => $notifiable,
                'code'    => $this->code,
                'context' => $this->context,
                'company' => $company,
            ])->subject($this->title);
        }

        return (new MailMessage)
            ->subject($this->title)
            ->line($this->message)
            ->line('El código expira en 10 minutos.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => $this->title,
            'message' => $this->message,
            'type'    => 'approval_otp',
        ];
    }
}

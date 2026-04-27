<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Company;

class CustomResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $company = Company::first() ?? new \stdClass();

        return (new MailMessage)
            ->subject('Restablecer Contraseña — ' . config('app.name'))
            ->view('mail.auth.reset-password', [
                'url' => $url,
                'company' => $company,
                'user' => $notifiable
            ]);
    }
}

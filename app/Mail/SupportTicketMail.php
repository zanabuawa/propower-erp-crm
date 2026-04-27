<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportTicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Company $company,
        public string $userMessage
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de Ticket de Soporte — ' . $this->company->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.support-notification',
        );
    }
}

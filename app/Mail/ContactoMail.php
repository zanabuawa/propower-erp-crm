<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactoMail extends Mailable
{
    use Queueable, SerializesModels;

    public Company $company;

    public function __construct(public array $data)
    {
        $this->company = Company::first() ?? new Company();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nuevo contacto web: {$this->data['nombre']}",
            replyTo: [new \Illuminate\Mail\Mailables\Address($this->data['correo'], $this->data['nombre'])],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.contacto');
    }
}

<?php

namespace App\Notifications;

use App\Models\SaleInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public SaleInvoice $invoice,
        public string      $recipientName = '',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $invoice  = $this->invoice;
        $customer = $invoice->customer;
        $balance  = number_format($invoice->total - $invoice->paid_amount, 2);
        $dueDate  = $invoice->due_at?->format('d/m/Y') ?? '—';
        $isOverdue = $invoice->due_at && $invoice->due_at->isPast();

        $subject = $isOverdue
            ? "Recordatorio de pago vencido — Factura {$invoice->folio}"
            : "Recordatorio de pago próximo — Factura {$invoice->folio}";

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting("Estimado/a {$customer->name},")
            ->line($isOverdue
                ? "Le informamos que la siguiente factura se encuentra **vencida** y pendiente de pago:"
                : "Le recordamos que la siguiente factura está próxima a vencer:")
            ->line("**Folio:** {$invoice->folio}")
            ->line("**Fecha de vencimiento:** {$dueDate}")
            ->line("**Saldo pendiente:** {$invoice->currency} \${$balance}");

        if ($invoice->cfdi_uuid) {
            $message->line("**UUID CFDI:** {$invoice->cfdi_uuid}");
        }

        $message
            ->line($isOverdue
                ? 'Le pedimos regularizar su pago a la brevedad posible.'
                : 'Le pedimos realizar el pago antes de la fecha de vencimiento.')
            ->line('Si ya realizó el pago, por favor omita este mensaje.')
            ->salutation('Atentamente, el equipo de cobranza.');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        $invoice  = $this->invoice;
        $balance  = $invoice->total - $invoice->paid_amount;
        $isOverdue = $invoice->due_at && $invoice->due_at->isPast();

        return [
            'type'          => 'payment_reminder',
            'title'         => $isOverdue ? 'Recordatorio enviado: factura vencida' : 'Recordatorio enviado: próximo vencimiento',
            'message'       => "Se envió recordatorio de pago para {$invoice->customer->name} — Factura {$invoice->folio} (saldo: \${$balance})",
            'invoice_id'    => $invoice->id,
            'invoice_folio' => $invoice->folio,
            'customer_name' => $invoice->customer->name,
            'balance'       => $balance,
            'url'           => route('sales.invoices.show', $invoice),
        ];
    }
}

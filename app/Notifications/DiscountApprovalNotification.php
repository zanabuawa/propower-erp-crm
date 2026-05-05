<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DiscountApprovalNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string  $type,       // 'requested' | 'approved' | 'rejected'
        public string  $folio,
        public float   $requestedPct,
        public float   $maxAllowedPct,
        public ?string $notes,
        public ?int    $approvalId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return match ($this->type) {
            'requested' => [
                'title'        => 'Solicitud de descuento especial',
                'message'      => "El documento {$this->folio} solicita un descuento del " .
                                  number_format($this->requestedPct, 1) . "% " .
                                  "(máx. permitido sin autorización: " . number_format($this->maxAllowedPct, 1) . "%).",
                'type'         => 'discount_requested',
                'approval_id'  => $this->approvalId,
            ],
            'approved' => [
                'title'        => 'Descuento autorizado',
                'message'      => "Tu descuento del " . number_format($this->requestedPct, 1) .
                                  "% en {$this->folio} fue autorizado." .
                                  ($this->notes ? " Nota: {$this->notes}" : ''),
                'type'         => 'discount_approved',
                'approval_id'  => $this->approvalId,
            ],
            'rejected' => [
                'title'        => 'Descuento rechazado',
                'message'      => "Tu descuento del " . number_format($this->requestedPct, 1) .
                                  "% en {$this->folio} fue rechazado." .
                                  ($this->notes ? " Motivo: {$this->notes}" : ''),
                'type'         => 'discount_rejected',
                'approval_id'  => $this->approvalId,
            ],
            default => [],
        };
    }
}

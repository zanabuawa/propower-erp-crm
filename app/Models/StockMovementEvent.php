<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovementEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'stock_movement_id', 'user_id', 'action', 'notes', 'data',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'data'       => 'array',
    ];

    public const LABELS = [
        'created'           => 'Solicitud creada',
        'accepted_complete' => 'Aceptada completamente',
        'accepted_partial'  => 'Aceptada parcialmente',
        'rejected'          => 'Rechazada',
        'sent'              => 'Mercancía enviada',
        'received_partial'  => 'Recepción parcial registrada',
        'received_complete' => 'Recepción completada',
        'cancelled'         => 'Cancelada',
    ];

    public const COLORS = [
        'created'           => 'blue',
        'accepted_complete' => 'indigo',
        'accepted_partial'  => 'amber',
        'rejected'          => 'red',
        'sent'              => 'indigo',
        'received_partial'  => 'orange',
        'received_complete' => 'emerald',
        'cancelled'         => 'red',
    ];

    public function movement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class, 'stock_movement_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

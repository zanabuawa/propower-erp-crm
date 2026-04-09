<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockMovement extends Model
{
    protected $fillable = [
        'company_id', 'warehouse_id', 'warehouse_destination_id',
        'user_id', 'type', 'folio', 'status', 'reference', 'notes', 'moved_at',
    ];

    protected $casts = [
        'moved_at' => 'datetime',
    ];

    public const TYPES = [
        'entry'        => 'Entrada',
        'exit'         => 'Salida',
        'adjustment'   => 'Ajuste',
        'transfer'     => 'Transferencia',
        'return'       => 'Devolución',
    ];

    public const STATUS = [
        'draft'               => 'Borrador',
        'confirmed'           => 'Confirmado',
        'cancelled'           => 'Cancelado',
        // Transfer-specific statuses
        'requested'           => 'Solicitada',
        'in_transit'          => 'En tránsito',
        'partially_received'  => 'Recepción parcial',
        'completed'           => 'Completada',
    ];

    public const TRANSFER_STATUSES = [
        'requested'          => 'Solicitada',
        'in_transit'         => 'En tránsito',
        'partially_received' => 'Recepción parcial',
        'completed'          => 'Completada',
        'cancelled'          => 'Cancelada',
    ];

    public function isTransfer(): bool
    {
        return $this->type === 'transfer';
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['requested', 'in_transit', 'partially_received']);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function warehouseDestination(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_destination_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockMovementItem::class);
    }
}